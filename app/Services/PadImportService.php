<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PadImportService
{
    protected $headerAliases = [
        'akun' => ['akun', 'namaakun', 'nama_akun', 'jenisakun', 'uraian', 'jenis'],
        'anggaran' => ['anggaran', 'target', 'pagu', 'anggaranpad'],
        'realisasi' => ['realisasi', 'realisasipad', 'actual', 'aktual'],
        'tahun' => ['tahun', 'thn', 'tahunanggaran'],
    ];

    public function getKotaOptions()
    {
        return DB::table('peta')
            ->select('ogc_fid', 'kabupaten')
            ->whereNotNull('kabupaten')
            ->orderBy('kabupaten')
            ->get();
    }

    public function import(UploadedFile $file, int $kotaId)
    {
        $kotaName = DB::table('peta')
            ->where('ogc_fid', $kotaId)
            ->value('kabupaten');

        if (empty($kotaName)) {
            throw new InvalidArgumentException('Kota/kabupaten yang dipilih tidak valid.');
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            throw new InvalidArgumentException('File Excel kosong dan tidak bisa diimport.');
        }

        $headerRow = array_shift($rows);
        $headerMap = $this->resolveHeaderMap($headerRow);

        $requiredHeaders = ['akun', 'anggaran', 'realisasi', 'tahun'];
        $missingHeaders = array_values(array_filter($requiredHeaders, function ($key) use ($headerMap) {
            return !array_key_exists($key, $headerMap);
        }));

        if (!empty($missingHeaders)) {
            throw new InvalidArgumentException(
                'Kolom wajib tidak ditemukan: ' . implode(', ', $missingHeaders) . '.'
            );
        }

        $insertRows = [];
        $tahunImported = [];

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $parsedRow = $this->parseRow($row, $headerMap, $kotaId, $excelRow);
            $insertRows[] = $parsedRow;
            $tahunImported[$parsedRow['tahun']] = true;
        }

        if (empty($insertRows)) {
            throw new InvalidArgumentException('Tidak ada data yang bisa diimport dari file tersebut.');
        }

        DB::transaction(function () use ($insertRows) {
            DB::table('tabel_pad')->insert($insertRows);
        });

        Cache::forget('peta.filter_options');

        return [
            'inserted' => count($insertRows),
            'kota_id' => $kotaId,
            'kota_name' => $kotaName,
            'tahun' => array_keys($tahunImported),
        ];
    }

    protected function parseRow(array $row, array $headerMap, int $kotaId, int $excelRow)
    {
        $akun = trim((string) $this->valueAt($row, $headerMap['akun']));
        if ($akun === '') {
            throw new InvalidArgumentException('Baris ' . $excelRow . ': kolom akun wajib diisi.');
        }

        $anggaran = $this->parseNumber(
            $this->valueAt($row, $headerMap['anggaran']),
            'anggaran',
            $excelRow
        );
        $realisasi = $this->parseNumber(
            $this->valueAt($row, $headerMap['realisasi']),
            'realisasi',
            $excelRow
        );
        $tahun = $this->parseYear(
            $this->valueAt($row, $headerMap['tahun']),
            $excelRow
        );

        $persentase = $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0;

        return [
            'akun' => $akun,
            'anggaran' => $anggaran,
            'realisasi' => $realisasi,
            'persentase' => $persentase,
            'kota' => $kotaId,
            'tahun' => $tahun,
        ];
    }

    protected function resolveHeaderMap(array $headerRow)
    {
        $headerMap = [];

        foreach ($headerRow as $index => $headerValue) {
            $normalizedHeader = $this->normalizeHeader($headerValue);

            if ($normalizedHeader === '') {
                continue;
            }

            foreach ($this->headerAliases as $field => $aliases) {
                if (in_array($normalizedHeader, $aliases, true) && !array_key_exists($field, $headerMap)) {
                    $headerMap[$field] = $index;
                    break;
                }
            }
        }

        return $headerMap;
    }

    protected function normalizeHeader($value)
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', '', $value);

        return $value;
    }

    protected function isEmptyRow(array $row)
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function valueAt(array $row, int $index)
    {
        return array_key_exists($index, $row) ? $row[$index] : null;
    }

    protected function parseNumber($value, string $label, int $excelRow, bool $allowBlank = false)
    {
        if ($value === null || trim((string) $value) === '') {
            if ($allowBlank) {
                return null;
            }

            throw new InvalidArgumentException('Baris ' . $excelRow . ': kolom ' . $label . ' wajib diisi.');
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $normalized = preg_replace('/\s+/u', '', (string) $value);
        $negative = false;

        if (preg_match('/^\((.*)\)$/', $normalized, $matches)) {
            $negative = true;
            $normalized = $matches[1];
        }

        $normalized = str_replace('%', '', $normalized);
        $normalized = preg_replace('/[^0-9,.\-]/', '', $normalized);

        if ($normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
            if ($allowBlank) {
                return null;
            }

            throw new InvalidArgumentException('Baris ' . $excelRow . ': kolom ' . $label . ' bukan angka yang valid.');
        }

        $hasComma = strpos($normalized, ',') !== false;
        $hasDot = strpos($normalized, '.') !== false;

        if ($hasComma && $hasDot) {
            if (strrpos($normalized, ',') > strrpos($normalized, '.')) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma) {
            $normalized = $this->normalizeSingleSeparatorNumber($normalized, ',');
        } elseif ($hasDot) {
            $normalized = $this->normalizeSingleSeparatorNumber($normalized, '.');
        }

        if (!is_numeric($normalized)) {
            throw new InvalidArgumentException('Baris ' . $excelRow . ': kolom ' . $label . ' bukan angka yang valid.');
        }

        $number = (float) $normalized;

        if ($negative) {
            $number *= -1;
        }

        return round($number, 2);
    }

    protected function normalizeSingleSeparatorNumber(string $value, string $separator)
    {
        $parts = explode($separator, $value);

        if (count($parts) === 2) {
            $decimalPart = $parts[1];

            if (strlen($decimalPart) <= 2) {
                return implode('.', $parts);
            }
        }

        return str_replace($separator, '', $value);
    }

    protected function parseYear($value, int $excelRow)
    {
        if ($value === null || trim((string) $value) === '') {
            throw new InvalidArgumentException('Baris ' . $excelRow . ': kolom tahun wajib diisi.');
        }

        if (is_numeric($value)) {
            $number = (float) $value;

            if ($number >= 1900 && $number <= 2100) {
                return (int) round($number);
            }

            if ($number > 30000) {
                return (int) ExcelDate::excelToDateTimeObject($number)->format('Y');
            }
        }

        if (preg_match('/(19|20)\d{2}/', (string) $value, $matches)) {
            return (int) $matches[0];
        }

        throw new InvalidArgumentException('Baris ' . $excelRow . ': kolom tahun tidak valid.');
    }
}

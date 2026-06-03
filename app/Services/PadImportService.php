<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PadImportService
{
    protected $headerAliases = [
        'akun' => ['akun', 'namaakun', 'nama_akun', 'jenisakun', 'uraian', 'jenis'],
        'anggaran' => ['anggaran', 'target', 'pagu', 'anggaranpad'],
        'realisasi' => ['realisasi', 'realisasipad', 'actual', 'aktual'],
    ];

    public function getKotaOptions()
    {
        return DB::table('peta')
            ->select('ogc_fid', 'kabupaten')
            ->whereNotNull('kabupaten')
            ->orderBy('kabupaten')
            ->get();
    }

    public function importBatch(array $uploads, int $kotaId)
    {
        $kotaName = DB::table('peta')
            ->where('ogc_fid', $kotaId)
            ->value('kabupaten');

        if (empty($kotaName)) {
            throw new InvalidArgumentException('Kota/kabupaten yang dipilih tidak valid.');
        }

        $insertRows = [];
        $tahunImported = [];
        $fileSummaries = [];

        foreach ($uploads as $index => $upload) {
            $file = $upload['file'];
            $tahun = (int) $upload['tahun'];
            $fileRows = $this->extractRowsFromFile($file, $kotaId, $tahun, $index + 1);

            foreach ($fileRows as $row) {
                $insertRows[] = $row;
                $tahunImported[$row['tahun']] = true;
            }

            $fileSummaries[] = [
                'slot' => $index + 1,
                'tahun' => $tahun,
                'inserted' => count($fileRows),
                'filename' => $file->getClientOriginalName(),
            ];
        }

        if (count(array_unique(array_column($fileSummaries, 'tahun'))) !== count($fileSummaries)) {
            throw new InvalidArgumentException('Tahun upload tidak boleh duplikat. Pilih 5 tahun yang berbeda.');
        }

        if (empty($insertRows)) {
            throw new InvalidArgumentException('Tidak ada data yang bisa diimport dari file tersebut.');
        }

        DB::transaction(function () use ($insertRows, $kotaId) {
            DB::table('tabel_pad')
                ->where('kota', $kotaId)
                ->delete();

            DB::table('tabel_pad')->insert($insertRows);
        });

        Cache::forget('peta.filter_options');

        return [
            'inserted' => count($insertRows),
            'kota_id' => $kotaId,
            'kota_name' => $kotaName,
            'tahun' => array_keys($tahunImported),
            'files' => $fileSummaries,
        ];
    }

    protected function extractRowsFromFile($file, int $kotaId, int $tahun, int $slotNumber)
    {
        $this->validateFileExtension($file, $slotNumber);

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (SpreadsheetException $exception) {
            throw new InvalidArgumentException(
                'Upload file ke-' . $slotNumber . ' tidak bisa dibaca sebagai file Excel. Gunakan file .xls, .xlsx, atau .csv yang valid.'
            );
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException(
                'Upload file ke-' . $slotNumber . ' gagal diproses. Pastikan file tidak rusak dan benar-benar file Excel 97-2003 (.xls) atau Excel biasa.'
            );
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            throw new InvalidArgumentException('Upload file ke-' . $slotNumber . ' kosong dan tidak bisa diimport.');
        }

        $headerRow = array_shift($rows);
        $headerMap = $this->resolveHeaderMap($headerRow);

        $requiredHeaders = ['akun', 'anggaran', 'realisasi'];
        $missingHeaders = array_values(array_filter($requiredHeaders, function ($key) use ($headerMap) {
            return !array_key_exists($key, $headerMap);
        }));

        if (!empty($missingHeaders)) {
            throw new InvalidArgumentException(
                'Upload file ke-' . $slotNumber . ': kolom wajib tidak ditemukan: ' . implode(', ', $missingHeaders) . '.'
            );
        }

        $insertRows = [];

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $insertRows[] = $this->parseRow($row, $headerMap, $kotaId, $excelRow, $tahun, $slotNumber);
        }

        if (empty($insertRows)) {
            throw new InvalidArgumentException('Upload file ke-' . $slotNumber . ' tidak memiliki baris data yang valid.');
        }

        return $insertRows;
    }

    protected function validateFileExtension($file, int $slotNumber)
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $allowedExtensions = ['xls', 'xlsx', 'csv'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new InvalidArgumentException(
                'Upload file ke-' . $slotNumber . ' harus memakai ekstensi .xls, .xlsx, atau .csv.'
            );
        }
    }

    protected function parseRow(array $row, array $headerMap, int $kotaId, int $excelRow, int $tahun, int $slotNumber)
    {
        $akun = trim((string) $this->valueAt($row, $headerMap['akun']));
        if ($akun === '') {
            throw new InvalidArgumentException('Upload file ke-' . $slotNumber . ', baris ' . $excelRow . ': kolom akun wajib diisi.');
        }

        $anggaran = $this->parseNumber(
            $this->valueAt($row, $headerMap['anggaran']),
            'anggaran',
            $excelRow,
            false,
            $slotNumber
        );
        $realisasi = $this->parseNumber(
            $this->valueAt($row, $headerMap['realisasi']),
            'realisasi',
            $excelRow,
            false,
            $slotNumber
        );

        $persentase = (float) $anggaran > 0
            ? round((((float) $realisasi) / ((float) $anggaran)) * 100, 2)
            : 0;

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

    protected function parseNumber($value, string $label, int $excelRow, bool $allowBlank = false, ?int $slotNumber = null)
    {
        $prefix = $slotNumber !== null
            ? 'Upload file ke-' . $slotNumber . ', baris ' . $excelRow . ': '
            : 'Baris ' . $excelRow . ': ';

        if ($value === null || trim((string) $value) === '') {
            if ($allowBlank) {
                return null;
            }

            throw new InvalidArgumentException($prefix . 'kolom ' . $label . ' wajib diisi.');
        }

        if (is_numeric($value)) {
            return $this->formatDatabaseNumber((float) $value);
        }

        $normalized = preg_replace('/\s+/u', '', (string) $value);
        $negative = false;

        if (preg_match('/^\((.*)\)$/', $normalized, $matches)) {
            $negative = true;
            $normalized = $matches[1];
        }

        $normalized = str_replace('%', '', $normalized);

        if ($this->looksLikeScientificNotation($normalized)) {
            $number = $this->parseScientificNotation($normalized, $label, $excelRow);

            if ($negative) {
                $number *= -1;
            }

            return $this->formatDatabaseNumber($number);
        }

        $normalized = preg_replace('/[^0-9,.\-]/', '', $normalized);

        if ($normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
            if ($allowBlank) {
                return null;
            }

            throw new InvalidArgumentException($prefix . 'kolom ' . $label . ' bukan angka yang valid.');
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
            throw new InvalidArgumentException($prefix . 'kolom ' . $label . ' bukan angka yang valid.');
        }

        $number = (float) $normalized;

        if ($negative) {
            $number *= -1;
        }

        return $this->formatDatabaseNumber($number);
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

    protected function looksLikeScientificNotation(string $value)
    {
        $candidate = str_replace(',', '.', $value);

        return preg_match('/^[+\-]?\d+(\.\d+)?[eE][+\-]?\d+$/', $candidate) === 1;
    }

    protected function parseScientificNotation(string $value, string $label, int $excelRow)
    {
        $candidate = str_replace(',', '.', $value);

        if (!is_numeric($candidate)) {
            throw new InvalidArgumentException('Baris ' . $excelRow . ': kolom ' . $label . ' bukan angka yang valid.');
        }

        return (float) $candidate;
    }

    protected function formatDatabaseNumber(float $number)
    {
        $formatted = rtrim(rtrim(sprintf('%.10F', $number), '0'), '.');

        if ($formatted === '' || $formatted === '-0') {
            return '0';
        }

        return $formatted;
    }
}

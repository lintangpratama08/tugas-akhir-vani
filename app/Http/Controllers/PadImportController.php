<?php

namespace App\Http\Controllers;

use App\Services\PadImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class PadImportController extends Controller
{
    protected $padImportService;

    public function __construct(PadImportService $padImportService)
    {
        $this->padImportService = $padImportService;
    }

    public function index()
    {
        return view('pad_import.index', [
            'kotaOptions' => $this->padImportService->getKotaOptions(),
            'tahunOptions' => $this->buildTahunOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kota' => ['required', 'integer', Rule::exists('peta', 'ogc_fid')],
            'uploads' => ['required', 'array', 'size:5'],
            'uploads.*.file' => ['required', 'file'],
        ], [
            'kota.required' => 'Kota/kabupaten wajib dipilih.',
            'kota.exists' => 'Kota/kabupaten yang dipilih tidak ditemukan.',
            'uploads.required' => 'Lima upload file wajib diisi.',
            'uploads.size' => 'Jumlah upload harus tepat 5 file.',
            'uploads.*.file.required' => 'File Excel pada setiap upload wajib dipilih.',
            'uploads.*.file.file' => 'Upload yang dipilih harus berupa file.',
        ]);

        try {
            $uploads = $this->attachFixedYears($validated['uploads']);

            $result = $this->padImportService->importBatch(
                $uploads,
                (int) $validated['kota']
            );

            return redirect()
                ->route('pad.import.index')
                ->with('success', 'Import PAD berhasil. ' . $result['inserted'] . ' baris dari 5 file disimpan untuk ' . $result['kota_name'] . '.')
                ->with('import_summary', $result);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'uploads' => $exception->getMessage(),
                ]);
        }
    }

    protected function buildTahunOptions()
    {
        return range(2021, 2025);
    }

    protected function attachFixedYears(array $uploads)
    {
        $tahunOptions = array_values($this->buildTahunOptions());

        return array_map(function ($upload, $index) use ($tahunOptions) {
            $upload['tahun'] = $tahunOptions[$index];

            return $upload;
        }, array_values($uploads), array_keys(array_values($uploads)));
    }
}

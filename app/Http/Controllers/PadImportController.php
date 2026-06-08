<?php

namespace App\Http\Controllers;

use App\Services\PadImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class PadImportController extends Controller
{
    protected const ACCESS_PASSWORD = 'Persija1928';
    protected const IMPORT_PASSWORD = '030423';
    protected const ACCESS_SESSION_KEY = 'pad_import_access_granted';

    protected $padImportService;

    public function __construct(PadImportService $padImportService)
    {
        $this->padImportService = $padImportService;
    }

    public function index()
    {
        if (!$this->hasAccess(request())) {
            return view('pad_import.lock');
        }

        return view('pad_import.index', [
            'kotaOptions' => $this->padImportService->getKotaOptions(),
            'tahunOptions' => $this->buildTahunOptions(),
        ]);
    }

    public function unlock(Request $request)
    {
        $validated = $request->validate([
            'access_password' => ['required', 'string'],
        ], [
            'access_password.required' => 'Password akses halaman upload wajib diisi.',
        ]);

        if ($validated['access_password'] !== self::ACCESS_PASSWORD) {
            return back()->withErrors([
                'access_password' => 'Password akses Upload Data tidak sesuai.',
            ]);
        }

        $request->session()->put(self::ACCESS_SESSION_KEY, true);

        return redirect()->route('pad.import.index');
    }

    public function store(Request $request)
    {
        if (!$this->hasAccess($request)) {
            return redirect()->route('pad.import.index');
        }

        $validated = $request->validate([
            'kota' => ['required', 'integer', Rule::exists('peta', 'ogc_fid')],
            'import_password' => ['required', 'string'],
            'uploads' => ['required', 'array', 'size:5'],
            'uploads.*.file' => ['required', 'file'],
        ], [
            'kota.required' => 'Kota/kabupaten wajib dipilih.',
            'kota.exists' => 'Kota/kabupaten yang dipilih tidak ditemukan.',
            'import_password.required' => 'Password impor wajib diisi.',
            'uploads.required' => 'Lima upload file wajib diisi.',
            'uploads.size' => 'Jumlah upload harus tepat 5 file.',
            'uploads.*.file.required' => 'File Excel pada setiap upload wajib dipilih.',
            'uploads.*.file.file' => 'Upload yang dipilih harus berupa file.',
        ]);

        if ($validated['import_password'] !== self::IMPORT_PASSWORD) {
            return back()
                ->withInput()
                ->withErrors([
                    'import_password' => 'Password untuk proses impor tidak sesuai.',
                ]);
        }

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

    protected function hasAccess(Request $request)
    {
        return (bool) $request->session()->get(self::ACCESS_SESSION_KEY, false);
    }
}

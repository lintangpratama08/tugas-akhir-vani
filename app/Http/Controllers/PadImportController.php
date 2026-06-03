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
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'kota' => ['required', 'integer', Rule::exists('peta', 'ogc_fid')],
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes' => 'File harus berformat xlsx, xls, atau csv.',
            'kota.required' => 'Kota/kabupaten wajib dipilih.',
            'kota.exists' => 'Kota/kabupaten yang dipilih tidak ditemukan.',
        ]);

        try {
            $result = $this->padImportService->import(
                $request->file('file'),
                (int) $validated['kota']
            );

            return redirect()
                ->route('pad.import.index')
                ->with('success', 'Import PAD berhasil. ' . $result['inserted'] . ' baris disimpan untuk ' . $result['kota_name'] . '.')
                ->with('import_summary', $result);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'file' => $exception->getMessage(),
                ]);
        }
    }
}

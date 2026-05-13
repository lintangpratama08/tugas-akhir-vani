<?php

namespace App\Http\Controllers;

use App\Services\PetaDashboardService;
use Illuminate\Http\Request;
use Throwable;

class PetaController extends Controller
{
    protected $petaDashboardService;

    public function __construct(PetaDashboardService $petaDashboardService)
    {
        $this->petaDashboardService = $petaDashboardService;
    }

    public function index()
    {
        try {
            $filters = $this->petaDashboardService->getFilterOptions();
            $filters['backendUnavailable'] = false;
            $filters['backendErrorMessage'] = null;
        } catch (Throwable $exception) {
            report($exception);

            $filters = $this->petaDashboardService->getFallbackFilterOptions();
            $filters['backendUnavailable'] = true;
            $filters['backendErrorMessage'] = 'Koneksi database peta sedang timeout. Halaman tetap dibuka, tetapi data belum bisa dimuat dari server.';
        }

        return view('peta.index', $filters);
    }

    public function ambil_data_peta(Request $request)
    {
        try {
            $filters = $this->petaDashboardService->normalizeFilters($request);

            return response()->json($this->petaDashboardService->getMapPayload($filters));
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Data peta gagal dimuat karena koneksi database timeout.',
            ], 503);
        }
    }

    public function ambil_data_dashboard(Request $request)
    {
        try {
            $filters = $this->petaDashboardService->normalizeFilters($request);

            return response()->json($this->petaDashboardService->getDashboardPayload($filters));
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Dashboard gagal dimuat karena koneksi database timeout.',
            ], 503);
        }
    }

    public function export_dashboard(Request $request)
    {
        try {
            $filters = $this->petaDashboardService->normalizeFilters($request);
            $section = $request->input('section', 'ringkasan');

            return $this->petaDashboardService->exportSection($filters, $section);
        } catch (Throwable $exception) {
            report($exception);

            return response('Export gagal karena koneksi database timeout.', 503);
        }
    }
}

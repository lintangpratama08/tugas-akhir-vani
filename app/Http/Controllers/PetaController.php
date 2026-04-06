<?php

namespace App\Http\Controllers;

use App\Services\PetaDashboardService;
use Illuminate\Http\Request;

class PetaController extends Controller
{
    protected $petaDashboardService;

    public function __construct(PetaDashboardService $petaDashboardService)
    {
        $this->petaDashboardService = $petaDashboardService;
    }

    public function index()
    {
        $filters = $this->petaDashboardService->getFilterOptions();

        return view('peta.index', $filters);
    }

    public function ambil_data_peta(Request $request)
    {
        $filters = $this->petaDashboardService->normalizeFilters($request);

        return response()->json($this->petaDashboardService->getMapPayload($filters));
    }

    public function ambil_data_dashboard(Request $request)
    {
        $filters = $this->petaDashboardService->normalizeFilters($request);

        return response()->json($this->petaDashboardService->getDashboardPayload($filters));
    }

    public function export_dashboard(Request $request)
    {
        $filters = $this->petaDashboardService->normalizeFilters($request);
        $section = $request->input('section', 'ringkasan');

        return $this->petaDashboardService->exportSection($filters, $section);
    }
}

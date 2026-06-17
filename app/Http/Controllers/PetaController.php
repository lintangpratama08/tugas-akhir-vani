<?php

namespace App\Http\Controllers;

use App\Services\DashboardInsightService;
use App\Services\PetaDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PetaController extends Controller
{
    protected $petaDashboardService;
    protected $dashboardInsightService;

    public function __construct(PetaDashboardService $petaDashboardService, DashboardInsightService $dashboardInsightService)
    {
        $this->petaDashboardService = $petaDashboardService;
        $this->dashboardInsightService = $dashboardInsightService;
    }

    public function index()
    {
        try {
            $filters = $this->petaDashboardService->getFilterOptions();
            $filters['backendUnavailable'] = false;
            $filters['backendErrorMessage'] = null;
        } catch (Throwable $exception) {
            $this->reportBackendException($exception, 'peta.index');

            $filters = $this->petaDashboardService->getFallbackFilterOptions();
            $filters['backendUnavailable'] = true;
            $filters['backendErrorMessage'] = $this->buildBackendMessage(
                $exception,
                'Halaman tetap dibuka, tetapi data belum bisa dimuat dari server.'
            );
        }

        return view('peta.index', $filters);
    }

    public function ambil_data_peta(Request $request)
    {
        try {
            $filters = $this->petaDashboardService->normalizeFilters($request);

            return response()->json($this->petaDashboardService->getMapPayload($filters));
        } catch (Throwable $exception) {
            $this->reportBackendException($exception, 'peta.data');

            return response()->json([
                'message' => $this->buildBackendMessage($exception, 'Data peta gagal dimuat.'),
            ], 503);
        }
    }

    public function ambil_data_dashboard(Request $request)
    {
        try {
            $filters = $this->petaDashboardService->normalizeFilters($request);

            return response()->json($this->petaDashboardService->getDashboardPayload($filters));
        } catch (Throwable $exception) {
            $this->reportBackendException($exception, 'peta.dashboard');

            return response()->json([
                'message' => $this->buildBackendMessage($exception, 'Dashboard gagal dimuat.'),
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
            $this->reportBackendException($exception, 'peta.export');

            return response($this->buildBackendMessage($exception, 'Export gagal.'), 503);
        }
    }

    public function generate_chart_insight(Request $request)
    {
        try {
            $validated = $request->validate([
                'section' => 'nullable|string|max:100',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'scope_label' => 'nullable|string|max:255',
                'filters' => 'nullable|array',
                'labels' => 'nullable|array',
                'datasets' => 'nullable|array',
                'rows' => 'nullable|array',
            ]);

            return response()->json($this->dashboardInsightService->generate($validated));
        } catch (Throwable $exception) {
            $this->reportBackendException($exception, 'peta.chart_insight');

            return response()->json([
                'message' => $this->buildBackendMessage($exception, 'Insight chart gagal dibuat.'),
            ], 503);
        }
    }

    protected function reportBackendException(Throwable $exception, string $context)
    {
        report($exception);

        Log::error('Peta backend error', [
            'context' => $context,
            'message' => $exception->getMessage(),
            'db_connection' => config('database.default'),
            'db_host' => config('database.connections.' . config('database.default') . '.host'),
            'db_port' => config('database.connections.' . config('database.default') . '.port'),
        ]);
    }

    protected function buildBackendMessage(Throwable $exception, string $suffix = '')
    {
        $message = strtolower($exception->getMessage());
        $baseMessage = 'Terjadi kendala saat mengakses database peta.';

        if (str_contains($message, 'could not find driver')) {
            $baseMessage = 'Driver PostgreSQL belum aktif di PHP web server.';
        } elseif (
            str_contains($message, 'timeout expired') ||
            str_contains($message, 'connection timed out') ||
            str_contains($message, 'no route to host')
        ) {
            $baseMessage = 'Koneksi ke database peta gagal karena timeout atau jalur jaringan belum terbuka.';
        } elseif (str_contains($message, 'connection refused')) {
            $baseMessage = 'Database peta terjangkau, tetapi koneksinya ditolak oleh server atau firewall.';
        } elseif (str_contains($message, 'password authentication failed')) {
            $baseMessage = 'Username atau password PostgreSQL tidak cocok.';
        } elseif (
            str_contains($message, 'undefined table') ||
            str_contains($message, 'relation "') ||
            str_contains($message, 'does not exist')
        ) {
            $baseMessage = 'Koneksi database berhasil, tetapi tabel peta yang dibutuhkan belum tersedia.';
        }

        $fullMessage = trim($baseMessage . ' ' . $suffix);

        if (config('app.debug')) {
            $fullMessage .= ' Detail: ' . $exception->getMessage();
        }

        return $fullMessage;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $upts = DB::table('data_master')
            ->select('upt')
            ->distinct()
            ->orderBy('upt')
            ->pluck('upt');

        $skts = DB::table('data_master')
            ->select('skt', 'label')
            ->distinct()
            ->orderBy('skt')
            ->get();

        $periodes = DB::table('data_master')
            ->select('periode_update')
            ->distinct()
            ->orderBy('periode_update')
            ->pluck('periode_update');

        return view('dashboard.index', compact('upts', 'skts', 'periodes'));
    }

    public function getTotalRealisasiTarget(Request $request)
    {
        $query = DB::table('data_master')
            ->selectRaw('SUM(CAST(target AS BIGINT)) as total_target, SUM(CAST(jumlah AS BIGINT)) as total_realisasi');

        if ($request->upt && $request->upt != '') {
            $query->where('upt', $request->upt);
        }

        if ($request->skt && $request->skt != '') {
            $query->where('skt', $request->skt);
        }

        if ($request->tahun && $request->tahun != '') {
            $query->where('tahun', $request->tahun);
        } elseif ($request->periode && $request->periode != '') {
            $query->where('periode_update', $request->periode);
        }

        return response()->json($query->first());
    }

    public function getRealisasiPerSKT(Request $request)
    {
        $query = DB::table('data_master')
            ->select('skt', 'label', DB::raw('SUM(CAST(target AS BIGINT)) as target'), DB::raw('SUM(CAST(jumlah AS BIGINT)) as realisasi'))
            ->groupBy('skt', 'label')
            ->orderBy('realisasi', 'desc');

        if ($request->upt && $request->upt != '') {
            $query->where('upt', $request->upt);
        }

        if ($request->tahun && $request->tahun != '') {
            $query->where('tahun', $request->tahun);
        } elseif ($request->periode && $request->periode != '') {
            $query->where('periode_update', $request->periode);
        }

        return response()->json($query->get());
    }

    public function getRealisasiPerUPT(Request $request)
    {
        $query = DB::table('data_master')
            ->select('upt', DB::raw('SUM(CAST(target AS BIGINT)) as target'), DB::raw('SUM(CAST(jumlah AS BIGINT)) as realisasi'))
            ->groupBy('upt')
            ->orderBy('realisasi', 'desc');

        if ($request->skt && $request->skt != '') {
            $query->where('skt', $request->skt);
        }

        if ($request->tahun && $request->tahun != '') {
            $query->where('tahun', $request->tahun);
        } elseif ($request->periode && $request->periode != '') {
            $query->where('periode_update', $request->periode);
        }

        return response()->json($query->get());
    }

    public function getTrendBulanan(Request $request)
    {
        $query = DB::table('data_master')
            ->select('periode_update', DB::raw('SUM(CAST(target AS BIGINT)) as target'), DB::raw('SUM(CAST(jumlah AS BIGINT)) as realisasi'))
            ->groupBy('periode_update')
            ->orderBy('periode_update');

        if ($request->upt && $request->upt != '') {
            $query->where('upt', $request->upt);
        }

        if ($request->skt && $request->skt != '') {
            $query->where('skt', $request->skt);
        }

        if ($request->tahun && $request->tahun != '') {
            $query->where('tahun', $request->tahun);
        }

        return response()->json($query->get());
    }

    public function getTrendTahunan(Request $request)
    {
        $query = DB::table('data_master')
            ->select('tahun', DB::raw('SUM(CAST(target AS BIGINT)) as target'), DB::raw('SUM(CAST(jumlah AS BIGINT)) as realisasi'))
            ->groupBy('tahun')
            ->orderBy('tahun');

        if ($request->upt && $request->upt != '') {
            $query->where('upt', $request->upt);
        }

        if ($request->skt && $request->skt != '') {
            $query->where('skt', $request->skt);
        }

        return response()->json($query->get());
    }

    public function getPersentaseRealisasi(Request $request)
    {
        $query = DB::table('data_master')
            ->select('skt', 'label', DB::raw('SUM(CAST(jumlah AS BIGINT)) as realisasi'), DB::raw('SUM(CAST(target AS BIGINT)) as target'))
            ->groupBy('skt', 'label');

        if ($request->upt && $request->upt != '') {
            $query->where('upt', $request->upt);
        }

        if ($request->tahun && $request->tahun != '') {
            $query->where('tahun', $request->tahun);
        } elseif ($request->periode && $request->periode != '') {
            $query->where('periode_update', $request->periode);
        }

        $data = $query->get()->map(function ($item) {
            $item->persentase = $item->target > 0 ? round(($item->realisasi / $item->target) * 100, 2) : 0;
            return $item;
        });

        return response()->json($data);
    }

    public function getTopPerformer(Request $request)
    {
        $query = DB::table('data_master')
            ->select('upt', DB::raw('SUM(CAST(jumlah AS BIGINT)) as realisasi'), DB::raw('SUM(CAST(target AS BIGINT)) as target'))
            ->groupBy('upt');

        if ($request->skt && $request->skt != '') {
            $query->where('skt', $request->skt);
        }

        if ($request->tahun && $request->tahun != '') {
            $query->where('tahun', $request->tahun);
        } elseif ($request->periode && $request->periode != '') {
            $query->where('periode_update', $request->periode);
        }

        $data = $query->get()->map(function ($item) {
            $item->persentase = $item->target > 0 ? round(($item->realisasi / $item->target) * 100, 2) : 0;
            return $item;
        })->sortByDesc('persentase')->take(10);

        return response()->json($data->values());
    }

    public function getTrendPerPajak(Request $request)
    {
        $upt = $request->upt ?? 'NGANJUK';

        $query = DB::table('data_master')
            ->select('periode_update', 'label', 'skt', DB::raw('SUM(CAST(jumlah AS BIGINT)) as realisasi'), DB::raw('SUM(CAST(target AS BIGINT)) as target'))
            ->where('upt', $upt)
            ->groupBy('periode_update', 'label', 'skt')
            ->orderBy('periode_update')
            ->orderBy('label');

        if ($request->skt && $request->skt != '') {
            $query->where('skt', $request->skt);
        }

        return response()->json($query->get());
    }

    public function getDetailPerUPT(Request $request)
    {
        $upt = $request->upt ?? 'NGANJUK';

        $query = DB::table('data_master')
            ->select('periode_update', 'label', 'skt', DB::raw('CAST(jumlah AS BIGINT) as realisasi'), DB::raw('CAST(target AS BIGINT) as target'))
            ->where('upt', $upt)
            ->orderBy('periode_update')
            ->orderBy('label');

        if ($request->periode && $request->periode != '') {
            $query->where('periode_update', $request->periode);
        }

        if ($request->skt && $request->skt != '') {
            $query->where('skt', $request->skt);
        }

        return response()->json($query->get());
    }
}

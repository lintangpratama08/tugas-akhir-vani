<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PajakDaerahController extends Controller
{
    public function index()
    {
        return view('pajak_daerah.index');
    }

    public function get_data_pertahun()
    {
        $data = DB::table('data_pertahun')
            ->select(DB::raw('kode, nama_pajak, tahun, SUM(realisasi) as total_realisasi, SUM(target) as total_target'))
            ->groupBy('nama_pajak', 'kode', 'tahun')
            ->orderBy('tahun', 'asc');
        if (request()->tahun && request()->tahun != '') {
            $data->where('tahun', request()->tahun);
        }
        if (request()->kode && request()->kode != '') {
            $data->where('kode', request()->kode);
        }
        $data = $data->get();
        return response()->json($data);
    }

    public function get_data_perbulan(Request $request)
    {
        $query = DB::table('data_perbulan')
            ->select(DB::raw('kode, nama_pajak, bulan, tahun, SUM(realisasi_bulan_ini) as total_realisasi, SUM(realisasi_smp_bln_ini) as total_realisasi_smp, SUM(target) as total_target'))
            ->groupBy('nama_pajak', 'kode', 'bulan', 'tahun')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc');

        if (request()->kode && request()->kode != '') {
            $query->where('kode', request()->kode);
        }
        if ($request->tahun && $request->tahun != '') {
            $query->where('tahun', $request->tahun);
        }
        if ($request->bulan && $request->bulan != '') {
            $query->where('bulan', $request->bulan);
        }

        $data = $query->get();
        return response()->json($data);
    }
}

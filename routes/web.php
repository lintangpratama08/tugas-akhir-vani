<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PadImportController;
use App\Http\Controllers\PajakDaerahController;
use App\Http\Controllers\PetaController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/total-realisasi-target', [DashboardController::class, 'getTotalRealisasiTarget']);
Route::get('/api/realisasi-per-skt', [DashboardController::class, 'getRealisasiPerSKT']);
Route::get('/api/realisasi-per-upt', [DashboardController::class, 'getRealisasiPerUPT']);
Route::get('/api/trend-bulanan', [DashboardController::class, 'getTrendBulanan']);
Route::get('/api/trend-tahunan', [DashboardController::class, 'getTrendTahunan']);
Route::get('/api/persentase-realisasi', [DashboardController::class, 'getPersentaseRealisasi']);
Route::get('/api/top-performer', [DashboardController::class, 'getTopPerformer']);
Route::get('/api/trend-per-pajak', [DashboardController::class, 'getTrendPerPajak']);
Route::get('/api/detail-per-upt', [DashboardController::class, 'getDetailPerUPT']);


Route::get('/pajak-daerah', [PajakDaerahController::class, 'index'])->name('pajak_daerah.index');
Route::get('/pajak-daerah/data-pertahun', [PajakDaerahController::class, 'get_data_pertahun'])->name('pajak_daerah.data_pertahun');
Route::get('/pajak-daerah/data-perbulan', [PajakDaerahController::class, 'get_data_perbulan'])->name('pajak_daerah.data_perbulan');

Route::get('/peta', [PetaController::class, 'index'])->name('peta.index');
Route::get('/peta/data', [PetaController::class, 'ambil_data_peta'])->name('peta.data');
Route::get('/peta/dashboard', [PetaController::class, 'ambil_data_dashboard'])->name('peta.dashboard');
Route::get('/peta/export', [PetaController::class, 'export_dashboard'])->name('peta.export');

Route::get('/import-pad', [PadImportController::class, 'index'])->name('pad.import.index');
Route::post('/import-pad/unlock', [PadImportController::class, 'unlock'])->name('pad.import.unlock');
Route::post('/import-pad', [PadImportController::class, 'store'])->name('pad.import.store');

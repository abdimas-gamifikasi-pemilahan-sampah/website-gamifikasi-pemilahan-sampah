<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SetoranController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\WargaController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('public.landing');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/sips/dashboard', function () { return view('sips.dashboard.index'); })->name('sips.dashboard');
    Route::get('/sips/leaderboard', function () { return view('sips.leaderboard.index'); })->name('sips.leaderboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('/sips/warga', [WargaController::class, 'index'])->name('sips.warga.index');
        Route::get('/sips/warga/create', [WargaController::class, 'create'])->name('sips.warga.create');
        Route::post('/sips/warga', [WargaController::class, 'store'])->name('sips.warga.store');
        Route::get('/sips/warga/{warga}/edit', [WargaController::class, 'edit'])->name('sips.warga.edit');
        Route::match(['put', 'patch'], '/sips/warga/{warga}', [WargaController::class, 'update'])->name('sips.warga.update');
        Route::patch('/sips/warga/{warga}/status', [WargaController::class, 'updateStatus'])->name('sips.warga.status');

        Route::get('/sips/tarif', [TarifController::class, 'index'])->name('sips.tarif.index');
        Route::get('/sips/tarif/create', [TarifController::class, 'create'])->name('sips.tarif.create');
        Route::post('/sips/tarif', [TarifController::class, 'store'])->name('sips.tarif.store');
        Route::get('/sips/tarif/{tarif}', [TarifController::class, 'show'])->name('sips.tarif.show');
        Route::get('/sips/tarif/{tarif}/edit', [TarifController::class, 'edit'])->name('sips.tarif.edit');
        Route::match(['put', 'patch'], '/sips/tarif/{tarif}', [TarifController::class, 'update'])->name('sips.tarif.update');
        Route::get('/sips/tarif/{tarif}/harga/create', [TarifController::class, 'createPrice'])->name('sips.tarif.price.create');
        Route::post('/sips/tarif/{tarif}/harga', [TarifController::class, 'storePrice'])->name('sips.tarif.price.store');
        Route::patch('/sips/tarif/{tarif}/status', [TarifController::class, 'updateStatus'])->name('sips.tarif.status');
    });

    Route::middleware('role:admin,petugas')->group(function () {
        Route::get('/sips/setoran', [SetoranController::class, 'index'])->name('sips.setoran.index');
        Route::get('/sips/setoran/create', [SetoranController::class, 'create'])->name('sips.setoran.create');
        Route::post('/sips/setoran', [SetoranController::class, 'store'])->name('sips.setoran.store');
        Route::get('/sips/setoran/{setoran}', [SetoranController::class, 'show'])->name('sips.setoran.show');
        Route::get('/sips/setoran/{setoran}/kwitansi', [SetoranController::class, 'kwitansi'])->name('sips.setoran.kwitansi');
        Route::post('/sips/setoran/{setoran}/bayar', [PembayaranController::class, 'store'])->name('sips.pembayaran.store');
        Route::get('/sips/pembayaran', [PembayaranController::class, 'index'])->name('sips.pembayaran.index');
    });
});

// Public user view
Route::get('/leaderboard', function () { return view('public.leaderboard.index'); });

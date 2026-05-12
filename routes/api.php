<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TarifController;
use App\Http\Controllers\Api\WargaController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');

    Route::middleware('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    });
});

Route::middleware(['web', 'auth', 'role:admin,petugas'])->group(function () {
    Route::prefix('warga')->group(function () {
        Route::get('/', [WargaController::class, 'index']);
        Route::post('/', [WargaController::class, 'store']);
        Route::get('{warga}', [WargaController::class, 'show']);
    });

    Route::prefix('tarif')->group(function () {
        Route::get('lookup', [TarifController::class, 'lookup']);
        Route::get('items', [TarifController::class, 'index']);
        Route::get('items/{tarifItem}', [TarifController::class, 'showItem']);
    });
});

Route::middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::prefix('warga')->group(function () {
        Route::match(['put', 'patch'], '{warga}', [WargaController::class, 'update']);
        Route::patch('{warga}/status', [WargaController::class, 'updateStatus']);
    });

    Route::prefix('tarif')->group(function () {
        Route::post('items', [TarifController::class, 'storeItem']);
        Route::match(['put', 'patch'], 'items/{tarifItem}', [TarifController::class, 'updateItem']);
        Route::post('items/{tarifItem}/harga', [TarifController::class, 'storePrice']);
    });
});

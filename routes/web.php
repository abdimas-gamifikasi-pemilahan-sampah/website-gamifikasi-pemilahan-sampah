<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetoranController;
use App\Http\Controllers\PembayaranController;

Route::get('/', function () {
    return view('public.landing');
})->name('landing');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// SIPS Routes (Frontend Only for now)
Route::get('/sips/dashboard', function () { return view('sips.dashboard.index'); });
Route::get('/sips/leaderboard', function () { return view('sips.leaderboard.index'); });
Route::get('/sips/warga', function () { return view('sips.warga.index'); });
Route::get('/sips/setoran', [SetoranController::class, 'index'])->name('sips.setoran.index');
Route::get('/sips/setoran/create', [SetoranController::class, 'create'])->name('sips.setoran.create');
Route::post('/sips/setoran', [SetoranController::class, 'store'])->name('sips.setoran.store');
Route::get('/sips/setoran/{setoran}', [SetoranController::class, 'show'])->name('sips.setoran.show');
Route::get('/sips/setoran/{setoran}/kwitansi', [SetoranController::class, 'kwitansi'])->name('sips.setoran.kwitansi');
Route::post('/sips/setoran/{setoran}/bayar', [PembayaranController::class, 'store'])->name('sips.pembayaran.store');
Route::get('/sips/pembayaran', [PembayaranController::class, 'index'])->name('sips.pembayaran.index');
Route::get('/sips/tarif', function () { return view('sips.tarif.index'); });
Route::get('/sips/hidden-links', function () {
    $basePath = resource_path('views/_unused_template');
    $hiddenPages = [];

    if (File::exists($basePath)) {
        $hiddenPages = collect(File::allFiles($basePath))
            ->filter(function ($file) {
                return str_ends_with($file->getFilename(), '.blade.php');
            })
            ->map(function ($file) use ($basePath) {
                $relative = str_replace('\\', '/', str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname()));
                return preg_replace('/\.blade\.php$/', '', $relative);
            })
            ->map(function ($pagePath) {
                return [
                    'name' => ucwords(str_replace(['-', '/'], [' ', ' / '], $pagePath)),
                    'url' => '/' . $pagePath,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    return view('sips.hidden-links', ['hiddenPages' => $hiddenPages]);
});

// Public user view
Route::get('/leaderboard', function () { return view('public.leaderboard.index'); });

Route::get('{any}',[DashboardController::class, 'index'])->where('any', '.*'); // Catch-all route for the dashboard.

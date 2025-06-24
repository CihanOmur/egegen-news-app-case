<?php

use App\Http\Controllers\Api\NewsController;
use App\Http\Middleware\LogRequestMiddleware;
use App\Http\Middleware\TokenAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('news')
    ->middleware(['api']) // Laravel varsayılan 'api' middleware grubu (rate limit, json, vs.)
    ->group(function () {

        // Haberleri listele (Herkese açık)
        Route::get('/', [NewsController::class, 'index']);

        // Habere özel erişim (Herkese açık)
        Route::get('/{id}', [NewsController::class, 'show']);

        Route::middleware([TokenAuthMiddleware::class, LogRequestMiddleware::class])->group(function () {

            // Haberi ekle (Token doğrulama + loglama)
            Route::post('/', [NewsController::class, 'store']);

            // Haberi güncelle (Token doğrulama + loglama)
            Route::post('/update/{id}', [NewsController::class, 'update']);

            // Haberi sil (Token doğrulama + loglama)
            Route::delete('/{id}', [NewsController::class, 'destroy']);
        });
    });

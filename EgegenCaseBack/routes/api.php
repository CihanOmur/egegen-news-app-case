<?php

use App\Http\Controllers\Api\NewsController;
use App\Http\Middleware\LogRequestMiddleware;
use App\Http\Middleware\TokenAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('news')
    ->middleware(['api']) // API middleware group
    ->group(function () {

        // Get all news 
        Route::get('/', [NewsController::class, 'index']);

        // Get news by ID
        Route::get('/{id}', [NewsController::class, 'show']);

        Route::middleware([TokenAuthMiddleware::class, LogRequestMiddleware::class])->group(function () {

            // Store a new news item (Token control + logging)
            Route::post('/', [NewsController::class, 'store']);

            // Update an existing news item (Token control + logging)
            Route::post('/update/{id}', [NewsController::class, 'update']);

            // Delete a news item (Token control + logging)
            Route::delete('/{id}', [NewsController::class, 'destroy']);
        });
    });

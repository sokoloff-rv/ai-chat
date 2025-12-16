<?php

use App\Http\Controllers\Api\WidgetController;
use Illuminate\Support\Facades\Route;

Route::prefix('widget/{publicId}')->group(function () {
    Route::get('/config', [WidgetController::class, 'config']);
    Route::post('/session', [WidgetController::class, 'startSession']);
    Route::post('/message', [WidgetController::class, 'sendMessage']);
    Route::get('/history', [WidgetController::class, 'getHistory']);
});

<?php

use App\Http\Controllers\ScreenshotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Welcome');
});

Route::post('/screenshots/generate', [ScreenshotController::class, 'generate'])
    ->name('screenshot.generate');

Route::get('/screenshots/download/{filename}', [ScreenshotController::class, 'download'])
    ->name('screenshot.download');

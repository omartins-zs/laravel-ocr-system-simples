<?php

use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\OcrController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return ApiResponse::success(
        message: 'API healthy.',
        data: [
            'service' => config('app.name', 'laravel-ocr-system-simples'),
            'timestamp' => now()->toIso8601String(),
        ],
        statusCode: 200
    );
});

Route::post('/ocr', [OcrController::class, 'store']);
Route::get('/history', [HistoryController::class, 'index']);
Route::get('/history/{ocrDocument}', [HistoryController::class, 'show']);

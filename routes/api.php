<?php

use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\OcrController;
use Illuminate\Support\Facades\Route;

Route::post('/ocr', [OcrController::class, 'store']);
Route::get('/history', [HistoryController::class, 'index']);
Route::get('/history/{ocrDocument}', [HistoryController::class, 'show']);

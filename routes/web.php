<?php

use App\Http\Controllers\Web\HistoryController;
use App\Http\Controllers\Web\OcrController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/ocr');

Route::get('/ocr', [OcrController::class, 'index'])->name('ocr.index');
Route::post('/ocr', [OcrController::class, 'store'])->name('ocr.store');

Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
Route::get('/history/{ocrDocument}', [HistoryController::class, 'show'])->name('history.show');
Route::post('/history/{ocrDocument}/rerun', [HistoryController::class, 'rerun'])->name('history.rerun');

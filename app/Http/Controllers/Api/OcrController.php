<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOcrFileRequest;
use App\Jobs\ProcessOcrDocumentJob;
use App\Models\OcrDocument;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class OcrController extends Controller
{
    public function store(StoreOcrFileRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $storedPath = $file->store('ocr_uploads', 'local');

        $document = OcrDocument::create([
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getClientMimeType() ?? 'application/octet-stream',
            'extension' => strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin'),
            'file_size' => $file->getSize(),
            'status' => OcrDocument::STATUS_PENDING,
        ]);

        ProcessOcrDocumentJob::dispatch($document->id);

        return ApiResponse::success(
            message: 'Arquivo recebido e enviado para a fila de processamento.',
            data: [
                'id' => $document->id,
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'extension' => $document->extension,
                'stored_path' => $document->stored_path,
                'processing_status' => $document->status,
                'extracted_text' => $document->extracted_text,
                'created_at' => $document->created_at?->toIso8601String(),
            ],
            statusCode: 202
        );
    }
}

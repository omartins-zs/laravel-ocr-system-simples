<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OcrDocument;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        $documents = OcrDocument::query()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($innerQuery) use ($query) {
                    $innerQuery
                        ->where('original_name', 'like', "%{$query}%")
                        ->orWhere('status', 'like', "%{$query}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return ApiResponse::success(
            message: 'Historico carregado com sucesso.',
            data: [
                'items' => collect($documents->items())
                    ->map(fn (OcrDocument $document) => $this->transformDocument($document, false))
                    ->values()
                    ->all(),
                'meta' => [
                    'current_page' => $documents->currentPage(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'total' => $documents->total(),
                ],
            ]
        );
    }

    public function show(OcrDocument $ocrDocument): JsonResponse
    {
        return ApiResponse::success(
            message: 'Documento encontrado.',
            data: $this->transformDocument($ocrDocument, true)
        );
    }

    private function transformDocument(OcrDocument $document, bool $withFullText): array
    {
        return [
            'id' => $document->id,
            'original_name' => $document->original_name,
            'mime_type' => $document->mime_type,
            'extension' => $document->extension,
            'stored_path' => $document->stored_path,
            'file_size' => $document->file_size,
            'processing_status' => $document->status,
            'extracted_text' => $withFullText ? $document->extracted_text : $document->excerpt(200),
            'error_message' => $document->error_message,
            'created_at' => $document->created_at?->toIso8601String(),
            'updated_at' => $document->updated_at?->toIso8601String(),
        ];
    }
}

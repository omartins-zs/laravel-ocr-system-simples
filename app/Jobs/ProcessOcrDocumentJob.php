<?php

namespace App\Jobs;

use App\Models\OcrDocument;
use App\Services\OcrService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessOcrDocumentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $maxExceptions = 3;

    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(public int $ocrDocumentId) {}

    /**
     * Execute the job.
     */
    public function handle(OcrService $ocrService): void
    {
        $document = OcrDocument::find($this->ocrDocumentId);

        if (! $document) {
            return;
        }

        $document->update([
            'status' => OcrDocument::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        try {
            $absolutePath = Storage::disk('local')->path($document->stored_path);
            $text = $ocrService->extractFromPath($absolutePath, $document->extension);

            $document->update([
                'extracted_text' => $text,
                'status' => OcrDocument::STATUS_COMPLETED,
                'processed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $document->update([
                'status' => OcrDocument::STATUS_FAILED,
                'error_message' => mb_substr($exception->getMessage(), 0, 2000),
                'processed_at' => now(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ProcessOcrDocumentJob falhou definitivamente.', [
            'ocr_document_id' => $this->ocrDocumentId,
            'erro' => $exception->getMessage(),
        ]);

        $document = OcrDocument::find($this->ocrDocumentId);

        if (! $document) {
            return;
        }

        $document->update([
            'status' => OcrDocument::STATUS_FAILED,
            'error_message' => mb_substr($exception->getMessage(), 0, 2000),
            'processed_at' => now(),
        ]);
    }
}

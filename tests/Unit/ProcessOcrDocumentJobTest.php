<?php

namespace Tests\Unit;

use App\Jobs\ProcessOcrDocumentJob;
use App\Models\OcrDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ProcessOcrDocumentJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_has_retry_timeout_and_backoff_defaults(): void
    {
        $job = new ProcessOcrDocumentJob(1);

        $this->assertSame(3, $job->tries);
        $this->assertSame(120, $job->timeout);
        $this->assertSame(3, $job->maxExceptions);
        $this->assertSame([10, 30, 60], $job->backoff);
    }

    public function test_failed_updates_document_status_and_error_message(): void
    {
        $document = OcrDocument::create([
            'original_name' => 'arquivo.pdf',
            'stored_path' => 'ocr_uploads/arquivo.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 2048,
            'status' => OcrDocument::STATUS_PROCESSING,
        ]);

        $job = new ProcessOcrDocumentJob($document->id);
        $job->failed(new RuntimeException('Falha final simulada'));

        $document->refresh();

        $this->assertSame(OcrDocument::STATUS_FAILED, $document->status);
        $this->assertSame('Falha final simulada', $document->error_message);
        $this->assertNotNull($document->processed_at);
    }
}

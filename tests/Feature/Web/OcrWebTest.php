<?php

namespace Tests\Feature\Web;

use App\Jobs\ProcessOcrDocumentJob;
use App\Models\OcrDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OcrWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_ocr_page(): void
    {
        $this->get('/')->assertRedirect('/ocr');
    }

    public function test_web_upload_creates_document_and_dispatches_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $response = $this->from('/ocr')->post('/ocr', [
            'file' => UploadedFile::fake()->create('contrato.pdf', 50, 'application/pdf'),
        ]);

        $document = OcrDocument::query()->firstOrFail();

        $response
            ->assertRedirect(route('ocr.index', ['document' => $document->id]))
            ->assertSessionHas('success');

        $this->assertSame(OcrDocument::STATUS_PENDING, $document->status);

        Queue::assertPushed(ProcessOcrDocumentJob::class, 1);
    }

    public function test_web_upload_validation_redirects_back_with_errors(): void
    {
        Queue::fake();

        $response = $this->from('/ocr')->post('/ocr', [
            'file' => UploadedFile::fake()->create('arquivo.txt', 5, 'text/plain'),
        ]);

        $response
            ->assertRedirect('/ocr')
            ->assertSessionHasErrors(['file']);

        Queue::assertNothingPushed();
        $this->assertDatabaseCount('ocr_documents', 0);
    }

    public function test_rerun_failed_document_resets_status_and_dispatches_job(): void
    {
        Queue::fake();

        $document = OcrDocument::create([
            'original_name' => 'erro.png',
            'stored_path' => 'ocr_uploads/erro.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'file_size' => 900,
            'status' => OcrDocument::STATUS_FAILED,
            'error_message' => 'falha anterior',
            'extracted_text' => null,
            'processed_at' => now(),
        ]);

        $response = $this->post(route('history.rerun', $document));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $document->refresh();

        $this->assertSame(OcrDocument::STATUS_PENDING, $document->status);
        $this->assertNull($document->error_message);
        $this->assertNull($document->processed_at);

        Queue::assertPushed(ProcessOcrDocumentJob::class, 1);
    }

    public function test_rerun_non_failed_document_does_not_dispatch_job(): void
    {
        Queue::fake();

        $document = OcrDocument::create([
            'original_name' => 'ok.pdf',
            'stored_path' => 'ocr_uploads/ok.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 1200,
            'status' => OcrDocument::STATUS_COMPLETED,
            'extracted_text' => 'texto pronto',
        ]);

        $response = $this->post(route('history.rerun', $document));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        Queue::assertNothingPushed();

        $document->refresh();
        $this->assertSame(OcrDocument::STATUS_COMPLETED, $document->status);
    }
}

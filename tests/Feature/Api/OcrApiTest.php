<?php

namespace Tests\Feature\Api;

use App\Jobs\ProcessOcrDocumentJob;
use App\Models\OcrDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OcrApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_returns_accepted_and_dispatches_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $response = $this->postJson('/api/ocr', [
            'file' => UploadedFile::fake()->create('nota-fiscal.pdf', 120, 'application/pdf'),
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('status_code', 202)
            ->assertJsonPath('data.original_name', 'nota-fiscal.pdf')
            ->assertJsonPath('data.processing_status', OcrDocument::STATUS_PENDING);

        $this->assertDatabaseCount('ocr_documents', 1);

        Queue::assertPushed(ProcessOcrDocumentJob::class, 1);
    }

    public function test_upload_returns_422_for_invalid_extension(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/ocr', [
            'file' => UploadedFile::fake()->create('arquivo.txt', 2, 'text/plain'),
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('status_code', 422)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data',
                'errors' => ['file'],
            ]);

        Queue::assertNothingPushed();
    }

    public function test_history_index_returns_standard_payload(): void
    {
        OcrDocument::create([
            'original_name' => 'a.pdf',
            'stored_path' => 'ocr_uploads/a.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 1000,
            'status' => OcrDocument::STATUS_COMPLETED,
            'extracted_text' => 'texto A',
        ]);

        OcrDocument::create([
            'original_name' => 'b.png',
            'stored_path' => 'ocr_uploads/b.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'file_size' => 1100,
            'status' => OcrDocument::STATUS_FAILED,
            'error_message' => 'falha simulada',
        ]);

        $response = $this->getJson('/api/history');

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('status_code', 200)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [
                    'items',
                    'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
                'errors',
            ]);
    }

    public function test_history_show_returns_not_found_pattern_for_invalid_id(): void
    {
        $response = $this->getJson('/api/history/999999');

        $response
            ->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('status_code', 404)
            ->assertJsonPath('message', 'Recurso nao encontrado.');
    }
}

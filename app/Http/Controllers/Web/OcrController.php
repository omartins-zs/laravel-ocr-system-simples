<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOcrFileRequest;
use App\Jobs\ProcessOcrDocumentJob;
use App\Models\OcrDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OcrController extends Controller
{
    public function index(Request $request): View
    {
        $document = null;
        $documentId = $request->integer('document');

        if ($documentId > 0) {
            $document = OcrDocument::find($documentId);
        }

        return view('ocr.index', [
            'document' => $document,
        ]);
    }

    public function store(StoreOcrFileRequest $request): RedirectResponse
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

        return redirect()
            ->route('ocr.index', ['document' => $document->id])
            ->with('success', 'Arquivo enviado com sucesso. O processamento foi para a fila local.');
    }
}

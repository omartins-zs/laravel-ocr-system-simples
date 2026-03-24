<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OcrDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
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
            ->paginate(12)
            ->withQueryString();

        return view('history.index', [
            'documents' => $documents,
            'query' => $query,
        ]);
    }

    public function show(OcrDocument $ocrDocument): View
    {
        return view('history.show', [
            'document' => $ocrDocument,
        ]);
    }
}

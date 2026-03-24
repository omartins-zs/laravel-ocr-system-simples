@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">OCR Simples</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Importar e processar arquivo</h1>
            <p class="mt-2 text-sm text-slate-600">
                Envie PDF, PNG, JPG, JPEG ou WEBP. O arquivo entra na fila local e o texto extraido fica salvo no historico.
            </p>

            <form method="POST" action="{{ route('ocr.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="file" class="mb-2 block text-sm font-medium text-slate-700">Arquivo</label>
                    <input
                        id="file"
                        name="file"
                        type="file"
                        accept=".pdf,.png,.jpg,.jpeg,.webp"
                        class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-800 file:px-4 file:py-2 file:text-white hover:file:bg-slate-700"
                        required
                    >

                    @error('file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200"
                >
                    Processar arquivo
                </button>
            </form>

            <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                Para processar na fila local, rode:
                <code class="rounded bg-blue-100 px-1 py-0.5">php artisan queue:work --queue=default</code>
            </div>
        </section>

        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            @if ($document)
                data-ocr-live
                data-endpoint="{{ url('/api/history/'.$document->id) }}"
                data-document-id="{{ $document->id }}"
            @endif
        >
            @if ($document)
                @php
                    $statusClasses = match ($document->status) {
                        'completed' => 'bg-emerald-100 text-emerald-800',
                        'failed' => 'bg-red-100 text-red-800',
                        'processing' => 'bg-amber-100 text-amber-800',
                        default => 'bg-slate-200 text-slate-700',
                    };
                    $statusLabel = match ($document->status) {
                        'completed' => 'Concluido',
                        'failed' => 'Falhou',
                        'processing' => 'Processando',
                        default => 'Pendente',
                    };
                @endphp

                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Ultimo envio</h2>
                    <span data-ocr-status-badge class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
                </div>
                <p data-ocr-live-state class="mt-2 text-xs text-slate-500">
                    Atualizacao automatica a cada 3 segundos enquanto o processamento nao finalizar.
                </p>

                <dl class="mt-4 space-y-2 text-sm text-slate-700">
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <dt class="font-medium">Arquivo</dt>
                        <dd data-ocr-original-name class="text-right">{{ $document->original_name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <dt class="font-medium">Tipo</dt>
                        <dd data-ocr-mime-type>{{ $document->mime_type }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <dt class="font-medium">Enviado em</dt>
                        <dd data-ocr-created-at>{{ $document->created_at?->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <dt class="font-medium">Atualizado em</dt>
                        <dd data-ocr-updated-at>{{ $document->updated_at?->format('d/m/Y H:i:s') }}</dd>
                    </div>
                </dl>

                <div
                    data-ocr-error-message
                    class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 {{ $document->status === 'failed' ? '' : 'hidden' }}"
                >{{ $document->error_message }}</div>

                <div class="mt-4">
                    <p class="text-sm font-semibold text-slate-700">Texto extraido</p>
                    <pre data-ocr-extracted-text class="mt-2 max-h-80 overflow-auto rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm whitespace-pre-wrap">{{ $document->extracted_text ?: 'Aguardando processamento da fila...' }}</pre>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('history.show', $document) }}"
                        class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                    >
                        Ver detalhe completo
                    </a>

                    @if ($document->status === 'failed')
                        <form method="POST" action="{{ route('history.rerun', $document) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 1 1-9.73-3.357.75.75 0 1 0-1.164-.946 7 7 0 1 0 12.11 4.803h1.222a.75.75 0 0 0 .53-1.28l-2.25-2.25a.75.75 0 0 0-1.28.53v2.5h.562Z" clip-rule="evenodd" />
                                </svg>
                                Re-run
                            </button>
                        </form>
                    @endif
                </div>
            @else
                <h2 class="text-xl font-semibold text-slate-900">Resultado</h2>
                <p class="mt-3 text-sm text-slate-600">
                    Apos o upload, o status e o texto extraido aparecem aqui automaticamente.
                </p>
            @endif
        </section>
    </div>
@endsection

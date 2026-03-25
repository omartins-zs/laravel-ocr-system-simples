@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">OCR Simples</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">Importar e processar arquivo</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Envie PDF, PNG, JPG, JPEG ou WEBP. O arquivo entra na fila local e o texto extraido fica salvo no historico.
            </p>

            <form method="POST" action="{{ route('ocr.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="file" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Arquivo</label>
                    <div class="rounded-xl border border-slate-300 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-800/80">
                        <input
                            id="file"
                            name="file"
                            type="file"
                            accept=".pdf,.png,.jpg,.jpeg,.webp"
                            class="sr-only"
                            required
                        >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <label
                                for="file"
                                class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 sm:w-auto"
                            >
                                <i class="fa-solid fa-upload"></i>
                                Selecionar arquivo
                            </label>
                            <p data-file-name class="min-w-0 break-all text-xs text-slate-600 dark:text-slate-300">
                                Nenhum arquivo selecionado
                            </p>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Tipos aceitos: PDF, PNG, JPG, JPEG, WEBP.</p>

                    @error('file')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                >
                    Processar arquivo
                </button>
            </form>

            <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-700/70 dark:bg-sky-900/20 dark:text-sky-200">
                <p class="font-semibold">Para processar na fila local, rode:</p>
                <code class="mt-2 block w-full overflow-x-auto rounded-lg border border-slate-300 bg-slate-900 px-3 py-2 font-mono text-xs text-slate-100 dark:border-slate-600 dark:bg-slate-950">
                    php artisan queue:work --queue=default
                </code>
            </div>
        </section>

        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
            @if ($document)
                data-ocr-live
                data-endpoint="{{ url('/api/history/'.$document->id) }}"
                data-document-id="{{ $document->id }}"
            @endif
        >
            @if ($document)
                @php
                    $statusClasses = match ($document->status) {
                        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                        'processing' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                        default => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                    };
                    $statusLabel = match ($document->status) {
                        'completed' => 'Concluido',
                        'failed' => 'Falhou',
                        'processing' => 'Processando',
                        default => 'Pendente',
                    };
                @endphp

                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Ultimo envio</h2>
                    <span data-ocr-status-badge class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
                </div>
                <p data-ocr-live-state class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    Atualizacao automatica a cada 5 segundos enquanto o processamento nao finalizar.
                </p>

                <dl class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-700">
                        <dt class="font-medium">Arquivo</dt>
                        <dd data-ocr-original-name class="text-right">{{ $document->original_name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-700">
                        <dt class="font-medium">Tipo</dt>
                        <dd data-ocr-mime-type>{{ $document->mime_type }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-700">
                        <dt class="font-medium">Enviado em</dt>
                        <dd data-ocr-created-at>{{ $document->created_at?->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-700">
                        <dt class="font-medium">Atualizado em</dt>
                        <dd data-ocr-updated-at>{{ $document->updated_at?->format('d/m/Y H:i:s') }}</dd>
                    </div>
                </dl>

                <div
                    data-ocr-error-message
                    class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300 {{ $document->status === 'failed' ? '' : 'hidden' }}"
                >{{ $document->error_message }}</div>

                <div class="mt-4">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Texto extraido</p>
                    <pre data-ocr-extracted-text class="mt-2 max-h-80 overflow-auto rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm whitespace-pre-wrap dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">{{ $document->extracted_text ?: 'Aguardando processamento da fila...' }}</pre>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('history.show', $document) }}"
                        class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Ver detalhe completo
                    </a>

                    @if ($document->status === 'failed')
                        <form method="POST" action="{{ route('history.rerun', $document) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/40"
                            >
                                <i class="fa-solid fa-repeat mr-1"></i>
                                Re run
                            </button>
                        </form>
                    @endif
                </div>
            @else
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Resultado</h2>
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                    Apos o upload, o status e o texto extraido aparecem aqui automaticamente.
                </p>
            @endif
        </section>
    </div>
@endsection

@extends('layouts.app')

@section('content')
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

    @if (in_array($document->status, ['pending', 'processing'], true))
        <div data-auto-refresh-seconds="3"></div>
    @endif

    <section class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">Detalhe do historico</p>
                <h1 class="text-2xl font-bold text-slate-900">{{ $document->original_name }}</h1>
            </div>

            <div class="flex items-center gap-2">
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>

                @if ($document->status === 'failed')
                    <form method="POST" action="{{ route('history.rerun', $document) }}">
                        @csrf
                        <button
                            type="submit"
                            title="Reprocessar"
                            class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 1 1-9.73-3.357.75.75 0 1 0-1.164-.946 7 7 0 1 0 12.11 4.803h1.222a.75.75 0 0 0 .53-1.28l-2.25-2.25a.75.75 0 0 0-1.28.53v2.5h.562Z" clip-rule="evenodd" />
                            </svg>
                            Re-run
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Metadados</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-slate-700">Nome original</dt>
                        <dd class="text-slate-600">{{ $document->original_name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700">Tipo</dt>
                        <dd class="text-slate-600">{{ $document->mime_type }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700">Extensao</dt>
                        <dd class="text-slate-600">{{ strtoupper($document->extension) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700">Tamanho</dt>
                        <dd class="text-slate-600">{{ $document->file_size ? number_format($document->file_size / 1024, 2, ',', '.') . ' KB' : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700">Arquivo salvo em</dt>
                        <dd class="break-all text-slate-600">{{ $document->stored_path }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700">Criado em</dt>
                        <dd class="text-slate-600">{{ $document->created_at?->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700">Processado em</dt>
                        <dd class="text-slate-600">{{ $document->processed_at?->format('d/m/Y H:i:s') ?? '-' }}</dd>
                    </div>
                </dl>

                @if ($document->error_message)
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        {{ $document->error_message }}
                    </div>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Texto completo extraido</h2>
                <pre class="mt-4 min-h-80 overflow-auto rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm whitespace-pre-wrap">{{ $document->extracted_text ?: 'Nenhum texto extraido ate o momento.' }}</pre>
            </article>
        </div>

        <a
            href="{{ route('history.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
        >
            Voltar ao historico
        </a>
    </section>
@endsection

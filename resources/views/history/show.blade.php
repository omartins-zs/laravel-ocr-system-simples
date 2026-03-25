@extends('layouts.app')

@section('content')
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

    @if (in_array($document->status, ['pending', 'processing'], true))
        <div data-auto-refresh-seconds="5" class="hidden" aria-hidden="true"></div>
    @endif

    <section class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Detalhe do historico</p>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $document->original_name }}</h1>
            </div>

            <div class="flex items-center gap-2">
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>

                @if ($document->status === 'failed')
                    <form method="POST" action="{{ route('history.rerun', $document) }}">
                        @csrf
                        <button
                            type="submit"
                            title="Reprocessar"
                            class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/40"
                        >
                            <i class="fa-solid fa-repeat mr-1"></i>
                            Re run
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Metadados</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-slate-700 dark:text-slate-200">Nome original</dt>
                        <dd class="text-slate-600 dark:text-slate-300">{{ $document->original_name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700 dark:text-slate-200">Tipo</dt>
                        <dd class="text-slate-600 dark:text-slate-300">{{ $document->mime_type }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700 dark:text-slate-200">Extensao</dt>
                        <dd class="text-slate-600 dark:text-slate-300">{{ strtoupper($document->extension) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700 dark:text-slate-200">Tamanho</dt>
                        <dd class="text-slate-600 dark:text-slate-300">{{ $document->file_size ? number_format($document->file_size / 1024, 2, ',', '.') . ' KB' : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700 dark:text-slate-200">Arquivo salvo em</dt>
                        <dd class="break-all text-slate-600 dark:text-slate-300">{{ $document->stored_path }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700 dark:text-slate-200">Criado em</dt>
                        <dd class="text-slate-600 dark:text-slate-300">{{ $document->created_at?->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-700 dark:text-slate-200">Processado em</dt>
                        <dd class="text-slate-600 dark:text-slate-300">{{ $document->processed_at?->format('d/m/Y H:i:s') ?? '-' }}</dd>
                    </div>
                </dl>

                @if ($document->error_message)
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        {{ $document->error_message }}
                    </div>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Texto completo extraido</h2>
                <pre class="mt-4 min-h-80 overflow-auto rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm whitespace-pre-wrap dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">{{ $document->extracted_text ?: 'Nenhum texto extraido ate o momento.' }}</pre>
            </article>
        </div>

        <a
            href="{{ route('history.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        >
            Voltar ao historico
        </a>
    </section>
@endsection

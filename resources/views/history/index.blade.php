@extends('layouts.app')

@section('content')
    @php
        $hasActiveProcessing = $documents->contains(fn ($item) => in_array($item->status, ['pending', 'processing'], true));
    @endphp

    @if ($hasActiveProcessing)
        <div data-auto-refresh-seconds="5" class="hidden" aria-hidden="true"></div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Historico</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">Arquivos processados</h1>
                @if ($hasActiveProcessing)
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Atualizacao automatica ativa a cada 5 segundos enquanto houver itens pendentes.
                    </p>
                @endif
            </div>

            <form method="GET" action="{{ route('history.index') }}" class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                <input
                    type="text"
                    name="q"
                    value="{{ $query }}"
                    placeholder="Buscar por nome ou status"
                    class="w-full min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 sm:max-w-sm md:w-72 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-400 dark:focus:ring-slate-700"
                >
                <button
                    type="submit"
                    class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600"
                >
                    Buscar
                </button>
            </form>
        </div>

        @if ($documents->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                Nenhum arquivo encontrado no historico.
            </div>
        @else
            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Arquivo</th>
                            <th class="px-4 py-3 font-semibold">Tipo</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Resumo</th>
                            <th class="px-4 py-3 font-semibold">Data</th>
                            <th class="px-4 py-3 font-semibold">Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
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

                            <tr class="border-b border-slate-100 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800/60">
                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $document->original_name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ strtoupper($document->extension) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $document->excerpt(100) }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $document->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a
                                            href="{{ route('history.show', $document) }}"
                                            class="inline-flex rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                        >
                                            Ver detalhe
                                        </a>

                                        @if ($document->status === 'failed')
                                            <form method="POST" action="{{ route('history.rerun', $document) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    title="Reprocessar"
                                                    class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-2 py-1.5 text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/40"
                                                >
                                                    <i class="fa-solid fa-repeat"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $documents->links() }}
            </div>
        @endif
    </section>
@endsection

@extends('layouts.app')

@section('content')
    @php
        $hasActiveProcessing = $documents->contains(fn ($item) => in_array($item->status, ['pending', 'processing'], true));
    @endphp

    @if ($hasActiveProcessing)
        <div data-auto-refresh-seconds="3"></div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-slate-500">Historico</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Arquivos processados</h1>
                @if ($hasActiveProcessing)
                    <p class="mt-1 text-xs text-slate-500">
                        Atualizacao automatica ativa a cada 3 segundos enquanto houver itens pendentes.
                    </p>
                @endif
            </div>

            <form method="GET" action="{{ route('history.index') }}" class="flex w-full gap-2 md:w-auto">
                <input
                    type="text"
                    name="q"
                    value="{{ $query }}"
                    placeholder="Buscar por nome ou status"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 md:w-72"
                >
                <button
                    type="submit"
                    class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
                >
                    Buscar
                </button>
            </form>
        </div>

        @if ($documents->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-600">
                Nenhum arquivo encontrado no historico.
            </div>
        @else
            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600">
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

                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $document->original_name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ strtoupper($document->extension) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $document->excerpt(100) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $document->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a
                                            href="{{ route('history.show', $document) }}"
                                            class="inline-flex rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"
                                        >
                                            Ver detalhe
                                        </a>

                                        @if ($document->status === 'failed')
                                            <form method="POST" action="{{ route('history.rerun', $document) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    title="Reprocessar"
                                                    class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-2 py-1.5 text-emerald-700 transition hover:bg-emerald-100"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 1 1-9.73-3.357.75.75 0 1 0-1.164-.946 7 7 0 1 0 12.11 4.803h1.222a.75.75 0 0 0 .53-1.28l-2.25-2.25a.75.75 0 0 0-1.28.53v2.5h.562Z" clip-rule="evenodd" />
                                                    </svg>
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

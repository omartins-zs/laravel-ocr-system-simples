<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <header class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 text-white shadow-lg">
        <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ route('ocr.index') }}" class="text-lg font-semibold tracking-tight">Laravel OCR System Simples</a>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('ocr.index') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('ocr.*') ? 'bg-white/20' : 'hover:bg-white/10' }}"
                >
                    OCR
                </a>
                <a
                    href="{{ route('history.index') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('history.*') ? 'bg-white/20' : 'hover:bg-white/10' }}"
                >
                    Histórico
                </a>
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>

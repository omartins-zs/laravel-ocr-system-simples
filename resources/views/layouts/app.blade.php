<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
    <header class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 text-white shadow-lg dark:from-slate-950 dark:via-slate-900 dark:to-slate-800">
        <nav class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4">
            <a href="{{ route('ocr.index') }}" class="text-lg font-semibold tracking-tight">Laravel OCR System Simples</a>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <button
                    type="button"
                    data-refresh-now
                    class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-3 py-2 text-sm font-medium transition hover:bg-white/10"
                    title="Atualizar pagina"
                >
                    <i class="fa-solid fa-arrows-rotate text-sm"></i>
                    <span class="hidden sm:inline">Atualizar</span>
                </button>

                <button
                    id="theme-toggle"
                    type="button"
                    class="inline-flex items-center rounded-lg border border-white/30 px-3 py-2 text-sm font-medium transition hover:bg-white/10"
                    title="Alternar tema"
                >
                    <i id="theme-toggle-icon" class="fa-solid fa-moon text-sm"></i>
                </button>

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
                    Historico
                </a>
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8">
        <div data-auto-refresh-label class="mb-4 hidden rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"></div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>

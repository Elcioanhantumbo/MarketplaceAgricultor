<!DOCTYPE html>
<html lang="pt-MZ">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AgroLink MZ' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <div class="min-h-screen">
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3">
                <a href="{{ url('/') }}" class="text-lg font-semibold text-green-700">AgroLink MZ</a>

                @auth
                    <div class="flex items-center gap-3 text-sm">
                        <span class="text-stone-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-stone-500 hover:text-stone-800">Sair</button>
                        </form>
                    </div>
                @endauth
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
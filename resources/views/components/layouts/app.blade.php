<!DOCTYPE html>
<html lang="pt-MZ">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#15803d">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>{{ $title ?? 'AgroLink MZ' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <div class="min-h-screen" x-data="{ menuOpen: false }">
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3">
                <a href="{{ url('/') }}" class="text-lg font-semibold text-green-700">AgroLink MZ</a>

                <nav class="hidden items-center gap-4 text-sm sm:flex">
                    <a href="{{ route('ofertas') }}" wire:navigate class="text-stone-600 hover:text-green-700">Ofertas</a>
                    <a href="{{ route('categorias') }}" wire:navigate class="text-stone-600 hover:text-green-700">Categorias</a>

                    @auth
                        <a href="{{ route('notificacoes') }}" wire:navigate class="text-stone-600 hover:text-green-700">Notificações</a>
                        <span class="text-stone-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-stone-500 hover:text-stone-800">Sair</button>
                        </form>
                    @endauth
                </nav>

                <button @click="menuOpen = !menuOpen" class="p-2 text-stone-600 sm:hidden" aria-label="Menu">
                    <svg x-show="!menuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="menuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav x-show="menuOpen" x-cloak @click.outside="menuOpen = false" class="border-t border-stone-100 px-4 py-3 text-sm sm:hidden">
                <a href="{{ route('ofertas') }}" wire:navigate class="block py-2 text-stone-600 hover:text-green-700">Ofertas</a>
                <a href="{{ route('categorias') }}" wire:navigate class="block py-2 text-stone-600 hover:text-green-700">Categorias</a>

                @auth
                    <a href="{{ route('notificacoes') }}" wire:navigate class="block py-2 text-stone-600 hover:text-green-700">Notificações</a>
                    <div class="mt-2 border-t border-stone-100 pt-2">
                        <span class="block py-1 text-stone-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="py-2 text-stone-500 hover:text-stone-800">Sair</button>
                        </form>
                    </div>
                @endauth
            </nav>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
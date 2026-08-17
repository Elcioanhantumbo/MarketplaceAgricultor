<!DOCTYPE html>
<html lang="pt-MZ">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#15803d">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>{{ $title ?? 'AgroLink MZ' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
    <div class="min-h-screen" x-data="{ menuOpen: false }">
        <header class="sticky top-0 z-10 border-b border-stone-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3 sm:px-6">
                <a href="{{ url('/') }}" wire:navigate class="flex items-center gap-2 text-base font-semibold text-stone-900">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-green-700 text-sm text-white">A</span>
                    AgroLink <span class="text-green-700">MZ</span>
                </a>

                <nav class="hidden items-center gap-1 text-sm sm:flex">
                    <a href="{{ route('ofertas') }}" wire:navigate class="rounded-lg px-3 py-2 font-medium transition {{ request()->routeIs('ofertas*') ? 'text-green-700' : 'text-stone-600 hover:text-stone-900' }}">Ofertas</a>
                    <a href="{{ route('categorias') }}" wire:navigate class="rounded-lg px-3 py-2 font-medium transition {{ request()->routeIs('categorias') ? 'text-green-700' : 'text-stone-600 hover:text-stone-900' }}">Categorias</a>

                    @auth
                        <a href="{{ route('painel') }}" wire:navigate class="rounded-lg px-3 py-2 font-medium transition {{ request()->routeIs('painel') ? 'text-green-700' : 'text-stone-600 hover:text-stone-900' }}">Painel</a>
                        <a href="{{ route('notificacoes') }}" wire:navigate class="rounded-lg px-3 py-2 font-medium text-stone-600 transition hover:text-stone-900">Notificações</a>

                        <div class="ml-2 flex items-center gap-3 border-l border-stone-200 pl-3">
                            <a href="{{ route('perfil') }}" wire:navigate class="flex items-center gap-2 text-stone-700 hover:text-stone-900">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-stone-100 text-xs font-medium text-stone-500">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <span class="max-w-[8rem] truncate font-medium">{{ auth()->user()->name }}</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-stone-400 hover:text-stone-700" title="Sair">Sair</button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="rounded-lg px-3 py-2 font-medium text-stone-600 hover:text-stone-900">Entrar</a>
                        <a href="{{ route('registo') }}" wire:navigate class="rounded-lg bg-green-700 px-3.5 py-2 font-medium text-white shadow-sm hover:bg-green-800">Criar conta</a>
                    @endauth
                </nav>

                <button @click="menuOpen = !menuOpen" class="rounded-lg p-2 text-stone-600 hover:bg-stone-100 sm:hidden" aria-label="Menu">
                    <svg x-show="!menuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="menuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav x-show="menuOpen" x-cloak @click.outside="menuOpen = false" class="border-t border-stone-100 px-4 py-3 text-sm sm:hidden">
                <a href="{{ route('ofertas') }}" wire:navigate class="block rounded-lg px-2 py-2 font-medium text-stone-600 hover:bg-stone-50">Ofertas</a>
                <a href="{{ route('categorias') }}" wire:navigate class="block rounded-lg px-2 py-2 font-medium text-stone-600 hover:bg-stone-50">Categorias</a>

                @auth
                    <a href="{{ route('painel') }}" wire:navigate class="block rounded-lg px-2 py-2 font-medium text-stone-600 hover:bg-stone-50">Painel</a>
                    <a href="{{ route('notificacoes') }}" wire:navigate class="block rounded-lg px-2 py-2 font-medium text-stone-600 hover:bg-stone-50">Notificações</a>
                    <a href="{{ route('perfil') }}" wire:navigate class="block rounded-lg px-2 py-2 font-medium text-stone-600 hover:bg-stone-50">Perfil</a>
                    <div class="mt-2 border-t border-stone-100 pt-2">
                        <span class="block px-2 py-1 text-xs text-stone-400">Sessão de {{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-lg px-2 py-2 text-left font-medium text-stone-600 hover:bg-stone-50">Sair</button>
                        </form>
                    </div>
                @else
                    <div class="mt-2 flex gap-2 border-t border-stone-100 pt-3">
                        <a href="{{ route('login') }}" wire:navigate class="flex-1 rounded-lg border border-stone-300 px-3 py-2 text-center font-medium text-stone-700">Entrar</a>
                        <a href="{{ route('registo') }}" wire:navigate class="flex-1 rounded-lg bg-green-700 px-3 py-2 text-center font-medium text-white">Criar conta</a>
                    </div>
                @endauth
            </nav>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>

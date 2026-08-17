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
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ url('/') }}" wire:navigate class="mb-6 flex items-center gap-2 text-lg font-semibold text-stone-900">
            <span class="flex h-8 w-8 items-center justify-center rounded-md bg-green-700 text-sm text-white">A</span>
            AgroLink <span class="text-green-700">MZ</span>
        </a>

        <div class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-center text-xs text-stone-400">Corredor Dondo/Nhamatanda — Beira</p>
    </div>

    @livewireScripts
</body>
</html>

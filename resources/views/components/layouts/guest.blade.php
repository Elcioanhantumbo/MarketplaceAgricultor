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
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ url('/') }}" class="mb-6 text-xl font-semibold text-green-700">AgroLink MZ</a>

        <div class="w-full max-w-sm rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
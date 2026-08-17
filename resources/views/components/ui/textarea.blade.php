@props(['name' => null])

@php
    $invalid = $name && $errors->has($name);
@endphp

<textarea @if($name) id="{{ $name }}" @endif {{ $attributes->class([
    'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400',
    'focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-stone-50 disabled:text-stone-400',
    'border-red-300 focus:border-red-500 focus:ring-red-500/20' => $invalid,
    'border-stone-300 focus:border-green-600 focus:ring-green-600/20' => ! $invalid,
]) }}>{{ $slot }}</textarea>

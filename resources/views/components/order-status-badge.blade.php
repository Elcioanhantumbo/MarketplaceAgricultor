@props(['status'])

@php
    $labels = [
        'pendente' => 'Pendente',
        'aceite' => 'Aceite',
        'em_preparacao' => 'Em preparação',
        'pronto' => 'Pronto',
        'em_transporte' => 'Em transporte',
        'entregue' => 'Entregue',
        'concluido' => 'Concluído',
        'rejeitado' => 'Rejeitado',
        'cancelado' => 'Cancelado',
    ];

    $colors = [
        'pendente' => 'bg-amber-100 text-amber-800',
        'aceite' => 'bg-blue-100 text-blue-800',
        'em_preparacao' => 'bg-blue-100 text-blue-800',
        'pronto' => 'bg-blue-100 text-blue-800',
        'em_transporte' => 'bg-blue-100 text-blue-800',
        'entregue' => 'bg-green-100 text-green-800',
        'concluido' => 'bg-green-100 text-green-800',
        'rejeitado' => 'bg-red-100 text-red-700',
        'cancelado' => 'bg-stone-200 text-stone-600',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'rounded-full px-2 py-0.5 text-xs font-medium ' . ($colors[$status] ?? 'bg-stone-100 text-stone-600')]) }}>
    {{ $labels[$status] ?? ucfirst($status) }}
</span>
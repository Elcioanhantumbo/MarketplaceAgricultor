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
        'pendente' => 'amber',
        'aceite' => 'blue',
        'em_preparacao' => 'blue',
        'pronto' => 'blue',
        'em_transporte' => 'blue',
        'entregue' => 'green',
        'concluido' => 'green',
        'rejeitado' => 'red',
        'cancelado' => 'stone',
    ];
@endphp

<x-ui.badge :color="$colors[$status] ?? 'stone'" {{ $attributes }}>
    {{ $labels[$status] ?? ucfirst($status) }}
</x-ui.badge>
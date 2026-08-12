<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'producer_id', 'farm_id', 'product_id', 'quantity', 'unit', 'price',
    'available_from', 'available_until', 'latitude', 'longitude', 'status',
])]
class ProductListing extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'available_from' => 'date',
            'available_until' => 'date',
        ];
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** RN08/RN17 — apenas ofertas disponíveis e dentro do período de validade. */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'disponivel')
            ->whereDate('available_until', '>=', now());
    }

    /**
     * Filtra por proximidade a um ponto (fórmula de Haversine), enquanto o
     * PostGIS não está disponível para esta versão do PostgreSQL — ver
     * docs/ROADMAP.md (Fase 2/6). Ofertas sem localização ficam de fora.
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        $haversine = '6371 * acos(least(1, greatest(-1,
            cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?))
            + sin(radians(?)) * sin(radians(latitude))
        )))';

        // O filtro repete a expressão no WHERE (em vez de usar o alias do
        // SELECT num HAVING) porque a subquery de contagem da paginação do
        // Laravel perde a visibilidade de alias definidos via selectRaw.
        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("({$haversine}) <= ?", [$lat, $lng, $lat, $radiusKm])
            ->selectRaw("product_listings.*, ({$haversine}) as distance_km", [$lat, $lng, $lat])
            ->orderByRaw("({$haversine})", [$lat, $lng, $lat]);
    }
}
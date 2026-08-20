<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
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
    use HasGeoLocation;

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
     * Filtra por proximidade real a um ponto, usando PostGIS
     * (ST_DWithin/ST_Distance sobre a coluna geography `geo_location`).
     * Substitui a fórmula de Haversine usada nas Fases 2/6 enquanto não
     * havia build do PostGIS para esta versão do PostgreSQL no Windows —
     * ver docs/ROADMAP.md. Ofertas sem localização ficam de fora.
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm): Builder
    {
        $point = 'ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography';
        $radiusMeters = $radiusKm * 1000;

        // O filtro repete a expressão no WHERE (em vez de usar o alias do
        // SELECT num HAVING) porque a subquery de contagem da paginação do
        // Laravel perde a visibilidade de alias definidos via selectRaw.
        return $query
            ->whereNotNull('geo_location')
            ->whereRaw("ST_DWithin(geo_location, {$point}, ?)", [$lng, $lat, $radiusMeters])
            ->selectRaw("product_listings.*, ST_Distance(geo_location, {$point}) / 1000 as distance_km", [$lng, $lat])
            ->orderByRaw("ST_Distance(geo_location, {$point})", [$lng, $lat]);
    }
}
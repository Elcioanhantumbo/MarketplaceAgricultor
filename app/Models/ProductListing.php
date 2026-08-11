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
}
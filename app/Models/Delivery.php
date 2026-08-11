<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'transporter_id', 'origin_lat', 'origin_lng', 'dest_lat', 'dest_lng',
    'weight_estimate', 'cost', 'status', 'pickup_at', 'delivered_at',
])]
class Delivery extends Model
{
    protected function casts(): array
    {
        return [
            'origin_lat' => 'decimal:7',
            'origin_lng' => 'decimal:7',
            'dest_lat' => 'decimal:7',
            'dest_lng' => 'decimal:7',
            'weight_estimate' => 'decimal:2',
            'cost' => 'decimal:2',
            'pickup_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }
}
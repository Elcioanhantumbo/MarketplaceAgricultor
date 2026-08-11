<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transporter_id', 'plate', 'type', 'capacity_kg'])]
class Vehicle extends Model
{
    protected function casts(): array
    {
        return [
            'capacity_kg' => 'decimal:2',
        ];
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }
}
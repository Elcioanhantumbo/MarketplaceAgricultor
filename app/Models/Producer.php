<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'business_name'])]
class Producer extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class);
    }

    public function productListings(): HasMany
    {
        return $this->hasMany(ProductListing::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** RN02 — perfil mínimo completo e pelo menos uma propriedade registada. */
    public function isReadyToPublish(): bool
    {
        return $this->user->hasMinimumProfile() && $this->farms()->exists();
    }
}
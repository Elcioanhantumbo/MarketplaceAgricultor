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

    /** RN13 — reputação a partir das avaliações recebidas pelo produtor. */
    public function reviews()
    {
        return Review::where('reviewee_id', $this->user_id);
    }

    public function averageRating(): ?float
    {
        return $this->reviews()->avg('rating');
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }
}
<?php

namespace App\Policies;

use App\Models\ProductListing;
use App\Models\User;

class ProductListingPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'producer' && $user->producer->isReadyToPublish();
    }

    public function update(User $user, ProductListing $listing): bool
    {
        return $listing->producer->user_id === $user->id;
    }
}
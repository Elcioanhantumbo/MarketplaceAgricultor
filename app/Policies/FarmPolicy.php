<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;

class FarmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'producer';
    }

    public function view(User $user, Farm $farm): bool
    {
        return $farm->producer->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'producer';
    }

    public function update(User $user, Farm $farm): bool
    {
        return $farm->producer->user_id === $user->id;
    }

    public function delete(User $user, Farm $farm): bool
    {
        return $farm->producer->user_id === $user->id;
    }
}
<?php

namespace App\Policies;

use App\Models\Bed;
use App\Models\User;

class BedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-beds');
    }

    public function view(User $user, Bed $bed): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-beds');
    }

    public function update(User $user, Bed $bed): bool
    {
        return $user->hasPermission('manage-beds');
    }

    public function assign(User $user, Bed $bed): bool
    {
        return $user->hasPermission('manage-beds');
    }

    public function reserve(User $user, Bed $bed): bool
    {
        return $user->hasPermission('manage-beds');
    }

    public function release(User $user, Bed $bed): bool
    {
        return $user->hasPermission('manage-beds');
    }

    public function delete(User $user, Bed $bed): bool
    {
        return $user->hasPermission('manage-beds');
    }
}

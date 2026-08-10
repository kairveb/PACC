<?php

namespace App\Policies;

use App\Models\Encounter;
use App\Models\User;

class EncounterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-encounters');
    }

    public function view(User $user, Encounter $encounter): bool
    {
        if ($user->hasPermission('view-encounters')) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-encounters');
    }

    public function update(User $user, Encounter $encounter): bool
    {
        if ($user->hasPermission('update-encounters')) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Encounter $encounter): bool
    {
        return $user->hasPermission('delete-patients');
    }
}

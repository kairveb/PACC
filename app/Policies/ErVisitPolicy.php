<?php

namespace App\Policies;

use App\Models\ErVisit;
use App\Models\User;

class ErVisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hospital-admin', 'nurse', 'doctor']);
    }

    public function view(User $user, ErVisit $visit): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hospital-admin', 'nurse']);
    }

    public function triage(User $user, ErVisit $visit): bool
    {
        // Clinical priority must remain under qualified healthcare staff (nurse/doctor)
        return $user->hasAnyRole(['super-admin', 'hospital-admin', 'nurse', 'doctor']);
    }

    public function update(User $user, ErVisit $visit): bool
    {
        return $user->hasAnyRole(['super-admin', 'hospital-admin', 'nurse']);
    }
}

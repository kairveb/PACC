<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-patients');
    }

    public function view(User $user, Patient $patient): bool
    {
        if ($user->hasPermission('view-patients')) {
            return true;
        }

        if ($user->isPatient() && $user->patient()?->id === $patient->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-patients');
    }

    public function update(User $user, Patient $patient): bool
    {
        if ($user->hasPermission('update-patients')) {
            return true;
        }

        if ($user->isPatient() && $user->patient()?->id === $patient->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasPermission('delete-patients');
    }

    public function verify(User $user, Patient $patient): bool
    {
        return $user->hasPermission('verify-patients');
    }
}

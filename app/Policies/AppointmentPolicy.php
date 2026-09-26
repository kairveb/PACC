<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-appointments');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasPermission('view-appointments')) {
            return true;
        }

        if ($user->isPatient() && $user->patient?->id === $appointment->patient_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasRole('registration');
    }

    public function book(User $user): bool
    {
        return $this->create($user);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isSuperAdmin() || $user->hasRole('registration')) {
            return true;
        }

        if ($user->hasRole('doctor') && $user->provider?->id === $appointment->provider_id) {
            return true;
        }

        if ($user->hasRole('nurse') && in_array($appointment->status, [
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_CHECKED_IN,
            Appointment::STATUS_IN_CONSULTATION,
        ], true)) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->hasPermission('cancel-appointments')) {
            return true;
        }

        if ($user->isPatient() && $user->patient?->id === $appointment->patient_id) {
            return in_array($appointment->status, ['PENDING', 'CONFIRMED']);
        }

        return false;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasPermission('delete-appointments');
    }
}

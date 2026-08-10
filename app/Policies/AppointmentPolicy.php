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

        if ($user->isPatient() && $user->patient()?->id === $appointment->patient_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-appointments');
    }

    public function book(User $user): bool
    {
        return $user->hasPermission('create-appointments');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->hasPermission('update-appointments');
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->hasPermission('cancel-appointments')) {
            return true;
        }

        if ($user->isPatient() && $user->patient()?->id === $appointment->patient_id) {
            return in_array($appointment->status, ['PENDING', 'CONFIRMED']);
        }

        return false;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasPermission('delete-appointments');
    }
}

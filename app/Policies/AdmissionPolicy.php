<?php

namespace App\Policies;

use App\Models\Admission;
use App\Models\User;

class AdmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hospital-admin', 'admission', 'nurse', 'doctor']);
    }

    public function view(User $user, Admission $admission): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hospital-admin', 'admission']);
    }

    public function update(User $user, Admission $admission): bool
    {
        return $user->hasAnyRole(['super-admin', 'hospital-admin', 'admission']);
    }

    public function discharge(User $user, Admission $admission): bool
    {
        return $user->hasAnyRole(['super-admin', 'hospital-admin', 'admission']);
    }

    public function delete(User $user, Admission $admission): bool
    {
        return $user->hasRole('super-admin');
    }
}

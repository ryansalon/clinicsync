<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'receptionist', 'physician']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * Receptionists may view demographics but clinical records are
     * withheld at the controller layer per the access matrix.
     */
    public function view(User $user, Patient $patient): bool
    {
        if ($user->hasAnyRole(['administrator', 'receptionist'])) {
            return true;
        }

        if ($user->hasRole('physician')) {
            return $patient->appointments()
                ->where('physician_id', $user->physician?->id)
                ->exists();
        }

        return $user->patient?->id === $patient->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'receptionist']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $user->hasAnyRole(['administrator', 'receptionist']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole('administrator');
    }
}

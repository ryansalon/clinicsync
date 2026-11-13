<?php

namespace App\Policies;

use App\Models\Encounter;
use App\Models\User;

class EncounterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'physician']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * Receptionists are excluded by design: encounter notes are clinical data.
     */
    public function view(User $user, Encounter $encounter): bool
    {
        if ($user->hasRole('administrator')) {
            return true;
        }

        if ($user->hasRole('physician')) {
            return $encounter->physician_id === $user->physician?->id;
        }

        if ($user->hasRole('patient')) {
            return $encounter->patient_id === $user->patient?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('physician');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Encounter $encounter): bool
    {
        if ($user->hasRole('administrator')) {
            return true;
        }

        return $user->hasRole('physician')
            && $encounter->physician_id === $user->physician?->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole('administrator');
    }
}

<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'receptionist', 'physician', 'patient']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasAnyRole(['administrator', 'receptionist'])) {
            return true;
        }

        if ($user->hasRole('patient')) {
            return $appointment->patient_id === $user->patient?->id;
        }

        if ($user->hasRole('physician')) {
            return $appointment->physician_id === $user->physician?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['patient', 'administrator', 'receptionist']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->hasAnyRole(['administrator', 'receptionist'])) {
            return true;
        }

        if ($user->hasRole('patient')) {
            return $appointment->patient_id === $user->patient?->id && $appointment->isCancellable();
        }

        if ($user->hasRole('physician')) {
            return $appointment->physician_id === $user->physician?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole('administrator');
    }
}

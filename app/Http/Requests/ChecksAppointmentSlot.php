<?php

namespace App\Http\Requests;

use App\Models\Physician;
use App\Rules\AppointmentSlotAvailable;
use Illuminate\Validation\Validator;

/**
 * Shared slot-availability check used by every request that books or moves an
 * appointment, so the guard and rule invocation live in exactly one place.
 */
trait ChecksAppointmentSlot
{
    /**
     * Run the availability rule unless an earlier rule already failed.
     *
     * @param  int|null  $exceptAppointmentId  Appointment allowed to keep its own slot
     */
    private function checkSlot(
        Validator $validator,
        Physician $physician,
        string $date,
        string $startTime,
        ?int $exceptAppointmentId = null,
    ): void {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        (new AppointmentSlotAvailable($physician, $exceptAppointmentId))->check(
            $date,
            $startTime,
            $validator,
        );
    }
}

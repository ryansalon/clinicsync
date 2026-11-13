<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Services\PhysicianAvailability;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class RescheduleAppointmentRequest extends FormRequest
{
    use ChecksAppointmentSlot;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('patient') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:+'.PhysicianAvailability::BOOKING_HORIZON_DAYS.' days',
            ],
            'start_time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'appointment_date.after_or_equal' => 'Appointments can only be moved to today or a future date.',
            'appointment_date.before_or_equal' => 'Appointments can only be moved up to '.PhysicianAvailability::BOOKING_HORIZON_DAYS.' days ahead.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $appointment = $this->route('appointment');

            if (! $appointment instanceof Appointment) {
                return;
            }

            // The appointment itself is excluded so keeping the current slot is allowed.
            $this->checkSlot(
                $validator,
                $appointment->physician,
                (string) $this->input('appointment_date'),
                (string) $this->input('start_time'),
                $appointment->id,
            );

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $date = (string) $this->input('appointment_date');
            $startTime = (string) $this->input('start_time');

            if (Carbon::parse($date)->setTimeFromTimeString($startTime)->isPast()) {
                $validator->errors()->add(
                    'start_time',
                    'Please choose a time later than now, or pick another date.',
                );
            }
        });
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Physician;
use App\Services\PhysicianAvailability;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'physician_id' => ['required', 'integer', 'exists:physicians,id'],
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:+'.PhysicianAvailability::BOOKING_HORIZON_DAYS.' days',
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'chief_complaint' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'appointment_date.after_or_equal' => 'Appointments can only be booked for today or a future date.',
            'appointment_date.before_or_equal' => 'Appointments can only be booked up to '.PhysicianAvailability::BOOKING_HORIZON_DAYS.' days ahead.',
        ];
    }

    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $physician = Physician::find($this->input('physician_id'));

            if ($physician === null) {
                return;
            }

            $this->checkSlot(
                $validator,
                $physician,
                (string) $this->input('appointment_date'),
                (string) $this->input('start_time'),
            );
        });
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Models\Physician;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    use ChecksAppointmentSlot;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['administrator', 'receptionist']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(Appointment::STATUSES)],
            'physician_id' => ['nullable', 'integer', 'exists:physicians,id'],
            'appointment_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * When the slot itself is being moved, re-run the availability check
     * against the appointment being edited rather than treating it as taken.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('start_time') === null) {
                return;
            }

            /** @var Appointment $appointment */
            $appointment = $this->route('appointment');

            $physicianId = (int) ($this->input('physician_id') ?? $appointment->physician_id);
            $date = (string) ($this->input('appointment_date') ?? $appointment->appointment_date->toDateString());

            $physician = Physician::find($physicianId);

            if ($physician === null) {
                return;
            }

            $this->checkSlot(
                $validator,
                $physician,
                $date,
                (string) $this->input('start_time'),
                $appointment->id,
            );
        });
    }
}

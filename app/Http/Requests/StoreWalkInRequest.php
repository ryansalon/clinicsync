<?php

namespace App\Http\Requests;

use App\Models\Physician;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalkInRequest extends FormRequest
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
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'physician_id' => ['required', 'integer', 'exists:physicians,id'],
            'appointment_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'chief_complaint' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

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

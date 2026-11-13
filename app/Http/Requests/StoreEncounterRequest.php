<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEncounterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('physician') ?? false;
    }

    /**
     * Accept height as feet/inches and normalize it to centimeters when the
     * browser did not already submit a computed height_cm value.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('height_cm') && ($this->filled('height_feet') || $this->filled('height_inches'))) {
            $feet = (float) $this->input('height_feet', 0);
            $inches = (float) $this->input('height_inches', 0);

            $this->merge([
                'height_cm' => round($feet * 30.48 + $inches * 2.54, 1),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chief_complaint' => ['nullable', 'string', 'max:255'],
            'diagnosis' => ['required', 'string', 'max:255'],
            'diagnosis_code' => ['nullable', 'string', 'max:20'],
            'assessment' => ['nullable', 'string', 'max:5000'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'clinical_notes' => ['nullable', 'string', 'max:5000'],
            'temperature_c' => ['nullable', 'numeric', 'between:25,45'],
            'systolic_bp' => ['nullable', 'integer', 'between:50,300'],
            'diastolic_bp' => ['nullable', 'integer', 'between:30,200'],
            'heart_rate' => ['nullable', 'integer', 'between:20,250'],
            'respiratory_rate' => ['nullable', 'integer', 'between:5,80'],
            'weight_kg' => ['nullable', 'numeric', 'between:1,500'],
            'height_feet' => ['nullable', 'integer', 'between:0,8'],
            'height_inches' => ['nullable', 'numeric', 'between:0,11.9'],
            'height_cm' => ['nullable', 'numeric', 'between:30,260'],
            'spo2' => ['nullable', 'integer', 'between:40,100'],
            'mark_completed' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('physician') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'medication_name' => ['required', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:50'],
            'dosage_form' => ['nullable', 'string', 'max:50'],
            'dosage' => ['nullable', 'string', 'max:50'],
            'frequency' => ['nullable', 'string', 'max:50'],
            'route' => ['nullable', 'string', 'max:50'],
            'duration' => ['nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

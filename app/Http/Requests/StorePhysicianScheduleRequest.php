<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePhysicianScheduleRequest extends FormRequest
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
        $physicianId = $this->user()->physician?->id;

        return [
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => [
                'required',
                'date_format:H:i',
                'before:end_time',
                Rule::unique('physician_schedules', 'start_time')
                    ->where('physician_id', $physicianId)
                    ->where('day_of_week', (int) $this->input('day_of_week'))
                    ->ignore($this->route('schedule')?->id),
            ],
            'end_time' => ['required', 'date_format:H:i'],
            'is_active' => ['nullable', 'boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}

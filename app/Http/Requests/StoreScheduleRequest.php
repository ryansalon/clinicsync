<?php

namespace App\Http\Requests;

use App\Services\PhysicianAvailability;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreScheduleRequest extends FormRequest
{
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
            'physician_id' => ['required', 'integer', 'exists:physicians,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i', 'before:end_time'],
            'end_time' => ['required', 'date_format:H:i'],
            'is_active' => ['nullable', 'boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $overlaps = app(PhysicianAvailability::class)->hasOverlappingSchedule(
                    (int) $this->input('physician_id'),
                    (int) $this->input('day_of_week'),
                    (string) $this->input('start_time'),
                    (string) $this->input('end_time'),
                    $this->input('effective_from') ?: null,
                    $this->input('effective_until') ?: null,
                    $this->route('schedule')?->id,
                );

                if ($overlaps) {
                    $validator->errors()->add('start_time', 'This window overlaps an existing schedule for that day.');
                }
            },
        ];
    }
}

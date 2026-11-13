<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vital extends Model
{
    /** @use HasFactory<VitalFactory> */
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'temperature_c',
        'systolic_bp',
        'diastolic_bp',
        'heart_rate',
        'respiratory_rate',
        'weight_kg',
        'height_cm',
        'spo2',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'temperature_c' => 'decimal:1',
            'systolic_bp' => 'integer',
            'diastolic_bp' => 'integer',
            'heart_rate' => 'integer',
            'respiratory_rate' => 'integer',
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:1',
            'spo2' => 'integer',
        ];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function getBmiAttribute(): ?float
    {
        if (! $this->weight_kg || ! $this->height_cm) {
            return null;
        }

        $heightM = $this->height_cm / 100;

        return round($this->weight_kg / ($heightM * $heightM), 1);
    }
}

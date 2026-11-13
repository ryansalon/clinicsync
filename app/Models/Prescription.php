<?php

namespace App\Models;

use Database\Factories\PrescriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    /** @use HasFactory<PrescriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'physician_id',
        'prescription_number',
        'medication_name',
        'strength',
        'dosage_form',
        'dosage',
        'frequency',
        'route',
        'duration',
        'quantity',
        'instructions',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'quantity' => 'integer',
        ];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class);
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }
}

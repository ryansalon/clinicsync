<?php

namespace App\Models;

use Database\Factories\EncounterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Encounter extends Model
{
    /** @use HasFactory<EncounterFactory> */
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'patient_id',
        'physician_id',
        'appointment_id',
        'encounter_number',
        'encountered_at',
        'chief_complaint',
        'diagnosis',
        'diagnosis_code',
        'assessment',
        'treatment_plan',
        'clinical_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'encountered_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function vitals(): HasOne
    {
        return $this->hasOne(Vital::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}

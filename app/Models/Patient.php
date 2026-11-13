<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'patient_number',
        'date_of_birth',
        'sex',
        'civil_status',
        'address',
        'blood_type',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'guardian_name',
        'guardian_phone',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function medicalHistories(): HasMany
    {
        return $this->hasMany(MedicalHistory::class);
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class);
    }

    public function activeAllergies(): HasMany
    {
        return $this->allergies()->where('is_active', true);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }
}

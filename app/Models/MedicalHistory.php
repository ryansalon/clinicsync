<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalHistory extends Model
{
    /** @use HasFactory<MedicalHistoryFactory> */
    use HasFactory;

    public const TYPES = ['past_medical', 'past_surgical', 'family', 'social'];

    protected $fillable = [
        'patient_id',
        'type',
        'condition_name',
        'year_diagnosed',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year_diagnosed' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

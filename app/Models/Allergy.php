<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allergy extends Model
{
    /** @use HasFactory<AllergyFactory> */
    use HasFactory;

    public const SEVERITIES = ['mild', 'moderate', 'severe'];

    protected $fillable = [
        'patient_id',
        'allergen',
        'reaction',
        'severity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

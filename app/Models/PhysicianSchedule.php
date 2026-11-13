<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicianSchedule extends Model
{
    protected $fillable = [
        'physician_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
        'effective_from',
        'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay(Builder $query, int $dayOfWeek): Builder
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function scopeEffectiveOn(Builder $query, string $date): Builder
    {
        return $query->where(function (Builder $builder) use ($date) {
            $builder->whereNull('effective_from')
                ->orWhere('effective_from', '<=', $date);
        })->where(function (Builder $builder) use ($date) {
            $builder->whereNull('effective_until')
                ->orWhere('effective_until', '>=', $date);
        });
    }
}

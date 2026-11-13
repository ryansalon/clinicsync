<?php

namespace App\Models;

use Database\Factories\PhysicianFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Physician extends Model
{
    /** @use HasFactory<PhysicianFactory> */
    use HasFactory;

    /** Cache key for the physician roster used by admin pickers. */
    private const ROSTER_CACHE_KEY = 'physicians:roster';

    protected $fillable = [
        'user_id',
        'physician_number',
        'specialization',
        'license_number',
        'photo_path',
        'bio',
        'appointment_slot_minutes',
        'daily_patient_limit',
    ];

    protected function casts(): array
    {
        return [
            'appointment_slot_minutes' => 'integer',
            'daily_patient_limit' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PhysicianSchedule::class);
    }

    public function activeSchedules(): HasMany
    {
        return $this->schedules()->where('is_active', true);
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

    /**
     * Every physician with their user, cached because admin schedule and
     * walk-in pickers re-read the same roster on each page load.
     *
     * @return Collection<int, Physician>
     */
    public static function cachedRoster(): Collection
    {
        return Cache::remember(
            self::ROSTER_CACHE_KEY,
            now()->addMinutes(10),
            fn () => self::query()->with('user')->orderBy('id')->get(),
        );
    }

    /**
     * Forget the roster after a physician is created so pickers see them.
     */
    public static function flushRosterCache(): void
    {
        Cache::forget(self::ROSTER_CACHE_KEY);
    }
}

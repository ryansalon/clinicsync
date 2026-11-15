<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_NO_SHOW = 'no_show';

    public const SOURCES = ['online', 'walk_in'];

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
        self::STATUS_NO_SHOW,
    ];

    protected $fillable = [
        'patient_id',
        'physician_id',
        'appointment_date',
        'start_time',
        'end_time',
        'source',
        'status',
        'chief_complaint',
        'reason',
        'queue_number',
        'booked_by',
        'handled_by',
        'called_at',
        'started_at',
        'finished_at',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'queue_number' => 'integer',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * Store appointment times in a single H:i:s shape so slot overlap
     * comparisons (which are string-based in SQL) behave predictably.
     */
    protected function startTime(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $this->normalizeTime($value),
        );
    }

    protected function endTime(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $this->normalizeTime($value),
        );
    }

    private function normalizeTime(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return Carbon::parse($value)->format('H:i:s');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class);
    }

    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function encounter(): HasOne
    {
        return $this->hasOne(Encounter::class);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        $day = Carbon::parse($date)->startOfDay();

        return $query
            ->where('appointment_date', '>=', $day->toDateString())
            ->where('appointment_date', '<', $day->copy()->addDay()->toDateString());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeForPhysician(Builder $query, int $physicianId): Builder
    {
        return $query->where('physician_id', $physicianId);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true)
            && $this->appointment_date->isFuture();
    }
}

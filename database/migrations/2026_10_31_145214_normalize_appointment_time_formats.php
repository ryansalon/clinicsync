<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Slot overlap checks compare times as strings in SQL, so every stored
        // time must share one shape. Legacy rows wrote start_time as H:i while
        // end_time was H:i:s, which made '09:00' < '09:00:00' evaluate true and
        // wrongly reject the slot immediately before a booking.
        DB::table('appointments')
            ->where(function ($query) {
                $query->whereNull('start_time')
                    ->orWhere('start_time', 'like', '__:__')
                    ->orWhere('end_time', 'like', '__:__');
            })
            ->orderBy('id')
            ->each(function ($appointment) {
                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update([
                        'start_time' => $this->normalize($appointment->start_time),
                        'end_time' => $this->normalize($appointment->end_time),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Normalizing to a canonical H:i:s shape is lossless in meaning; there is
        // no meaningful inverse that restores the previous mixed formats.
    }

    private function normalize(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }

        return date('H:i:s', strtotime($time));
    }
};

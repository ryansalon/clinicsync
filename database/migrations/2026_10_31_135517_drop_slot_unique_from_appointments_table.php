<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Slot occupancy is governed by the AppointmentSlotAvailable rule,
            // which only counts pending/confirmed rows so cancelled slots can be
            // rebooked. This blanket unique index treated every status as
            // occupying the slot and made rebooking a cancelled slot a 500.
            $table->dropUnique(['physician_id', 'appointment_date', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unique(['physician_id', 'appointment_date', 'start_time']);
        });
    }
};

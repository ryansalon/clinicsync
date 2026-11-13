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
        Schema::table('physicians', function (Blueprint $table) {
            $table->unsignedSmallInteger('daily_patient_limit')->default(30)->after('appointment_slot_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('physicians', function (Blueprint $table) {
            $table->dropColumn('daily_patient_limit');
        });
    }
};

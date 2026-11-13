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
        Schema::table('vitals', function (Blueprint $table) {
            // The unique index also serves every encounter_id lookup, so the
            // plain index from create_vitals_table is redundant.
            $table->dropIndex(['encounter_id']);
            $table->unique(['encounter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vitals', function (Blueprint $table) {
            $table->dropUnique(['encounter_id']);
            $table->index(['encounter_id']);
        });
    }
};

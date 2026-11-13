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
        Schema::create('vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->cascadeOnDelete();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->unsignedSmallInteger('systolic_bp')->nullable();
            $table->unsignedSmallInteger('diastolic_bp')->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->unsignedTinyInteger('spo2')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('encounter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vitals');
    }
};

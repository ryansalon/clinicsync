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
        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('physician_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('encounter_number', 30)->unique();
            $table->timestamp('encountered_at');
            $table->string('chief_complaint')->nullable();
            $table->string('diagnosis')->nullable();
            $table->string('diagnosis_code', 20)->nullable();
            $table->text('assessment')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])
                ->default('in_progress');
            $table->timestamps();

            $table->index(['patient_id', 'encountered_at']);
            $table->index(['physician_id', 'encountered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};

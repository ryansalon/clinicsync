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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('physician_id')->constrained()->cascadeOnDelete();
            $table->string('prescription_number', 30)->unique();
            $table->string('medication_name');
            $table->string('strength', 50)->nullable();
            $table->string('dosage_form', 50)->nullable();
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('route', 50)->default('oral');
            $table->string('duration')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->index(['patient_id', 'issued_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};

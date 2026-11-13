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
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'past_medical',
                'past_surgical',
                'family',
                'social',
            ])->default('past_medical');
            $table->string('condition_name');
            $table->year('year_diagnosed')->nullable();
            $table->string('status', 40)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_histories');
    }
};

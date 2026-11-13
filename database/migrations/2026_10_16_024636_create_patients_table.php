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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('patient_number', 20)->unique();
            $table->date('date_of_birth');
            $table->enum('sex', ['male', 'female', 'other']);
            $table->string('civil_status', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 30)->nullable();
            $table->text('photo_path')->nullable();
            $table->timestamps();

            $table->index('patient_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};

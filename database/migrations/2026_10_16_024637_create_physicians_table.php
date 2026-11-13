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
        Schema::create('physicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('physician_number', 20)->unique();
            $table->string('specialization')->default('General Medicine');
            $table->string('license_number', 50)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedSmallInteger('appointment_slot_minutes')->default(15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physicians');
    }
};

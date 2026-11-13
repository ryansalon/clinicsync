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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('physician_id')->constrained()->cascadeOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('source', ['online', 'walk_in'])->default('online');
            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
                'completed',
                'no_show',
            ])->default('pending');
            $table->string('chief_complaint')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedInteger('queue_number');
            $table->foreignId('booked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['physician_id', 'appointment_date', 'start_time']);
            $table->unique(['appointment_date', 'queue_number']);
            $table->index(['appointment_date', 'status']);
            $table->index(['physician_id', 'appointment_date', 'status']);
            $table->index(['patient_id', 'appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

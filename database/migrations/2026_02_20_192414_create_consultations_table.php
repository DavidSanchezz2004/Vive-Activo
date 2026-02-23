<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students')
                ->nullOnDelete();

            $table->string('type', 80)->nullable(); // evaluacion_inicial, revision_examenes, etc.

            $table->enum('mode', ['presencial', 'zoom', 'meet'])->default('presencial')->index();

            $table->enum('status', ['pending_confirmation', 'confirmed', 'completed', 'cancelled'])
                ->default('pending_confirmation')
                ->index();

            $table->dateTime('requested_at')->index();
            $table->dateTime('scheduled_at')->nullable()->index();

            $table->string('meeting_url', 500)->nullable(); // zoom o meet
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(['patient_id', 'status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
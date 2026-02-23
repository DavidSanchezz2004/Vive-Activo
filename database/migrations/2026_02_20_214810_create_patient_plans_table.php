<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->cascadeOnDelete();

            $table->foreignId('plan_id')
                  ->constrained('plans')
                  ->restrictOnDelete();

            $table->date('starts_at');
            $table->date('ends_at');

            $table->unsignedSmallInteger('sessions_used')
                  ->default(0)
                  ->comment('Sesiones con deducts=true ya consumidas');

            $table->enum('status', ['active', 'completed', 'cancelled'])
                  ->default('active')
                  ->index();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->timestamps();

            $table->index(['patient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_plans');
    }
};

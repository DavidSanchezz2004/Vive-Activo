<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nutrition_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->cascadeOnDelete();

            $table->string('phase')->nullable()         // "Fase 1 – Déficit calórico", "Mantenimiento"
                  ->comment('Fase o nombre del plan');
            $table->string('goal')->nullable()          // "Perder grasa", "Hipertrofia", etc.
                  ->comment('Objetivo nutricional');

            $table->date('valid_from');                 // inicio de vigencia
            $table->date('valid_until')->nullable();    // fin de vigencia (null = sin límite)

            $table->unsignedSmallInteger('kcal_target')->nullable()
                  ->comment('Calorías objetivo/día');
            $table->decimal('protein_g', 6, 1)->nullable()
                  ->comment('Proteínas objetivo g/día');
            $table->decimal('carbs_g', 6, 1)->nullable();
            $table->decimal('fat_g', 6, 1)->nullable();

            $table->text('notes')->nullable();

            $table->string('pdf_path')->nullable()
                  ->comment('Ruta del PDF del plan si se adjunta');

            $table->boolean('is_active')->default(true)->index();

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->timestamps();

            $table->index(['patient_id', 'is_active', 'valid_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_plans');
    }
};

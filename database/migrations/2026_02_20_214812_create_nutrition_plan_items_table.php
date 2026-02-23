<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nutrition_plan_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nutrition_plan_id')
                  ->constrained('nutrition_plans')
                  ->cascadeOnDelete();

            // Tiempo de comida: desayuno, media mañana, almuerzo, merienda, cena, post-entreno, etc.
            $table->string('meal_time', 60);

            $table->unsignedTinyInteger('order')
                  ->default(0)
                  ->comment('Orden de visualización dentro del tiempo');

            $table->string('food_name');               // "Avena con plátano"
            $table->string('quantity', 80)->nullable(); // "1 taza / 200g / 2 unidades"
            $table->text('notes')->nullable();         // "Sin azúcar", "Con leche descremada"

            $table->unsignedSmallInteger('kcal')->nullable();
            $table->decimal('protein_g', 5, 1)->nullable();
            $table->decimal('carbs_g', 5, 1)->nullable();
            $table->decimal('fat_g', 5, 1)->nullable();

            $table->timestamps();

            $table->index(['nutrition_plan_id', 'meal_time', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_plan_items');
    }
};

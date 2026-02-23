<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nutrition_plan_template_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nutrition_plan_template_id')
                ->constrained('nutrition_plan_templates')
                ->cascadeOnDelete();

            $table->string('meal_time', 60);
            $table->unsignedSmallInteger('order')->default(0);

            $table->string('food_name');
            $table->string('quantity', 80)->nullable();
            $table->string('notes', 500)->nullable();

            $table->unsignedSmallInteger('kcal')->nullable();
            $table->decimal('protein_g', 6, 1)->nullable();
            $table->decimal('carbs_g', 6, 1)->nullable();
            $table->decimal('fat_g', 6, 1)->nullable();

            $table->timestamps();

            $table->index(['nutrition_plan_template_id', 'meal_time', 'order'], 'npti_tpl_meal_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_plan_template_items');
    }
};

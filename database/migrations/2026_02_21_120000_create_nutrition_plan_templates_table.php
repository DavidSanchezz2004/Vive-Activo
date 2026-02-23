<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nutrition_plan_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name', 120);

            $table->string('phase')->nullable();
            $table->string('goal')->nullable();

            $table->unsignedSmallInteger('kcal_target')->nullable();
            $table->decimal('protein_g', 6, 1)->nullable();
            $table->decimal('carbs_g', 6, 1)->nullable();
            $table->decimal('fat_g', 6, 1)->nullable();

            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_plan_templates');
    }
};

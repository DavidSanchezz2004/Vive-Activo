<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');                            // "Plan Básico", "Plan Anual Pro"
            $table->text('description')->nullable();
            $table->string('slug')->unique();                  // para URLs y referencias

            $table->unsignedSmallInteger('sessions_total')     // sesiones incluidas en el plan
                  ->default(0)
                  ->comment('0 = ilimitado');
            $table->unsignedSmallInteger('duration_months')    // vigencia en meses
                  ->default(1);

            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('PEN');

            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

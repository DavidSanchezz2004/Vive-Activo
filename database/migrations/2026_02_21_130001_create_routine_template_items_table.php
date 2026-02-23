<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routine_template_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('routine_template_id')
                ->constrained('routine_templates')
                ->cascadeOnDelete();

            $table->string('day', 20);
            $table->unsignedInteger('order')->default(0);
            $table->string('exercise_name');
            $table->unsignedTinyInteger('sets')->nullable();
            $table->string('reps', 50)->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->string('notes', 500)->nullable();

            $table->timestamps();

            $table->index(['routine_template_id', 'day', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_template_items');
    }
};

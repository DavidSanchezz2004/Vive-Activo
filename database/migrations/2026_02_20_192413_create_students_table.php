<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->unique();

            $table->foreignId('district_id')
                ->nullable()
                ->constrained('districts')
                ->nullOnDelete();

            $table->foreignId('university_id')
                ->nullable()
                ->constrained('universities')
                ->nullOnDelete();

            $table->foreignId('career_id')
                ->nullable()
                ->constrained('careers')
                ->nullOnDelete();

            $table->unsignedTinyInteger('cycle')->nullable()->index(); // 7, 8, 9...

            $table->enum('sex', ['M', 'F', 'O'])->nullable()->index();
            $table->date('birthdate')->nullable()->index();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
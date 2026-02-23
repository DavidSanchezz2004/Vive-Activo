<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('user_profiles', function (Blueprint $table) {
      $table->id();

      $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete()
        ->unique();

      $table->string('first_name')->nullable();
      $table->string('last_name')->nullable();
      $table->string('phone', 30)->nullable();

      $table->string('document_type', 10)->nullable();     // DNI / CE / PAS
      $table->string('document_number', 20)->nullable();

      $table->string('country')->nullable();               // Perú
      $table->string('region')->nullable();                // Lima Metropolitana
      $table->string('district')->nullable();              // Lima
      $table->string('address_line')->nullable();

      $table->string('avatar_path')->nullable();           // storage path: avatars/xxx.webp

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('user_profiles');
  }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->string('concept', 190)->nullable();
            $table->decimal('amount', 10, 2);

            $table->char('currency', 3)->default('PEN');

            $table->dateTime('paid_at')->nullable()->index();

            $table->enum('status', ['paid', 'pending', 'cancelled'])
                ->default('pending')
                ->index();

            $table->string('receipt_path', 500)->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(['patient_id', 'status', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
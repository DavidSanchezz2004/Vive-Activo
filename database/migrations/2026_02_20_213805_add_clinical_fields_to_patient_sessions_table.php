<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->decimal('weight_kg', 5, 2)->nullable()->after('notes')
                  ->comment('Peso registrado en la sesión (kg)');
            $table->unsignedTinyInteger('rpe')->nullable()->after('weight_kg')
                  ->comment('Esfuerzo percibido 1-10');
            $table->dateTime('attended_at')->nullable()->after('rpe')
                  ->comment('Cuándo se marcó como atendida');
            $table->dateTime('rescheduled_at')->nullable()->after('attended_at')
                  ->comment('Nueva fecha al reprogramar');
        });
    }

    public function down(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->dropColumn(['weight_kg', 'rpe', 'attended_at', 'rescheduled_at']);
        });
    }
};

<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\SessionReview;
use App\Models\Student;
use App\Models\User;
use App\Models\NutritionPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('123'),
                'role' => UserRole::Admin, // o 'admin' si tu columna no es enum cast
            ]
        );

        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@demo.com'],
            [
                'name' => 'Supervisor User',
                'password' => Hash::make('123'),
                'role' => UserRole::Supervisor, // o 'supervisor'
            ]
        );

        $alumnoUser = User::updateOrCreate(
            ['email' => 'alumno@demo.com'],
            [
                'name' => 'Alumno User',
                'password' => Hash::make('123'),
                'role' => UserRole::Student,
            ]
        );

        $pacienteUser = User::updateOrCreate(
            ['email' => 'paciente@demo.com'],
            [
                'name' => 'Paciente User',
                'password' => Hash::make('123'),
                'role' => UserRole::Patient,
            ]
        );

        $this->call([
            AdminUserSeeder::class,
            CareerSeeder::class,
            UniversitySeeder::class,
            DistrictSeeder::class,
            NutritionPlanTemplateSeeder::class,
            RoutineTemplateSeeder::class,
        ]);

        $student = Student::updateOrCreate(
            ['user_id' => $alumnoUser->id],
            ['is_active' => true]
        );

        $patient = Patient::updateOrCreate(
            ['user_id' => $pacienteUser->id],
            ['is_active' => true]
        );

        $activeAssignments = Assignment::query()
            ->where('patient_id', $patient->id)
            ->where('is_active', true)
            ->get();

        $alreadyAssignedToDemoStudent = $activeAssignments->count() === 1
            && (int) $activeAssignments->first()->student_id === (int) $student->id;

        if (! $alreadyAssignedToDemoStudent) {
            Assignment::query()
                ->where('patient_id', $patient->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'unassigned_at' => now(),
                ]);

            Assignment::create([
                'patient_id' => $patient->id,
                'student_id' => $student->id,
                'assigned_by' => $admin->id,
                'assigned_at' => now(),
                'unassigned_at' => null,
                'is_active' => true,
                'reason' => 'Asignación demo',
            ]);
        }

        $activeSince = now()->subDays(30);
        $hasRecentDoneSession = PatientSession::query()
            ->where('student_id', $student->id)
            ->where('patient_id', $patient->id)
            ->where('status', 'done')
            ->where('scheduled_at', '>=', $activeSince)
            ->exists();

        if (! $hasRecentDoneSession) {
            $session = PatientSession::create([
                'patient_id' => $patient->id,
                'student_id' => $student->id,
                'consultation_id' => null,
                'scheduled_at' => now()->subDay(),
                'status' => 'done',
                'deducts' => false,
                'notes' => 'Sesión demo',
                'created_by' => $admin->id,
            ]);

            SessionReview::updateOrCreate(
                ['session_id' => $session->id],
                [
                    'patient_id' => $patient->id,
                    'rating' => 5,
                    'comment' => 'Excelente sesión (demo).',
                ]
            );
        }

        // --- PLAN NUTRICIONAL DE EJEMPLO ---
        if ($patient->nutritionPlans()->count() === 0) {
            $plan = NutritionPlan::create([
                'patient_id' => $patient->id,
                'created_by' => $alumnoUser->id,
                'phase' => 'Fase 1 - Reducción de grasa',
                'goal' => 'Déficit calórico moderado (-300 kcal)',
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addDays(30),
                'kcal_target' => 1850,
                'protein_g' => 160,
                'carbs_g' => 180,
                'fat_g' => 55,
                'is_active' => true,
                'notes' => "Tomar 2.5 a 3 litros de agua diarios.\nEvitar azúcares refinados y priorizar proteínas en cada comida.",
            ]);

            // Desayuno
            $plan->items()->createMany([
                ['meal_time' => 'breakfast', 'food_name' => 'Avena en hojuelas', 'quantity' => '40g', 'kcal' => 150, 'protein_g' => 5, 'carbs_g' => 27, 'fat_g' => 3],
                ['meal_time' => 'breakfast', 'food_name' => 'Claras de huevo', 'quantity' => '4 unidades', 'kcal' => 68, 'protein_g' => 14, 'carbs_g' => 1, 'fat_g' => 0],
                ['meal_time' => 'breakfast', 'food_name' => 'Huevo entero', 'quantity' => '1 unidad', 'kcal' => 78, 'protein_g' => 6, 'carbs_g' => 1, 'fat_g' => 5],
                ['meal_time' => 'breakfast', 'food_name' => 'Plátano', 'quantity' => '1 unidad (media)', 'kcal' => 105, 'protein_g' => 1, 'carbs_g' => 27, 'fat_g' => 0],
            ]);

            // Almuerzo
            $plan->items()->createMany([
                ['meal_time' => 'lunch', 'food_name' => 'Pechuga de pollo a la plancha', 'quantity' => '150g', 'kcal' => 248, 'protein_g' => 46, 'carbs_g' => 0, 'fat_g' => 5],
                ['meal_time' => 'lunch', 'food_name' => 'Arroz integral cocido', 'quantity' => '100g', 'kcal' => 112, 'protein_g' => 3, 'carbs_g' => 24, 'fat_g' => 1],
                ['meal_time' => 'lunch', 'food_name' => 'Brócoli al vapor', 'quantity' => '1 taza', 'kcal' => 55, 'protein_g' => 4, 'carbs_g' => 11, 'fat_g' => 1, 'notes' => 'Añadir 1 cdta de aceite de oliva'],
                ['meal_time' => 'lunch', 'food_name' => 'Aceite de oliva extra virgen', 'quantity' => '1 cucharadita', 'kcal' => 40, 'protein_g' => 0, 'carbs_g' => 0, 'fat_g' => 5],
            ]);

            // Snack / Pre-entreno
            $plan->items()->createMany([
                ['meal_time' => 'pre_workout', 'food_name' => 'Yogur griego natural (sin azúcar)', 'quantity' => '150g', 'kcal' => 88, 'protein_g' => 15, 'carbs_g' => 6, 'fat_g' => 0],
                ['meal_time' => 'pre_workout', 'food_name' => 'Almendras', 'quantity' => '15g', 'kcal' => 87, 'protein_g' => 3, 'carbs_g' => 3, 'fat_g' => 8],
                ['meal_time' => 'pre_workout', 'food_name' => 'Café negro sin azúcar', 'quantity' => '1 taza', 'kcal' => 2, 'protein_g' => 0, 'carbs_g' => 0, 'fat_g' => 0, 'notes' => 'Opcional 30 min antes de entrenar'],
            ]);

            // Cena
            $plan->items()->createMany([
                ['meal_time' => 'dinner', 'food_name' => 'Atún en agua', 'quantity' => '1 lata (120g drenado)', 'kcal' => 115, 'protein_g' => 26, 'carbs_g' => 0, 'fat_g' => 1],
                ['meal_time' => 'dinner', 'food_name' => 'Papa sancochada', 'quantity' => '150g', 'kcal' => 130, 'protein_g' => 3, 'carbs_g' => 30, 'fat_g' => 0],
                ['meal_time' => 'dinner', 'food_name' => 'Ensalada mixta (lechuga, tomate, pepino)', 'quantity' => 'Libre', 'kcal' => 30, 'protein_g' => 1, 'carbs_g' => 6, 'fat_g' => 0, 'notes' => 'Con limón y sal algusto'],
            ]);
        }
    }
}
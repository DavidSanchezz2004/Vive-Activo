<?php

namespace Database\Seeders;

use App\Models\RoutineTemplate;
use Illuminate\Database\Seeder;

class RoutineTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (RoutineTemplate::query()->count() > 0) {
            return;
        }

        $templates = [
            [
                'name' => 'Full Body (Principiante) - 3 días',
                'goal' => 'Acondicionamiento general, técnica y fuerza base',
                'notes' => "Calentar 5-10 min. Mantener 1-2 repeticiones en reserva.\nProgresar semana a semana si te sientes cómodo.",
                'is_active' => true,
                'items' => [
                    ['day' => 'monday', 'exercise_name' => 'Sentadilla (goblet)', 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 90],
                    ['day' => 'monday', 'exercise_name' => 'Press pecho (mancuernas)', 'sets' => 3, 'reps' => '8-12', 'rest_seconds' => 90],
                    ['day' => 'monday', 'exercise_name' => 'Remo (mancuernas)', 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 90],

                    ['day' => 'wednesday', 'exercise_name' => 'Peso muerto rumano', 'sets' => 3, 'reps' => '8-10', 'rest_seconds' => 120],
                    ['day' => 'wednesday', 'exercise_name' => 'Press hombros (mancuernas)', 'sets' => 3, 'reps' => '8-12', 'rest_seconds' => 90],
                    ['day' => 'wednesday', 'exercise_name' => 'Plancha', 'sets' => 3, 'reps' => '30-45 seg', 'rest_seconds' => 60],

                    ['day' => 'friday', 'exercise_name' => 'Zancadas', 'sets' => 3, 'reps' => '10 c/pierna', 'rest_seconds' => 90],
                    ['day' => 'friday', 'exercise_name' => 'Jalón al pecho / dominadas asistidas', 'sets' => 3, 'reps' => '8-12', 'rest_seconds' => 90],
                    ['day' => 'friday', 'exercise_name' => 'Caminata (cardio suave)', 'sets' => null, 'reps' => '20-30 min', 'rest_seconds' => null],
                ],
            ],
            [
                'name' => 'Upper / Lower (Intermedio) - 4 días',
                'goal' => 'Hipertrofia y fuerza (división tren superior/inferior)',
                'notes' => 'Mantener descansos 60-120s según el ejercicio.',
                'is_active' => true,
                'items' => [
                    ['day' => 'monday', 'exercise_name' => 'Press banca', 'sets' => 4, 'reps' => '6-10', 'rest_seconds' => 120],
                    ['day' => 'monday', 'exercise_name' => 'Remo con barra', 'sets' => 4, 'reps' => '6-10', 'rest_seconds' => 120],
                    ['day' => 'monday', 'exercise_name' => 'Elevaciones laterales', 'sets' => 3, 'reps' => '12-15', 'rest_seconds' => 60],

                    ['day' => 'tuesday', 'exercise_name' => 'Sentadilla', 'sets' => 4, 'reps' => '5-8', 'rest_seconds' => 150],
                    ['day' => 'tuesday', 'exercise_name' => 'Prensa', 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 120],
                    ['day' => 'tuesday', 'exercise_name' => 'Curl femoral', 'sets' => 3, 'reps' => '10-15', 'rest_seconds' => 90],

                    ['day' => 'thursday', 'exercise_name' => 'Press inclinado (mancuernas)', 'sets' => 3, 'reps' => '8-12', 'rest_seconds' => 120],
                    ['day' => 'thursday', 'exercise_name' => 'Jalón al pecho', 'sets' => 3, 'reps' => '8-12', 'rest_seconds' => 120],
                    ['day' => 'thursday', 'exercise_name' => 'Curl bíceps', 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 60],

                    ['day' => 'friday', 'exercise_name' => 'Peso muerto rumano', 'sets' => 3, 'reps' => '6-10', 'rest_seconds' => 150],
                    ['day' => 'friday', 'exercise_name' => 'Hip thrust', 'sets' => 3, 'reps' => '8-12', 'rest_seconds' => 150],
                    ['day' => 'friday', 'exercise_name' => 'Gemelos', 'sets' => 4, 'reps' => '12-20', 'rest_seconds' => 60],
                ],
            ],
            [
                'name' => 'Cardio + Movilidad (Suave) - 5 días',
                'goal' => 'Hábitos, salud cardiovascular y movilidad',
                'notes' => 'Rutina liviana. Ajustar intensidades según tolerancia.',
                'is_active' => true,
                'items' => [
                    ['day' => 'monday', 'exercise_name' => 'Caminata', 'sets' => null, 'reps' => '30 min', 'rest_seconds' => null],
                    ['day' => 'monday', 'exercise_name' => 'Movilidad (cadera/torácica)', 'sets' => null, 'reps' => '10 min', 'rest_seconds' => null],

                    ['day' => 'tuesday', 'exercise_name' => 'Bicicleta', 'sets' => null, 'reps' => '25 min', 'rest_seconds' => null],
                    ['day' => 'tuesday', 'exercise_name' => 'Estiramientos', 'sets' => null, 'reps' => '10 min', 'rest_seconds' => null],

                    ['day' => 'wednesday', 'exercise_name' => 'Caminata', 'sets' => null, 'reps' => '30 min', 'rest_seconds' => null],
                    ['day' => 'wednesday', 'exercise_name' => 'Respiración / core suave', 'sets' => null, 'reps' => '10 min', 'rest_seconds' => null],

                    ['day' => 'thursday', 'exercise_name' => 'Elíptica', 'sets' => null, 'reps' => '20 min', 'rest_seconds' => null],
                    ['day' => 'thursday', 'exercise_name' => 'Movilidad (hombros)', 'sets' => null, 'reps' => '10 min', 'rest_seconds' => null],

                    ['day' => 'friday', 'exercise_name' => 'Caminata', 'sets' => null, 'reps' => '30 min', 'rest_seconds' => null],
                    ['day' => 'friday', 'exercise_name' => 'Estiramientos', 'sets' => null, 'reps' => '10 min', 'rest_seconds' => null],
                ],
            ],
        ];

        foreach ($templates as $tplData) {
            $items = $tplData['items'] ?? [];
            unset($tplData['items']);

            $tpl = RoutineTemplate::create($tplData);

            $order = 0;
            foreach ($items as $it) {
                $tpl->items()->create([
                    'day' => $it['day'],
                    'order' => $order++,
                    'exercise_name' => $it['exercise_name'],
                    'sets' => $it['sets'] ?? null,
                    'reps' => $it['reps'] ?? null,
                    'rest_seconds' => $it['rest_seconds'] ?? null,
                    'notes' => $it['notes'] ?? null,
                ]);
            }
        }
    }
}

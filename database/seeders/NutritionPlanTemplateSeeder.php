<?php

namespace Database\Seeders;

use App\Models\NutritionPlanTemplate;
use Illuminate\Database\Seeder;

class NutritionPlanTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Déficit calórico (ejemplo)',
                'phase' => 'Fase 1 – Déficit calórico',
                'goal' => 'Reducción de grasa',
                'kcal_target' => 1800,
                'protein_g' => 140,
                'carbs_g' => 170,
                'fat_g' => 55,
                'notes' => 'Plantilla sugerida. Ajustar por contexto clínico y antropometría.',
                'items' => [
                    'desayuno' => [
                        ['food_name' => 'Avena', 'quantity' => '50g', 'notes' => 'Con agua o leche descremada', 'kcal' => 190, 'protein_g' => 7, 'carbs_g' => 32, 'fat_g' => 3],
                        ['food_name' => 'Banano', 'quantity' => '1 unidad', 'notes' => null, 'kcal' => 105, 'protein_g' => 1, 'carbs_g' => 27, 'fat_g' => 0.4],
                        ['food_name' => 'Yogur griego natural', 'quantity' => '200g', 'notes' => 'Sin azúcar', 'kcal' => 120, 'protein_g' => 20, 'carbs_g' => 8, 'fat_g' => 0],
                    ],
                    'almuerzo' => [
                        ['food_name' => 'Pechuga de pollo', 'quantity' => '150g', 'notes' => 'A la plancha', 'kcal' => 250, 'protein_g' => 46, 'carbs_g' => 0, 'fat_g' => 5],
                        ['food_name' => 'Arroz integral', 'quantity' => '1 taza cocida', 'notes' => null, 'kcal' => 215, 'protein_g' => 5, 'carbs_g' => 45, 'fat_g' => 1.8],
                        ['food_name' => 'Ensalada mixta', 'quantity' => '1 porción', 'notes' => 'Agregar limón/sal al gusto', 'kcal' => 80, 'protein_g' => 2, 'carbs_g' => 10, 'fat_g' => 4],
                    ],
                    'cena' => [
                        ['food_name' => 'Pescado blanco', 'quantity' => '180g', 'notes' => 'Horno o plancha', 'kcal' => 200, 'protein_g' => 40, 'carbs_g' => 0, 'fat_g' => 3],
                        ['food_name' => 'Verduras al vapor', 'quantity' => '1–2 tazas', 'notes' => null, 'kcal' => 90, 'protein_g' => 4, 'carbs_g' => 16, 'fat_g' => 1],
                        ['food_name' => 'Aguacate', 'quantity' => '50g', 'notes' => null, 'kcal' => 80, 'protein_g' => 1, 'carbs_g' => 4, 'fat_g' => 7],
                    ],
                ],
            ],
            [
                'name' => 'Mantenimiento (ejemplo)',
                'phase' => 'Mantenimiento',
                'goal' => 'Mantener peso y mejorar hábitos',
                'kcal_target' => 2200,
                'protein_g' => 150,
                'carbs_g' => 240,
                'fat_g' => 70,
                'notes' => 'Plantilla sugerida. Ajustar por actividad física y preferencias.',
                'items' => [
                    'desayuno' => [
                        ['food_name' => 'Huevos', 'quantity' => '2 unidades', 'notes' => 'Revueltos o cocidos', 'kcal' => 140, 'protein_g' => 12, 'carbs_g' => 1, 'fat_g' => 10],
                        ['food_name' => 'Pan integral', 'quantity' => '2 rebanadas', 'notes' => null, 'kcal' => 160, 'protein_g' => 8, 'carbs_g' => 28, 'fat_g' => 2],
                        ['food_name' => 'Fruta', 'quantity' => '1 porción', 'notes' => 'Manzana/pera/naranja', 'kcal' => 80, 'protein_g' => 0.5, 'carbs_g' => 20, 'fat_g' => 0.2],
                    ],
                    'media_manana' => [
                        ['food_name' => 'Frutos secos', 'quantity' => '20g', 'notes' => 'Nueces/almendras', 'kcal' => 120, 'protein_g' => 4, 'carbs_g' => 4, 'fat_g' => 10],
                        ['food_name' => 'Yogur natural', 'quantity' => '150g', 'notes' => 'Sin azúcar', 'kcal' => 90, 'protein_g' => 8, 'carbs_g' => 8, 'fat_g' => 3],
                    ],
                    'almuerzo' => [
                        ['food_name' => 'Carne magra', 'quantity' => '150g', 'notes' => 'Res/pavo/cerdo magro', 'kcal' => 260, 'protein_g' => 35, 'carbs_g' => 0, 'fat_g' => 12],
                        ['food_name' => 'Papa o camote', 'quantity' => '200g', 'notes' => null, 'kcal' => 170, 'protein_g' => 4, 'carbs_g' => 37, 'fat_g' => 0.2],
                        ['food_name' => 'Vegetales', 'quantity' => '1–2 tazas', 'notes' => null, 'kcal' => 80, 'protein_g' => 4, 'carbs_g' => 14, 'fat_g' => 1],
                    ],
                    'cena' => [
                        ['food_name' => 'Atún o pollo', 'quantity' => '150g', 'notes' => null, 'kcal' => 220, 'protein_g' => 40, 'carbs_g' => 0, 'fat_g' => 4],
                        ['food_name' => 'Arroz / quinoa', 'quantity' => '1 taza cocida', 'notes' => null, 'kcal' => 220, 'protein_g' => 6, 'carbs_g' => 40, 'fat_g' => 3],
                    ],
                ],
            ],
            [
                'name' => 'Hipertrofia (ejemplo)',
                'phase' => 'Hipertrofia',
                'goal' => 'Aumento de masa muscular',
                'kcal_target' => 2800,
                'protein_g' => 170,
                'carbs_g' => 330,
                'fat_g' => 80,
                'notes' => 'Plantilla sugerida. Ajustar por volumen de entrenamiento y tolerancia digestiva.',
                'items' => [
                    'desayuno' => [
                        ['food_name' => 'Avena', 'quantity' => '80g', 'notes' => null, 'kcal' => 300, 'protein_g' => 11, 'carbs_g' => 52, 'fat_g' => 6],
                        ['food_name' => 'Leche', 'quantity' => '300ml', 'notes' => 'Descremada o semidescremada', 'kcal' => 150, 'protein_g' => 10, 'carbs_g' => 14, 'fat_g' => 5],
                        ['food_name' => 'Mantequilla de maní', 'quantity' => '20g', 'notes' => null, 'kcal' => 120, 'protein_g' => 5, 'carbs_g' => 4, 'fat_g' => 10],
                    ],
                    'pre_entreno' => [
                        ['food_name' => 'Banano', 'quantity' => '1 unidad', 'notes' => null, 'kcal' => 105, 'protein_g' => 1, 'carbs_g' => 27, 'fat_g' => 0.4],
                        ['food_name' => 'Galletas de arroz', 'quantity' => '2 unidades', 'notes' => null, 'kcal' => 70, 'protein_g' => 1, 'carbs_g' => 14, 'fat_g' => 0.5],
                    ],
                    'post_entreno' => [
                        ['food_name' => 'Batido de proteína', 'quantity' => '1 porción', 'notes' => 'En agua o leche', 'kcal' => 120, 'protein_g' => 24, 'carbs_g' => 3, 'fat_g' => 2],
                        ['food_name' => 'Fruta', 'quantity' => '1 porción', 'notes' => null, 'kcal' => 80, 'protein_g' => 0.5, 'carbs_g' => 20, 'fat_g' => 0.2],
                    ],
                    'almuerzo' => [
                        ['food_name' => 'Pechuga de pollo', 'quantity' => '180g', 'notes' => null, 'kcal' => 300, 'protein_g' => 55, 'carbs_g' => 0, 'fat_g' => 6],
                        ['food_name' => 'Pasta', 'quantity' => '1.5 tazas cocidas', 'notes' => null, 'kcal' => 300, 'protein_g' => 10, 'carbs_g' => 60, 'fat_g' => 2],
                        ['food_name' => 'Vegetales', 'quantity' => '1–2 tazas', 'notes' => null, 'kcal' => 80, 'protein_g' => 4, 'carbs_g' => 14, 'fat_g' => 1],
                    ],
                    'cena' => [
                        ['food_name' => 'Salmón / pescado', 'quantity' => '180g', 'notes' => null, 'kcal' => 320, 'protein_g' => 40, 'carbs_g' => 0, 'fat_g' => 18],
                        ['food_name' => 'Arroz / quinoa', 'quantity' => '1 taza cocida', 'notes' => null, 'kcal' => 220, 'protein_g' => 6, 'carbs_g' => 40, 'fat_g' => 3],
                    ],
                ],
            ],
        ];

        foreach ($templates as $tplData) {
            $itemsByMeal = $tplData['items'];
            unset($tplData['items']);

            $template = NutritionPlanTemplate::updateOrCreate(
                ['name' => $tplData['name']],
                $tplData + ['is_active' => true]
            );

            $template->items()->delete();

            foreach ($itemsByMeal as $mealTime => $items) {
                foreach (array_values($items) as $order => $item) {
                    $template->items()->create([
                        'meal_time' => $mealTime,
                        'order' => $order,
                        'food_name' => $item['food_name'],
                        'quantity' => $item['quantity'] ?? null,
                        'notes' => $item['notes'] ?? null,
                        'kcal' => $item['kcal'] ?? null,
                        'protein_g' => $item['protein_g'] ?? null,
                        'carbs_g' => $item['carbs_g'] ?? null,
                        'fat_g' => $item['fat_g'] ?? null,
                    ]);
                }
            }
        }
    }
}

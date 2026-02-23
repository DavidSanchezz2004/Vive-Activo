<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NutritionPlan;
use App\Models\NutritionPlanTemplate;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NutritionPlanController extends Controller
{
    /**
     * Formulario de creación para un paciente específico.
     */
    public function create(Patient $patient)
    {
        $templates = NutritionPlanTemplate::query()
            ->where('is_active', true)
            ->with(['items' => fn ($q) => $q->orderBy('meal_time')->orderBy('order')])
            ->orderBy('name')
            ->get();

        $templatesJson = $templates
            ->mapWithKeys(function ($tpl) {
                return [
                    (string) $tpl->id => [
                        'phase' => $tpl->phase,
                        'goal' => $tpl->goal,
                        'kcal_target' => $tpl->kcal_target,
                        'protein_g' => $tpl->protein_g,
                        'carbs_g' => $tpl->carbs_g,
                        'fat_g' => $tpl->fat_g,
                        'notes' => $tpl->notes,
                        'items' => $tpl->items
                            ->groupBy('meal_time')
                            ->map(function ($items) {
                                return $items
                                    ->values()
                                    ->map(function ($it) {
                                        return [
                                            'food_name' => $it->food_name,
                                            'quantity' => $it->quantity,
                                            'notes' => $it->notes,
                                            'kcal' => $it->kcal,
                                            'protein_g' => $it->protein_g,
                                            'carbs_g' => $it->carbs_g,
                                            'fat_g' => $it->fat_g,
                                        ];
                                    })
                                    ->all();
                            })
                            ->all(),
                    ],
                ];
            })
            ->all();

        return view('admin.nutrition_plans.create', [
            'patient'   => $patient,
            'mealTimes' => NutritionPlan::mealTimes(),
            'templates' => $templates->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values(),
            'templatesJson' => $templatesJson,
        ]);
    }

    /**
     * Guarda el plan nutricional con sus ítems.
     */
    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'phase'         => ['nullable', 'string', 'max:120'],
            'goal'          => ['nullable', 'string', 'max:200'],
            'valid_from'    => ['required', 'date'],
            'valid_until'   => ['nullable', 'date', 'after_or_equal:valid_from'],
            'kcal_target'   => ['nullable', 'integer', 'min:500', 'max:10000'],
            'protein_g'     => ['nullable', 'numeric', 'min:0'],
            'carbs_g'       => ['nullable', 'numeric', 'min:0'],
            'fat_g'         => ['nullable', 'numeric', 'min:0'],
            'notes'         => ['nullable', 'string', 'max:1000'],
            'pdf'           => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // ítems dinámicos
            'items'                  => ['nullable', 'array'],
            'items.*.meal_time'      => ['required_with:items.*', 'string'],
            'items.*.food_name'      => ['required_with:items.*', 'string', 'max:255'],
            'items.*.quantity'       => ['nullable', 'string', 'max:80'],
            'items.*.kcal'           => ['nullable', 'integer', 'min:0'],
            'items.*.protein_g'      => ['nullable', 'numeric', 'min:0'],
            'items.*.carbs_g'        => ['nullable', 'numeric', 'min:0'],
            'items.*.fat_g'          => ['nullable', 'numeric', 'min:0'],
            'items.*.notes'          => ['nullable', 'string', 'max:500'],
        ]);

        // PDF opcional
        $pdfPath = null;
        if ($request->hasFile('pdf')) {
            $pdfPath = $request->file('pdf')->store("nutrition_plans/{$patient->id}", 'local');
        }

        // Desactivar planes anteriores activos
        $patient->nutritionPlans()->where('is_active', true)->update(['is_active' => false]);

        $plan = $patient->nutritionPlans()->create([
            'phase'         => $data['phase'] ?? null,
            'goal'          => $data['goal'] ?? null,
            'valid_from'    => $data['valid_from'],
            'valid_until'   => $data['valid_until'] ?? null,
            'kcal_target'   => $data['kcal_target'] ?? null,
            'protein_g'     => $data['protein_g'] ?? null,
            'carbs_g'       => $data['carbs_g'] ?? null,
            'fat_g'         => $data['fat_g'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'pdf_path'      => $pdfPath,
            'is_active'     => true,
            'created_by'    => Auth::id(),
        ]);

        // Insertar ítems
        if (!empty($data['items'])) {
            foreach ($data['items'] as $order => $item) {
                if (empty($item['food_name'])) continue;
                $plan->items()->create([
                    'meal_time'  => $item['meal_time'],
                    'order'      => $order,
                    'food_name'  => $item['food_name'],
                    'quantity'   => $item['quantity'] ?? null,
                    'kcal'       => $item['kcal'] ?? null,
                    'protein_g'  => $item['protein_g'] ?? null,
                    'carbs_g'    => $item['carbs_g'] ?? null,
                    'fat_g'      => $item['fat_g'] ?? null,
                    'notes'      => $item['notes'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('admin.pacientes.show', $patient)
            ->with('success', 'Plan nutricional creado correctamente.');
    }

    /**
     * Detalle / edición de un plan.
     */
    public function show(Patient $patient, NutritionPlan $plan)
    {
        abort_if($plan->patient_id !== $patient->id, 404);
        $plan->load('items');
        return view('admin.nutrition_plans.show', [
            'patient'   => $patient,
            'plan'      => $plan,
            'mealTimes' => NutritionPlan::mealTimes(),
        ]);
    }

    /**
     * Desactivar / archivar un plan.
     */
    public function deactivate(Patient $patient, NutritionPlan $plan)
    {
        abort_if($plan->patient_id !== $patient->id, 404);
        $plan->update(['is_active' => false]);
        return back()->with('success', 'Plan archivado.');
    }

    /**
     * Eliminar un plan (solo si no hay PDF o se elimina también).
     */
    public function destroy(Patient $patient, NutritionPlan $plan)
    {
        abort_if($plan->patient_id !== $patient->id, 404);
        if ($plan->pdf_path) {
            Storage::disk('local')->delete($plan->pdf_path);
        }
        $plan->delete();
        return redirect()
            ->route('admin.pacientes.show', $patient)
            ->with('success', 'Plan nutricional eliminado.');
    }
}

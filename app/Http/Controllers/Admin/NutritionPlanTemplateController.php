<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NutritionPlan;
use App\Models\NutritionPlanTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NutritionPlanTemplateController extends Controller
{
    public function index()
    {
        $templates = NutritionPlanTemplate::query()
            ->withCount('items')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.nutrition_templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return view('admin.nutrition_templates.form', [
            'template' => new NutritionPlanTemplate(['is_active' => true]),
            'mealTimes' => NutritionPlan::mealTimes(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);

        $template = NutritionPlanTemplate::create([
            'name' => $data['name'],
            'phase' => $data['phase'] ?? null,
            'goal' => $data['goal'] ?? null,
            'kcal_target' => $data['kcal_target'] ?? null,
            'protein_g' => $data['protein_g'] ?? null,
            'carbs_g' => $data['carbs_g'] ?? null,
            'fat_g' => $data['fat_g'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'created_by' => Auth::id(),
        ]);

        $this->syncItems($template, $data['items'] ?? []);

        return redirect()
            ->route('admin.nutrition_templates.index')
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(NutritionPlanTemplate $nutritionTemplate)
    {
        $nutritionTemplate->load('items');

        return view('admin.nutrition_templates.form', [
            'template' => $nutritionTemplate,
            'mealTimes' => NutritionPlan::mealTimes(),
        ]);
    }

    public function update(Request $request, NutritionPlanTemplate $nutritionTemplate)
    {
        $data = $this->validateTemplate($request, $nutritionTemplate->id);

        $nutritionTemplate->update([
            'name' => $data['name'],
            'phase' => $data['phase'] ?? null,
            'goal' => $data['goal'] ?? null,
            'kcal_target' => $data['kcal_target'] ?? null,
            'protein_g' => $data['protein_g'] ?? null,
            'carbs_g' => $data['carbs_g'] ?? null,
            'fat_g' => $data['fat_g'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $this->syncItems($nutritionTemplate, $data['items'] ?? []);

        return redirect()
            ->route('admin.nutrition_templates.index')
            ->with('success', 'Plantilla actualizada.');
    }

    public function destroy(NutritionPlanTemplate $nutritionTemplate)
    {
        $nutritionTemplate->delete();

        return redirect()
            ->route('admin.nutrition_templates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    private function validateTemplate(Request $request, ?int $ignoreId = null): array
    {
        $uniqueNameRule = ['unique:nutrition_plan_templates,name'];
        if ($ignoreId) {
            $uniqueNameRule = ['unique:nutrition_plan_templates,name,' . $ignoreId];
        }

        return $request->validate([
            'name' => array_merge(['required', 'string', 'max:120'], $uniqueNameRule),
            'phase' => ['nullable', 'string', 'max:120'],
            'goal' => ['nullable', 'string', 'max:200'],
            'kcal_target' => ['nullable', 'integer', 'min:500', 'max:10000'],
            'protein_g' => ['nullable', 'numeric', 'min:0'],
            'carbs_g' => ['nullable', 'numeric', 'min:0'],
            'fat_g' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],

            'items' => ['nullable', 'array'],
            'items.*.meal_time' => ['required_with:items.*', 'string'],
            'items.*.food_name' => ['required_with:items.*', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'string', 'max:80'],
            'items.*.kcal' => ['nullable', 'integer', 'min:0'],
            'items.*.protein_g' => ['nullable', 'numeric', 'min:0'],
            'items.*.carbs_g' => ['nullable', 'numeric', 'min:0'],
            'items.*.fat_g' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function syncItems(NutritionPlanTemplate $template, array $items): void
    {
        $template->items()->delete();

        $order = 0;
        foreach ($items as $item) {
            if (empty($item['food_name'])) {
                continue;
            }

            $template->items()->create([
                'meal_time' => $item['meal_time'],
                'order' => $order++,
                'food_name' => $item['food_name'],
                'quantity' => $item['quantity'] ?? null,
                'kcal' => $item['kcal'] ?? null,
                'protein_g' => $item['protein_g'] ?? null,
                'carbs_g' => $item['carbs_g'] ?? null,
                'fat_g' => $item['fat_g'] ?? null,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}

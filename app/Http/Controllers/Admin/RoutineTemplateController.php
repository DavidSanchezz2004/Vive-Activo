<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Models\RoutineTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutineTemplateController extends Controller
{
    public function index()
    {
        $templates = RoutineTemplate::query()
            ->withCount('items')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.routine_templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return view('admin.routine_templates.form', [
            'template' => new RoutineTemplate(['is_active' => true]),
            'days' => Routine::days(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);

        $template = RoutineTemplate::create([
            'name' => $data['name'],
            'goal' => $data['goal'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'created_by' => Auth::id(),
        ]);

        $this->syncItems($template, $data['items'] ?? []);

        return redirect()
            ->route('admin.routine_templates.index')
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(RoutineTemplate $routineTemplate)
    {
        $routineTemplate->load('items');

        return view('admin.routine_templates.form', [
            'template' => $routineTemplate,
            'days' => Routine::days(),
        ]);
    }

    public function update(Request $request, RoutineTemplate $routineTemplate)
    {
        $data = $this->validateTemplate($request, $routineTemplate->id);

        $routineTemplate->update([
            'name' => $data['name'],
            'goal' => $data['goal'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $this->syncItems($routineTemplate, $data['items'] ?? []);

        return redirect()
            ->route('admin.routine_templates.index')
            ->with('success', 'Plantilla actualizada.');
    }

    public function destroy(RoutineTemplate $routineTemplate)
    {
        $routineTemplate->delete();

        return redirect()
            ->route('admin.routine_templates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    private function validateTemplate(Request $request, ?int $ignoreId = null): array
    {
        $uniqueNameRule = ['unique:routine_templates,name'];
        if ($ignoreId) {
            $uniqueNameRule = ['unique:routine_templates,name,' . $ignoreId];
        }

        return $request->validate([
            'name' => array_merge(['required', 'string', 'max:120'], $uniqueNameRule),
            'goal' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],

            'items' => ['nullable', 'array'],
            'items.*.day' => ['required_with:items.*', 'string', 'in:' . implode(',', array_keys(Routine::days()))],
            'items.*.exercise_name' => ['required_with:items.*', 'string', 'max:255'],
            'items.*.sets' => ['nullable', 'integer', 'min:1', 'max:50'],
            'items.*.reps' => ['nullable', 'string', 'max:50'],
            'items.*.rest_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function syncItems(RoutineTemplate $template, array $items): void
    {
        $template->items()->delete();

        $order = 0;
        foreach ($items as $item) {
            if (empty($item['exercise_name'])) {
                continue;
            }

            $template->items()->create([
                'day' => $item['day'],
                'order' => $order++,
                'exercise_name' => $item['exercise_name'],
                'sets' => $item['sets'] ?? null,
                'reps' => $item['reps'] ?? null,
                'rest_seconds' => $item['rest_seconds'] ?? null,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Routine;
use App\Models\RoutineTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutineController extends Controller
{
    public function create(Patient $patient)
    {
        $templates = RoutineTemplate::query()
            ->where('is_active', true)
            ->with(['items'])
            ->orderBy('name')
            ->get();

        $templatesJson = $templates
            ->mapWithKeys(function (RoutineTemplate $tpl) {
                return [
                    (string) $tpl->id => [
                        'id' => $tpl->id,
                        'name' => $tpl->name,
                        'goal' => $tpl->goal,
                        'notes' => $tpl->notes,
                        'items' => $tpl->items
                            ->values()
                            ->map(function ($it) {
                                return [
                                    'day' => $it->day,
                                    'exercise_name' => $it->exercise_name,
                                    'sets' => $it->sets,
                                    'reps' => $it->reps,
                                    'rest_seconds' => $it->rest_seconds,
                                    'notes' => $it->notes,
                                ];
                            })
                            ->values()
                            ->all(),
                    ],
                ];
            })
            ->all();

        return view('supervisor.routines.create', [
            'patient' => $patient,
            'days' => Routine::days(),
            'templates' => $templates,
            'templatesJson' => $templatesJson,
        ]);
    }

    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'goal' => ['nullable', 'string', 'max:200'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['nullable', 'array'],
            'items.*.day' => ['required_with:items.*', 'string', 'in:' . implode(',', array_keys(Routine::days()))],
            'items.*.exercise_name' => ['required_with:items.*', 'string', 'max:255'],
            'items.*.sets' => ['nullable', 'integer', 'min:1', 'max:50'],
            'items.*.reps' => ['nullable', 'string', 'max:50'],
            'items.*.rest_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $patient->routines()->where('is_active', true)->update(['is_active' => false]);

        $routine = $patient->routines()->create([
            'title' => $data['title'] ?? null,
            'goal' => $data['goal'] ?? null,
            'valid_from' => $data['valid_from'],
            'valid_until' => $data['valid_until'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        if (!empty($data['items'])) {
            foreach ($data['items'] as $order => $item) {
                if (empty($item['exercise_name'])) {
                    continue;
                }

                $routine->items()->create([
                    'day' => $item['day'],
                    'order' => $order,
                    'exercise_name' => $item['exercise_name'],
                    'sets' => $item['sets'] ?? null,
                    'reps' => $item['reps'] ?? null,
                    'rest_seconds' => $item['rest_seconds'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('supervisor.pacientes.show', $patient)
            ->with('success', 'Rutina creada correctamente.');
    }

    public function show(Patient $patient, Routine $routine)
    {
        abort_if($routine->patient_id !== $patient->id, 404);
        $routine->load('items');

        return view('supervisor.routines.show', [
            'patient' => $patient,
            'routine' => $routine,
            'days' => Routine::days(),
        ]);
    }

    public function deactivate(Patient $patient, Routine $routine)
    {
        abort_if($routine->patient_id !== $patient->id, 404);
        $routine->update(['is_active' => false]);
        return back()->with('success', 'Rutina archivada.');
    }

    public function destroy(Patient $patient, Routine $routine)
    {
        abort_if($routine->patient_id !== $patient->id, 404);
        $routine->delete();

        return redirect()
            ->route('supervisor.pacientes.show', $patient)
            ->with('success', 'Rutina eliminada.');
    }
}

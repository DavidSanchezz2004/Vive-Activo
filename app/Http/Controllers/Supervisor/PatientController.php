<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $patients = Patient::query()
            ->with(['user', 'activeAssignment.student.user'])
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('supervisor.pacientes.index', compact('patients', 'q'));
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'user',
            'activeAssignment.student.user',
        ]);

        $nutritionPlans = $patient->nutritionPlans()->withCount('items')->get();
        $routines = $patient->routines()->withCount('items')->get();

        return view('supervisor.pacientes.show', compact('patient', 'nutritionPlans', 'routines'));
    }
}

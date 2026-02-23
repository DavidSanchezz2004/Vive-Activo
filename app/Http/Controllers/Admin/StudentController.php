<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\District;
use App\Models\Student;
use App\Models\University;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::query()
            ->with(['user', 'district', 'university', 'career'])
            ->withCount([
                'assignments as active_patients' => fn ($q) => $q->where('is_active', true),
            ])
            ->withAvg('sessionReviews as avg_rating', 'rating')
            ->when($request->filled('distrito'), fn ($q) => $q->where('district_id', $request->distrito))
            ->when($request->filled('universidad'), fn ($q) => $q->where('university_id', $request->universidad))
            ->when($request->filled('carrera'), fn ($q) => $q->where('career_id', $request->carrera))
            ->when($request->filled('ciclo'), fn ($q) => $q->where('cycle', $request->ciclo))
            ->when($request->filled('estado'), function ($q) use ($request) {
                $q->where('is_active', $request->estado === 'activo');
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->q;
                $q->whereHas('user', fn ($u) => $u
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                );
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $kpis = [
            'total'       => Student::count(),
            'activos'     => Student::where('is_active', true)->count(),
            'asignados'   => Student::whereHas('assignments', fn ($q) => $q->where('is_active', true))->count(),
            'sin_asignar' => Student::whereDoesntHave('assignments', fn ($q) => $q->where('is_active', true))->count(),
        ];

        $distritos   = District::orderBy('name')->get();
        $universidades = University::where('is_active', true)->orderBy('name')->get();
        $carreras    = Career::where('is_active', true)->orderBy('name')->get();

        return view('admin.alumnos.index', compact(
            'students', 'kpis', 'distritos', 'universidades', 'carreras'
        ));
    }

    public function edit(Student $student)
    {
        $student->load(['user', 'district', 'university', 'career']);

        $distritos   = District::orderBy('name')->get();
        $universidades = University::where('is_active', true)->orderBy('name')->get();
        $carreras    = Career::where('is_active', true)->orderBy('name')->get();

        return view('admin.alumnos.edit', compact(
            'student', 'distritos', 'universidades', 'carreras'
        ));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'district_id'   => ['nullable', 'exists:districts,id'],
            'university_id' => ['nullable', 'exists:universities,id'],
            'career_id'     => ['nullable', 'exists:careers,id'],
            'cycle'         => ['nullable', 'integer', 'min:1', 'max:12'],
            'sex'           => ['nullable', 'in:M,F,O'],
            'birthdate'     => ['nullable', 'date', 'before:today'],
            'is_active'     => ['boolean'],
        ]);

        $student->update($data);

        return redirect()
            ->route('admin.alumnos.index')
            ->with('ok', "Perfil de {$student->user->name} actualizado.");
    }

    public function toggle(Student $student)
    {
        $student->update(['is_active' => !$student->is_active]);

        $estado = $student->is_active ? 'activado' : 'desactivado';

        return back()->with('ok', "Alumno {$estado} correctamente.");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Student;
use App\Models\Assignment;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::query()
            ->with([
                'user',
                'user.profile',
                'activeAssignment.student.user',
            ])
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->q;
                $q->whereHas('user', fn ($u) => $u
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                )->orWhereHas('user.profile', fn ($p) => $p
                    ->where('document_number', 'like', "%{$search}%")
                );
            })
            ->when($request->filled('distrito'), function ($q) use ($request) {
                $q->whereHas('user.profile', fn ($p) => $p
                    ->where('district', 'like', "%{$request->distrito}%")
                );
            })
            ->when($request->filled('estado'), function ($q) use ($request) {
                $q->where('is_active', $request->estado === 'activo');
            })
            ->when($request->filled('asignacion'), function ($q) use ($request) {
                if ($request->asignacion === 'con') {
                    $q->whereHas('assignments', fn ($a) => $a->where('is_active', true));
                } else {
                    $q->whereDoesntHave('assignments', fn ($a) => $a->where('is_active', true));
                }
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $kpis = [
            'total'       => Patient::count(),
            'activos'     => Patient::where('is_active', true)->count(),
            'asignados'   => Patient::whereHas('assignments', fn ($q) => $q->where('is_active', true))->count(),
            'sin_asignar' => Patient::whereDoesntHave('assignments', fn ($q) => $q->where('is_active', true))->count(),
        ];

        return view('admin.pacientes.index', compact('patients', 'kpis'));
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'user.profile',
            'activeAssignment.student.user',
            'assignments' => fn ($q) => $q->with('student.user', 'assignedBy')->orderByDesc('assigned_at'),
            'activePlan.plan',
            'patientPlans.plan',
            'patientPlans.createdBy',
        ]);

        // ── Detectar conflictos para el warning de reasignación ──
        $assignment = $patient->activeAssignment;
        $conflicts  = collect();

        if ($assignment) {
            $pendingSessions = $patient->patientSessions()
                ->where('student_id', $assignment->student_id)
                ->where('status', 'pending')
                ->where('scheduled_at', '>=', now())
                ->with('student.user')
                ->orderBy('scheduled_at')
                ->get();

            $confirmedConsultations = $patient->consultations()
                ->where('student_id', $assignment->student_id)
                ->where('status', 'confirmed')
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->get();

            $conflicts = $pendingSessions->toBase()->map(fn ($s) => [
                'type' => 'sesion',
                'date' => $s->scheduled_at?->format('d/m/Y H:i'),
                'desc' => 'Sesión pendiente con ' . ($s->student?->user?->name ?? '—'),
            ])->merge(
                $confirmedConsultations->toBase()->map(fn ($c) => [
                    'type' => 'consulta',
                    'date' => $c->scheduled_at?->format('d/m/Y H:i'),
                    'desc' => 'Consulta confirmada (' . $c->modeLabel() . ')',
                ])
            )->sortBy('date')->values();
        }

        // Alumnos activos para el modal de asignación
        $students = Student::where('is_active', true)
            ->whereHas('user', fn ($q) => $q->where('role', 'student'))
            ->with('user')
            ->get();

        // Planes nutricionales (todos, activos + archivados)
        $nutritionPlans = $patient->nutritionPlans()->with('createdBy')->get();

        // Catálogo de planes activos
        $plans = Plan::active()->orderBy('name')->get();

        return view('admin.pacientes.show', compact('patient', 'students', 'conflicts', 'nutritionPlans', 'plans'));
    }

    public function assign(Request $request, Patient $patient)
    {
        $hasConflicts = (bool) $request->input('has_conflicts', false);

        $rules = [
            'student_id' => ['required', 'exists:students,id'],
            'reason'     => ['nullable', 'string', 'max:500'],
        ];

        if ($hasConflicts) {
            $rules['reason']            = ['required', 'string', 'min:5', 'max:500'];
            $rules['confirm_conflicts'] = ['required', 'accepted'];
        }

        $data = $request->validate($rules, [
            'confirm_conflicts.required' => 'Debes confirmar que entiendes el impacto de la reasignación.',
            'confirm_conflicts.accepted' => 'Debes marcar la casilla de confirmación para continuar.',
            'reason.required'            => 'El motivo es obligatorio cuando hay sesiones o consultas pendientes.',
            'reason.min'                 => 'El motivo debe ser descriptivo (mínimo 5 caracteres).',
        ]);

        DB::transaction(function () use ($patient, $data) {
            Assignment::where('patient_id', $patient->id)
                ->where('is_active', true)
                ->update([
                    'is_active'     => false,
                    'unassigned_at' => now(),
                ]);

            Assignment::create([
                'patient_id'  => $patient->id,
                'student_id'  => $data['student_id'],
                'assigned_by' => Auth::id(),
                'is_active'   => true,
                'reason'      => $data['reason'] ?? null,
            ]);
        });

        return back()->with('ok', 'Asignación realizada correctamente.');
    }

    public function unassign(Patient $patient)
    {
        Assignment::where('patient_id', $patient->id)
            ->where('is_active', true)
            ->update([
                'is_active'     => false,
                'unassigned_at' => now(),
            ]);

        return back()->with('ok', 'Asignación cerrada correctamente.');
    }

    public function toggle(Patient $patient)
    {
        $patient->update(['is_active' => !$patient->is_active]);

        return back()->with('ok', 'Estado del paciente actualizado.');
    }
}

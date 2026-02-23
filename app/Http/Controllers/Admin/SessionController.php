<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatientPlan;
use App\Models\PatientSession;
use App\Models\Patient;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = PatientSession::query()
            ->with(['patient.user', 'student.user'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->whereHas('patient.user', fn ($u) =>
                    $u->where('name', 'like', "%{$request->q}%")
                      ->orWhere('email', 'like', "%{$request->q}%")
                );
            })
            ->when($request->filled('estado'), fn ($q) => $q->where('status', $request->estado))
            ->when($request->filled('alumno'), fn ($q) =>
                $q->whereHas('student.user', fn ($u) =>
                    $u->where('name', 'like', "%{$request->alumno}%")
                )
            )
            ->when($request->filled('fecha'), fn ($q) =>
                $q->whereDate('scheduled_at', $request->fecha)
            )
            ->orderByDesc('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sesiones.index', compact('sessions'));
    }

    public function create(Request $request)
    {
        $patients = Patient::where('is_active', true)
            ->with(['user', 'activeAssignment.student.user'])
            ->get();

        $students = Student::where('is_active', true)->with('user')->get();

        $selectedPatient = $request->filled('patient_id')
            ? Patient::with('user', 'activeAssignment.student.user')->find($request->patient_id)
            : null;

        return view('admin.sesiones.create', compact('patients', 'students', 'selectedPatient'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id'   => ['required', 'exists:patients,id'],
            'student_id'   => ['required', 'exists:students,id'],
            'scheduled_at' => ['required', 'date'],
            'status'       => ['required', 'in:pending,done,no_show,rescheduled,cancelled'],
            'deducts'      => ['boolean'],
            'notes'        => ['nullable', 'string'],
        ]);

        PatientSession::create([
            ...$data,
            'deducts'    => $request->boolean('deducts'),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route(request()->routeIs('admin.*') ? 'admin.sesiones.index' : 'supervisor.sesiones.index')
            ->with('ok', 'Sesión programada correctamente.');
    }

    public function edit(PatientSession $sesione)
    {
        $sesione->load(['patient.user', 'student.user']);
        $patients = Patient::where('is_active', true)->with('user')->get();
        $students = Student::where('is_active', true)->with('user')->get();

        return view('admin.sesiones.edit', [
            'session'  => $sesione,
            'patients' => $patients,
            'students' => $students,
        ]);
    }

    public function update(Request $request, PatientSession $sesione)
    {
        $data = $request->validate([
            'student_id'   => ['required', 'exists:students,id'],
            'scheduled_at' => ['required', 'date'],
            'status'       => ['required', 'in:pending,done,no_show,rescheduled,cancelled'],
            'deducts'      => ['boolean'],
            'notes'        => ['nullable', 'string'],
        ]);

        $sesione->update([
            ...$data,
            'deducts' => $request->boolean('deducts'),
        ]);

        return redirect()
            ->route(request()->routeIs('admin.*') ? 'admin.sesiones.index' : 'supervisor.sesiones.index')
            ->with('ok', 'Sesión actualizada correctamente.');
    }

    public function updateStatus(Request $request, PatientSession $sesione)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,done,no_show,rescheduled,cancelled'],
        ]);

        $newStatus = $data['status'];
        $updates = ['status' => $newStatus];

        if ($newStatus === 'done') {
            $updates['attended_at'] = $sesione->attended_at ?? now();

            $date = $sesione->scheduled_at ?? now();

            $plan = PatientPlan::query()
                ->with('plan')
                ->where('patient_id', $sesione->patient_id)
                ->whereDate('starts_at', '<=', $date)
                ->whereDate('ends_at', '>=', $date)
                ->orderByDesc('starts_at')
                ->first();

            $shouldDeduct = false;
            if ($plan) {
                $plan->recalculateSessionsUsed();

                $total = (int) ($plan->plan?->sessions_total ?? 0);
                $shouldDeduct = $total === 0 || ($plan->sessionsRemaining() ?? 0) > 0;
            }

            $updates['deducts'] = $shouldDeduct;
        } else {
            $updates['deducts'] = false;
            $updates['attended_at'] = null;
        }

        $sesione->update($updates);

        return back()->with('ok', 'Estado de sesión actualizado.');
    }
}

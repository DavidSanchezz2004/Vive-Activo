<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    private function routePrefix(Request $request): string
    {
        return $request->routeIs('supervisor.*') ? 'supervisor' : 'admin';
    }

    public function index(Request $request)
    {
        $consultations = Consultation::query()
            ->with(['patient.user', 'student.user'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->whereHas('patient.user', fn ($u) =>
                    $u->where('name', 'like', "%{$request->q}%")
                      ->orWhere('email', 'like', "%{$request->q}%")
                );
            })
            ->when($request->filled('modo'), fn ($q) => $q->where('mode', $request->modo))
            ->when($request->filled('estado'), fn ($q) => $q->where('status', $request->estado))
            ->when($request->filled('fecha'), fn ($q) =>
                $q->whereDate('scheduled_at', $request->fecha)
            )
            ->orderByDesc('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.consultas.index', compact('consultations'));
    }

    public function create(Request $request)
    {
        $patients = Patient::where('is_active', true)
            ->with(['user', 'activeAssignment.student.user'])
            ->get();

        // Si viene patient_id preseleccionado (desde show del paciente)
        $selectedPatient = $request->filled('patient_id')
            ? Patient::with('user', 'activeAssignment.student.user')->find($request->patient_id)
            : null;

        return view('admin.consultas.create', compact('patients', 'selectedPatient'));
    }

    public function store(Request $request)
    {
        $prefix = $this->routePrefix($request);

        $data = $request->validate([
            'patient_id'   => ['required', 'exists:patients,id'],
            'mode'         => ['required', 'in:presencial,zoom,meet'],
            'type'         => ['nullable', 'string', 'max:80'],
            'scheduled_at' => ['required', 'date'],
            'meeting_url'  => ['nullable', 'url', 'max:500', 'required_if:mode,zoom', 'required_if:mode,meet'],
            'notes'        => ['nullable', 'string'],
        ]);

        // Auto-asignar el alumno activo del paciente
        $patient = Patient::with('activeAssignment.student')->find($data['patient_id']);
        $studentId = $patient->activeAssignment?->student_id ?? null;

        Consultation::create([
            ...$data,
            'student_id'   => $studentId,
            'status'       => 'pending_confirmation',
            'requested_at' => now(),
            'created_by'   => Auth::id(),
        ]);

        return redirect()
            ->route("{$prefix}.consultas.index")
            ->with('ok', 'Consulta programada correctamente.');
    }

    public function edit(Consultation $consultum)
    {
        $consultum->load(['patient.user', 'student.user']);
        $patients = Patient::where('is_active', true)->with('user')->get();
        $students = Student::where('is_active', true)->with('user')->get();

        return view('admin.consultas.edit', [
            'consultation' => $consultum,
            'patients'     => $patients,
            'students'     => $students,
            'modes'        => Consultation::MODES,
            'statuses'     => Consultation::STATUSES,
        ]);
    }

    public function update(Request $request, Consultation $consultum)
    {
        $prefix = $this->routePrefix($request);

        $data = $request->validate([
            'student_id'   => ['nullable', 'exists:students,id'],
            'mode'         => ['required', 'in:presencial,zoom,meet'],
            'type'         => ['nullable', 'string', 'max:80'],
            'status'       => ['required', 'in:pending_confirmation,confirmed,completed,cancelled'],
            'scheduled_at' => ['required', 'date'],
            'meeting_url'  => ['nullable', 'url', 'max:500'],
            'notes'        => ['nullable', 'string'],
        ]);

        $consultum->update($data);

        return redirect()
            ->route("{$prefix}.consultas.index")
            ->with('ok', 'Consulta actualizada correctamente.');
    }

    public function updateStatus(Request $request, Consultation $consultum)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending_confirmation,confirmed,completed,cancelled'],
        ]);

        $consultum->update($data);

        return back()->with('ok', 'Estado de consulta actualizado.');
    }

    public function destroy(Consultation $consultum)
    {
        if ($consultum->status !== 'pending_confirmation') {
            return back()->with('error', 'Solo se pueden eliminar consultas en estado pendiente.');
        }

        $consultum->delete();

        return redirect()
            ->route(request()->routeIs('supervisor.*') ? 'supervisor.consultas.index' : 'admin.consultas.index')
            ->with('ok', 'Consulta eliminada.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PatientPlan;
use App\Models\PatientSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    /** Obtiene el Student del usuario autenticado o 404 */
    private function getStudent(): Student
    {
        return Student::where('user_id', Auth::id())->with('user')->firstOrFail();
    }

    /**
     * Dashboard principal: KPIs + próximas sesiones + resumen.
     */
    public function dashboard()
    {
        $student = $this->getStudent();

        // KPIs del mes actual
        $now           = now();
        $startOfMonth  = $now->copy()->startOfMonth();

        $sesionesEsteMes   = $student->sessions()
            ->where('status', 'done')
            ->where('scheduled_at', '>=', $startOfMonth)
            ->count();

        $sesionesTotales   = $student->sessions()->where('status', 'done')->count();

        $noShowEsteMes     = $student->sessions()
            ->where('status', 'no_show')
            ->where('scheduled_at', '>=', $startOfMonth)
            ->count();

        $pacientesActivos  = $student->activePatients()->count();

        // Próximas 5 sesiones pending
        $proximasSesiones  = $student->sessions()
            ->where('status', 'pending')
            ->where('scheduled_at', '>=', $now)
            ->with('patient.user')
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        // Últimas 5 sesiones completadas o no_show
        $historialSesiones = $student->sessions()
            ->whereIn('status', ['done', 'no_show', 'rescheduled', 'cancelled'])
            ->with('patient.user')
            ->orderByDesc('scheduled_at')
            ->take(5)
            ->get();

        // Cumplimiento del mes (done / (done + no_show))
        $doneMes    = $sesionesEsteMes;
        $noShowMes  = $noShowEsteMes;
        $totalMes   = $doneMes + $noShowMes;
        $cumplimiento = $totalMes > 0 ? round(($doneMes / $totalMes) * 100) : 100;

        return view('estudiante.dashboard', compact(
            'student',
            'sesionesEsteMes',
            'sesionesTotales',
            'noShowEsteMes',
            'pacientesActivos',
            'proximasSesiones',
            'historialSesiones',
            'cumplimiento',
        ));
    }

    /**
     * Lista de mis pacientes asignados activamente.
     */
    public function pacientes(Request $request)
    {
        $student   = $this->getStudent();

        $pacientes = $student->activePatients()
            ->with([
                'user',
                'user.profile',
                'patientSessions' => fn ($q) => $q->where('student_id', $student->id)
                    ->where('status', 'pending')
                    ->where('scheduled_at', '>=', now())
                    ->orderBy('scheduled_at')
                    ->take(1),
            ])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$request->q}%"));
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('estudiante.pacientes', compact('student', 'pacientes'));
    }

    /**
     * Cola de sesiones pendientes del alumno.
     */
    public function sesiones(Request $request)
    {
        $student = $this->getStudent();

        $pendientes = $student->sessions()
            ->where('status', 'pending')
            ->with('patient.user', 'patient.user.profile')
            ->when($request->filled('fecha'), fn ($q) =>
                $q->whereDate('scheduled_at', $request->fecha)
            )
            ->orderBy('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        $historial = $student->sessions()
            ->whereIn('status', ['done', 'no_show', 'rescheduled', 'cancelled'])
            ->with('patient.user')
            ->orderByDesc('scheduled_at')
            ->paginate(15, ['*'], 'hist_page')
            ->withQueryString();

        return view('estudiante.sesiones', compact('student', 'pendientes', 'historial'));
    }

    /**
     * Formulario / modal de registro de atención de una sesión.
     */
    public function registrarAtencion(Request $request, PatientSession $sesione)
    {
        $student = $this->getStudent();

        // Solo puede registrar atención de sus propias sesiones
        abort_if($sesione->student_id !== $student->id, 403);
        abort_if($sesione->status !== 'pending', 409, 'Esta sesión ya fue cerrada.');

        $data = $request->validate([
            'status'       => ['required', 'in:done,no_show,rescheduled,cancelled'],
            'notes'        => ['nullable', 'string', 'max:1000'],
            'weight_kg'    => ['nullable', 'numeric', 'min:20', 'max:300'],
            'rpe'          => ['nullable', 'integer', 'min:1', 'max:10'],
            'rescheduled_at' => ['nullable', 'required_if:status,rescheduled', 'date'],
        ], [
            'rescheduled_at.required_if' => 'Debes indicar la nueva fecha si reprogramas la sesión.',
        ]);

        $deducts = false;
        if ($data['status'] === 'done') {
            $date = $sesione->scheduled_at ?? now();

            $plan = PatientPlan::query()
                ->with('plan')
                ->where('patient_id', $sesione->patient_id)
                ->whereDate('starts_at', '<=', $date)
                ->whereDate('ends_at', '>=', $date)
                ->orderByDesc('starts_at')
                ->first();

            if ($plan) {
                $plan->recalculateSessionsUsed();
                $total = (int) ($plan->plan?->sessions_total ?? 0);
                $deducts = $total === 0 || ($plan->sessionsRemaining() ?? 0) > 0;
            }
        }

        $sesione->update([
            'status'         => $data['status'],
            'notes'          => $data['notes'] ?? null,
            'weight_kg'      => $data['weight_kg'] ?? null,
            'rpe'            => $data['rpe'] ?? null,
            'rescheduled_at' => $data['status'] === 'rescheduled' ? ($data['rescheduled_at'] ?? null) : null,
            'attended_at'    => in_array($data['status'], ['done', 'no_show']) ? now() : null,
            'deducts'        => $deducts,
        ]);

        return back()->with('ok_sesion', match($data['status']) {
            'done'        => 'Sesión registrada como realizada. ¡Buen trabajo!',
            'no_show'     => 'Sesión marcada como no asistida.',
            'rescheduled' => 'Sesión reprogramada correctamente.',
            'cancelled'   => 'Sesión cancelada.',
            default       => 'Sesión actualizada.',
        });
    }
}

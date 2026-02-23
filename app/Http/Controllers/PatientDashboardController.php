<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;

class PatientDashboardController extends Controller
{
    private function getPatient()
    {
        return Patient::where('user_id', Auth::id())
            ->with(['user', 'activeAssignment.student.user'])
            ->firstOrFail();
    }

    public function dashboard()
    {
        $patient = $this->getPatient();

        $activePlan = $patient->activePlan()->with('plan')->first();

        $nextConsultation = $patient->consultations()
            ->whereIn('status', ['confirmed', 'pending_confirmation'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->first();

        $nextSession = $patient->patientSessions()
            ->where('status', 'pending')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->with('student.user')
            ->first();

        $recentSessions = $patient->patientSessions()
            ->whereIn('status', ['done', 'no_show', 'rescheduled', 'cancelled'])
            ->orderByDesc('scheduled_at')
            ->with('student.user')
            ->take(5)
            ->get();

        $sessionsDone      = $patient->patientSessions()->where('status', 'done')->count();
        $sessionsTotal     = $patient->patientSessions()->count();
        $consultationsDone = $patient->consultations()->where('status', 'completed')->count();

        return view('paciente.dashboard', compact(
            'patient', 'nextConsultation', 'nextSession',
            'recentSessions', 'sessionsDone', 'sessionsTotal', 'consultationsDone',
            'activePlan',
        ));
    }

    public function plan()
    {
        $patient = $this->getPatient();

        // Plan nutricional activo con sus ítems
        $nutritionPlan = $patient->activeNutritionPlan()->with('items')->first();

        // Plan comercial activo
        $activePlan = $patient->activePlan()->with('plan')->first();

        return view('paciente.plan', compact('patient', 'nutritionPlan', 'activePlan'));
    }

    public function rutina()
    {
        $patient = $this->getPatient();

        $routine = $patient->activeRoutine()->with('items')->first();

        return view('paciente.rutina', compact('patient', 'routine'));
    }

    public function consultas()
    {
        $patient = $this->getPatient();

        $proximas = $patient->consultations()
            ->whereIn('status', ['confirmed', 'pending_confirmation'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        $historial = $patient->consultations()
            ->whereIn('status', ['completed', 'cancelled', 'no_show'])
            ->orderByDesc('scheduled_at')
            ->paginate(10);

        return view('paciente.consultas', compact('patient', 'proximas', 'historial'));
    }

    public function sesiones()
    {
        $patient = $this->getPatient();

        $proximas = $patient->patientSessions()
            ->where('status', 'pending')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->with('student.user')
            ->get();

        $historial = $patient->patientSessions()
            ->whereIn('status', ['done', 'no_show', 'rescheduled', 'cancelled'])
            ->orderByDesc('scheduled_at')
            ->with(['student.user', 'review'])
            ->paginate(10);

        return view('paciente.sesiones', compact('patient', 'proximas', 'historial'));
    }
}

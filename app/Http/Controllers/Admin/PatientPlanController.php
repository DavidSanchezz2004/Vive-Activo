<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientPlan;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientPlanController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'plan_id'   => ['required', 'exists:plans,id'],
            'starts_at' => ['required', 'date'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);

        $startsAt = Carbon::parse($data['starts_at'])->startOfDay();
        $endsAt   = $startsAt->copy()->addMonthsNoOverflow(max(1, (int) $plan->duration_months))->endOfDay();

        DB::transaction(function () use ($patient, $plan, $startsAt, $endsAt, $data) {
            // Cierra planes activos previos para evitar superposición
            PatientPlan::where('patient_id', $patient->id)
                ->where('status', 'active')
                ->update([
                    'status'  => 'completed',
                    'ends_at' => $startsAt->copy()->subDay()->toDateString(),
                ]);

            $patientPlan = PatientPlan::create([
                'patient_id'    => $patient->id,
                'plan_id'       => $plan->id,
                'starts_at'     => $startsAt->toDateString(),
                'ends_at'       => $endsAt->toDateString(),
                'sessions_used' => 0,
                'status'        => 'active',
                'notes'         => $data['notes'] ?? null,
                'created_by'    => Auth::id(),
            ]);

            $patientPlan->recalculateSessionsUsed();
        });

        return back()->with('ok', 'Plan comercial asignado correctamente.');
    }

    public function cancel(Patient $patient, PatientPlan $patientPlan)
    {
        abort_unless($patientPlan->patient_id === $patient->id, 404);

        $patientPlan->update([
            'status'  => 'cancelled',
            'ends_at' => now()->toDateString(),
        ]);

        return back()->with('ok', 'Plan comercial cancelado.');
    }
}

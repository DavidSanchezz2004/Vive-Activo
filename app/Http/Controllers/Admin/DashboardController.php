<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\SessionReview;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $riskDaysWithoutAttendance = (int) config('risk.days_without_attendance', 14);
        $riskPlanExpiringDays = (int) config('risk.plan_expiring_days', 7);
        $riskNoShowWindowDays = (int) config('risk.no_show_window_days', 30);
        $riskNoShowThreshold = (int) config('risk.no_show_threshold', 3);
        $riskLowComplianceThreshold = (int) config('risk.low_compliance_threshold', 60);
        $riskLowComplianceMinSessions = (int) config('risk.low_compliance_min_sessions', 5);
        $riskMaxItems = (int) config('risk.max_items', 8);

        $activePatientsTotal = Patient::query()->where('is_active', true)->count();
        $activePatientsWithoutPlan = Patient::query()
            ->where('is_active', true)
            ->whereDoesntHave('activePlan')
            ->count();

        $activePatientsWithPlan = max(0, $activePatientsTotal - $activePatientsWithoutPlan);

        $patientsByPlan = Plan::query()
            ->active()
            ->withCount([
                'patientPlans as active_patients_count' => function ($q) {
                    $q->where('status', 'active')
                        ->whereHas('patient', fn ($p) => $p->where('is_active', true));
                },
            ])
            ->orderByDesc('active_patients_count')
            ->orderBy('name')
            ->get();

        $patientsByDistrict = Patient::query()
            ->where('patients.is_active', true)
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->selectRaw("COALESCE(NULLIF(user_profiles.district,''), 'Sin distrito') as district")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('district')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $plansForFilter = Plan::query()->active()->orderBy('name')->get(['id', 'name']);

        $patientsQuery = Patient::query()
            ->with(['user.profile', 'activePlan.plan', 'activeAssignment.student.user'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q')->toString();
                $q->whereHas('user', fn ($u) =>
                    $u->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                )->orWhereHas('user.profile', fn ($p) =>
                    $p->where('document_number', 'like', "%{$term}%")
                );
            })
            ->when($request->filled('district'), function ($q) use ($request) {
                $district = $request->string('district')->toString();
                $q->whereHas('user.profile', fn ($p) => $p->where('district', 'like', "%{$district}%"));
            })
            ->when($request->filled('plan_id'), function ($q) use ($request) {
                $planId = (int) $request->input('plan_id');
                $q->whereHas('activePlan', fn ($pp) => $pp->where('plan_id', $planId));
            })
            ->orderByDesc('id');

        $patients = $patientsQuery->paginate(10)->withQueryString();

        $students = Student::query()
            ->where('is_active', true)
            ->with(['user', 'district', 'career', 'university'])
            ->withCount([
                'sessions as done_month' => fn ($q) => $q->where('status', 'done')->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth]),
                'sessions as no_show_month' => fn ($q) => $q->where('status', 'no_show')->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth]),
            ])
            ->orderByDesc('done_month')
            ->orderBy('id')
            ->paginate(10, ['*'], 'stu_page')
            ->withQueryString();

        $topSessionReviews = SessionReview::query()
            ->with(['session.student.user', 'patient.user'])
            ->orderByDesc('rating')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // ---------------------
        // Fase 11 (A3): Alertas de riesgo (en vivo)
        // ---------------------

        // Subquery: última sesión done por paciente
        $lastDoneSub = DB::table('patient_sessions')
            ->select('patient_id', DB::raw('MAX(scheduled_at) as last_done_at'))
            ->where('status', 'done')
            ->groupBy('patient_id');

        $cutoffAttendance = $now->copy()->subDays($riskDaysWithoutAttendance)->startOfDay();

        $patientsWithoutAttendanceQuery = DB::table('patients')
            ->join('patient_plans', function ($j) {
                $j->on('patient_plans.patient_id', '=', 'patients.id')
                    ->where('patient_plans.status', '=', 'active');
            })
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->leftJoinSub($lastDoneSub, 'sd', fn ($j) => $j->on('sd.patient_id', '=', 'patients.id'))
            ->where('patients.is_active', true)
            ->where(function ($q) use ($cutoffAttendance) {
                $q->whereNull('sd.last_done_at')
                    ->orWhere('sd.last_done_at', '<', $cutoffAttendance);
            });

        $alertNoAttendanceCount = (int) (clone $patientsWithoutAttendanceQuery)
            ->distinct('patients.id')
            ->count('patients.id');

        $alertNoAttendanceItems = (clone $patientsWithoutAttendanceQuery)
            ->select([
                'patients.id as patient_id',
                'users.name as patient_name',
                'users.email as patient_email',
                'sd.last_done_at',
            ])
            ->orderByRaw('sd.last_done_at IS NULL DESC')
            ->orderBy('sd.last_done_at')
            ->limit($riskMaxItems)
            ->get();

        // Plan por vencer
        $planExpiringFrom = $now->copy()->toDateString();
        $planExpiringTo = $now->copy()->addDays($riskPlanExpiringDays)->toDateString();

        $plansExpiringQuery = DB::table('patient_plans')
            ->join('patients', 'patient_plans.patient_id', '=', 'patients.id')
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->join('plans', 'patient_plans.plan_id', '=', 'plans.id')
            ->where('patient_plans.status', 'active')
            ->where('patients.is_active', true)
            ->whereBetween('patient_plans.ends_at', [$planExpiringFrom, $planExpiringTo]);

        $alertPlansExpiringCount = (int) (clone $plansExpiringQuery)->count();
        $alertPlansExpiringItems = (clone $plansExpiringQuery)
            ->select([
                'patients.id as patient_id',
                'users.name as patient_name',
                'plans.name as plan_name',
                'patient_plans.ends_at as ends_at',
            ])
            ->orderBy('patient_plans.ends_at')
            ->limit($riskMaxItems)
            ->get();

        // Demasiados no_show en ventana
        $noShowFrom = $now->copy()->subDays($riskNoShowWindowDays)->startOfDay();
        $noShowAgg = DB::table('patient_sessions')
            ->select('patient_id', DB::raw('COUNT(*) as no_show_count'), DB::raw('MAX(scheduled_at) as last_no_show_at'))
            ->where('status', 'no_show')
            ->where('scheduled_at', '>=', $noShowFrom)
            ->groupBy('patient_id')
            ->having('no_show_count', '>=', $riskNoShowThreshold);

        $noShowQuery = DB::table('patients')
            ->join('patient_plans', function ($j) {
                $j->on('patient_plans.patient_id', '=', 'patients.id')
                    ->where('patient_plans.status', '=', 'active');
            })
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->joinSub($noShowAgg, 'ns', fn ($j) => $j->on('ns.patient_id', '=', 'patients.id'))
            ->where('patients.is_active', true);

        $alertNoShowCount = (int) (clone $noShowQuery)->count();
        $alertNoShowItems = (clone $noShowQuery)
            ->select([
                'patients.id as patient_id',
                'users.name as patient_name',
                'ns.no_show_count',
                'ns.last_no_show_at',
            ])
            ->orderByDesc('ns.no_show_count')
            ->orderByDesc('ns.last_no_show_at')
            ->limit($riskMaxItems)
            ->get();

        // Paciente con plan activo pero sin próximas sesiones pending
        $noUpcomingQuery = DB::table('patients')
            ->join('patient_plans', function ($j) {
                $j->on('patient_plans.patient_id', '=', 'patients.id')
                    ->where('patient_plans.status', '=', 'active');
            })
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->where('patients.is_active', true)
            ->whereNotExists(function ($q) use ($now) {
                $q->select(DB::raw(1))
                    ->from('patient_sessions')
                    ->whereColumn('patient_sessions.patient_id', 'patients.id')
                    ->where('patient_sessions.status', 'pending')
                    ->where('patient_sessions.scheduled_at', '>=', $now);
            });

        $alertNoUpcomingCount = (int) (clone $noUpcomingQuery)
            ->distinct('patients.id')
            ->count('patients.id');

        $alertNoUpcomingItems = (clone $noUpcomingQuery)
            ->select([
                'patients.id as patient_id',
                'users.name as patient_name',
                'users.email as patient_email',
            ])
            ->orderBy('users.name')
            ->limit($riskMaxItems)
            ->get();

        // Alumno con cumplimiento bajo (mes actual)
        $studentAgg = DB::table('patient_sessions')
            ->select('student_id')
            ->selectRaw("SUM(CASE WHEN status='done' THEN 1 ELSE 0 END) as done_count")
            ->selectRaw("SUM(CASE WHEN status='no_show' THEN 1 ELSE 0 END) as no_show_count")
            ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
            ->groupBy('student_id');

        $lowComplianceQuery = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoinSub($studentAgg, 'sa', fn ($j) => $j->on('sa.student_id', '=', 'students.id'))
            ->where('students.is_active', true)
            ->whereRaw('COALESCE(sa.done_count,0) + COALESCE(sa.no_show_count,0) >= ?', [$riskLowComplianceMinSessions])
            ->whereRaw(
                "(CASE WHEN (COALESCE(sa.done_count,0) + COALESCE(sa.no_show_count,0)) = 0 THEN 100 ELSE ROUND((COALESCE(sa.done_count,0) / (COALESCE(sa.done_count,0) + COALESCE(sa.no_show_count,0))) * 100) END) < ?",
                [$riskLowComplianceThreshold]
            );

        $alertLowComplianceCount = (int) (clone $lowComplianceQuery)->count();
        $alertLowComplianceItems = (clone $lowComplianceQuery)
            ->select([
                'students.id as student_id',
                'users.name as student_name',
                DB::raw('COALESCE(sa.done_count,0) as done_count'),
                DB::raw('COALESCE(sa.no_show_count,0) as no_show_count'),
            ])
            ->orderByRaw('(COALESCE(sa.done_count,0) + COALESCE(sa.no_show_count,0)) DESC')
            ->limit($riskMaxItems)
            ->get()
            ->map(function ($row) {
                $done = (int) $row->done_count;
                $noShow = (int) $row->no_show_count;
                $den = $done + $noShow;
                $row->compliance_pct = $den > 0 ? (int) round(($done / $den) * 100) : 100;
                return $row;
            });

        $riskAlerts = [
            [
                'key' => 'no_attendance',
                'title' => "Sin asistir {$riskDaysWithoutAttendance}+ días",
                'severity' => 'danger',
                'count' => $alertNoAttendanceCount,
                'items' => $alertNoAttendanceItems,
            ],
            [
                'key' => 'plan_expiring',
                'title' => "Plan por vencer (≤ {$riskPlanExpiringDays} días)",
                'severity' => 'warning',
                'count' => $alertPlansExpiringCount,
                'items' => $alertPlansExpiringItems,
            ],
            [
                'key' => 'too_many_no_show',
                'title' => "No show ≥ {$riskNoShowThreshold} (últ. {$riskNoShowWindowDays} días)",
                'severity' => 'warning',
                'count' => $alertNoShowCount,
                'items' => $alertNoShowItems,
            ],
            [
                'key' => 'no_upcoming_sessions',
                'title' => 'Sin próximas sesiones (pending)',
                'severity' => 'info',
                'count' => $alertNoUpcomingCount,
                'items' => $alertNoUpcomingItems,
            ],
            [
                'key' => 'low_compliance_students',
                'title' => "Alumnos con cumplimiento < {$riskLowComplianceThreshold}%",
                'severity' => 'warning',
                'count' => $alertLowComplianceCount,
                'items' => $alertLowComplianceItems,
            ],
        ];

        return view('admin.dashboard', [
            'kpis' => [
                'patients_active' => $activePatientsTotal,
                'patients_with_plan' => $activePatientsWithPlan,
                'patients_without_plan' => $activePatientsWithoutPlan,
                'plans_active' => $plansForFilter->count(),
            ],
            'patientsByPlan' => $patientsByPlan,
            'patientsByDistrict' => $patientsByDistrict,
            'plansForFilter' => $plansForFilter,
            'patients' => $patients,
            'students' => $students,
            'topSessionReviews' => $topSessionReviews,
            'riskAlerts' => $riskAlerts,
            'monthLabel' => $startOfMonth->format('m/Y'),
        ]);
    }
}

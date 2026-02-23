<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\SessionReview;
use App\Models\Student;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $now = now();
        $windowDays = 30;
        $since = $now->copy()->subDays($windowDays);

        $kpis = [
            'patients_total' => Patient::count(),
            'patients_unassigned' => Patient::query()
                ->whereDoesntHave('assignments', fn ($q) => $q->where('is_active', true))
                ->count(),
            'students_active' => Student::query()->where('is_active', true)->count(),
            'sessions_pending_7d' => PatientSession::query()
                ->where('status', 'pending')
                ->whereBetween('scheduled_at', [$now, $now->copy()->addDays(7)])
                ->count(),
            'sessions_done_30d' => PatientSession::query()
                ->where('status', 'done')
                ->where('scheduled_at', '>=', $since)
                ->count(),
            'avg_rating' => round((float) SessionReview::query()->avg('rating'), 2),
        ];

        $topStudents = Student::query()
            ->with(['user'])
            ->withCount('sessionReviews as reviews_count')
            ->withAvg('sessionReviews as avg_rating', 'rating')
            ->orderByDesc('avg_rating')
            ->orderByDesc('reviews_count')
            ->limit(5)
            ->get();

        $lowStudents = Student::query()
            ->with(['user'])
            ->withCount('sessionReviews as reviews_count')
            ->withAvg('sessionReviews as avg_rating', 'rating')
            ->orderBy('avg_rating')
            ->orderByDesc('reviews_count')
            ->limit(5)
            ->get();

        $upcomingSessions = PatientSession::query()
            ->with(['patient.user', 'student.user'])
            ->where('status', 'pending')
            ->whereBetween('scheduled_at', [$now, $now->copy()->addDays(7)])
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get();

        return view('supervisor.dashboard', compact(
            'kpis',
            'windowDays',
            'topStudents',
            'lowStudents',
            'upcomingSessions'
        ));
    }

    public function reportes()
    {
        $windowDays = 30;
        $since = now()->subDays($windowDays);

        $unassignedPatients = Patient::query()
            ->with(['user', 'user.profile'])
            ->whereDoesntHave('assignments', fn ($q) => $q->where('is_active', true))
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $studentsByRating = Student::query()
            ->with(['user'])
            ->withCount('sessionReviews as reviews_count')
            ->withAvg('sessionReviews as avg_rating', 'rating')
            ->orderByDesc('avg_rating')
            ->orderByDesc('reviews_count')
            ->limit(20)
            ->get();

        $sessionsDone30d = PatientSession::query()
            ->selectRaw('student_id, count(*) as done_count')
            ->where('status', 'done')
            ->where('scheduled_at', '>=', $since)
            ->groupBy('student_id')
            ->orderByDesc('done_count')
            ->with(['student.user'])
            ->limit(20)
            ->get();

        return view('supervisor.reportes', compact(
            'windowDays',
            'unassignedPatients',
            'studentsByRating',
            'sessionsDone30d'
        ));
    }
}

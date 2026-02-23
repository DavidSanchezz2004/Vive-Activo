<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\District;
use App\Models\Student;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $activeWindowDays = 30;
        $activeSince = now()->subDays($activeWindowDays);

        $students = Student::query()
            ->with(['user', 'district', 'university', 'career'])
            ->withCount([
                'sessions as active_patients' => fn ($q) => $q
                    ->where('status', 'done')
                    ->where('scheduled_at', '>=', $activeSince)
                    ->select(DB::raw('count(distinct patient_id)')),
                'sessions as attended_patients' => fn ($q) => $q
                    ->where('status', 'done')
                    ->select(DB::raw('count(distinct patient_id)')),
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
            'total'   => Student::count(),
            'activos' => Student::where('is_active', true)->count(),
        ];

        $distritos     = District::orderBy('name')->get();
        $universidades = University::where('is_active', true)->orderBy('name')->get();
        $carreras      = Career::where('is_active', true)->orderBy('name')->get();

        return view('supervisor.alumnos.index', compact(
            'students', 'kpis', 'distritos', 'universidades', 'carreras', 'activeWindowDays'
        ));
    }

    public function show(Student $student)
    {
        $student->load(['user', 'district', 'university', 'career']);

        $reviews = $student->sessionReviews()
            ->with(['session.patient.user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $avgRating = round((float) $student->sessionReviews()->avg('rating'), 2);
        $reviewsCount = (int) $student->sessionReviews()->count();

        return view('supervisor.alumnos.show', compact('student', 'reviews', 'avgRating', 'reviewsCount'));
    }
}

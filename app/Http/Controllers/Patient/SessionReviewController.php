<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\SessionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionReviewController extends Controller
{
    public function store(PatientSession $patientSession, Request $request)
    {
        $patient = Patient::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ((int) $patientSession->patient_id !== (int) $patient->id) {
            abort(403);
        }

        if ($patientSession->status !== 'done') {
            return back()->with('error', 'Solo puedes calificar sesiones completadas.');
        }

        $validated = $request->validate([
            'session_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ((int) $validated['session_id'] !== (int) $patientSession->id) {
            abort(422);
        }

        $existing = $patientSession->review;
        if ($existing && (int) $existing->patient_id !== (int) $patient->id) {
            abort(403);
        }

        SessionReview::updateOrCreate(
            ['session_id' => $patientSession->id],
            [
                'patient_id' => $patient->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return redirect()
            ->route('paciente.sesiones')
            ->with('success', '¡Gracias! Tu calificación fue guardada.');
    }
}

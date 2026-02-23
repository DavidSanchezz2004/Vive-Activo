<?php

namespace App\Observers;

use App\Models\PatientPlan;
use App\Models\PatientSession;

class PatientSessionObserver
{
    public function saved(PatientSession $session): void
    {
        $this->recalculateForRelevantPlans($session);
    }

    public function deleted(PatientSession $session): void
    {
        $this->recalculateForRelevantPlans($session);
    }

    private function recalculateForRelevantPlans(PatientSession $session): void
    {
        $dates = [];

        $current = $session->scheduled_at;
        if ($current) {
            $dates[] = $current;
        }

        $original = $session->getOriginal('scheduled_at');
        if ($original && (!$current || $original != $current)) {
            $dates[] = $original;
        }

        $patientIds = array_filter([
            $session->patient_id,
            $session->getOriginal('patient_id'),
        ]);

        $patientIds = array_values(array_unique($patientIds));

        foreach ($patientIds as $patientId) {
            foreach ($dates as $date) {
                $plan = PatientPlan::query()
                    ->where('patient_id', $patientId)
                    ->whereDate('starts_at', '<=', $date)
                    ->whereDate('ends_at', '>=', $date)
                    ->orderByDesc('starts_at')
                    ->first();

                if ($plan) {
                    $plan->recalculateSessionsUsed();
                }
            }

            $active = PatientPlan::query()
                ->where('patient_id', $patientId)
                ->where('status', 'active')
                ->orderByDesc('starts_at')
                ->first();

            if ($active) {
                $active->recalculateSessionsUsed();
            }
        }
    }
}
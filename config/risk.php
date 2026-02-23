<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fase 11 (A3) - Alertas de riesgo (calculadas en vivo)
    |--------------------------------------------------------------------------
    */

    // Paciente sin asistir en X días (usa última sesión status=done)
    'days_without_attendance' => (int) env('RISK_DAYS_WITHOUT_ATTENDANCE', 14),

    // Plan activo por vencer en N días
    'plan_expiring_days' => (int) env('RISK_PLAN_EXPIRING_DAYS', 7),

    // Ventana para contar no_show
    'no_show_window_days' => (int) env('RISK_NO_SHOW_WINDOW_DAYS', 30),
    'no_show_threshold' => (int) env('RISK_NO_SHOW_THRESHOLD', 3),

    // Umbral de cumplimiento bajo de alumno (done/(done+no_show))
    'low_compliance_threshold' => (int) env('RISK_LOW_COMPLIANCE_THRESHOLD', 60),
    'low_compliance_min_sessions' => (int) env('RISK_LOW_COMPLIANCE_MIN_SESSIONS', 5),

    // Máximo de casos a mostrar por alerta
    'max_items' => (int) env('RISK_MAX_ITEMS', 8),
];

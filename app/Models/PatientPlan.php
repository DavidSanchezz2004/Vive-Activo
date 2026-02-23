<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PatientPlan extends Model
{
    protected $fillable = [
        'patient_id', 'plan_id',
        'starts_at', 'ends_at',
        'sessions_used', 'status',
        'notes', 'created_by',
    ];

    protected $casts = [
        'starts_at'     => 'date',
        'ends_at'       => 'date',
        'sessions_used' => 'integer',
    ];

    const STATUSES = [
        'active'    => 'Activo',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
    ];

    /* ─────────── Relaciones ─────────── */

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ─────────── Helpers ─────────── */

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Sesiones restantes (null = ilimitadas) */
    public function sessionsRemaining(): ?int
    {
        $total = $this->plan?->sessions_total ?? 0;
        if ($total === 0) return null;
        return max(0, $total - $this->sessions_used);
    }

    /** Días que quedan */
    public function daysLeft(): int
    {
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    /**
     * Recalcula y persiste sessions_used en base a patient_sessions.
     * Cuenta solo sesiones con deducts=true y status=done dentro de la vigencia del plan.
     */
    public function recalculateSessionsUsed(): int
    {
        $from = $this->starts_at?->startOfDay();
        $to   = $this->ends_at?->endOfDay();

        if (!$from || !$to) {
            $this->sessions_used = 0;
            $this->save();
            return 0;
        }

        $used = (int) DB::table('patient_sessions')
            ->where('patient_id', $this->patient_id)
            ->where('deducts', true)
            ->where('status', 'done')
            ->whereBetween('scheduled_at', [$from, $to])
            ->count();

        if ((int) $this->sessions_used !== $used) {
            $this->sessions_used = $used;
            $this->save();
        }

        return $used;
    }

    public function isExpired(): bool
    {
        return $this->ends_at?->isPast() ?? false;
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}

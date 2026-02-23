<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'description', 'slug',
        'sessions_total', 'duration_months',
        'price', 'currency', 'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'price'           => 'decimal:2',
        'sessions_total'  => 'integer',
        'duration_months' => 'integer',
    ];

    /* ─────────── Relaciones ─────────── */

    public function patientPlans(): HasMany
    {
        return $this->hasMany(PatientPlan::class);
    }

    /* ─────────── Helpers ─────────── */

    public function sessionsLabel(): string
    {
        return $this->sessions_total === 0
            ? 'Ilimitadas'
            : "{$this->sessions_total} sesiones";
    }

    public function formattedPrice(): string
    {
        return "{$this->currency} " . number_format($this->price, 2);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}

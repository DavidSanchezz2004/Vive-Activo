<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionPlan extends Model
{
    protected $fillable = [
        'patient_id', 'phase', 'goal',
        'valid_from', 'valid_until',
        'kcal_target', 'protein_g', 'carbs_g', 'fat_g',
        'notes', 'pdf_path', 'is_active', 'created_by',
    ];

    protected $casts = [
        'valid_from'  => 'date',
        'valid_until' => 'date',
        'is_active'   => 'boolean',
        'kcal_target' => 'integer',
        'protein_g'   => 'decimal:1',
        'carbs_g'     => 'decimal:1',
        'fat_g'       => 'decimal:1',
    ];

    /* ─────────── Relaciones ─────────── */

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(NutritionPlanItem::class)
                    ->orderBy('meal_time')
                    ->orderBy('order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ─────────── Helpers ─────────── */

    /** Ítems agrupados por tiempo de comida */
    public function itemsByMealTime(): \Illuminate\Support\Collection
    {
        return $this->items->groupBy('meal_time');
    }

    /** Calorías totales reales sumadas de los ítems */
    public function totalKcal(): int
    {
        return (int) $this->items->sum('kcal');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public static function mealTimes(): array
    {
        return [
            'desayuno'      => 'Desayuno',
            'media_manana'  => 'Media mañana',
            'almuerzo'      => 'Almuerzo',
            'merienda'      => 'Merienda',
            'cena'          => 'Cena',
            'pre_entreno'   => 'Pre-entreno',
            'post_entreno'  => 'Post-entreno',
        ];
    }
}

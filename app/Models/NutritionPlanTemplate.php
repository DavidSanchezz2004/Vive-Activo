<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NutritionPlanTemplate extends Model
{
    protected $fillable = [
        'name',
        'phase',
        'goal',
        'kcal_target',
        'protein_g',
        'carbs_g',
        'fat_g',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'kcal_target' => 'integer',
        'protein_g' => 'decimal:1',
        'carbs_g' => 'decimal:1',
        'fat_g' => 'decimal:1',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(NutritionPlanTemplateItem::class)
            ->orderBy('meal_time')
            ->orderBy('order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

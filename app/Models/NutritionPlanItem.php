<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionPlanItem extends Model
{
    protected $fillable = [
        'nutrition_plan_id', 'meal_time', 'order',
        'food_name', 'quantity', 'notes',
        'kcal', 'protein_g', 'carbs_g', 'fat_g',
    ];

    protected $casts = [
        'order'     => 'integer',
        'kcal'      => 'integer',
        'protein_g' => 'decimal:1',
        'carbs_g'   => 'decimal:1',
        'fat_g'     => 'decimal:1',
    ];

    public function nutritionPlan(): BelongsTo
    {
        return $this->belongsTo(NutritionPlan::class);
    }

    public function mealTimeLabel(): string
    {
        return NutritionPlan::mealTimes()[$this->meal_time] ?? $this->meal_time;
    }
}

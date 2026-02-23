<?php

namespace App\Models;

use App\Models\RoutineTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineTemplateItem extends Model
{
    protected $fillable = [
        'routine_template_id',
        'day',
        'order',
        'exercise_name',
        'sets',
        'reps',
        'rest_seconds',
        'notes',
    ];

    protected $casts = [
        'order' => 'integer',
        'sets' => 'integer',
        'rest_seconds' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(RoutineTemplate::class, 'routine_template_id');
    }
}

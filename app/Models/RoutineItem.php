<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Routine;

class RoutineItem extends Model
{
    protected $fillable = [
        'routine_id',
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

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function dayLabel(): string
    {
        return Routine::days()[$this->day] ?? $this->day;
    }
}

<?php

namespace App\Models;

use App\Models\RoutineTemplateItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineTemplate extends Model
{
    protected $fillable = [
        'name',
        'goal',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RoutineTemplateItem::class)
            ->orderBy('day')
            ->orderBy('order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

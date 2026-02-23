<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Routine;

class Patient extends Model
{
    protected $fillable = ['user_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->hasOneThrough(
            UserProfile::class,
            User::class,
            'id',        // users.id
            'user_id',   // user_profiles.user_id
            'user_id',   // patients.user_id
            'id'         // users.id
        );
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(Assignment::class)->where('is_active', true)->latest('assigned_at');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function patientSessions()
    {
        return $this->hasMany(PatientSession::class);
    }

    public function nutritionPlans()
    {
        return $this->hasMany(NutritionPlan::class)->orderByDesc('valid_from');
    }

    public function activeNutritionPlan()
    {
        return $this->hasOne(NutritionPlan::class)->where('is_active', true)->latest('valid_from');
    }

    public function patientPlans()
    {
        return $this->hasMany(PatientPlan::class)->orderByDesc('starts_at');
    }

    public function activePlan()
    {
        return $this->hasOne(PatientPlan::class)->where('status', 'active')->latest('starts_at');
    }

    public function routines()
    {
        return $this->hasMany(Routine::class)->orderByDesc('valid_from');
    }

    public function activeRoutine()
    {
        return $this->hasOne(Routine::class)->where('is_active', true)->latest('valid_from');
    }
}

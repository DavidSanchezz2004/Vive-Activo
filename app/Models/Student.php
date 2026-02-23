<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'district_id',
        'university_id',
        'career_id',
        'cycle',
        'sex',
        'birthdate',
        'is_active',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'is_active' => 'boolean',
    ];

    /* ─────────── Relaciones ─────────── */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function career()
    {
        return $this->belongsTo(Career::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    /** Sesiones registradas para este alumno */
    public function sessions()
    {
        return $this->hasMany(PatientSession::class, 'student_id');
    }

    /** Pacientes con asignación activa para este alumno */
    public function activePatients()
    {
        return $this->hasManyThrough(
            Patient::class,
            Assignment::class,
            'student_id', // FK en assignments
            'id',         // FK en patients
            'id',         // PK en students
            'patient_id'  // PK local en assignments
        )->where('assignments.is_active', true);
    }

    public function sessionReviews()
    {
        return $this->hasManyThrough(
            SessionReview::class,
            PatientSession::class,
            'student_id',
            'session_id'
        );
    }

    /* ─────────── Helpers ─────────── */

    public function age(): ?int
    {
        return $this->birthdate?->age;
    }
}

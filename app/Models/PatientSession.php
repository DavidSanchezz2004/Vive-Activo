<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSession extends Model
{
    protected $table = 'patient_sessions';

    protected $fillable = [
        'patient_id', 'student_id', 'consultation_id',
        'scheduled_at', 'status', 'deducts', 'notes', 'created_by',
        'weight_kg', 'rpe', 'attended_at', 'rescheduled_at',
    ];

    protected $casts = [
        'scheduled_at'   => 'datetime',
        'attended_at'    => 'datetime',
        'rescheduled_at' => 'datetime',
        'deducts'        => 'boolean',
        'weight_kg'      => 'decimal:2',
        'rpe'            => 'integer',
    ];

    const STATUSES = [
        'pending'     => 'Pendiente',
        'done'        => 'Completada',
        'no_show'     => 'No asistió',
        'rescheduled' => 'Reprogramada',
        'cancelled'   => 'Cancelada',
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->with('user');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function review()
    {
        return $this->hasOne(SessionReview::class, 'session_id');
    }
}

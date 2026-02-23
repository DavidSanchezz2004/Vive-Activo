<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'patient_id',
        'student_id',
        'type',
        'mode',
        'status',
        'requested_at',
        'scheduled_at',
        'meeting_url',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    const MODES = [
        'presencial' => 'Presencial',
        'zoom'       => 'Zoom',
        'meet'       => 'Google Meet',
    ];

    const STATUSES = [
        'pending_confirmation' => 'Pendiente',
        'confirmed'            => 'Confirmada',
        'completed'            => 'Completada',
        'cancelled'            => 'Cancelada',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->with('user');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions()
    {
        return $this->hasMany(PatientSession::class);
    }

    // Helpers
    public function isMeetingMode(): bool
    {
        return in_array($this->mode, ['zoom', 'meet']);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function modeLabel(): string
    {
        return self::MODES[$this->mode] ?? $this->mode;
    }
}

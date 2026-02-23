<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionReview extends Model
{
    protected $table = 'session_reviews';

    protected $fillable = [
        'session_id',
        'patient_id',
        'rating',
        'comment',
    ];

    public function session()
    {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

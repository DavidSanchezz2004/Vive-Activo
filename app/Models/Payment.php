<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'patient_id',
        'concept',
        'amount',
        'currency',
        'paid_at',
        'status',
        'receipt_path',
        'created_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    const STATUSES = [
        'paid'      => 'Pagado',
        'pending'   => 'Pendiente',
        'cancelled' => 'Cancelado',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}

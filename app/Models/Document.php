<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'patient_id',
        'type',
        'original_name',
        'file_path',
        'mime_type',
        'size_bytes',
        'uploaded_by',
    ];

    const TYPES = [
        'hemograma'   => 'Hemograma',
        'bioquimica'  => 'Bioquímica',
        'radiografia' => 'Radiografía',
        'ecografia'   => 'Ecografía',
        'receta'      => 'Receta médica',
        'plan'        => 'Plan nutricional',
        'otro'        => 'Otro',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ($this->type ?: 'Documento');
    }

    public function humanSize(): string
    {
        $bytes = $this->size_bytes ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 0) . ' KB';
        return $bytes . ' B';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}

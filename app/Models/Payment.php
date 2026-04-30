<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\StudioScope;

class Payment extends Model
{
    // Permite guardar estos datos masivamente
    protected $fillable = [
        'studio_id', // <- Clave Multi-Tenant para aislar los datos
        'student_id', 
        'workshop_id', 
        'payment_type', 
        'amount', 
        'receipt_path' // Opcional, por si sube la foto del comprobante
    ];

    /**
     * ==========================================
     * LÓGICA MULTI-TENANT (Aislamiento)
     * ==========================================
     */
    protected static function booted()
    {
        // 1. Aplica el filtro automáticamente: Nadie verá pagos de otro estudio
        static::addGlobalScope(new StudioScope);

        // 2. Al registrar un pago nuevo, le asigna el estudio de la sesión
        static::creating(function ($model) {
            if (session()->has('current_studio_id')) {
                $model->studio_id = session('current_studio_id');
            }
        });
    }

    // Relación: Un pago le pertenece a un Estudio
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    /**
     * ==========================================
     * RELACIONES DE NEGOCIO
     * ==========================================
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    // Relación pivot (magia automática)
    public function classSessions()
    {
        return $this->belongsToMany(ClassSession::class, 'class_session_payment')
                    ->withPivot('student_id')
                    ->withTimestamps();
    }
}
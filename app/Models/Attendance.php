<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\StudioScope;

class Attendance extends Model
{
    protected $fillable = [
        'studio_id', // <- Clave Multi-Tenant
        'class_session_id',
        'student_id'
    ];

    /**
     * ==========================================
     * LÓGICA MULTI-TENANT (Aislamiento)
     * ==========================================
     */
    protected static function booted()
    {
        static::addGlobalScope(new StudioScope);

        static::creating(function ($model) {
            if (session()->has('current_studio_id')) {
                $model->studio_id = session('current_studio_id');
            }
        });
    }

    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    /**
     * ==========================================
     * RELACIONES DE NEGOCIO
     * ==========================================
     */
    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
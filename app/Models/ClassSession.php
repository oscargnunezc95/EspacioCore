<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\StudioScope;

class ClassSession extends Model
{
    protected $fillable = [
        'studio_id', // <- Clave Multi-Tenant
        'workshop_id',
        'date',
        'start_time',
        'is_cancelled'
    ];

    protected $casts = [
        'is_cancelled' => 'boolean',
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
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'class_session_payment')
                    ->withPivot('student_id')
                    ->withTimestamps();
    }
}
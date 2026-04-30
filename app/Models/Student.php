<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\StudioScope;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'studio_id', // <- Clave para aislar los datos
        'rut',
        'name',
        'phone',
        'is_guest'
    ];

    /**
     * ==========================================
     * LÓGICA MULTI-TENANT (Aislamiento)
     * ==========================================
     */
    protected static function booted()
    {
        // 1. Aplica el filtro automáticamente: Nadie verá alumnas de otro estudio
        static::addGlobalScope(new StudioScope);

        // 2. Al crear una alumna nueva, Laravel le asigna el estudio automáticamente
        static::creating(function ($model) {
            if (session()->has('current_studio_id')) {
                $model->studio_id = session('current_studio_id');
            }
        });
    }

    // Relación: Una alumna le pertenece a un Estudio
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }


    /**
     * ==========================================
     * RELACIONES DE NEGOCIO
     * ==========================================
     */
     
    // Relación original con los talleres
    public function workshops()
    {
        return $this->belongsToMany(Workshop::class)
                    ->withPivot('credits_available')
                    ->withTimestamps();
    }

    // Una alumna tiene muchos pagos
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
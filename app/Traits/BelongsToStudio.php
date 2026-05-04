<?php

namespace App\Traits;

use App\Models\Studio;
use App\Models\Scopes\StudioScope;

trait BelongsToStudio
{
    protected static function bootBelongsToStudio()
    {
        // 1. Al consultar (Select): Aplicar el filtro de seguridad
        static::addGlobalScope(new StudioScope);

        // 2. Al crear (Insert): Inyectar el ID del estudio silenciosamente
        static::creating(function ($model) {
            if (session()->has('current_studio_id')) {
                $model->studio_id = session('current_studio_id');
            }
        });
    }

    // 3. Relación: Todo modelo con este Trait pertenece a un Estudio
    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
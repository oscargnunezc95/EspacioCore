<?php

namespace App\Traits;

use App\Models\Studio;
use App\Models\Scopes\StudioScope;
use Illuminate\Support\Facades\Config;

trait BelongsToStudio
{
    protected static function bootBelongsToStudio()
    {
        // 1. Al consultar (Select): Aplicar el filtro de seguridad
        static::addGlobalScope(new StudioScope);

        // 2. Al crear (Insert): Inyectar el ID del estudio silenciosamente
        static::creating(function ($model) {
            // LA CURA FINAL: Inyectamos el ID basado en el ciclo de vida de la petición
            $studioId = Config::get('tenant.studio_id');
            
            if ($studioId !== null) {
                $model->studio_id = $studioId;
            }
        });
    }

    // 3. Relación: Todo modelo con este Trait pertenece a un Estudio
    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }
}
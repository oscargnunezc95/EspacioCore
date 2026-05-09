<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Config;

class StudioScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // LA CURA FINAL: Leemos de la Configuración en tiempo de ejecución, NO de la Sesión.
        // Si el middleware no corrió (ej. en el Explorador), esto será null y no se aplicará.
        $studioId = Config::get('tenant.studio_id');

        if ($studioId !== null) {
            $builder->where($model->getTable() . '.studio_id', $studioId);
        }
    }
}
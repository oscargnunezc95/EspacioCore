<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StudioScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (session()->has('current_studio_id')) {
            $builder->where($model->getTable() . '.studio_id', session('current_studio_id'));
        }
    }
}
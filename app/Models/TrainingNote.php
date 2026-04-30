<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\StudioScope;

class TrainingNote extends Model
{
    protected $fillable = [
        'studio_id', // <- Clave Multi-Tenant
        'title',
        'training_date',
        'content'
    ];

    protected $casts = [
        'training_date' => 'date',
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
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopPrice extends Model
{
    protected $fillable = ['workshop_id', 'class_count', 'price', 'is_monthly'];

    protected $casts = [
        'is_monthly' => 'boolean',
    ];

    // Agrega esto a tu modelo WorkshopPrice existente
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
}
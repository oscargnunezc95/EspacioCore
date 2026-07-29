<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStudio;

class Promotion extends Model
{
    use BelongsToStudio;

    protected $fillable = [
        'studio_id', 'name', 'type', 'total_price',
        'additional_price', 'validity_months', 'validity_type',
        'allows_retroactive', 'is_active', 'class_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'validity_months' => 'integer',
        'validity_type' => 'string',
        'allows_retroactive' => 'boolean',
    ];

    // Cambiamos workshops() por workshopPrices()
    public function workshopPrices()
    {
        return $this->belongsToMany(WorkshopPrice::class, 'promotion_workshop_price');
    }
}
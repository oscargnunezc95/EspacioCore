<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStudio;

class Promotion extends Model
{
    use BelongsToStudio;

    protected $fillable = ['studio_id', 'name', 'type', 'total_price', 'additional_price', 'is_active'];

    // Cambiamos workshops() por workshopPrices()
    public function workshopPrices()
    {
        return $this->belongsToMany(WorkshopPrice::class, 'promotion_workshop_price');
    }
}
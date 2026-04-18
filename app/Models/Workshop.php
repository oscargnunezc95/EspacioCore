<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    protected $fillable = [
        'name', 'color', 'is_single_class', 'specific_date', 
        'trainer', 'trainer_phone', 'repeat_day', 'start_time', 
        'payment_info' // Precios eliminados de aquí también
    ];

    // Eliminamos el cast de 'schedule' porque ya no usamos esa columna
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopSchedule extends Model
{
    // Agregamos max_students
    protected $fillable = ['workshop_id', 'day_of_week', 'start_time', 'max_students'];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
}
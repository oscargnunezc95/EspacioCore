<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopSchedule extends Model
{
    // Agregamos max_students
    protected $fillable = ['workshop_id', 'day_of_week', 'start_time', 'max_students'];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'workshop_schedule_id');
    }
}

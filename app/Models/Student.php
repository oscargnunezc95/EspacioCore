<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rut',
        'name',
        'phone',
        'is_guest'
    ];

    // Relación original con los talleres
    public function workshops()
    {
        return $this->belongsToMany(Workshop::class)
                    ->withPivot('credits_available')
                    ->withTimestamps();
    }

    // NUEVA RELACIÓN: Una alumna tiene muchos pagos
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
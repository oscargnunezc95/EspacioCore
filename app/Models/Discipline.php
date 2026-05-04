<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Discipline extends Model
{
    protected $fillable = ['area_id', 'name'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }
}
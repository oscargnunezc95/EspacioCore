<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'country_id',
        'subdomain',
        'logo_path',
        'address',
        'latitude',
        'longitude',
        'city',
        'region',
        'country',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }

    // Puedes agregar las demás relaciones (payments, classSessions, etc) si las necesitas
}
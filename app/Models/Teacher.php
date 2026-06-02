<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStudio;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\DocumentService;
use App\Models\Country;

class Teacher extends Model
{
    use HasFactory, SoftDeletes, BelongsToStudio;

    protected $fillable = [
        'user_id', 
        'first_name', 
        'last_name',
        'email',
        'national_id', // <--- AGREGAR ESTO
        'phone',
        'is_active',
        'country_id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function teacherPayments()
    {
        return $this->hasMany(TeacherPayment::class);
    }

    // LÓGICA DE VINCULACIÓN: Cuando el Admin crea/edita al profe
    protected static function booted()
    {
        static::saving(function ($teacher) {
            if ($teacher->isDirty('national_id') && !empty($teacher->national_id)) {
                
                // Buscamos usuario con el MISMO documento y el MISMO país
                $user = User::where('national_id', $teacher->national_id)
                            ->where('country_id', $teacher->country_id) // <-- CRÍTICO
                            ->first();
                
                $teacher->user_id = $user ? $user->id : null;
            }
        });
    }

    public function getFormattedNationalIdAttribute()
    {
        // Ya no asumimos CL por defecto ciegamente
        $countryCode = $this->country ? $this->country->code : 'CL'; 

        return DocumentService::format($this->national_id, $countryCode);
    }

    protected function nationalId(): Attribute
    {
        return Attribute::make(
            set: function ($value, $attributes) {
                if (empty($value)) return null;

                $countryCode = null;
                if (!empty($attributes['country_id'])) {
                    $countryCode = Country::find($attributes['country_id'])?->code;
                }

                return DocumentService::standardize($value, $countryCode);
            }
        );
    }
}
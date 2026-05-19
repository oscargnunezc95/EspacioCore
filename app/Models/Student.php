<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStudio;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Services\DocumentService;
use App\Models\Country;

class Student extends Model
{
    use HasFactory, SoftDeletes, BelongsToStudio;

    protected $fillable = [
        'user_id',          // Llave foránea del Usuario Global
        'first_name',       // Obligatorio
        'last_name',        // Opcional
        'email',            
        'national_id', 
        'phone',
        'is_guest',
        'country_id'
    ];

    // MAGIA 1: Mantener compatibilidad con el frontend ($student->name)
    public function getNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // --- RELACIONES ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function workshops()
    {
        return $this->belongsToMany(Workshop::class, 'student_workshop')
                    ->withTimestamps();
    }
    
    // AQUÍ ESTÁ LA OPTIMIZACIÓN DEL CARRITO
    public function classSessions()
    {
        return $this->belongsToMany(ClassSession::class, 'class_session_student')
                    ->withPivot('payment_status') // <-- Vínculo mágico para saber si pagó
                    ->withTimestamps();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // MAGIA 2: Sincronización Automática (Lado del Estudio)
    protected static function booted()
    {
        static::saving(function ($student) {
            if ($student->isDirty('national_id') && !empty($student->national_id)) {
                
                // Buscamos usuario global exacto (Documento + País)
                $user = User::where('national_id', $student->national_id)
                            ->where('country_id', $student->country_id) // <-- CRÍTICO
                            ->first();
                
                $student->user_id = $user ? $user->id : null;
            }
        });
    }

    public function getFormattedNationalIdAttribute()
    {
        // 100% Dinámico
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStudio;
use App\Services\DocumentService;

class Student extends Model
{
    use HasFactory, SoftDeletes, BelongsToStudio;

    protected $fillable = [
        'user_id',          // Llave foránea del Usuario Global
        'first_name',       // Obligatorio
        'last_name',        // Opcional
        'email',            
        'national_id', // <--- AGREGAR ESTO
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

    // Agrega esto en app/Models/Student.php
    public function workshops()
    {
        return $this->belongsToMany(Workshop::class, 'student_workshop')
                    ->withTimestamps();
    }
    
    // (Opcional, preparándolo para el futuro si luego tomas asistencia por clase individual)
    public function classSessions()
    {
        return $this->belongsToMany(ClassSession::class, 'class_session_student')
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
}
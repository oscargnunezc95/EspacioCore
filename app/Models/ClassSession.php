<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    // Permitimos la asignación masiva para estos campos
    protected $fillable = [
        'workshop_id',
        'date',
        'start_time',
        'is_cancelled'
    ];

    // Aseguramos que Laravel trate este campo como booleano
    protected $casts = [
        'is_cancelled' => 'boolean',
    ];

    // Relación: Una sesión pertenece a un taller
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    // Relación: Una sesión tiene muchas asistencias
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    // Agrega esto dentro de la clase ClassSession
    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'class_session_payment')
                    ->withPivot('student_id')
                    ->withTimestamps();
    }
}
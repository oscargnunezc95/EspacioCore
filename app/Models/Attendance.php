<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // Permitimos que el controlador guarde estos datos masivamente
    protected $fillable = [
        'class_session_id',
        'student_id'
    ];

    // Relación: Una asistencia pertenece a una sesión de clase
    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    // Relación: Una asistencia pertenece a una estudiante
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
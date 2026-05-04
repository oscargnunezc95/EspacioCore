<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStudio;

class Attendance extends Model
{
    use HasFactory, BelongsToStudio;

    protected $fillable = [
        'class_session_id',
        'student_id',
    ];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student()
    {
        // Al igual que en pagos, agregamos withTrashed() para mantener el registro 
        // histórico de asistencias de alumnas/os desactivadas.
        return $this->belongsTo(Student::class)->withTrashed();
    }
}
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
        'studio_id', // 🚨 BLINDAJE: Agregado para permitir inyección desde Webhooks
    ];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStudio;

class ClassSession extends Model
{
    use HasFactory, BelongsToStudio;

    protected $fillable = [
        'workshop_id',
        'date',
        'start_time',
        'is_cancelled',
    ];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Relación a través de la tabla pivote que creaste (class_session_payment)
    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'class_session_payment')
                    ->withPivot('student_id')
                    ->withTimestamps();
    }
    public function students()
    {
        // Las alumnas que reservaron/se inscribieron a esta clase
        return $this->belongsToMany(Student::class, 'class_session_student')->withTimestamps();
    }
}
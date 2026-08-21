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
        'workshop_schedule_id',
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

    // Relación a través de la tabla pivote de pagos (class_session_payment)
    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'class_session_payment')
                    ->withPivot('student_id')
                    ->withTimestamps();
    }

    // Relación directa al WorkshopSchedule (jerarquía Workshop -> Schedule -> Session)
    public function schedule()
    {
        return $this->belongsTo(WorkshopSchedule::class, 'workshop_schedule_id');
    }

    /**
     * Accessor: devuelve el max_students según la jerarquía.
     * 1. Si la sesión tiene horario → usa schedule.max_students
     * 2. Si es clase única (sin horario) → usa workshop.max_students
     * 3. Fallback → 99 (sin límite)
     */
    public function getMaxStudentsAttribute(): int
    {
        // Prioridad 1: el horario específico
        if ($this->schedule && $this->schedule->max_students !== null) {
            return (int) $this->schedule->max_students;
        }

        // Prioridad 2: el workshop (para clases únicas sin schedule)
        if ($this->workshop && $this->workshop->max_students !== null) {
            return (int) $this->workshop->max_students;
        }

        // Fallback
        return 99;
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_session_student') 
                    ->withPivot('payment_status', 'workshop_price_id') // <-- AÑADIDO
                    ->withTimestamps();
    }
}
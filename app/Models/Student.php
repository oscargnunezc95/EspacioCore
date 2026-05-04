<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStudio;

class Student extends Model
{
    use HasFactory, SoftDeletes, BelongsToStudio;

    protected $fillable = [
        'user_id',          // Llave foránea del Usuario Global
        'first_name',       // Obligatorio
        'last_name',        // Opcional
        'email',            // Para el Match
        'phone',
        'is_guest'
    ];

    // MAGIA 1: Mantener compatibilidad con el frontend ($student->name)
    public function getNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // MAGIA 2: Sincronización Automática (Lado del Estudio)
    protected static function booted()
    {
        // Se ejecuta justo antes de crear o actualizar una alumna/oen la BD
        static::saving(function ($student) {
            // Si el correo fue modificado o es nuevo, y no está vacío
            if ($student->isDirty('email') && !empty($student->email)) {
                
                // Buscamos si existe un usuario registrado con ese correo
                $user = User::where('email', $student->email)->first();
                
                // Si existe, lo vinculamos. Si no, lo dejamos null (a la espera)
                $student->user_id = $user ? $user->id : null;
            }
        });
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
}
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // 1. Descomenta o agrega esta línea
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\DocumentService;

// 2. Agrega "implements MustVerifyEmail" al final de esta línea
#[Fillable(['name', 'email', 'password', 'google_id','national_id','country_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail 
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function studios()
    {
        return $this->hasMany(Studio::class);
    }

    // Relaciones para saber en qué estudios es Profesor y en cuáles es Alumna
    public function teacherProfiles()
    {
        return $this->hasMany(Teacher::class);
    }

    public function studentProfiles()
    {
        return $this->hasMany(Student::class);
    }


    /**
     * Calcula la cantidad de clases futuras en las que el usuario 
     * está inscrito pero aún no ha pagado, EN CUALQUIER ESTUDIO.
     */
    public function getUnpaidClassesCount(): int
    {
        // 1. Buscamos todas sus fichas de alumna ignorando el estudio actual
        $studentIds = \App\Models\Student::withoutGlobalScopes()
            ->where('user_id', $this->id)
            ->pluck('id');

        if ($studentIds->isEmpty()) return 0;

        // 2. Contamos sesiones futuras impagas en TODO el sistema
        return \App\Models\ClassSession::withoutGlobalScopes()
            ->whereHas('students', function ($q) {
                $q->withoutGlobalScopes()->where('students.user_id', $this->id);
            })
            ->whereDoesntHave('payments', function ($q) use ($studentIds) {
                $q->whereIn('class_session_payment.student_id', $studentIds);
            })
            ->where('date', '>=', now()->toDateString())
            ->count();
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // LÓGICA DE VINCULACIÓN INVERSA: Cuando el Usuario Global cambia sus datos
    protected static function booted()
    {
        static::saved(function ($user) {
            // Si el usuario acaba de agregar o modificar su RUT/DNI
            if ($user->isDirty('national_id') && !empty($user->national_id)) {
                
                // 1. Buscamos profesores que tengan este RUT Y EL MISMO PAÍS
                Teacher::where('national_id', $user->national_id)
                       ->where('country_id', $user->country_id) // <-- CRÍTICO
                       ->update(['user_id' => $user->id]);

                // 2. Hacemos lo mismo para las alumnas
                Student::where('national_id', $user->national_id)
                       ->where('country_id', $user->country_id) // <-- CRÍTICO
                       ->update(['user_id' => $user->id]);
            }
        });
    }

    public function getFormattedNationalIdAttribute()
    {
        // Ahora lee dinámicamente el código real del país (ej: 'CL', 'AR', 'OT')
        $countryCode = $this->country ? $this->country->code : 'CL'; 

        return DocumentService::format($this->national_id, $countryCode);
    }

}
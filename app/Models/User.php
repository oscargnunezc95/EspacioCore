<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // 1. Descomenta o agrega esta línea
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 2. Agrega "implements MustVerifyEmail" al final de esta línea
#[Fillable(['name', 'email', 'password', 'google_id'])]
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
    // LÓGICA DE VINCULACIÓN INVERSA: Cuando el Usuario Global cambia sus datos
    protected static function booted()
    {
        static::saved(function ($user) {
            // Si el usuario acaba de verificar o cambiar su correo global
            if ($user->isDirty('email')) {
                
                // 1. Buscamos todas las fichas de Profesor en todos los estudios que tengan este correo y los vinculamos
                Teacher::where('email', $user->email)
                       ->update(['user_id' => $user->id]);

                // 2. Hacemos lo mismo para las fichas de Alumnas (aprovechando la misma lógica)
                Student::where('email', $user->email)
                       ->update(['user_id' => $user->id]);
            }
        });
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
}
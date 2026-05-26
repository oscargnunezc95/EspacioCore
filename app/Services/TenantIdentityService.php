<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TenantIdentityService
{
    /**
     * 1. RESOLVEDOR GLOBAL DE USUARIO
     * Solo toca la tabla `users`. Busca por RUT (o email como fallback).
     * Si no existe, lo crea y genera la password temporal.
     */
    public function resolveGlobalUser(?string $nationalId, ?string $email, string $fullName): array
    {
        // Si no hay datos clave, no podemos resolver a nivel global
        if (!$nationalId && !$email) {
            return ['user' => null, 'is_new' => false, 'temp_password' => null];
        }

        $query = User::query();
        
        if ($nationalId) {
            $query->where('national_id', $nationalId);
        } else {
            $query->where('email', $email);
        }

        $user = $query->first();

        if ($user) {
            // Autocorrección: Completar el email si faltaba
            if (!$user->email && $email) {
                $user->update(['email' => $email]);
            }
            return ['user' => $user, 'is_new' => false, 'temp_password' => null];
        }

        // Creación de Usuario 100% Nuevo
        $tempPassword = Str::random(8);
        $user = User::create([
            'name'        => $fullName,
            'email'       => $email,
            'national_id' => $nationalId,
            'password'    => Hash::make($tempPassword),
        ]);

        return ['user' => $user, 'is_new' => true, 'temp_password' => $tempPassword];
    }

    /**
     * 2. VERIFICADOR LOCAL DE ALUMNA
     * Consulta súper liviana para saber si ya existe la ficha en el estudio.
     */
    public function isStudentInStudio(?string $nationalId, int $studioId): bool
    {
        if (!$nationalId) return false;

        return Student::withoutGlobalScopes()
            ->where('national_id', $nationalId)
            ->where('studio_id', $studioId)
            ->exists();
    }

    /**
     * 3. VERIFICADOR LOCAL DE PROFESOR
     * Consulta súper liviana para evitar profesores duplicados.
     */
    public function isTeacherInStudio(?string $nationalId, int $studioId): bool
    {
        if (!$nationalId) return false;

        return Teacher::withoutGlobalScopes()
            ->where('national_id', $nationalId)
            ->where('studio_id', $studioId)
            ->exists();
    }
}
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\UserDependent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FamilyDecisionService
{
    /**
     * Desvincula al usuario de su apoderado y le transfiere sus perfiles de estudiante.
     */
    public function unlinkAndTransferClasses(User $user, int $oldOwnerId): int
    {
        return DB::transaction(function () use ($user, $oldOwnerId) {
            
            // 1. Estandarizar el documento ANTES de buscar para asegurar coincidencia
            $countryCode = $user->country?->code ?? 'OT';
            $standardizedId = DocumentService::standardize($user->national_id, $countryCode);

            // 2. Transferir perfiles Student
            $transferredCount = Student::withoutGlobalScopes()
                ->where('national_id', $standardizedId) 
                // Evitamos usar el country_id del usuario si es propenso a ser null
                ->where(function ($q) use ($oldOwnerId) {
                    $q->where('user_id', $oldOwnerId)
                      ->orWhereNull('user_id');
                })
                ->update([
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);

            Log::info("Dependent unlink: {$transferredCount} Student profiles transferidos de user #{$oldOwnerId} a user #{$user->id}");

            // 3. Eliminar el vínculo (buscando también por el documento estandarizado)
            UserDependent::where('national_id', $standardizedId)
                ->where('user_id', $oldOwnerId)
                ->delete();

            // 4. Limpiar estado de decisión
            $user->update([
                'dependent_decision_pending'  => false,
                'dependent_decision_owner_id' => null,
            ]);

            return $transferredCount;
        });
    }
}
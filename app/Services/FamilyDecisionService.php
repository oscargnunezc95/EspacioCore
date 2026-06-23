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
     * Desvincula al usuario de su apoderado, le transfiere sus perfiles de estudiante
     * y sobreescribe los correos electrónicos heredados.
     */
    public function unlinkAndTransferClasses(User $user, int $oldOwnerId): int
    {
        return DB::transaction(function () use ($user, $oldOwnerId) {
            
            $countryCode = $user->country?->code ?? 'OT';
            $standardizedId = DocumentService::standardize($user->national_id, $countryCode);

            // Supremacía de Identidad: Actualiza TODO lo que tenga su RUT y País
            $transferredCount = Student::withoutGlobalScopes()
                ->where('national_id', $standardizedId) 
                ->where('country_id', $user->country_id)
                ->update([
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);

            Log::info("Dependent unlink: {$transferredCount} Student profiles transferidos y correos actualizados de user #{$oldOwnerId} a user #{$user->id}");

            UserDependent::where('national_id', $standardizedId)
                ->where('country_id', $user->country_id)
                ->where('user_id', $oldOwnerId)
                ->delete();

            $user->update([
                'dependent_decision_pending'  => false,
                'dependent_decision_owner_id' => null,
            ]);

            return $transferredCount;
        });
    }
}
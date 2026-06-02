<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Student;
use App\Models\UserDependent;
use App\Models\Country;
use App\Services\DocumentService;
use Illuminate\Validation\Rule;
use App\Rules\ValidDocument;
use App\Mail\DependentTransferRequestMail;
use App\Mail\FamilyLinkRequestMail;

class FamilyController extends Controller
{
    public function index()
    {
        $dependents = Auth::user()->dependents()->orderBy('first_name')->get();
        $countries = Country::orderBy('name', 'asc')->get(); 
        
        return view('profile.family.index', compact('dependents', 'countries'));
    }

    public function store(Request $request)
    {
        // 1. Obtener el código del país dinámicamente
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        // 2. Estandarizar antes de validar
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 3. DETECCIÓN DE CONFLICTOS (antes de validar, para mensajes específicos)
        if (! $request->has('confirmed')) {
            $conflict = $this->detectConflict($request);
            if ($conflict) {
                return back()
                    ->with('conflict_type', $conflict['type'])
                    ->with('conflict_data', $conflict['data'])
                    ->withInput();
            }
        }

        // 4. Validación Blindada
        $attributes = [
            'first_name'   => 'nombre',
            'last_name'    => 'apellido',
            'country_id'   => 'país',
            'national_id'  => 'documento de identidad',
            'relationship' => 'parentesco',
        ];

        $messages = [
            'required' => 'El :attribute es obligatorio.',
            'string'   => 'El :attribute debe ser texto.',
            'max'      => 'El :attribute no debe superar los :max caracteres.',
            'exists'   => 'El :attribute seleccionado no es válido.',
            'unique'   => 'Este familiar ya está registrado en tu cuenta.',
        ];

        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'nullable|string|max:255',
            'country_id'   => 'required|exists:countries,id', 
            'national_id'  => [
                'required', 'string', 'max:255',
                new ValidDocument($countryCode),
                // Candado A: Único dentro del grupo familiar, para el mismo país
                Rule::unique('user_dependents', 'national_id')
                    ->where('user_id', Auth::id())
                    ->where('country_id', $request->country_id),
            ],
            'relationship' => 'nullable|string|max:100',
        ], $messages, $attributes);

        // 5. Inserción
        $dependent = Auth::user()->dependents()->create($validated);

        // 6. Notificaciones según el tipo de confirmación
        if ($request->has('confirmed')) {
            $this->sendConflictNotification($request, $dependent);
        }

        return back()->with('success', 'Familiar registrado correctamente.');
    }

    /**
     * Detecta conflictos antes de validar.
     * Retorna null si no hay conflicto, o un array con type y data.
     */
    private function detectConflict(Request $request): ?array
    {
        $nationalId = $request->national_id;
        $countryId = $request->country_id;

        if (! $nationalId || ! $countryId) return null;

        $myId = Auth::id();

        // Conflicto 1: Ya es dependiente de OTRO usuario
        $otherDependent = UserDependent::where('national_id', $nationalId)
            ->where('country_id', $countryId)
            ->where('user_id', '!=', $myId)
            ->first();

        if ($otherDependent) {
            $owner = User::find($otherDependent->user_id);
            return [
                'type' => 'transfer',
                'data' => [
                    'owner_name'      => $owner?->name ?? 'otro usuario',
                    'owner_email'     => $owner?->email,
                    'owner_id'        => $owner?->id,
                    'dependent_name'  => $otherDependent->first_name . ' ' . $otherDependent->last_name,
                    'dependent_id'    => $otherDependent->id,
                    'relationship'    => $otherDependent->relationship,
                ],
            ];
        }

        // Conflicto 2: El documento pertenece a un USUARIO GLOBAL
        $globalUser = User::where('national_id', $nationalId)
            ->where('country_id', $countryId)
            ->first();

        if ($globalUser) {
            return [
                'type' => 'link',
                'data' => [
                    'user_name'  => $globalUser->name,
                    'user_email' => $globalUser->email,
                    'user_id'    => $globalUser->id,
                ],
            ];
        }

        return null;
    }

    /**
     * Envía las notificaciones correspondientes cuando se confirma un conflicto.
     */
    private function sendConflictNotification(Request $request, UserDependent $dependent): void
    {
        $type = $request->confirmed;

        try {
            if ($type === 'transfer') {
                // Notificar al dueño actual pidiendo la transferencia
                $ownerId = $request->owner_id;
                $owner = User::find($ownerId);
                if ($owner) {
                    Mail::to($owner->email)->queue(
                        new DependentTransferRequestMail($owner, Auth::user(), $dependent)
                    );
                }
            } elseif ($type === 'link') {
                // Notificar al usuario global que fue agregado como familiar
                $targetUserId = $request->target_user_id;
                $targetUser = User::find($targetUserId);
                if ($targetUser) {
                    Mail::to($targetUser->email)->queue(
                        new FamilyLinkRequestMail($targetUser, Auth::user(), $dependent)
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de conflicto familiar: ' . $e->getMessage());
        }
    }

    public function update(Request $request, UserDependent $dependent)
    {
        // Seguridad: ¿Es realmente su familiar?
        if ($dependent->user_id !== Auth::id()) abort(403);

        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        $attributes = [
            'first_name'   => 'nombre',
            'country_id'   => 'país',
            'national_id'  => 'documento de identidad',
        ];

        $messages = [
            'required' => 'El :attribute es obligatorio.',
            'unique'   => 'Este documento ya está registrado en tu grupo familiar.',
        ];

        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'nullable|string|max:255',
            'country_id'   => 'required|exists:countries,id',
            'national_id'  => [
                'required', 'string', 'max:255',
                new ValidDocument($countryCode),
                Rule::unique('user_dependents', 'national_id')
                    ->ignore($dependent->id)
                    ->where('user_id', Auth::id())
                    ->where('country_id', $request->country_id),
            ],
            'relationship' => 'nullable|string|max:100',
        ], $messages, $attributes);

        $dependent->update($validated);

        return back()->with('success', 'Datos del familiar actualizados.');
    }

    public function destroy(UserDependent $dependent)
    {
        if ($dependent->user_id !== Auth::id()) abort(403);
        $dependent->delete();
        return back()->with('success', 'Familiar removido de tu cuenta.');
    }

    // ─── DECISIÓN POST-REGISTRO: DEPENDIENTE PRE-EXISTENTE ─────────────

    /**
     * Muestra la página de decisión cuando un nuevo usuario era dependiente de alguien.
     * Lee el estado desde la BD (no sesión) para que persista.
     */
    public function dependentDecision()
    {
        $user = Auth::user();

        if (! $user->dependent_decision_pending) {
            return redirect()->route('explore');
        }

        $owner = User::find($user->dependent_decision_owner_id);

        return view('profile.dependent-decision', [
            'ownerName'   => $owner?->name ?? 'otro usuario',
            'ownerId'     => $user->dependent_decision_owner_id,
            'dependentId' => null, // ya no necesitamos el ID del dependiente, lo buscamos en unlink
        ]);
    }

    /**
     * El nuevo usuario elige DESVINCULARSE: borra el UserDependent 
     * y transfiere todos los Student profiles asociados a su nueva cuenta.
     */
    public function unlinkDependent(Request $request)
    {
        $user = Auth::user();

        if (! $user->dependent_decision_pending) {
            return redirect()->route('explore');
        }

        $oldOwnerId = $user->dependent_decision_owner_id;

        try {
            // 1. Transferir Student profiles al nuevo usuario
            //    - Pre-fix: user_id = oldOwnerId (el apoderado)
            //    - Post-fix: user_id = null (huérfano, la persona real aún no reclama)
            $transferred = Student::withoutGlobalScopes()
                ->where('national_id', $user->national_id)
                ->where('country_id', $user->country_id)
                ->where(function ($q) use ($oldOwnerId) {
                    $q->where('user_id', $oldOwnerId)
                      ->orWhereNull('user_id');
                })
                ->update(['user_id' => $user->id]);

            Log::info("Dependent unlink: {$transferred} Student profiles transferidos de user #{$oldOwnerId} a user #{$user->id}");

            // 2. Eliminar el vínculo de dependiente (buscar por national_id + country_id + owner)
            UserDependent::where('national_id', $user->national_id)
                ->where('country_id', $user->country_id)
                ->where('user_id', $oldOwnerId)
                ->delete();

            // 3. Limpiar flag de decisión
            $user->update([
                'dependent_decision_pending'  => false,
                'dependent_decision_owner_id' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en unlinkDependent: ' . $e->getMessage());
            return redirect()->route('explore')->with('error', 'Hubo un error al desvincular. Intenta de nuevo.');
        }

        return redirect()->route('explore')->with('success', 
            'Te has desvinculado como familiar. ' . ($transferred > 0 ? "Se transfirieron {$transferred} clases a tu cuenta." : '')
        );
    }

    /**
     * El nuevo usuario elige MANTENER EL VÍNCULO familiar.
     * Transfiere los Student profiles al nuevo usuario (para que vea sus clases)
     * pero mantiene el UserDependent (el apoderado sigue gestionando).
     */
    public function shareAndKeepDependent()
    {
        $user = Auth::user();

        if (! $user->dependent_decision_pending) {
            return redirect()->route('explore');
        }

        $oldOwnerId = $user->dependent_decision_owner_id;

        // Barrido: transferir Student profiles al nuevo usuario
        //    - Pre-fix: user_id = oldOwnerId (el apoderado)
        //    - Post-fix: user_id = null (huérfano, la persona real aún no reclama)
        // (así el hijo también puede ver sus propias clases desde su cuenta)
        $transferred = Student::withoutGlobalScopes()
            ->where('national_id', $user->national_id)
            ->where('country_id', $user->country_id)
            ->where(function ($q) use ($oldOwnerId) {
                $q->where('user_id', $oldOwnerId)
                  ->orWhereNull('user_id');
            })
            ->update(['user_id' => $user->id]);

        // MANTENER el UserDependent — el apoderado sigue viendo al familiar
        // Solo limpiar el flag
        $user->update([
            'dependent_decision_pending'  => false,
            'dependent_decision_owner_id' => null,
        ]);

        Log::info("Dependent share: user #{$user->id} mantiene vínculo, {$transferred} perfiles transferidos desde owner #{$oldOwnerId}.");

        return redirect()->route('explore')->with('success',
            'Has mantenido el vínculo familiar. Ahora puedes ver tus clases desde tu cuenta y tu familiar también puede gestionarlas.'
        );
    }
}

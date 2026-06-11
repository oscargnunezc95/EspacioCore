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
use App\Services\FamilyDecisionService;
use Illuminate\Validation\Rule;
use App\Rules\ValidDocument;
use App\Mail\DependentTransferRequestMail;
use App\Mail\FamilyLinkRequestMail;

class FamilyController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 1. Las personas que YO administro
        $dependents = $user->dependents()->orderBy('first_name')->get();
        $countries = Country::orderBy('name', 'asc')->get(); 
        
        // 2. Las familias a las que YO pertenezco (Alguien me agregó a mí)
        $memberships = UserDependent::with('user')
            ->where('national_id', $user->national_id)
            ->where('country_id', $user->country_id)
            ->get();
        
        return view('profile.family.index', compact('dependents', 'countries', 'memberships'));
    }

    /**
     * Permite a un usuario independiente salir de la familia de un Apoderado
     */
    public function leaveFamily(UserDependent $dependent, FamilyDecisionService $decisionService)
    {
        $user = Auth::user();

        // Seguridad: Solo el dueño real del documento puede sacarse a sí mismo
        if ($user->national_id !== $dependent->national_id || $user->country_id !== $dependent->country_id) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        // Notificar al apoderado ANTES de que el servicio elimine el registro
        try {
            $dependent->user->notify(
                new \App\Notifications\FamilyMemberLeftNotification($user->name)
            );
        } catch (\Exception $e) {
            Log::error('Error notificando salida de familiar: ' . $e->getMessage());
        }

        try {
            // Reutilizamos tu servicio estrella para desvincular y traer las clases de vuelta
            $transferredCount = $decisionService->unlinkAndTransferClasses($user, $dependent->user_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Hubo un error al salir del grupo familiar.');
        }

        $message = "Has salido del grupo familiar de {$dependent->user->name}.";
        if ($transferredCount > 0) {
            $message .= " Se transfirieron {$transferredCount} clases a tu cuenta personal.";
        }

        return back()->with('success', $message);
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
        $status = ($request->input('confirmed') === 'link') ? 'pending' : 'active';
        $dependent = Auth::user()->dependents()->create(array_merge($validated, [
            'status' => $status,
        ]));

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
                    // Notificación in-app
                    try {
                        $targetUser->notify(
                            new \App\Notifications\FamilyLinkRequestedNotification(Auth::user())
                        );
                    } catch (\Exception $e) {
                        Log::error('Error notificando in-app link familiar: ' . $e->getMessage());
                    }

                    // Correo
                    try {
                        Mail::to($targetUser->email)->queue(
                            new FamilyLinkRequestMail($targetUser, Auth::user(), $dependent)
                        );
                    } catch (\Exception $e) {
                        Log::error('Error encolando correo link familiar: ' . $e->getMessage());
                    }
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
        // Seguridad: ¿Es realmente su familiar?
        if ($dependent->user_id !== Auth::id()) abort(403);

        // REGLA DE NEGOCIO: Solo notificamos si el vínculo era oficial (Activo)
        if ($dependent->status === 'active') {
            
            // 1. Buscar si este familiar tiene una cuenta real de usuario en el sistema
            $targetUser = User::where('national_id', $dependent->national_id)
                              ->where('country_id', $dependent->country_id)
                              ->first();

            // 2. Si es un usuario real, le notificamos ANTES de destruir el registro
            if ($targetUser) {
                try {
                    $targetUser->notify(
                        new \App\Notifications\RemovedFromFamilyNotification(Auth::user()->name)
                    );
                } catch (\Exception $e) {
                    Log::error('Error notificando expulsión de familiar: ' . $e->getMessage());
                }
            }
        }

        // 3. Destruimos el vínculo familiar (ya sea activo o una invitación pendiente)
        $dependent->delete();
        
        return back()->with('success', 'Familiar removido de tu cuenta.');
    }

    /**
     * Acepta una solicitud de vínculo familiar (link).
     * Requiere firma válida y que el usuario autenticado sea el titular del documento.
     */
    public function acceptLink(Request $request, UserDependent $dependent)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'El enlace de confirmación no es válido o ha expirado.');
        }

        if (! Auth::check() || Auth::user()->national_id !== $dependent->national_id) {
            abort(403, 'No tienes permiso para aceptar esta solicitud.');
        }

        $dependent->update(['status' => 'active']);

        // Notificar al apoderado que su solicitud fue aceptada
        try {
            $dependent->user->notify(
                new \App\Notifications\FamilyLinkAcceptedNotification(Auth::user())
            );
        } catch (\Exception $e) {
            Log::error('Error notificando aceptación de vínculo familiar: ' . $e->getMessage());
        }

        return redirect()->route('explore')->with('success',
            'Has aceptado la solicitud de vínculo familiar. Ahora tu apoderado puede inscribirte en clases y gestionar tus reservas.'
        );
    }

    /**
     * Rechaza una solicitud de vínculo familiar (link).
     * Requiere firma válida y que el usuario autenticado sea el titular del documento.
     */
    public function rejectLink(Request $request, UserDependent $dependent)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'El enlace de confirmación no es válido o ha expirado.');
        }

        if (! Auth::check() || Auth::user()->national_id !== $dependent->national_id) {
            abort(403, 'No tienes permiso para rechazar esta solicitud.');
        }

        $dependent->delete();

        return redirect()->route('explore')->with('success',
            'Has rechazado la solicitud de vínculo familiar. Tus datos no serán gestionados por esta persona.'
        );
    }

    /**
     * Acepta un vínculo familiar desde la interfaz web (sin firma).
     * Solo el titular del documento puede aceptar.
     */
    public function acceptMembership(UserDependent $dependent): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        if ($user->national_id !== $dependent->national_id || $user->country_id !== $dependent->country_id) {
            abort(403, 'No tienes permiso para aceptar esta solicitud.');
        }

        if ($dependent->status !== 'pending') {
            return back()->with('error', 'Esta solicitud ya no está pendiente.');
        }

        $dependent->update(['status' => 'active']);

        // Notificar al apoderado que su solicitud fue aceptada
        try {
            $dependent->user->notify(
                new \App\Notifications\FamilyLinkAcceptedNotification(Auth::user())
            );
        } catch (\Exception $e) {
            Log::error('Error notificando aceptación de membresía: ' . $e->getMessage());
        }

        return back()->with('success',
            'Has aceptado la solicitud de vínculo familiar. Ahora tu apoderado puede inscribirte en clases y gestionar tus reservas.'
        );
    }

    /**
     * Rechaza un vínculo familiar desde la interfaz web (sin firma).
     * Solo el titular del documento puede rechazar.
     */
    public function rejectMembership(UserDependent $dependent): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        if ($user->national_id !== $dependent->national_id || $user->country_id !== $dependent->country_id) {
            abort(403, 'No tienes permiso para rechazar esta solicitud.');
        }

        $dependent->delete();

        return back()->with('success',
            'Has rechazado la solicitud de vínculo familiar.'
        );
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
    public function unlinkDependent(FamilyDecisionService $decisionService)
    {
        $user = Auth::user();

        if (! $user->dependent_decision_pending) {
            return redirect()->route('explore');
        }

        try {
            $transferredCount = $decisionService->unlinkAndTransferClasses(
                $user, 
                $user->dependent_decision_owner_id
            );

        } catch (\Exception $e) {
            return redirect()->route('explore')
                ->with('error', 'Hubo un error al desvincular. Intenta de nuevo.');
        }

        $message = 'Te has desvinculado como familiar.';
        if ($transferredCount > 0) {
            $message .= " Se transfirieron {$transferredCount} clases a tu cuenta.";
        }

        return redirect()->route('explore')->with('success', $message);
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
        // (así el hijo también puede ver sus propias clases desde su cuenta)
        $transferred = Student::withoutGlobalScopes()
            ->where('national_id', $user->national_id)
            ->where('country_id', $user->country_id)
            ->where(function ($q) use ($oldOwnerId) {
                $q->where('user_id', $oldOwnerId)
                  ->orWhereNull('user_id');
            })
            ->update(['user_id' => $user->id, 'country_id' => $user->country_id, 'email' => $user->email]);

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

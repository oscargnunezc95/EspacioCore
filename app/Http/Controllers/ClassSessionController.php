<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Services\DocumentService;
use App\Rules\ValidDocument;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentWelcomeMail;
use App\Mail\UserLinkedToStudioMail;
use App\Notifications\StudentAddedNotification;
use App\Services\TenantIdentityService; // 👈 NUEVO

class ClassSessionController extends Controller
{
    public function show($subdomain, ClassSession $session)
    {
        // 0. SEGURIDAD: Obtenemos el estudio actual para evitar fuga de datos
        $studio = \App\Models\Studio::where('subdomain', $subdomain)->firstOrFail();

        // 1. Cargamos las relaciones con Eloquent
        $session->load(['attendances', 'payments']);
        
        // 2. Extraemos los IDs de pago usando la relación nativa
        $paidStudentIds = $session->payments->pluck('pivot.student_id')->toArray();

        // Regla de Seguridad (Self-Healing): Si alguien pagó, aseguramos su inscripción
        foreach ($paidStudentIds as $paidId) {
            $session->students()->syncWithoutDetaching([$paidId]);
            $session->attendances()->firstOrCreate(['student_id' => $paidId]);
        }
        
        // LA ÚNICA FUENTE DE VERDAD: Alumnas inscritas en esta sesión
        $students = $session->students()
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();

        $enrolledIds = $students->pluck('id')->toArray();

        // 3. CORRECCIÓN: Alumnas SOLO DEL ESTUDIO ACTUAL que aún no se inscriben
        $otherStudents = Student::where('studio_id', $studio->id)
            ->where('is_guest', false)
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();
        
        // 4. CORRECCIÓN: Cargamos los países para el select del Modal
        $countries = Country::orderBy('name', 'asc')->get();
        
        $monthId = Carbon::parse($session->date)->format('Y-m');
            
        // 5. Profesores filtrados por el estudio seguro
        $teachers = Teacher::where('studio_id', $studio->id)->orderBy('first_name')->get();
        
        return view('classsessions.show', compact(
            'session', 
            'students', 
            'paidStudentIds', 
            'otherStudents', 
            'monthId', 
            'teachers', 
            'countries'
        ));
    }

    public function update(Request $request, $subdomain, ClassSession $session)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_cancelled' => 'boolean'
        ]);

        $studio = \App\Models\Studio::where('subdomain', $subdomain)->firstOrFail();

        // CAPTURAMOS EL ESTADO ORIGINAL
        $originalIsCancelled = $session->is_cancelled;
        $originalDate = $session->date;
        $originalTime = $session->start_time;
        $originalTeacher = $session->teacher_id;

        // APLICAMOS CAMBIOS
        $session->update([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'teacher_id' => $request->teacher_id, 
            'is_cancelled' => $request->boolean('is_cancelled') 
        ]);

        // =========================================================
        // MOTOR DE NOTIFICACIONES (CORREO BCC + IN-APP)
        // =========================================================
        $wasCancelledNow = (!$originalIsCancelled && $session->is_cancelled);
        $wasModified = (!$session->is_cancelled && (
            $originalDate != $session->date ||
            $originalTime != $session->start_time ||
            $originalTeacher != $session->teacher_id
        ));

        if ($wasCancelledNow || $wasModified) {
            // 1. DATA PARA CORREOS
            $studentEmails = $session->students()->whereNotNull('email')->pluck('email')->toArray();
            $teacherEmail = $session->teacher->email ?? null;
            $ownerEmail = $studio->user->email ?? null;
            $bccEmails = array_filter(array_unique(array_merge($studentEmails, [$teacherEmail])));

            // 2. DATA PARA CAMPANITA IN-APP (Necesitamos los modelos User, no solo emails)
            $userIds = $session->students()->whereNotNull('user_id')->pluck('user_id')->toArray();
            if ($session->teacher && $session->teacher->user_id) {
                $userIds[] = $session->teacher->user_id;
            }
            // Evitamos notificar a la dueña en la campanita, ella fue quien hizo el cambio
            $usersToNotify = \App\Models\User::whereIn('id', array_unique($userIds))->get();

            try {
                if ($wasCancelledNow) {
                    // Disparamos Correo BCC
                    if ($ownerEmail) {
                        \Illuminate\Support\Facades\Mail::to($ownerEmail)
                            ->bcc($bccEmails)
                            ->send(new \App\Mail\ClassCancelledMail($session, $studio));
                    }
                    // Disparamos Campanita In-App
                    \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Notifications\ClassCancelledNotification($session, $studio));
                    
                } elseif ($wasModified) {
                    // Disparamos Correo BCC
                    if ($ownerEmail) {
                        \Illuminate\Support\Facades\Mail::to($ownerEmail)
                            ->bcc($bccEmails)
                            ->send(new \App\Mail\ClassModifiedMail($session, $studio));
                    }
                    // Disparamos Campanita In-App
                    \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Notifications\ClassModifiedNotification($session, $studio));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error en notificaciones de clase: ' . $e->getMessage());
            }
        }

        $newMonthId = \Carbon\Carbon::parse($request->date)->format('Y-m');
        
        return redirect()->route('trainingmonth.show', [
            'subdomain' => $subdomain, 
            'month' => $newMonthId
        ])->with('success', 'Sesión actualizada. Las notificaciones in-app y correos fueron enviados.');
    }

    public function enrollStudent(Request $request, $subdomain, ClassSession $session, TenantIdentityService $identityService) // 👈 Inyección
    {
        $studio = \App\Models\Studio::where('subdomain', $subdomain)->firstOrFail();
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // VALIDACIÓN RÁPIDA: Si está inscribiendo a una nueva alumna, verificar que no exista ya en el estudio.
        if ($request->enroll_mode === 'new' && $identityService->isStudentInStudio($request->national_id, $studio->id)) {
             return back()->withErrors(['national_id' => 'Esta alumna ya existe en tu estudio. Inscríbela desde la pestaña "Existentes".'])->withInput();
        }

        $validated = $request->validate([
            'enroll_mode'   => 'required|in:existing,new',
            'student_ids'   => 'nullable|required_if:enroll_mode,existing|array',
            'student_ids.*' => 'exists:students,id',
            'first_name'    => 'nullable|required_if:enroll_mode,new|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'email'         => 'nullable|required_if:enroll_mode,new|email',
            'country_id'    => 'nullable|required_if:enroll_mode,new|exists:countries,id',
            'national_id'   => [
                'nullable', 'required_if:enroll_mode,new', 'string', 'max:50', new ValidDocument($countryCode)
            ],
            'phone'         => 'nullable|string|max:20',
        ]);

        if ($request->enroll_mode === 'existing') {
            
            $syncData = [];
            foreach ($request->student_ids as $id) {
                $syncData[$id] = ['payment_status' => 'pending'];
            }
            $session->students()->syncWithoutDetaching($syncData);  

            foreach ($request->student_ids as $studentId) {
                \App\Models\Attendance::firstOrCreate([
                    'class_session_id' => $session->id,
                    'student_id'       => $studentId,
                ]);
            }
            
            $mensaje = count($request->student_ids) > 1 
                        ? count($request->student_ids) . ' alumnas inscritas correctamente.' 
                        : 'Alumna inscrita correctamente.';

        } else {
            
            // =========================================================
            // 2. MOTOR DE ONBOARDING VÍA SERVICIO
            // =========================================================
            $identity = $identityService->resolveGlobalUser(
                $request->national_id, 
                $request->email, 
                trim($request->first_name . ' ' . $request->last_name)
            );

            $user = $identity['user'];

            $student = Student::create([
                'studio_id'   => $studio->id,
                'user_id'     => $user ? $user->id : null, 
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'name'        => trim($request->first_name . ' ' . $request->last_name),
                'email'       => $request->email,
                'country_id'  => $request->country_id,
                'national_id' => $request->national_id,
                'phone'       => $request->phone,
            ]);

            $session->students()->attach($student->id, ['payment_status' => 'pending']);

            \App\Models\Attendance::create([
                'class_session_id' => $session->id,
                'student_id'       => $student->id,
            ]);

            // DISPARO DE NOTIFICACIONES
            if ($user) {
                try {
                    if ($identity['is_new']) {
                        Mail::to($user->email)->send(new StudentWelcomeMail($studio, $student, $identity['temp_password']));
                    } else {
                        Mail::to($user->email)->send(new UserLinkedToStudioMail($studio, $user->name));
                    }
                    $user->notify(new StudentAddedNotification($studio));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Fallo onboarding alumna express: ' . $e->getMessage());
                }
            }

            $mensaje = 'Nueva alumna creada, inscrita y notificada correctamente.';
        }

        return back()->with('success', $mensaje);
    }
}
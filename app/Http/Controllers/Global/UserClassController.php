<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassSession;
use App\Models\Studio;
use App\Models\UserDependent;
use Carbon\Carbon;
use App\Notifications\StudentAddedNotification;
use App\Notifications\SpotReservedNotification;
use App\Notifications\ClassFullNotification;
use App\Services\ExploreService;

class UserClassController extends Controller
{
    public function asStudent(Request $request)
    {
        $user = Auth::user();
        
        $month = $request->query('month');
        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        // ─── PRINCIPIO: "Separar la Identidad de la Tutoría" ─────────────────
        // El user_id en students es la persona que ASISTE a la clase.
        // El apoderado gestiona familiares vía user_dependents, NO vía user_id.

        // 1. Mis propias fichas de alumno (yo asisto)
        $ownStudentIds = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        // 2. Fichas de mis familiares/dependientes (ellos asisten, yo gestiono)
        $dependentNationalIds = UserDependent::where('user_id', $user->id)
            ->pluck('national_id')
            ->toArray();

        $dependentStudentIds = [];
        if (!empty($dependentNationalIds)) {
            $dependentStudentIds = Student::withoutGlobalScopes()
                ->whereIn('national_id', $dependentNationalIds)
                ->where(function ($q) use ($user) {
                    // Excluir mis propias fichas (yo puedo ser dependiente de alguien más)
                    $q->whereNull('user_id')
                      ->orWhere('user_id', '!=', $user->id);
                })
                ->pluck('id')
                ->toArray();
        }

        $allStudentIds = array_unique(array_merge($ownStudentIds, $dependentStudentIds));

        if (empty($allStudentIds)) {
            $sessionsByDate = collect();
            $dependentStudentIdsFlat = [];
            return view('global.classes.student', compact('monthDate', 'sessionsByDate', 'dependentStudentIdsFlat'));
        }

        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'schedule',
                'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                'workshop.studio', 
                'workshop.teacher',
                'students' => fn($q) => $q->withoutGlobalScopes()->whereIn('students.id', $allStudentIds)
            ])
            ->whereHas('students', function ($q) use ($allStudentIds) {
                $q->withoutGlobalScopes()->whereIn('students.id', $allStudentIds);
            })
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Enriquecer con cupos disponibles e interesados
        $exploreService = app(ExploreService::class);
        $sessions = $exploreService->enrichSessionCollection($sessions);

        // Marcar cada sesión con qué estudiantes son familiares (para la vista)
        $dependentStudentIdsFlat = $dependentStudentIds;
        $sessions->each(function ($session) use ($dependentStudentIdsFlat) {
            $session->family_student_ids = $session->students
                ->filter(fn($st) => in_array($st->id, $dependentStudentIdsFlat))
                ->pluck('id')
                ->toArray();
        });

        $sessionsByDate = $sessions->groupBy('date');

        return view('global.classes.student', compact('monthDate', 'sessionsByDate', 'dependentStudentIdsFlat'));
    }

    public function asTeacher(Request $request)
    {
        $user = Auth::user();
        
        $month = $request->query('month');
        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        $teacherProfileIds = Teacher::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id');

        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                'workshop.studio' => fn($q) => $q->withoutGlobalScopes(), 
                'workshop.teacher' => fn($q) => $q->withoutGlobalScopes()
            ])
            ->whereHas('workshop', function ($query) use ($teacherProfileIds) {
                $query->withoutGlobalScopes()->whereIn('teacher_id', $teacherProfileIds);
            })
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $sessionsByDate = $sessions->groupBy('date');

        $user = Auth::user();
        $mpLinked = !empty($user->mp_access_token);

        return view('global.classes.teacher', compact('monthDate', 'sessionsByDate', 'mpLinked'));
    }

    public function teacherSession($id)
    {
        $session = ClassSession::withoutGlobalScopes()
            ->with(['workshop' => function($q) {
                $q->withoutGlobalScopes();
            }])
            ->findOrFail($id);

        $teacherProfileIds = Teacher::withoutGlobalScopes()->where('user_id', Auth::id())->pluck('id')->toArray();
        if (!in_array($session->workshop->teacher_id, $teacherProfileIds)) {
            abort(403, 'No tienes permiso para ver esta clase.');
        }

        $session->load(['attendances', 'workshop.studio', 'payments']);
        
        $paidStudentIds = $session->payments->pluck('pivot.student_id')->toArray();

        foreach ($paidStudentIds as $paidId) {
            $session->students()->syncWithoutDetaching([
                $paidId => ['payment_status' => 'paid']
            ]);
            $session->attendances()->firstOrCreate(['student_id' => $paidId]);
        }
        
        $students = $session->students()
            ->withoutGlobalScopes()
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();

        $parsedMonth = Carbon::parse($session->date);

        return view('global.classes.teacher_session', compact('session', 'students', 'paidStudentIds', 'parsedMonth'));
    }

    public function toggleEnrollment(Request $request)
    {
        dd($request->all());
        try {
            $request->validate([
                'class_session_id' => 'required|integer',
                'dependent_id'     => 'nullable|integer'
            ]);
            
            $user = Auth::user();

            $attendee = [
                'first_name'  => $user->name,
                'last_name'   => null,
                'national_id' => $user->national_id,
            ];

            if ($request->filled('dependent_id')) {
                $dependent = $user->dependents()->find($request->dependent_id);
                if (!$dependent) {
                    return response()->json(['error' => true, 'message' => 'Familiar no encontrado.'], 403);
                }
                $attendee = [
                    'first_name'  => $dependent->first_name,
                    'last_name'   => $dependent->last_name,
                    'national_id' => $dependent->national_id,
                ];
            }

            $session = ClassSession::withoutGlobalScopes()
                ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                ->findOrFail($request->class_session_id);

            $studioId = $session->studio_id ?? $session->workshop->studio_id;

            $studentQuery = Student::withoutGlobalScopes()->where('studio_id', $studioId);
                
            if (!empty($attendee['national_id'])) {
                $studentQuery->where('national_id', $attendee['national_id']);
            } else {
                $studentQuery->where('user_id', $user->id)->whereNull('national_id')->where('first_name', $attendee['first_name']);
            }

            $student = $studentQuery->first();

            if ($student) {
                if (empty($student->user_id)) {
                    // PRINCIPIO: user_id es quien ASISTE. Si es un dependiente sin cuenta, queda null.
                    // Si el dependiente tiene cuenta, Scenario A del booted::saving lo vinculará.
                    // NUNCA asignar $user->id si la persona que asiste es otra.
                    $isSelf = ($attendee['national_id'] === $user->national_id);
                    if ($isSelf) {
                        $student->update(['user_id' => $user->id, 'email' => $user->email]);
                    }
                    // Si es familiar, el user_id queda null (o lo setea booted::saving si el User existe)
                }
            } else {
                $student = new Student();
                // PRINCIPIO: user_id es la persona que ASISTE, no quien gestiona
                $isSelf = ($attendee['national_id'] === $user->national_id);
                $student->user_id     = $isSelf ? $user->id : null;
                $student->studio_id   = $studioId;
                $student->first_name  = $attendee['first_name'];
                $student->last_name   = $attendee['last_name'];
                $student->email       = $isSelf ? $user->email : null;
                $student->national_id = $attendee['national_id'];
                $student->is_guest    = false;
                $student->save();

                try {
                    $studio = Studio::find($studioId);
                    $user->notify(new StudentAddedNotification($studio));
                } catch (\Exception $e) {}
            }

            $existingStudent = $session->students()->withoutGlobalScopes()
                                       ->where('students.id', $student->id)->first();

            if ($existingStudent) {
                if ($existingStudent->pivot->payment_status !== 'paid') {
                    $session->students()->withoutGlobalScopes()->detach($student->id);
                    $status = 'removed';
                } else {
                    $status = 'enrolled'; 
                }
            } else {
                $session->students()->withoutGlobalScopes()->attach(
                    $student->id, ['payment_status' => 'pending']
                );
                $status = 'enrolled';
            }

            return response()->json([
                'status' => $status,
                'cart_count' => $user->pending_reservations_count
            ]);

        } catch (\Exception $e) {
            Log::error('Error en Toggle Global: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Error al procesar la reserva.'], 500);
        }
    }

    public function bulkEnroll(Request $request)
    {
        $request->validate([
            'enrollments'                => 'required|array',
            'enrollments.*.session_id'   => 'required|integer',
            'enrollments.*.dependent_id' => 'nullable|integer',
            'enrollments.*.action'       => 'required|in:add,remove'
        ]);

        try {
            $user = Auth::user();
            DB::beginTransaction();

            foreach ($request->enrollments as $enrollment) {
                $sessionId = $enrollment['session_id'];
                $dependentId = $enrollment['dependent_id'] ?? null;
                $action = $enrollment['action'];

                $session = ClassSession::withoutGlobalScopes()
                    ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                    ->find($sessionId);

                if (!$session) continue;

                $studioId = $session->studio_id ?? $session->workshop->studio_id;

                $attendee = [
                    'first_name'  => $user->name,
                    'last_name'   => null,
                    'national_id' => $user->national_id,
                    'country_id'  => $user->country_id,
                ];

                if ($dependentId) {
                    $dependent = $user->dependents()->find($dependentId);
                    if ($dependent) {
                        $attendee = [
                            'first_name'  => $dependent->first_name,
                            'last_name'   => $dependent->last_name,
                            'national_id' => $dependent->national_id,
                            'country_id'  => $dependent->country_id ?? $user->country_id, 
                        ];
                    } else {
                        // Familiar no encontrado o no pertenece a este usuario
                        continue;
                    }
                }

                $studentQuery = Student::withoutGlobalScopes()->where('studio_id', $studioId);

                if (!empty($attendee['national_id'])) {
                    $studentQuery->where('national_id', $attendee['national_id']);
                } else {
                    $studentQuery->where('user_id', $user->id)->whereNull('national_id')->where('first_name', $attendee['first_name']);
                }

                $student = $studentQuery->first();

                if ($student) {
                    if (empty($student->user_id)) {
                        // PRINCIPIO: user_id es quien ASISTE. Solo asignar si es el propio usuario.
                        $isSelf = ($attendee['national_id'] === $user->national_id);
                        if ($isSelf) {
                            $student->update([
                                'user_id'    => $user->id, 
                                'email'      => $user->email,
                                'country_id' => $attendee['country_id']
                            ]);
                        }
                    }
                } else {
                    if ($action === 'remove') continue; // No crear ficha si solo queremos borrar

                    // PRINCIPIO: user_id es la persona que ASISTE, no quien gestiona
                    $isSelf = ($attendee['national_id'] === $user->national_id);

                    $student = new Student();
                    $student->user_id     = $isSelf ? $user->id : null;
                    $student->studio_id   = $studioId;
                    $student->first_name  = $attendee['first_name'];
                    $student->last_name   = $attendee['last_name'];
                    $student->email       = $isSelf ? $user->email : null;
                    $student->country_id  = $attendee['country_id']; 
                    $student->national_id = $attendee['national_id'];
                    $student->is_guest    = false;
                    $student->save();
                    
                    try {
                        $studio = Studio::find($studioId);
                        $user->notify(new StudentAddedNotification($studio));
                    } catch (\Exception $e) {}
                }

                // ==========================================
                // LÓGICA EXPLÍCITA DE ORDENES (ESCUDO DE PAGO)
                // ==========================================
                $existingStudent = $session->students()->withoutGlobalScopes()
                                           ->where('students.id', $student->id)->first();

                if ($action === 'add') {
                    if (!$existingStudent) {
                        $session->students()->withoutGlobalScopes()->attach($student->id, ['payment_status' => 'pending']);
                    }
                } elseif ($action === 'remove') {
                    if ($existingStudent && $existingStudent->pivot->payment_status !== 'paid') {
                        $session->students()->withoutGlobalScopes()->detach($student->id);
                    }
                }
            }

            DB::commit();

            // ===================================================
            // NOTIFICACIONES: Avisar a otros interesados que los cupos bajan
            // ===================================================
            $affectedSessionIds = collect($request->enrollments)
                ->where('action', 'add')
                ->pluck('session_id')
                ->unique();

            foreach ($affectedSessionIds as $sid) {
                $session = ClassSession::withoutGlobalScopes()
                    ->with(['workshop' => fn($q) => $q->withoutGlobalScopes(), 'schedule'])
                    ->find($sid);

                if (!$session) continue;

                $maxStudents = $session->max_students; // Accessor: schedule->max_students ?? 99
                $paidCount = DB::table('class_session_student')
                    ->where('class_session_id', $sid)
                    ->where('payment_status', 'paid')
                    ->count();
                $availableSpots = max(0, $maxStudents - $paidCount);

                // Buscar TODOS los estudiantes pendientes de esta sesión (excepto los que acaban de pagar)
                $pendingStudentIds = DB::table('class_session_student')
                    ->where('class_session_id', $sid)
                    ->where('payment_status', 'pending')
                    ->pluck('student_id');

                if ($pendingStudentIds->isEmpty()) continue;

                // Obtener los Users dueños de esos Students
                $pendingUsers = \App\Models\User::whereHas('studentProfiles', function ($q) use ($pendingStudentIds) {
                    $q->withoutGlobalScopes()->whereIn('id', $pendingStudentIds);
                })->get();

                if ($availableSpots <= 0) {
                    // CLASE LLENA: email + in-app para todos los pendientes
                    foreach ($pendingUsers as $user) {
                        try {
                            $user->notify(new ClassFullNotification($session));
                        } catch (\Exception $e) {
                            Log::error('Error enviando ClassFullNotification: ' . $e->getMessage());
                        }
                    }
                } else {
                    // SpotReserved: solo in-app para los pendientes
                    foreach ($pendingUsers as $user) {
                        try {
                            $user->notify(new SpotReservedNotification($session, $availableSpots));
                        } catch (\Exception $e) {
                            Log::error('Error enviando SpotReservedNotification: ' . $e->getMessage());
                        }
                    }
                }
            }

            return response()->json([
                'status' => 'success', 
                'cart_count' => $user->pending_reservations_count
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Error en Bulk Enroll: ' . $e->getMessage() . ' Línea: ' . $e->getLine());
            return response()->json(['error' => true, 'message' => 'Error al procesar las reservas.'], 500);
        }
    }
}
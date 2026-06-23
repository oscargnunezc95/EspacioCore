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
use App\Services\ExploreService;
use App\Services\StudentProfileService;
use App\Services\EnrollmentService;

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
            ->where('status', 'active') // FILTRO APLICADO
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
        try {
            $request->validate([
                'class_session_id' => 'required|integer',
                'dependent_id'     => 'nullable|integer'
            ]);
            
            $user = Auth::user();

            // ─── Determinar quién asiste ──────────────────────────────
            $attendee = [
                'first_name'  => $user->name,
                'last_name'   => null,
                'national_id' => $user->national_id,
                'country_id'  => $user->country_id,
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
                    'country_id'  => $dependent->country_id ?? $user->country_id,
                ];
            }

            // ─── Cargar sesión ────────────────────────────────────────
            $session = ClassSession::withoutGlobalScopes()
                ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                ->findOrFail($request->class_session_id);

            $studioId = $session->studio_id ?? $session->workshop->studio_id;

            // ─── Buscar o crear ficha (StudentProfileService) ─────────
            $profileService = app(StudentProfileService::class);
            $student = $profileService->findOrCreateAttendee($attendee, $studioId, $user);

            // ─── Determinar acción (toggle) ───────────────────────────
            $existing = $session->students()
                ->withoutGlobalScopes()
                ->where('students.id', $student->id)
                ->first();

            $action = ($existing && $existing->pivot->payment_status !== 'paid')
                ? 'remove'
                : 'add';

            // ─── CAPA 1 ANTI-OVERBOOKING: Verificar cupos antes de agregar ─
            $enrollmentService = app(EnrollmentService::class);

            if ($action === 'add') {
                $capacity = $enrollmentService->getCapacityInfo($session->id);
                $available = $capacity[$session->id]['available_spots'] ?? 0;

                if ($available <= 0) {
                    return response()->json([
                        'error'   => true,
                        'message' => 'Lo sentimos, esta clase acaba de llenarse.',
                        'code'    => 'CLASS_FULL'
                    ], 422);
                }
            }

            // ─── Ejecutar toggle (EnrollmentService) ──────────────────
            $status = $enrollmentService->toggleSpot($session, $student, $action);

            return response()->json([
                'status'     => $status,
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
            $profileService = app(StudentProfileService::class);
            $enrollmentService = app(EnrollmentService::class);

            // ─── CAPA 1 ANTI-OVERBOOKING: Verificar cupos antes de la transacción ────
            $addsBySession = collect($request->enrollments)
                ->where('action', 'add')
                ->groupBy('session_id')
                ->map(fn($items) => $items->count());

            if ($addsBySession->isNotEmpty()) {
                $capacityInfo = $enrollmentService->getCapacityInfo($addsBySession->keys()->toArray());

                $overbooked = [];
                foreach ($addsBySession as $sessionId => $requested) {
                    $available = $capacityInfo[$sessionId]['available_spots'] ?? 0;
                    if ($requested > $available) {
                        $overbooked[] = [
                            'session_id' => $sessionId,
                            'requested'  => $requested,
                            'available'  => $available,
                        ];
                    }
                }

                if (!empty($overbooked)) {
                    $first = $overbooked[0];
                    $plural = $first['requested'] > 1;
                    $quedan = $first['available'] === 0
                        ? 'no quedan cupos'
                        : ($first['available'] === 1 ? 'solo queda 1 cupo' : "solo quedan {$first['available']} cupos");

                    return response()->json([
                        'error'   => true,
                        'message' => $plural
                            ? "Lo sentimos, estás intentando reservar {$first['requested']} cupos pero {$quedan} en esta clase."
                            : "Lo sentimos, {$quedan} en esta clase.",
                        'code'    => 'CLASS_FULL',
                        'details' => $overbooked,
                    ], 422);
                }
            }

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
                        continue;
                    }
                }

                // ─── Buscar o crear ficha (StudentProfileService) ─────
                $student = $profileService->findOrCreateAttendee($attendee, $studioId, $user);

                // ─── Ejecutar acción en pivote (EnrollmentService) ────
                $enrollmentService->toggleSpot($session, $student, $action);
            }

            DB::commit();

            // ─── Notificaciones de capacidad (EnrollmentService) ──────
            $affectedSessionIds = collect($request->enrollments)
                ->where('action', 'add')
                ->pluck('session_id')
                ->unique()
                ->toArray();

            if (!empty($affectedSessionIds)) {
                $enrollmentService->notifyCapacityChange($affectedSessionIds, $user->id);
            }

            return response()->json([
                'status'     => 'success',
                'cart_count' => $user->pending_reservations_count
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en Bulk Enroll: ' . $e->getMessage() . ' Línea: ' . $e->getLine());
            return response()->json(['error' => true, 'message' => 'Error al procesar las reservas.'], 500);
        }
    }
}
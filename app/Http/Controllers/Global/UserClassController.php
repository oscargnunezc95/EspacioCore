<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Workshop;
use App\Models\ClassSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserClassController extends Controller
{

    public function asStudent(Request $request)
    {
        $user = Auth::user();
        
        // Si no viene mes por URL, usamos el actual
        $month = $request->query('month');
        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        // 1. Obtenemos todas las "Fichas de Alumna" de este usuario en cualquier estudio
        $studentIds = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            $sessionsByDate = collect();
            return view('global.classes.student', compact('monthDate', 'sessionsByDate'));
        }

        // 2. Consulta blindada: Trae inscritas O pagadas
        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                'workshop.studio', 
                'workshop.teacher'
            ])
            ->withExists(['payments as is_paid' => function ($q) use ($studentIds) {
                // Verificamos si existe un pago registrado por ALGUNO de los perfiles de este usuario
                $q->whereIn('class_session_payment.student_id', $studentIds);
            }])
            ->where(function ($query) use ($studentIds) {
                // Condición A: Está inscrito en la tabla pivote (Carrito / En el Portal)
                $query->whereHas('students', function ($q) use ($studentIds) {
                    $q->withoutGlobalScopes()->whereIn('students.id', $studentIds);
                })
                // Condición B: Tiene un pago registrado directamente en la tabla de pagos
                ->orWhereExists(function ($q) use ($studentIds) {
                    $q->select(DB::raw(1))
                      ->from('class_session_payment')
                      ->whereColumn('class_session_payment.class_session_id', 'class_sessions.id')
                      ->whereIn('class_session_payment.student_id', $studentIds);
                });
            })
            // Filtramos SOLO las clases de este mes exacto
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Agrupamos por la fecha exacta (Y-m-d) para la grilla
        $sessionsByDate = $sessions->groupBy('date');

        return view('global.classes.student', compact('monthDate', 'sessionsByDate'));
    }

    // ==========================================
    // PORTAL DE PROFESOR: Ver agenda sin caos visual
    // ==========================================
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
                'workshop.studio'
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

        return view('global.classes.teacher', compact('monthDate', 'sessionsByDate'));
    }

    // ==========================================
    // PORTAL DE PROFESOR: Detalle de una sesión
    // ==========================================
    public function teacherSession($id)
    {
        $session = ClassSession::withoutGlobalScopes()
            ->with(['workshop' => function($q) {
                $q->withoutGlobalScopes();
            }])
            ->findOrFail($id);

        // Validar que el usuario sea el profesor de esta clase
        $teacherProfileIds = Teacher::withoutGlobalScopes()->where('user_id', Auth::id())->pluck('id')->toArray();
        if (!in_array($session->workshop->teacher_id, $teacherProfileIds)) {
            abort(403, 'No tienes permiso para ver esta clase.');
        }

        // CARGAMOS RELACIONES EAGER PARA EVITAR QUERIES CRUDAS
        $session->load(['attendances', 'workshop.studio', 'payments']);
        
        // Obtenemos los IDs de los estudiantes que pagaron a través de la relación nativa
        // Esto es mucho más seguro y limpio que hacer DB::table()
        $paidStudentIds = $session->payments->pluck('pivot.student_id')->toArray();

        foreach ($paidStudentIds as $paidId) {
            $session->students()->syncWithoutDetaching([$paidId]);
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
            $request->validate(['class_session_id' => 'required|integer']);
            $user = Auth::user();

            $session = ClassSession::withoutGlobalScopes()
                ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                ->findOrFail($request->class_session_id);

            $studioId = $session->studio_id ?? $session->workshop->studio_id;

            $student = Student::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->where('studio_id', $studioId)
                ->first();

            if (!$student) {
                $student = new Student();
                $student->user_id = $user->id;
                $student->studio_id = $studioId;
                $student->first_name = $user->name;
                $student->email = $user->email;
                $student->is_guest = false;
                $student->save();
            }

            $detached = $session->students()->withoutGlobalScopes()->detach($student->id);

            if ($detached > 0) {
                $status = 'removed';
            } else {
                $session->students()->withoutGlobalScopes()->attach($student->id);
                $status = 'enrolled';
            }

            $cartCount = $user->pending_reservations_count;

            return response()->json([
                'status' => $status,
                'cart_count' => $cartCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error en Toggle Global: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkEnroll(Request $request)
    {
        $request->validate([
            'session_ids' => 'required|array',
            'session_ids.*' => 'integer'
        ]);

        try {
            $user = Auth::user();
            
            DB::beginTransaction();

            foreach ($request->session_ids as $sessionId) {
                $session = ClassSession::withoutGlobalScopes()
                    ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                    ->find($sessionId);

                if (!$session) continue;

                $studioId = $session->studio_id ?? $session->workshop->studio_id;

                $student = Student::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->where('studio_id', $studioId)
                    ->first();

                if (!$student) {
                    $student = new Student();
                    $student->user_id = $user->id;
                    $student->studio_id = $studioId;
                    $student->first_name = $user->name;
                    $student->email = $user->email;
                    $student->is_guest = false;
                    $student->country_id = $user->country_id; 
                    $student->national_id = $user->national_id;
                    $student->save();
                }

                $detached = $session->students()->withoutGlobalScopes()->detach($student->id);

                if ($detached === 0) {
                    $session->students()->withoutGlobalScopes()->attach($student->id);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success', 
                'cart_count' => $user->pending_reservations_count
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Error en Bulk Enroll: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Error al procesar las reservas: ' . $e->getMessage()], 500);
        }
    }
}
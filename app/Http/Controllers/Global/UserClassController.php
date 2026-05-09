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

    // PORTAL DE ALUMNO: Ver todo sin filtros de estudio
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

        // 2. Consulta blindada: Trae inscritas O pagadas
        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                'workshop.studio', 
                'workshop.teacher'
            ])
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
        
        // 1. Recibir mes por URL o usar el actual
        $month = $request->query('month');
        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        // 2. Obtener los IDs de profesor de este usuario en todos los estudios
        $teacherProfileIds = Teacher::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id');

        // 3. Traer solo las sesiones de ESTE mes
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

        // 4. Agrupar por fecha exacta
        $sessionsByDate = $sessions->groupBy('date');

        return view('global.classes.teacher', compact('monthDate', 'sessionsByDate'));
    }


    public function teacherSession($id) // Cambiado a ID para buscarlo sin Global Scopes
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

        $session->load(['attendances', 'workshop.studio']);
        
        $paidStudentIds = \Illuminate\Support\Facades\DB::table('class_session_payment')
            ->where('class_session_id', $session->id)
            ->pluck('student_id')
            ->toArray();

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

            // 1. Buscamos la sesión ignorando el estudio de la sesión actual
            $session = ClassSession::withoutGlobalScopes()
                ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                ->findOrFail($request->class_session_id);

            $studioId = $session->studio_id ?? $session->workshop->studio_id;

            // 2. Buscamos la ficha de alumna ignorando el estudio de la sesión
            $student = Student::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->where('studio_id', $studioId)
                ->first();

            // 3. Si no existe, la creamos para ESE estudio específico
            if (!$student) {
                $student = new Student();
                $student->user_id = $user->id;
                $student->studio_id = $studioId;
                $student->first_name = $user->name;
                $student->email = $user->email;
                $student->is_guest = false;
                $student->save();
            }

            // 4. ELIMINACIÓN/INSERCIÓN DIRECTA (Sin interferencia de Scopes)
            // Intentamos borrar. Si detach devuelve > 0, es que ya existía.
            $detached = $session->students()->withoutGlobalScopes()->detach($student->id);

            if ($detached > 0) {
                $status = 'removed';
            } else {
                $session->students()->withoutGlobalScopes()->attach($student->id);
                $status = 'enrolled';
            }

            // 5. Recalcular el badge global
            $cartCount = $user->getUnpaidClassesCount();

            return response()->json([
                'status' => $status,
                'cart_count' => $cartCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error en Toggle Global: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // CHECKOUT MASIVO (Marketplace)
    // ==========================================
    public function bulkEnroll(Request $request)
    {
        $request->validate([
            'session_ids' => 'required|array',
            'session_ids.*' => 'integer'
        ]);

        try {
            $user = Auth::user();
            
            // Iniciamos la transacción
            DB::beginTransaction();

            foreach ($request->session_ids as $sessionId) {
                // 1. Buscamos la sesión y su estudio
                $session = ClassSession::withoutGlobalScopes()
                    ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                    ->find($sessionId);

                if (!$session) continue;

                $studioId = $session->studio_id ?? $session->workshop->studio_id;

                // 2. Buscamos o creamos la ficha de alumna en ese estudio
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

                // 3. LA MAGIA: Lógica de Toggle Masivo
                // Intentamos desenlazar (detach). Si devuelve > 0, significa que la alumna estaba inscrita y fue eliminada.
                $detached = $session->students()->withoutGlobalScopes()->detach($student->id);

                // Si devuelve 0, significa que NO estaba inscrita, por lo tanto, la agregamos (attach).
                if ($detached === 0) {
                    $session->students()->withoutGlobalScopes()->attach($student->id);
                }
            }

            DB::commit(); // Consolidamos todo

            return response()->json(['status' => 'success', 'cart_count' => $user->getUnpaidClassesCount()]);

        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Error en Bulk Enroll: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Error al procesar las reservas: ' . $e->getMessage()], 500);
        }
    }
}
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
use Carbon\Carbon;
use App\Notifications\StudentAddedNotification;

class UserClassController extends Controller
{
    public function asStudent(Request $request)
    {
        $user = Auth::user();
        
        $month = $request->query('month');
        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        $studentIds = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            $sessionsByDate = collect();
            return view('global.classes.student', compact('monthDate', 'sessionsByDate'));
        }

        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                'workshop.studio', 
                'workshop.teacher',
                'students' => fn($q) => $q->withoutGlobalScopes()->whereIn('students.id', $studentIds)
            ])
            ->whereHas('students', function ($q) use ($studentIds) {
                $q->withoutGlobalScopes()->whereIn('students.id', $studentIds);
            })
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $sessionsByDate = $sessions->groupBy('date');

        return view('global.classes.student', compact('monthDate', 'sessionsByDate'));
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

        return view('global.classes.teacher', compact('monthDate', 'sessionsByDate'));
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
                    $student->update(['user_id' => $user->id, 'email' => $user->email]);
                }
            } else {
                $student = new Student();
                $student->user_id     = $user->id; 
                $student->studio_id   = $studioId;
                $student->first_name  = $attendee['first_name'];
                $student->last_name   = $attendee['last_name'];
                $student->email       = $user->email; 
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
                        $student->update([
                            'user_id' => $user->id, 
                            'email' => $user->email,
                            'country_id' => $attendee['country_id']
                        ]);
                    }
                } else {
                    if ($action === 'remove') continue; // No crear ficha si solo queremos borrar

                    $student = new Student();
                    $student->user_id     = $user->id;
                    $student->studio_id   = $studioId;
                    $student->first_name  = $attendee['first_name'];
                    $student->last_name   = $attendee['last_name'];
                    $student->email       = $user->email; 
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
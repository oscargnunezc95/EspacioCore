<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Student;
use App\Models\Studio;
use App\Models\ClassSession;
use App\Models\Payment;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\Services\DocumentService;
use App\Rules\ValidDocument;
use App\Models\Country; 

class StudentController extends Controller
{
    public function index($subdomain)
    {
        $students = Student::where('is_guest', false)
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();
            
        $inactiveStudents = Student::onlyTrashed()
            ->where('is_guest', false)
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();
            
        $countries = Country::orderBy('name', 'asc')->get();
        
        return view('students.index', compact('students', 'inactiveStudents', 'countries'));
    }

    public function store(Request $request, $subdomain)
    {
        $studioId = Config::get('tenant.studio_id');
        
        // 1. OBTENER EL CÓDIGO DEL PAÍS (Elegancia PHP 8: en 1 sola línea)
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        // 2. ESTANDARIZAR EL DOCUMENTO ANTES DE VALIDAR (Defensa en Profundidad)
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);

            // LÓGICA DE RESCATE (Papelera de Alumnas)
            $existingStudent = Student::withTrashed()
                ->where('studio_id', $studioId)
                ->where('national_id', $request->national_id)
                ->first();

            if ($existingStudent) {
                if ($existingStudent->trashed()) {
                    $existingStudent->restore();
                    $existingStudent->update($request->except(['national_id']));
                    return back()->with('success', 'La alumna/o estaba en la papelera y ha sido reactivada con sus nuevos datos.');
                }
                return back()->withErrors(['national_id' => 'Este documento ya pertenece a una alumna/o activa en tu estudio.'])->withInput();
            }
        }

        // 3. VALIDACIÓN BLINDADA
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'country_id'  => 'required|exists:countries,id', 
            'national_id' => [
                'nullable', 
                'string',
                'max:255',
                new ValidDocument($countryCode), // Usamos el código real seleccionado
                Rule::unique('students', 'national_id')->where(function ($query) use ($studioId) {
                    return $query->where('studio_id', $studioId);
                })
            ],
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:255'
        ]);

        Student::create($request->all());
        return back()->with('success', 'Alumna/o creada correctamente.');
    }

    public function update(Request $request, $subdomain, Student $student)
    {
        $studioId = Config::get('tenant.studio_id');
        
        // 1. OBTENER EL CÓDIGO DEL PAÍS (Elegancia PHP 8)
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        // 2. ESTANDARIZAR EL DOCUMENTO ANTES DE VALIDAR
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 3. VALIDACIÓN BLINDADA
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'country_id'  => 'required|exists:countries,id',
            'national_id' => [
                'nullable',
                'string',
                'max:255',
                new ValidDocument($countryCode), // Usamos el código real seleccionado
                Rule::unique('students', 'national_id')
                    ->ignore($student->id)
                    ->where(function ($query) use ($studioId) {
                        return $query->where('studio_id', $studioId);
                    })
            ],
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:255'
        ]);

        $student->update($request->all());
        return back()->with('success', 'Datos actualizados.');
    }

    public function destroy($subdomain, Student $student)
    {
        $student->delete();
        return back()->with('success', 'Alumna/o desactivada. Podrás encontrarla en la pestaña de Inactivas.');
    }

    public function restore($subdomain, $id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();
        return back()->with('success', 'Alumna/o reactivada correctamente.');
    }

    public function forceDelete($subdomain, $id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->forceDelete();
        return back()->with('success', 'Alumna/o eliminada permanentemente del sistema.');
    }

    public function calendar($subdomain, $studentId, $month = null)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $student = Student::withTrashed()->findOrFail($studentId);
        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        // 1. Cargamos las relaciones completas para la grilla del calendario
        $sessions = ClassSession::with(['workshop.studio', 'students', 'attendances'])
            ->where('studio_id', $studio->id)
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('start_time', 'asc')
            ->get();

        $sessionsByDate = $sessions->groupBy('date');

        // 2. Consulta Eloquent limpia para extraer los IDs pagados sin usar DB::table()
        $paidSessionIds = Payment::where('student_id', $student->id)
            ->with('classSessions')
            ->get()
            ->flatMap(function($payment) {
                return $payment->classSessions->pluck('id');
            })
            ->unique()
            ->toArray();

        return view('students.calendar', compact('student', 'monthDate', 'sessionsByDate', 'paidSessionIds'));
    }
}
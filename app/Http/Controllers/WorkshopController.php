<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Workshop;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Area;
use App\Models\Discipline;
use App\Models\Studio; // <-- Aseguramos la importación del modelo

class WorkshopController extends Controller
{
    public function index($subdomain)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $workshops = Workshop::with(['prices', 'teacher', 'discipline.area'])->orderBy('name', 'asc')->get();
        $teachers = Teacher::orderBy('name', 'asc')->get();
        
        $areas = Area::with('disciplines')->get();
        $categoryTree = [];
        
        foreach ($areas as $area) {
            $categoryTree[$area->name] = $area->disciplines->pluck('name')->toArray();
        }

        $existingAreas = array_keys($categoryTree);

        return view('workshops.index', compact('workshops', 'teachers', 'existingAreas', 'categoryTree'));
    }

    public function store(Request $request, $subdomain)
    {
        $this->validateWorkshop($request);

        // CRÍTICO: Obtenemos el Estudio actual usando el subdominio para heredar datos
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        DB::transaction(function () use ($request, $studio) {
            $area = Area::firstOrCreate(['name' => trim($request->area)]);
            $discipline = Discipline::firstOrCreate([
                'area_id' => $area->id,
                'name' => trim($request->discipline)
            ]);

            $data = $request->except(['prices', 'area', 'discipline']);
            
            // Asignaciones base
            $data['studio_id'] = $studio->id; // <-- Enlace vital
            $data['discipline_id'] = $discipline->id;
            $data['is_single_class'] = $request->is_single_class == '1';
            $data['use_main_location'] = $request->boolean('use_main_location');

            // Lógica de Ubicación (DRY)
            if ($data['use_main_location']) {
                $data['address'] = $studio->address;
                $data['latitude'] = $studio->latitude;
                $data['longitude'] = $studio->longitude;
                $data['city'] = $studio->city;
                $data['region'] = $studio->region;
                $data['country'] = $studio->country;
            } 
            // Si es falso, $data ya trae los valores del mapa personalizado gracias al $request->except()

            // Limpieza de fechas
            if ($data['is_single_class']) {
                $data['repeat_days'] = null;
            } else {
                $data['specific_date'] = null;
            }

            $workshop = Workshop::create($data);

            if ($request->has('prices')) {
                foreach ($request->prices as $priceRow) {
                    $workshop->prices()->create([
                        'class_count' => $priceRow['class_count'],
                        'price' => $priceRow['price'],
                        'is_monthly' => isset($priceRow['is_monthly']) ? true : false,
                    ]);
                }
            }

            if ($workshop->is_single_class) {
                ClassSession::create([
                    'studio_id' => $workshop->studio_id,
                    'workshop_id' => $workshop->id,
                    'date' => $workshop->specific_date,
                    'start_time' => $workshop->start_time
                ]);
            }
        });

        return back()->with('success', 'Taller configurado y guardado correctamente.');
    }

    public function update(Request $request, $subdomain, Workshop $workshop)
    {
        $this->validateWorkshop($request);

        // Obtenemos el Estudio para heredar datos si decide volver a la sede principal
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        DB::transaction(function () use ($request, $workshop, $studio) {
            $area = Area::firstOrCreate(['name' => trim($request->area)]);
            $discipline = Discipline::firstOrCreate([
                'area_id' => $area->id,
                'name' => trim($request->discipline)
            ]);

            $data = $request->except(['prices', 'area', 'discipline']);
            $data['discipline_id'] = $discipline->id;
            $data['is_single_class'] = $request->is_single_class == '1';
            $data['use_main_location'] = $request->boolean('use_main_location');

            // Lógica de Ubicación (DRY)
            if ($data['use_main_location']) {
                $data['address'] = $studio->address;
                $data['latitude'] = $studio->latitude;
                $data['longitude'] = $studio->longitude;
                $data['city'] = $studio->city;
                $data['region'] = $studio->region;
                $data['country'] = $studio->country;
            }

            // Limpieza de fechas
            if ($data['is_single_class']) {
                $data['repeat_days'] = null;
            } else {
                $data['specific_date'] = null;
            }

            $workshop->update($data);

            // Sincronizar precios
            $workshop->prices()->delete();
            if ($request->has('prices')) {
                foreach ($request->prices as $priceRow) {
                    $workshop->prices()->create([
                        'class_count' => $priceRow['class_count'],
                        'price' => $priceRow['price'],
                        'is_monthly' => isset($priceRow['is_monthly']) ? true : false,
                    ]);
                }
            }

            // Sincronizar Sesiones de forma segura (NO DESTRUCTIVA)
            if ($workshop->is_single_class) {
                // Si es Masterclass, actualizamos su fecha y hora
                ClassSession::updateOrCreate(
                    ['workshop_id' => $workshop->id],
                    [
                        'studio_id' => $workshop->studio_id,
                        'date' => $workshop->specific_date, 
                        'start_time' => $workshop->start_time
                    ]
                );
            } else {
                // Si es un Taller Mensual, NUNCA borramos las sesiones.
                // Solo actualizamos la hora de inicio de las clases que aún no han ocurrido
                // para no alterar el historial de clases pasadas ni borrar a los inscritos.
                ClassSession::where('workshop_id', $workshop->id)
                    ->where('date', '>=', now()->toDateString())
                    ->update([
                        'start_time' => $workshop->start_time
                    ]);
            }
        });

        return back()->with('success', 'Taller actualizado exitosamente.');
    }

    public function destroy($subdomain, Workshop $workshop)
    {
        $workshop->delete();
        return back()->with('success', 'Taller eliminado.');
    }

    private function validateWorkshop(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'area' => 'required|string|max:100',
            'discipline' => 'required|string|max:100',
            'target_audience' => 'required|in:kids,teens,adults,all',
            
            // Campos de Ubicación
            'use_main_location' => 'nullable|boolean',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'room_location' => 'nullable|string|max:255',
            
            'color' => 'required|string',
            'teacher_id' => 'nullable|exists:teachers,id', 
            'start_time' => 'required',
            'is_single_class' => 'required|in:0,1',
            'repeat_days' => 'required_if:is_single_class,0|array',
            'repeat_days.*' => 'integer|min:0|max:6',
            'specific_date' => 'required_if:is_single_class,1|nullable|date',
            'max_students' => 'nullable|integer|min:1',
            'prices' => 'nullable|array',
            'prices.*.class_count' => 'required|integer|min:1',
            'prices.*.price' => 'required|numeric|min:0',
        ];

        $messages = [
            'name.required' => 'El nombre del taller es obligatorio.',
            'area.required' => 'Debes indicar un área general.',
            'discipline.required' => 'Debes seleccionar o escribir una disciplina específica.',
            'start_time.required' => 'Debes ingresar la hora de inicio.',
            'repeat_days.required_if' => 'Para un taller mensual, debes seleccionar al menos un día.',
            'specific_date.required_if' => 'Para una clase única, debes seleccionar una fecha exacta.',
            'prices.*.class_count.required' => 'Falta indicar la cantidad de clases en un precio.',
            'prices.*.price.required' => 'Falta indicar el costo en un precio.',
            'target_audience.required' => 'Debes indicar a quién va dirigido el taller.'
        ];

        $request->validate($rules, $messages);
    }

    public function students($subdomain, Workshop $workshop)
    {
        $students = Student::where('is_guest', false)
                    ->orderBy('first_name', 'asc')
                    ->orderBy('last_name', 'asc')
                    ->get();
        
        $enrolledIds = $workshop->students()->pluck('students.id')->toArray();

        return view('workshops.students', compact('workshop', 'students', 'enrolledIds'));
    }

    public function syncStudents(Request $request, $subdomain, Workshop $workshop)
    {
        $request->validate([
            'students' => 'nullable|array',
            'students.*' => 'exists:students,id'
        ]);

        $workshop->students()->sync($request->students ?? []);

        return redirect()->route('workshops.index', ['subdomain' => $subdomain])
                         ->with('success', 'Lista de alumnas/os actualizada para el taller: ' . $workshop->name);
    }
}
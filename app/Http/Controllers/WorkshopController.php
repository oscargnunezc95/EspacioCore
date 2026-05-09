<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Workshop;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Area;
use App\Models\Discipline;
use App\Models\Studio;

class WorkshopController extends Controller
{
    public function index($subdomain)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        // 1. CORRECCIÓN DE RENDIMIENTO (N+1): Agregamos 'schedules' al Eager Loading
        $workshops = Workshop::with(['prices', 'teacher', 'discipline.area', 'schedules'])
                            ->orderBy('name', 'asc')
                            ->get();
        
        // 2. ORDENAMIENTO COMPLETO: Apellido y luego Nombre
        $teachers = Teacher::orderBy('first_name', 'asc')
                           ->orderBy('last_name', 'asc')
                           ->get();
        
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

        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        DB::transaction(function () use ($request, $studio) {
            $area = Area::firstOrCreate(['name' => trim($request->area)]);
            $discipline = Discipline::firstOrCreate([
                'area_id' => $area->id,
                'name' => trim($request->discipline)
            ]);

            // Excluimos los arrays y la imagen para procesarlos individualmente
            $data = $request->except(['prices', 'schedules', 'area', 'discipline', 'image']);
            
            $data['studio_id'] = $studio->id;
            $data['discipline_id'] = $discipline->id;
            $data['is_single_class'] = $request->is_single_class == '1';
            $data['use_main_location'] = $request->boolean('use_main_location');

            // Subida de la imagen
            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('workshops/images', 'public');
            }

            // Ubicación
            if ($data['use_main_location']) {
                $data['address'] = $studio->address;
                $data['latitude'] = $studio->latitude;
                $data['longitude'] = $studio->longitude;
                $data['city'] = $studio->city;
                $data['region'] = $studio->region;
                $data['country'] = $studio->country;
            } 

            // Limpieza cruzada
            if (!$data['is_single_class']) {
                $data['specific_date'] = null;
                $data['start_time'] = null;
            }

            // Persistencia del Taller Base
            $workshop = Workshop::create($data);

            // 1. Guardar Horarios Dinámicos Múltiples (Solo si es recurrente)
            if (!$workshop->is_single_class && $request->has('schedules')) {
                foreach ($request->schedules as $schedule) {
                    $workshop->schedules()->create([
                        'day_of_week'  => $schedule['day'],
                        'start_time'   => $schedule['time'],
                        'max_students' => isset($schedule['max_students']) && $schedule['max_students'] !== '' ? $schedule['max_students'] : null,
                    ]);
                }
            }

            // 2. Guardar Planes de Precios
            if ($request->has('prices')) {
                foreach ($request->prices as $priceRow) {
                    $workshop->prices()->create([
                        'class_count'            => $priceRow['class_count'],
                        'price'                  => $priceRow['price'],
                        'is_monthly'             => isset($priceRow['is_monthly']) ? true : false,
                        'introductory_price'     => !empty($priceRow['introductory_price']) ? $priceRow['introductory_price'] : null,
                        'is_introductory_active' => isset($priceRow['is_introductory_active']) ? true : false,
                    ]);
                }
            }

            // 3. Generar Sesión para Clase Única (Masterclass)
            if ($workshop->is_single_class) {
                ClassSession::create([
                    'studio_id'   => $workshop->studio_id,
                    'workshop_id' => $workshop->id,
                    'date'        => $workshop->specific_date,
                    'start_time'  => $request->start_time
                ]);
            }
        });

        return back()->with('success', 'Taller configurado y guardado correctamente.');
    }

    public function update(Request $request, $subdomain, Workshop $workshop)
    {
        $this->validateWorkshop($request);

        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        DB::transaction(function () use ($request, $workshop, $studio) {
            $area = Area::firstOrCreate(['name' => trim($request->area)]);
            $discipline = Discipline::firstOrCreate([
                'area_id' => $area->id,
                'name' => trim($request->discipline)
            ]);

            $data = $request->except(['prices', 'schedules', 'area', 'discipline', 'image']);
            $data['discipline_id'] = $discipline->id;
            $data['is_single_class'] = $request->is_single_class == '1';
            $data['use_main_location'] = $request->boolean('use_main_location');

            // Reemplazo de Imagen
            if ($request->hasFile('image')) {
                if ($workshop->image_path) {
                    Storage::disk('public')->delete($workshop->image_path);
                }
                $data['image_path'] = $request->file('image')->store('workshops/images', 'public');
            }

            // Ubicación
            if ($data['use_main_location']) {
                $data['address'] = $studio->address;
                $data['latitude'] = $studio->latitude;
                $data['longitude'] = $studio->longitude;
                $data['city'] = $studio->city;
                $data['region'] = $studio->region;
                $data['country'] = $studio->country;
            }

            // Limpieza cruzada y limpieza de la base de datos
            if ($data['is_single_class']) {
                $workshop->schedules()->delete();
            } else {
                $data['specific_date'] = null;
                $data['start_time'] = null;
            }

            $workshop->update($data);

            // 1. Sincronizar Horarios Dinámicos Múltiples
            if (!$workshop->is_single_class) {
                $workshop->schedules()->delete(); // Limpiamos los viejos por seguridad
                if ($request->has('schedules')) {
                    foreach ($request->schedules as $schedule) {
                        $workshop->schedules()->create([
                            'day_of_week'  => $schedule['day'],
                            'start_time'   => $schedule['time'],
                            'max_students' => isset($schedule['max_students']) && $schedule['max_students'] !== '' ? $schedule['max_students'] : null,
                        ]);
                    }
                }
            }

            // 2. Sincronizar Planes de Precios
            $workshop->prices()->delete();
            if ($request->has('prices')) {
                foreach ($request->prices as $priceRow) {
                    $workshop->prices()->create([
                        'class_count'            => $priceRow['class_count'],
                        'price'                  => $priceRow['price'],
                        'is_monthly'             => isset($priceRow['is_monthly']) ? true : false,
                        'introductory_price'     => !empty($priceRow['introductory_price']) ? $priceRow['introductory_price'] : null,
                        'is_introductory_active' => isset($priceRow['is_introductory_active']) ? true : false,
                    ]);
                }
            }

            // 3. Sincronizar Sesión para Clase Única
            if ($workshop->is_single_class) {
                ClassSession::updateOrCreate(
                    ['workshop_id' => $workshop->id],
                    [
                        'studio_id'  => $workshop->studio_id,
                        'date'       => $workshop->specific_date, 
                        'start_time' => $request->start_time
                    ]
                );
            }
        });

        return back()->with('success', 'Taller actualizado exitosamente.');
    }

    public function destroy($subdomain, Workshop $workshop)
    {
        // Borrar imagen del taller antes de eliminarlo
        if ($workshop->image_path) {
            Storage::disk('public')->delete($workshop->image_path);
        }
        
        $workshop->delete();
        return back()->with('success', 'Taller eliminado.');
    }

    private function validateWorkshop(Request $request)
    {
        $rules = [
            'name'              => 'required|string|max:255',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'area'              => 'required|string|max:100',
            'discipline'        => 'required|string|max:100',
            'target_audience'   => 'required|in:kids,teens,adults,all',
            'use_main_location' => 'nullable|boolean',
            'address'           => 'nullable|string|max:255',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
            'city'              => 'nullable|string|max:255',
            'region'            => 'nullable|string|max:255',
            'country'           => 'nullable|string|max:255',
            'room_location'     => 'nullable|string|max:255',
            'color'             => 'required|string',
            'teacher_id'        => 'nullable|exists:teachers,id', 
            
            // Lógica de Clase Única (Masterclass)
            'is_single_class'   => 'required|in:0,1',
            'specific_date'     => 'required_if:is_single_class,1|nullable|date',
            'start_time'        => 'required_if:is_single_class,1', 
            
            // LÓGICA DE HORARIOS MÚLTIPLES
            'schedules'                  => 'required_if:is_single_class,0|array|min:1',
            'schedules.*.day'            => 'required_with:schedules|integer|min:0|max:6',
            'schedules.*.time'           => 'required_with:schedules',
            'schedules.*.max_students'   => 'nullable|integer|min:1',

            'max_students'      => 'nullable|integer|min:1',
            
            // VALIDACIÓN DEL ARREGLO DE PRECIOS
            'prices'                       => 'nullable|array',
            'prices.*.class_count'         => 'required|integer|min:1',
            'prices.*.price'               => 'required|numeric|min:0',
            'prices.*.introductory_price'  => 'nullable|numeric|min:0',
        ];

        $messages = [
            'name.required' => 'El nombre de la clase o taller es obligatorio.',
            'area.required' => 'Debes asignar un área o categoría principal.',
            'discipline.required' => 'Debes especificar la disciplina de la clase.',
            'target_audience.required' => 'Selecciona a qué público va dirigido.',
            'color.required' => 'Elige un color para identificar este taller.',
            
            'start_time.required_if' => 'Debes asignar una hora para tu Masterclass.',
            'specific_date.required_if' => 'Debes indicar la fecha exacta en el calendario para tu Masterclass.',
            
            'schedules.required_if' => 'Debes agregar al menos un horario en la semana para este taller.',
            'schedules.*.day.required_with' => 'Asegúrate de seleccionar el día en todos los horarios.',
            'schedules.*.time.required_with' => 'Asegúrate de indicar la hora en todos los horarios.',

            'prices.*.class_count.required' => 'Indica la cantidad de clases para este paquete (ej: 4 clases).',
            'prices.*.price.required' => 'Debes asignarle un precio base a tu paquete.',
        ];

        $request->validate($rules, $messages);
    }

    public function students($subdomain, Workshop $workshop)
    {
        // Se carga la lista de alumnas ordenando por nombre y luego apellido
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
                         ->with('success', 'Lista actualizada para: ' . $workshop->name);
    }
}
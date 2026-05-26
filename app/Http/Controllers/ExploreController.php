<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Area;
use Illuminate\Support\Facades\Log;
use App\Models\Workshop;
use Carbon\Carbon;

class ExploreController extends Controller
{
    public function index(Request $request)
    {
        // 1. Consulta base apagando scopes globales en cascada
        $query = ClassSession::withoutGlobalScopes()->with([
            'workshop' => function($q) {
                $q->withoutGlobalScopes(); 
            },
            'workshop.studio', 
            'workshop.discipline.area',
            'workshop.teacher' => function($q) {
                $q->withoutGlobalScopes(); 
            },
            'workshop.prices' => function($q) {
                $q->withoutGlobalScopes(); 
            }
        ])
        ->where('is_cancelled', false)
        ->where('date', '>=', Carbon::today()->toDateString());

        // 2. Aplicación de Filtros
        if ($request->filled('area')) {
            $query->whereHas('workshop.discipline.area', function($q) use ($request) {
                $q->where('name', $request->area);
            });
        }

        if ($request->filled('city')) {
            $query->whereHas('workshop', function($q) use ($request) {
                $q->withoutGlobalScopes()->where('city', $request->city);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // 3. Ejecución de la consulta con paginación
        $sessions = $query->orderBy('date', 'asc')
                          ->orderBy('start_time', 'asc')
                          ->paginate(24)
                          ->withQueryString();

        // 4. Datos para selectores
        $areas = Area::withoutGlobalScopes()->orderBy('name', 'asc')->get();
        $cities = Workshop::withoutGlobalScopes()
                          ->whereNotNull('city')
                          ->distinct()
                          ->orderBy('city', 'asc')
                          ->pluck('city');

        // 5. ESTADO DE USUARIO FAMILIAR (ARQUITECTURA DE MAPAS)
        $dbSelectionsBySession = [];

        if (auth()->check()) {
            $user = auth()->user();
            $userId = $user->id;
            
            // Traemos TODAS las sesiones de la alumna y su familia desde hoy en adelante
            $userSessions = ClassSession::withoutGlobalScopes()
                ->whereHas('students', function ($q) use ($userId) {
                    $q->withoutGlobalScopes()->where('students.user_id', $userId);
                })
                ->where('date', '>=', Carbon::today()->toDateString())
                ->with(['students' => function ($q) use ($userId) {
                    $q->withoutGlobalScopes()->where('students.user_id', $userId);
                }])
                ->get();

            foreach ($userSessions as $session) {
                $selections = [];
                foreach ($session->students as $st) {
                    $status = $st->pivot->payment_status; 
                    
                    if (!empty($st->national_id)) {
                        if ($st->national_id === $user->national_id) {
                            $selections['titular'] = $status;
                        } else {
                            // AQUÍ ESTABA EL DETALLE: 
                            // Buscamos el ID del familiar que coincide con este RUT
                            $dep = $user->dependents->where('national_id', $st->national_id)->first();
                            if ($dep) {
                                $selections[$dep->id] = $status; // Mantenemos el ID numérico
                            }
                        }
                    } else {
                        if ($st->first_name === $user->name) {
                            $selections['titular'] = $status;
                        }
                    }
                }
            }
        }
        Log::info("DEBUG DE SELECCIONES:", $dbSelectionsBySession);
        return view('explore.index', compact('sessions', 'areas', 'cities', 'dbSelectionsBySession'));
    }
}
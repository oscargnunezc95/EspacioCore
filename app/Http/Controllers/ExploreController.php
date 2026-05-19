<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Area;
use App\Models\Workshop;
use Carbon\Carbon;
// use Illuminate\Support\Facades\DB; // <-- Lo quitamos, ya no hace falta.

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
            // CRÍTICO: Apagar scopes para que el profesor y los precios aparezcan
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

        // 5. ESTADO DE USUARIO (ARQUITECTURA OPTIMIZADA O(1))
        $enrolledSessionIds = [];
        $paidSessionIds = []; 

        if (auth()->check()) {
            $userId = auth()->id();
            
            // Traemos TODAS las sesiones de la alumna desde hoy en adelante
            // incluyendo el estado de pago directamente de la tabla pivote.
            $userSessions = ClassSession::withoutGlobalScopes()
                ->whereHas('students', function ($q) use ($userId) {
                    $q->withoutGlobalScopes()->where('students.user_id', $userId);
                })
                ->where('date', '>=', Carbon::today()->toDateString())
                // Traemos los datos de la alumna para poder leer el pivote
                ->with(['students' => function ($q) use ($userId) {
                    $q->withoutGlobalScopes()->where('students.user_id', $userId);
                }])
                ->get();

            foreach ($userSessions as $session) {
                // Si la sesión llegó aquí, es porque está inscrita (enrolled)
                $enrolledSessionIds[] = $session->id;
                
                // Extraemos a la alumna de la colección (debería ser solo una)
                $student = $session->students->first();
                
                // Si el status mágico de la pivote dice 'paid', lo agregamos a la otra lista
                if ($student && $student->pivot->payment_status === 'paid') {
                    $paidSessionIds[] = $session->id;
                }
            }
        }

        return view('explore.index', compact('sessions', 'areas', 'cities', 'enrolledSessionIds', 'paidSessionIds'));
    }
}
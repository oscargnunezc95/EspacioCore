<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Area;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <-- Asegúrate de importar DB arriba

class ExploreController extends Controller
{
    public function index(Request $request)
    {
        // 1. Iniciamos la consulta apagando los Global Scopes del Multi-tenant
        $query = ClassSession::withoutGlobalScopes()->with([
            'workshop' => function($q) {
                $q->withoutGlobalScopes(); // Apagamos el scope también en la relación
            },
            'workshop.studio', 
            'workshop.discipline.area',
            'workshop.teacher',
            'workshop.prices'
        ])
        ->where('is_cancelled', false)
        ->where('date', '>=', Carbon::today()->toDateString());

        // 2. Filtro por Área
        if ($request->filled('area')) {
            $query->whereHas('workshop.discipline.area', function($q) use ($request) {
                $q->where('name', $request->area);
            });
        }

        // 3. Filtro por Ciudad
        if ($request->filled('city')) {
            $query->whereHas('workshop', function($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        // 4. Filtro por Fechas
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // 5. Ordenamos y paginamos (Solo una vez)
        $sessions = $query->orderBy('date', 'asc')
                          ->orderBy('start_time', 'asc')
                          ->paginate(24)
                          ->withQueryString();

        // 6. Datos extra para los selectores (Apagando Scopes para obtener ciudades de todos los estudios)
        $areas = Area::withoutGlobalScopes()->orderBy('name', 'asc')->get();
        
        $cities = Workshop::withoutGlobalScopes()
                          ->whereNotNull('city')
                          ->distinct()
                          ->orderBy('city', 'asc')
                          ->pluck('city');

        // 7. Buscar clases del usuario actual en todo el sistema
        $enrolledSessionIds = [];
        $paidSessionIds = []; // <--- NUEVA VARI    ABLE PARA CLASES PAGADAS

        if (auth()->check()) {
            $userId = auth()->id();

            // A) Clases donde está inscrito (Botones Verdes)
            $enrolledSessionIds = ClassSession::withoutGlobalScopes()
                ->whereHas('students', function ($q) use ($userId) {
                    $q->withoutGlobalScopes()->where('students.user_id', $userId);
                })
                ->where('date', '>=', Carbon::today()->toDateString())
                ->pluck('id')
                ->toArray();

            // B) Clases que ya están PAGADAS (Botones Azules Bloqueados)
            $paidSessionIds = \Illuminate\Support\Facades\DB::table('class_session_payment')
                ->whereIn('student_id', function($q) use ($userId) {
                    // Subconsulta: Obtenemos todos los IDs de alumno que le pertenecen a este usuario en cualquier estudio
                    $q->select('id')->from('students')->where('user_id', $userId);
                })
                ->pluck('class_session_id')
                ->toArray();
        }

        // 8. Pasamos ambas variables a la vista
        return view('explore.index', compact('sessions', 'areas', 'cities', 'enrolledSessionIds', 'paidSessionIds'));
    }

}
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

        // 2. Aplicacion de Filtros
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

        // 3. Ejecucion de la consulta con paginacion
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
                            $dep = $user->dependents->where('national_id', $st->national_id)->first();
                            if ($dep) {
                                $selections[$dep->id] = $status;
                            }
                        }
                    } else {
                        if ($st->first_name === $user->name) {
                            $selections['titular'] = $status;
                        }
                    }
                }
                $dbSelectionsBySession[$session->id] = $selections;
            }
        }

        // ============================================================
        // 6. SEO DINAMICO (tipo MercadoLibre)
        // ============================================================
        $seo = $this->buildSeo($request, $sessions, $areas);
        $breadcrumbs = $this->buildBreadcrumbs($request);

        return view('explore.index', compact(
            'sessions', 'areas', 'cities', 'dbSelectionsBySession', 'seo', 'breadcrumbs'
        ));
    }

    /**
     * Construye meta tags dinamicos basados en los filtros activos.
     */
    private function buildSeo(Request $request, $sessions, $areas): array
    {
        $city = $request->get('city');
        $area = $request->get('area');
        $total = $sessions->total();

        // Pagina actual para title
        $page = $request->get('page', 1);
        $pageSuffix = $page > 1 ? " — Pagina {$page}" : '';

        // --- TITULO ---
        if ($city && $area) {
            $title = "Clases de {$area} en {$city} — Encuentra y Reserva{$pageSuffix} | EstadoPrisma";
        } elseif ($city) {
            $title = "Talleres y Clases en {$city} — Circo, Danza, Acrobacia{$pageSuffix} | EstadoPrisma";
        } elseif ($area) {
            $title = "Clases de {$area} — Talleres y Cursos Cerca de Ti{$pageSuffix} | EstadoPrisma";
        } else {
            $title = "Clases y Talleres de Circo, Danza, Acrobacia y mas — Encuentra tu Proxima Clase{$pageSuffix} | EstadoPrisma";
        }

        // --- DESCRIPTION ---
        if ($city && $area) {
            $description = "Encuentra {$total} clases de {$area} en {$city}. Reserva tu cupo: clases sueltas, packs mensuales y talleres en los mejores estudios cerca de ti. ¡Agenda ahora!";
        } elseif ($city) {
            $description = "Descubre {$total} talleres y clases en {$city}. Circo, danza, acrobacia y mas. Compara precios, ve horarios y reserva tu lugar en segundos.";
        } elseif ($area) {
            $description = "Explora {$total} clases de {$area}. Clases sueltas y planes mensuales en estudios verificados. Encuentra tu taller ideal y reserva al instante.";
        } else {
            $description = "Encuentra {$total} clases y talleres de circo, danza, acrobacia y mas disciplinas. Busca por ciudad, categoria y fecha. ¡Reserva tu proxima clase ahora!";
        }

        // --- CANONICAL ---
        $canonical = route('explore', $request->only(['city', 'area']));

        return [
            'title'       => $title,
            'description' => $description,
            'canonical'   => $canonical,
            'city'        => $city,
            'area'        => $area,
            'total'       => $total,
            'page'        => $page,
        ];
    }

    /**
     * Breadcrumbs semanticos para SEO y navegacion.
     */
    private function buildBreadcrumbs(Request $request): array
    {
        $bc = [
            ['label' => 'Inicio', 'url' => route('home')],
            ['label' => 'Explorar Clases', 'url' => route('explore')],
        ];

        if ($request->get('area')) {
            $bc[] = ['label' => $request->get('area'), 'url' => route('explore', ['area' => $request->get('area')])];
        }
        if ($request->get('city')) {
            $bc[] = ['label' => $request->get('city'), 'url' => route('explore', $request->only(['city', 'area']))];
        }

        return $bc;
    }
}

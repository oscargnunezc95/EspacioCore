<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Area;
use App\Models\Country;
use App\Models\Discipline;
use Illuminate\Support\Facades\Log;
use App\Models\Workshop;
use App\Services\ExploreService;
use Carbon\Carbon;

class ExploreController extends Controller
{
    /**
     * Muestra la página pública de exploración de clases con:
     * - Filtros por país, región, ciudad, área, disciplina, público objetivo, fecha
     * - Cupos disponibles e interesados por sesión
     * - Cola visual de estudiantes (nombres solo para auth)
     */
    public function index(Request $request, ExploreService $exploreService)
    {
        // 1. Consulta base apagando scopes globales en cascada
        $query = ClassSession::withoutGlobalScopes()->with([
            'schedule',
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
        ->where('date', '>=', Carbon::today()->toDateString())
        // 🔴 FILTRO MAESTRO: Excluir clases de estudios con facturas vencidas
        ->whereHas('workshop.studio', function ($studioQuery) {
            $studioQuery->withoutGlobalScopes()
                        ->whereDoesntHave('invoices', function ($invoiceQuery) {
                            $invoiceQuery->where('status', 'past_due');
                        });
        });

        // 2. Aplicación de Filtros

        // País
        if ($request->filled('country')) {
            $query->whereHas('workshop', function($q) use ($request) {
                $q->withoutGlobalScopes()->where('country', $request->country);
            });
        }

        // Región
        if ($request->filled('region')) {
            $query->whereHas('workshop', function($q) use ($request) {
                $q->withoutGlobalScopes()->where('region', $request->region);
            });
        }

        // Ciudad
        if ($request->filled('city')) {
            $query->whereHas('workshop', function($q) use ($request) {
                $q->withoutGlobalScopes()->where('city', $request->city);
            });
        }

        // Área
        if ($request->filled('area')) {
            $query->whereHas('workshop.discipline.area', function($q) use ($request) {
                $q->where('name', $request->area);
            });
        }

        // Disciplina
        if ($request->filled('discipline')) {
            $query->whereHas('workshop.discipline', function($q) use ($request) {
                $q->where('name', $request->discipline);
            });
        }

        // Público Objetivo
        if ($request->filled('target_audience')) {
            $query->whereHas('workshop', function($q) use ($request) {
                $q->withoutGlobalScopes()->where('target_audience', $request->target_audience);
            });
        }

        // Fechas
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

        // 3.5 ENRIQUECER: Cupos disponibles, interesados y cola visual por sesión
        $sessions = $exploreService->enrichSessionStats($sessions);

        // 4. Datos para selectores del sidebar
        // Áreas (siempre todas)
        $areas = Area::withoutGlobalScopes()->orderBy('name', 'asc')->get();

        // Países (desde modelo Country normalizado)
        $countries = Country::orderBy('name', 'asc')->get();

        // Regiones: todas si no hay país seleccionado, o filtradas por país
        if ($request->filled('country')) {
            $regions = $exploreService->getRegionsByCountry($request->country);
        } else {
            $regions = Workshop::withoutGlobalScopes()
                ->whereNotNull('region')
                ->where('region', '!=', '')
                ->distinct()
                ->orderBy('region', 'asc')
                ->pluck('region');
        }

        // Ciudades: todas si no hay región seleccionada, o filtradas por región
        if ($request->filled('region')) {
            $cities = $exploreService->getCitiesByRegion($request->region);
        } else {
            $cities = Workshop::withoutGlobalScopes()
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->distinct()
                ->orderBy('city', 'asc')
                ->pluck('city');
        }

        // Disciplinas: precargadas si hay área seleccionada (para recarga GET)
        if ($request->filled('area')) {
            $disciplines = $exploreService->getDisciplinesByArea($request->area);
        } else {
            $disciplines = collect();
        }

        // Públicos objetivos (valores fijos)
        $targetAudiences = [
            (object)['value' => 'all',     'label' => 'Todas las edades'],
            (object)['value' => 'kids',    'label' => 'Niñas/os (hasta 12 años)'],
            (object)['value' => 'teens',   'label' => 'Adolescentes (13 - 17 años)'],
            (object)['value' => 'adults',  'label' => 'Adultos (+18 años)'],
        ];

        // 5. ESTADO DE USUARIO FAMILIAR (ARQUITECTURA DE MAPAS)
        $dbSelectionsBySession = [];
        $activeDependents = collect();

        if (auth()->check()) {
            $user = auth()->user();
            $userId = $user->id;

            $activeDependents = $user->activeDependents;
            $dependentNationalIds = $activeDependents->pluck('national_id')->toArray();

            $userSessions = ClassSession::withoutGlobalScopes()
                ->whereHas('students', function ($q) use ($userId, $dependentNationalIds) {
                    $q->withoutGlobalScopes()
                      ->where(function ($sub) use ($userId, $dependentNationalIds) {
                          $sub->where('students.user_id', $userId);
                          if (!empty($dependentNationalIds)) {
                              $sub->orWhereIn('students.national_id', $dependentNationalIds);
                          }
                      });
                })
                ->where('date', '>=', Carbon::today()->toDateString())
                ->with(['students' => function ($q) use ($userId, $dependentNationalIds) {
                    $q->withoutGlobalScopes()
                      ->where(function ($sub) use ($userId, $dependentNationalIds) {
                          $sub->where('students.user_id', $userId);
                          if (!empty($dependentNationalIds)) {
                              $sub->orWhereIn('students.national_id', $dependentNationalIds);
                          }
                      });
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
                            $dep = $activeDependents->where('national_id', $st->national_id)->first();
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

        $seo = $this->buildSeo($request, $sessions, $areas);
        $breadcrumbs = $this->buildBreadcrumbs($request);

        return view('explore.index', compact(
            'sessions', 'areas', 'countries', 'regions', 'cities', 'disciplines',
            'targetAudiences', 'dbSelectionsBySession', 'seo', 'breadcrumbs', 'activeDependents'
        ));
    }

    /**
     * Endpoint AJAX: Devuelve disciplinas filtradas por área.
     * GET /api/explore/disciplines?area=Circo
     */
    public function disciplinesByArea(Request $request, ExploreService $exploreService)
    {
        $area = $request->query('area');

        if (empty($area)) {
            return response()->json([], 200);
        }

        $disciplines = $exploreService->getDisciplinesByArea($area);

        return response()->json($disciplines);
    }

    /**
     * Endpoint AJAX: Devuelve regiones filtradas por país.
     * GET /api/explore/regions?country=Chile
     */
    public function regionsByCountry(Request $request, ExploreService $exploreService)
    {
        $country = $request->query('country');

        if (empty($country)) {
            return response()->json([], 200);
        }

        $regions = $exploreService->getRegionsByCountry($country);

        return response()->json($regions);
    }

    /**
     * Endpoint AJAX: Devuelve ciudades filtradas por región.
     * GET /api/explore/cities?region=Los+Lagos
     */
    public function citiesByRegion(Request $request, ExploreService $exploreService)
    {
        $region = $request->query('region');

        if (empty($region)) {
            return response()->json([], 200);
        }

        $cities = $exploreService->getCitiesByRegion($region);

        return response()->json($cities);
    }

    /**
     * Construye meta tags dinámicos basados en los filtros activos.
     */
    private function buildSeo(Request $request, $sessions, $areas): array
    {
        $country    = $request->get('country');
        $region     = $request->get('region');
        $city       = $request->get('city');
        $area       = $request->get('area');
        $discipline = $request->get('discipline');
        $target     = $request->get('target_audience');
        $total      = $sessions->total();

        $page = $request->get('page', 1);
        $pageSuffix = $page > 1 ? " — Página {$page}" : '';

        // Construir ubicación dinámicamente (ej. "Puerto Montt, Los Lagos, Chile")
        $locationParts = array_filter([$city, $region, $country]);
        $locationString = !empty($locationParts) ? implode(', ', $locationParts) : '';

        // Etiqueta de público objetivo
        $targetLabels = [
            'kids' => 'para niños', 'teens' => 'para adolescentes',
            'adults' => 'para adultos', 'all' => 'para todas las edades',
        ];
        $targetLabel = $target ? ($targetLabels[$target] ?? '') : '';

        // --- TÍTULO ---
        $titleParts = [];
        if ($discipline) {
            $titleParts[] = "Clases de {$discipline}";
        } elseif ($area) {
            $titleParts[] = "Clases de {$area}";
        } else {
            $titleParts[] = "Clases y Talleres de Circo, Danza, Acrobacia y más";
        }

        if ($locationString) {
            $titleParts[] = "en {$locationString}";
        }

        if ($targetLabel) {
            $titleParts[] = $targetLabel;
        }

        $titleParts[] = $discipline || $area || $locationString
            ? "— Encuentra y Reserva{$pageSuffix} | EstadoPrisma"
            : "— Encuentra tu Próxima Clase{$pageSuffix} | EstadoPrisma";

        $title = implode(' ', $titleParts);

        // --- DESCRIPCIÓN ---
        $descParts = [];
        if ($total > 0) {
            $descParts[] = "Encuentra {$total}";
        } else {
            $descParts[] = "Explora";
        }

        if ($discipline) {
            $descParts[] = "clases de {$discipline}";
        } elseif ($area) {
            $descParts[] = "clases de {$area}";
        } else {
            $descParts[] = "clases y talleres";
        }

        if ($locationString) {
            $descParts[] = "en {$locationString}";
        }

        if ($targetLabel) {
            $descParts[] = $targetLabel;
        }

        $descParts[] = ". Reserva tu cupo: clases sueltas, packs mensuales y talleres en los mejores estudios cerca de ti. ¡Agenda ahora!";
        $description = implode(' ', $descParts);

        $canonical = route('explore', $request->only(['country', 'region', 'city', 'area', 'discipline', 'target_audience']));

        return [
            'title'       => $title,
            'description' => $description,
            'canonical'   => $canonical,
            'country'     => $country,
            'region'      => $region,
            'city'        => $city,
            'area'        => $area,
            'discipline'  => $discipline,
            'target'      => $target,
            'total'       => $total,
            'page'        => $page,
        ];
    }

    /**
     * Breadcrumbs semánticos para SEO y navegación.
     */
    private function buildBreadcrumbs(Request $request): array
    {
        $bc = [
            ['label' => 'Explorar Clases', 'url' => route('explore')],
        ];

        if ($request->get('country')) {
            $bc[] = ['label' => $request->get('country'), 'url' => route('explore', ['country' => $request->get('country')])];
        }

        if ($request->get('region')) {
            $bc[] = ['label' => $request->get('region'), 'url' => route('explore', $request->only(['country', 'region']))];
        }

        if ($request->get('city')) {
            $bc[] = ['label' => $request->get('city'), 'url' => route('explore', $request->only(['country', 'region', 'city']))];
        }

        if ($request->get('area')) {
            $bc[] = ['label' => $request->get('area'), 'url' => route('explore', $request->only(['country', 'region', 'city', 'area']))];
        }

        if ($request->get('discipline')) {
            $bc[] = ['label' => $request->get('discipline'), 'url' => route('explore', $request->only(['country', 'region', 'city', 'area', 'discipline']))];
        }

        if ($request->get('target_audience')) {
            $targetLabels = ['kids' => 'Infantil', 'teens' => 'Adolescentes', 'adults' => 'Adultos', 'all' => 'Todas las edades'];
            $bc[] = ['label' => $targetLabels[$request->get('target_audience')] ?? $request->get('target_audience'), 'url' => route('explore', $request->only(['country', 'region', 'city', 'area', 'discipline', 'target_audience']))];
        }

        return $bc;
    }
}
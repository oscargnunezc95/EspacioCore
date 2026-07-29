<?php

namespace App\Http\Controllers;

use App\Models\Studio;
use App\Models\ClassSession;
use App\Services\ExploreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudioPublicController extends Controller
{
    /**
     * Carga los datos base compartidos entre todas las vistas del estudio:
     * studio, domain, fullStudioUrl, activeDependents, SEO y breadcrumbs.
     */
    private function baseStudioData(string $subdomain): array
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $domain   = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
        $protocol = request()->secure() ? 'https://' : 'http://';
        $fullStudioUrl = $protocol . $studio->subdomain . '.' . $domain;

        $activeDependents = auth()->check()
            ? auth()->user()->activeDependents
            : collect();

        $seo = [
            'title'       => $studio->name . ' — Clases, Talleres y Reservas',
            'description' => $studio->description
                ? Str::limit($studio->description, 150)
                : 'Descubre las clases y talleres disponibles en ' . $studio->name . '. Reserva tu cupo online de forma rápida y segura.',
            'canonical'   => $fullStudioUrl,
        ];

        $breadcrumbs = [
            ['label' => 'Inicio',   'url' => route('home')],
            ['label' => 'Explorar', 'url' => route('explore')],
            ['label' => $studio->name, 'url' => $fullStudioUrl],
        ];

        return compact(
            'studio',
            'domain',
            'protocol',
            'fullStudioUrl',
            'activeDependents',
            'seo',
            'breadcrumbs'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // 1. PERFIL (Información del estudio)
    // ─────────────────────────────────────────────────────────────
    public function perfil(string $subdomain)
    {
        $data = $this->baseStudioData($subdomain);
        $data['activeTab'] = 'perfil';

        return view('public.studio.perfil', $data);
    }

    // ─────────────────────────────────────────────────────────────
    // 2. TALLERES
    // ─────────────────────────────────────────────────────────────
    public function talleres(string $subdomain)
    {
        $data = $this->baseStudioData($subdomain);

        $data['studio']->load([
            'workshops.teacher',
            'workshops.discipline.area',
            'workshops.prices',
        ]);

        $data['workshops'] = $data['studio']->workshops;
        $data['activeTab'] = 'talleres';

        return view('public.studio.talleres', $data);
    }

    // ─────────────────────────────────────────────────────────────
    // 3. PROMOS Y PACKS
    // ─────────────────────────────────────────────────────────────
    public function promos(string $subdomain)
    {
        $data = $this->baseStudioData($subdomain);

        $data['studio']->load([
            'promotions.workshopPrices.workshop',
        ]);

        $data['activeTab'] = 'promos';

        return view('public.studio.promos', $data);
    }

    // ─────────────────────────────────────────────────────────────
    // 4. CALENDARIO (Clases, enrollment, mapa)
    // ─────────────────────────────────────────────────────────────
    public function calendario(Request $request, string $subdomain, ExploreService $exploreService)
    {
        $data = $this->baseStudioData($subdomain);
        // 1. Obtener fecha del mes solicitado (Por defecto: mes actual)
        $monthParam = $request->input('month', Carbon::now()->format('Y-m'));
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = Carbon::now()->startOfMonth();
        }

        $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $monthDate->copy()->endOfMonth()->toDateString();

        // 2. Consulta del Estudio con Eager Loading Quirúrgico (Corregido)
        $studio = Studio::where('subdomain', $subdomain)
            ->with([
                'workshops:id,studio_id,teacher_id,discipline_id,name,image_path,promo_video_url,description,address,latitude,longitude,max_students',
                'workshops.teacher:id,first_name,last_name,email',
                'workshops.discipline:id,area_id,name',
                'workshops.discipline.area:id,name',
                'workshops.prices',
                'promotions.workshopPrices.workshop:id,name',
                'classSessions' => function ($query) use ($request, $startOfMonth, $endOfMonth) {
                    $now = \Carbon\Carbon::now();

                    // 1. Carga estricta por mes (Windowed Fetching) y sin canceladas
                    $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                          ->where('is_cancelled', false)
                          // 🚀 NUEVO: Filtro cronológico estricto (Día y hora superior a la actual)
                          ->where(function ($q) use ($now) {
                              $q->where('date', '>', $now->toDateString())
                                ->orWhere(function ($sub) use ($now) {
                                    $sub->where('date', '=', $now->toDateString())
                                        ->where('start_time', '>=', $now->toTimeString());
                                });
                          });

                    if ($request->filled('workshop')) {
                        $query->where('workshop_id', $request->workshop);
                    }

                    if ($request->filled('day')) {
                        $driver = $query->getConnection()->getDriverName();
                        $day    = $request->day;

                        if ($driver === 'sqlite') {
                            $sqliteDay = $day == 7 ? 0 : $day;
                            $query->whereRaw("strftime('%w', date) = ?", [(string) $sqliteDay]);
                        } else {
                            $mysqlDay = ($day % 7) + 1;
                            $query->whereRaw('DAYOFWEEK(date) = ?', [$mysqlDay]);
                        }
                    }

                    $query->with([
                        'schedule', 
                        'workshop:id,studio_id,teacher_id,discipline_id,name,image_path,promo_video_url,description,address,max_students',
                        'workshop.teacher:id,first_name,last_name,email',
                        'workshop.discipline:id,area_id,name',
                        'workshop.discipline.area:id,name',
                        'workshop.prices',
                        'workshop.studio:id,name,subdomain,logo_path,icon_path,address'
                    ])
                    ->orderBy('date', 'asc')
                    ->orderBy('start_time', 'asc');
                },
            ])
            ->firstOrFail();
        
            // 3. Enriquecer sesiones con cupos disponibles (Service)
        $studio->setRelation('classSessions',
            $exploreService->enrichSessionCollection($studio->classSessions)
        );

        // 4. Configuración de Dominio y URLs
        $domain   = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
        $protocol = request()->secure() ? 'https://' : 'http://';
        $studioUrl = $protocol . $studio->subdomain . '.' . $domain;

        // 5. Consulta Optimizado al Pivot DB (Sin hidratar Eloquent Models)
        $dbSelectionsBySession = [];
        $activeDependents      = collect();

        if (Auth::check()) {
            $user             = Auth::user();
            $activeDependents = $user->activeDependents ?? collect();
            $sessionIds       = $studio->classSessions->pluck('id');

            if ($sessionIds->isNotEmpty()) {
                $rawSelections = DB::table('class_session_student')
                    ->join('students', 'class_session_student.student_id', '=', 'students.id')
                    ->whereIn('class_session_student.class_session_id', $sessionIds)
                    ->where(function ($q) use ($user, $activeDependents) {
                        $q->where('students.user_id', $user->id);
                        if ($activeDependents->isNotEmpty()) {
                            $q->orWhereIn('students.national_id', $activeDependents->pluck('national_id')->filter());
                        }
                    })
                    ->select(
                        'class_session_student.class_session_id',
                        'students.id as student_id',
                        'students.national_id',
                        'students.first_name',
                        'class_session_student.payment_status'
                    )
                    ->get();

                foreach ($rawSelections as $row) {
                    $sessionId = $row->class_session_id;
                    if (!isset($dbSelectionsBySession[$sessionId])) {
                        $dbSelectionsBySession[$sessionId] = [];
                    }

                    if ($row->national_id === $user->national_id || $row->first_name === $user->name) {
                        $dbSelectionsBySession[$sessionId]['titular'] = $row->payment_status;
                    } else {
                        $dep = $activeDependents->firstWhere('national_id', $row->national_id);
                        if ($dep) {
                            $dbSelectionsBySession[$sessionId][$dep->id] = $row->payment_status;
                        }
                    }
                }
            }
        }

        // 6. Catálogo JS Centralizado (Evita duplicar textos largos en el DOM)
        $workshopsCatalog = $studio->workshops->mapWithKeys(function ($workshop) use ($studio, $studioUrl) {
            $imageUrl = $workshop->image_path
                ? asset('storage/' . $workshop->image_path)
                : 'https://ui-avatars.com/api/?name=' . urlencode($workshop->name) . '&color=dc2626&background=fef2f2&size=512';

            return [$workshop->id => [
                'title'         => $workshop->name,
                'studio'        => $studio->name,
                'studio_url'    => $studioUrl,
                'teacher'       => $workshop->teacher ? trim($workshop->teacher->first_name . ' ' . $workshop->teacher->last_name) : 'Por asignar',
                'teacher_email' => $workshop->teacher->email ?? '',
                'image'         => $imageUrl,
                'address'       => $workshop->address ?? $studio->address ?? 'Dirección no especificada',
                'description'   => $workshop->description ?? 'Sin descripción disponible.',
                'video_url'     => $workshop->promo_video_url ?? '',
                'category'      => $workshop->discipline->area->name ?? $workshop->discipline->name ?? 'Clase',
            ]];
        });

        // 7. Ubicaciones del Mapa optimizadas iterando sobre talleres, no sesiones
        $mapLocationsData = $studio->workshops->map(function ($workshop) use ($studio, $studioUrl) {
            $imageUrl = $workshop->image_path
                ? asset('storage/' . $workshop->image_path)
                : 'https://ui-avatars.com/api/?name=' . urlencode($workshop->name) . '&color=dc2626&background=fef2f2&size=512';

            return [
                'id'     => $workshop->id,
                'title'  => $workshop->name,
                'studio' => $studio->name,
                'lat'    => (float) ($workshop->latitude ?? $studio->latitude ?? 0),
                'lng'    => (float) ($workshop->longitude ?? $studio->longitude ?? 0),
                'image'  => $imageUrl,
                'url'    => $studioUrl,
            ];
        })->filter(fn ($l) => $l['lat'] !== 0.0)->values();

        // 8. Retornar vista con datos aligerados
        return view('public.studio.calendario', [
            'studio'                => $studio,
            'workshops'             => $studio->workshops,
            'workshopsCatalog'      => $workshopsCatalog,
            'dbSelectionsBySession' => $dbSelectionsBySession,
            'activeDependents'      => $activeDependents,
            'mapLocationsData'      => $mapLocationsData,
            'monthDate'             => $monthDate,
            'activeTab'             => 'clases',
            'seo'                   => [
                'title'       => $studio->name . ' — Horarios y Clases',
                'description' => $studio->description ?? 'Reserva clases online en ' . $studio->name,
                'canonical'   => $studioUrl,
                'total'       => $studio->classSessions->count(),
            ],
        ], $data);
    }
}

<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Studio;
use App\Models\Workshop;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExploreService
{
    /**
     * Versión para colecciones simples (no paginadas).
     */
    public function enrichSessionCollection(Collection $sessions): Collection
    {
        if ($sessions->isEmpty()) {
            return $sessions;
        }

        $sessionIds = $sessions->pluck('id')->toArray();

        $stats = DB::table('class_session_student')
            ->select('class_session_id')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->whereIn('class_session_id', $sessionIds)
            ->groupBy('class_session_id')
            ->get()
            ->keyBy('class_session_id');

        // Solución: Usar transform() y setAttribute()
        $sessions->transform(function ($session) use ($stats) {
            $stat = $stats->get($session->id);
            $maxStudents = $session->max_students;

            $paidCount    = (int) ($stat?->paid_count ?? 0);
            $pendingCount = (int) ($stat?->pending_count ?? 0);
            $available    = max(0, $maxStudents - $paidCount);

            $session->setAttribute('paid_count', $paidCount);
            $session->setAttribute('pending_count', $pendingCount);
            $session->setAttribute('available_spots', $available);
            $session->setAttribute('max_spots', $maxStudents);

            return $session;
        });

        return $sessions;
    }

    /**
     * Enriquece una colección paginada de ClassSessions.
     */
    public function enrichSessionStats(LengthAwarePaginator $sessions): LengthAwarePaginator
    {
        if ($sessions->isEmpty()) {
            return $sessions;
        }

        $sessionIds = $sessions->pluck('id')->toArray();

        $stats = DB::table('class_session_student')
            ->select('class_session_id')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->whereIn('class_session_id', $sessionIds)
            ->groupBy('class_session_id')
            ->get()
            ->keyBy('class_session_id');

        // Solución: Acceder a getCollection() del Paginator, transformarla e inyectar atributos
        $sessions->getCollection()->transform(function ($session) use ($stats) {
            $stat = $stats->get($session->id);
            $maxStudents = $session->max_students; // Utiliza el accessor del modelo

            // Nullsafe operator (?->) para evitar errores silenciosos en PHP 8 si $stat es nulo
            $paidCount    = (int) ($stat?->paid_count ?? 0);
            $pendingCount = (int) ($stat?->pending_count ?? 0);
            $available    = max(0, $maxStudents - $paidCount);

            // Inyección forzada a los Attributes de Eloquent (Garantiza que la vista los lea)
            $session->setAttribute('paid_count', $paidCount);
            $session->setAttribute('pending_count', $pendingCount);
            $session->setAttribute('available_spots', $available);
            $session->setAttribute('max_spots', $maxStudents);

            return $session;
        });

        return $sessions;
    }

    /**
     * Obtiene las disciplinas pertenecientes a un área específica.
     * Usado para carga asíncrona de dropdowns dependientes en el explorer.
     */
    public function getDisciplinesByArea(string $areaName): Collection
    {
        $area = Area::withoutGlobalScopes()
            ->where('name', $areaName)
            ->first();

        if (!$area) {
            return collect();
        }

        return $area->disciplines()
            ->withoutGlobalScopes()
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
    }

    /**
     * Obtiene regiones distintas para un país dado.
     * Combina valores de workshops y studios (strings planos).
     */
    public function getRegionsByCountry(string $country): Collection
    {
        $workshopRegions = Workshop::withoutGlobalScopes()
            ->where('country', $country)
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->orderBy('region', 'asc')
            ->pluck('region');

        $studioRegions = Studio::where('country', $country)
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->orderBy('region', 'asc')
            ->pluck('region');

        return $workshopRegions->merge($studioRegions)->unique()->sort()->values();
    }

    /**
     * Obtiene ciudades distintas para una región dada.
     * Combina valores de workshops y studios (strings planos).
     */
    public function getCitiesByRegion(string $region): Collection
    {
        $workshopCities = Workshop::withoutGlobalScopes()
            ->where('region', $region)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city', 'asc')
            ->pluck('city');

        $studioCities = Studio::where('region', $region)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city', 'asc')
            ->pluck('city');

        return $workshopCities->merge($studioCities)->unique()->sort()->values();
    }
}
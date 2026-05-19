<?php

namespace App\Http\Controllers;

use App\Models\Studio;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudioPublicController extends Controller
{
    // 1. Inyectamos la clase Request para atrapar los filtros de la URL
    public function show(Request $request, $subdomain)
    {
        $studio = Studio::where('subdomain', $subdomain)
            ->with([
                'workshops.teacher',
                'workshops.discipline',
                'workshops.prices',
                'promotions.workshopPrices.workshop',
                
                // 2. Pasamos el $request al closure usando 'use ($request)'
                'classSessions' => function($query) use ($request) {
                    $query->where('date', '>=', Carbon::today())
                          ->where('is_cancelled', false);

                    // FILTRO 1: Por Taller específico
                    if ($request->filled('workshop')) {
                        $query->where('workshop_id', $request->workshop);
                    }

                    // FILTRO 2: Por Día de la Semana (Arquitectura Cross-Database)
                    if ($request->filled('day')) {
                        $driver = $query->getConnection()->getDriverName();
                        $day = $request->day; // Viene del HTML: 1 = Lunes, 7 = Domingo

                        if ($driver === 'sqlite') {
                            // En SQLite: strftime('%w', date) devuelve 0 (Domingo) a 6 (Sábado)
                            $sqliteDay = $day == 7 ? 0 : $day;
                            $query->whereRaw("strftime('%w', date) = ?", [(string) $sqliteDay]);
                        } else {
                            // En MySQL/MariaDB: DAYOFWEEK() devuelve 1 (Domingo) a 7 (Sábado)
                            $mysqlDay = ($day % 7) + 1;
                            $query->whereRaw('DAYOFWEEK(date) = ?', [$mysqlDay]);
                        }
                    }

                    $query->with('workshop.teacher')
                          ->orderBy('date', 'asc')
                          ->orderBy('start_time', 'asc');
                }
            ])
            ->firstOrFail();

        $enrolledSessionIds = [];
        if (auth()->check() && auth()->user()->student) {
            $enrolledSessionIds = auth()->user()->student->classSessions()
                ->where('date', '>=', Carbon::today())
                ->pluck('class_sessions.id')
                ->toArray();
        }

        // Extraemos los talleres ya cargados del estudio para poblar el <select> de la vista
        $workshops = $studio->workshops;

        return view('public.studio.show', compact('studio', 'enrolledSessionIds', 'workshops'));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workshop;
use App\Models\ClassSession;
use App\Models\Studio;
use Carbon\Carbon;

class TrainingMonthController extends Controller
{
    public function index($subdomain)
    {
        $months = ClassSession::selectRaw('strftime("%Y-%m", date) as month_id, MIN(date) as first_date')
            ->groupBy('month_id')
            ->orderBy('first_date', 'desc')
            ->get();

        $workshops = Workshop::orderBy('name', 'asc')->get();

        return view('trainingmonth.index', compact('months', 'workshops'));
    }

    public function store(Request $request, $subdomain)
    {
        $request->validate([
            'month_year' => 'required',
            'workshops' => 'required|array'
        ]);

        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $date = Carbon::parse($request->month_year . '-01');
        $year = $date->year;
        $month = $date->month;

        // OPTIMIZACIÓN SENIOR: Cargamos la relación 'schedules' para evitar el problema de N+1
        $selectedWorkshops = Workshop::with('schedules')->whereIn('id', $request->workshops)->get();
        $clasesGeneradas = 0;

        foreach ($selectedWorkshops as $workshop) {
            
            if ($workshop->is_single_class) {
                // ---------------------------------------------------
                // LÓGICA 1: MASTERCLASS (Clase Única)
                // ---------------------------------------------------
                if (!$workshop->specific_date) continue;
                
                $specificDate = Carbon::parse($workshop->specific_date);
                
                if ($specificDate->year == $year && $specificDate->month == $month) {
                    $session = ClassSession::firstOrCreate(
                        // Agregamos start_time a la búsqueda para evitar falsos positivos
                        [
                            'workshop_id' => $workshop->id, 
                            'date' => $specificDate->toDateString(),
                            'start_time' => $workshop->start_time
                        ],
                        [
                            'studio_id' => $studio->id
                        ]
                    );
                    if ($session->wasRecentlyCreated) $clasesGeneradas++;
                }

            } else {
                // ---------------------------------------------------
                // LÓGICA 2: TALLERES MENSUALES (Horarios Dinámicos)
                // ---------------------------------------------------
                if ($workshop->schedules->isEmpty()) continue;

                $daysInMonth = $date->daysInMonth;
                
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $currentDay = Carbon::create($year, $month, $d);
                    $dayOfWeek = $currentDay->dayOfWeek; // Devuelve 0 (Dom) a 6 (Sáb)
                    
                    // Extraemos todos los bloques de horario configurados para este día exacto
                    $schedulesToday = $workshop->schedules->where('day_of_week', $dayOfWeek);
                    
                    foreach ($schedulesToday as $schedule) {
                        $session = ClassSession::firstOrCreate(
                            [
                                'workshop_id' => $workshop->id, 
                                'date' => $currentDay->toDateString(),
                                'start_time' => $schedule->start_time // Crucial para permitir varios bloques al día
                            ],
                            [
                                'studio_id' => $studio->id
                            ]
                        );
                        
                        if ($session->wasRecentlyCreated) $clasesGeneradas++;
                    }
                }
            }
        }

        return redirect()->route('trainingmonth.show', ['subdomain' => $subdomain, 'month' => $request->month_year])
                         ->with('success', "Calendario generado exitosamente. Se agregaron $clasesGeneradas clases nuevas.");
    }

    public function show($subdomain, $monthId)
    {
        $monthDate = Carbon::parse($monthId . '-01');
        
        $sessions = ClassSession::with('workshop')
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('date', 'asc') // Añadido para que el calendario se renderice en orden
            ->orderBy('start_time', 'asc') // Añadido para ordenar bloques del mismo día
            ->get();

        $sessionsByDate = $sessions->groupBy('date');

        return view('trainingmonth.show', compact('sessionsByDate', 'monthDate', 'monthId'));
    }
}
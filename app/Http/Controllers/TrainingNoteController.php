<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workshop;
use App\Models\ClassSession;
use Carbon\Carbon;

class TrainingMonthController extends Controller
{
    public function index()
    {
        // Agrupamos las sesiones por mes y año para la lista principal
        $months = ClassSession::selectRaw('strftime("%Y-%m", date) as month_id, MIN(date) as first_date')
            ->groupBy('month_id')
            ->orderBy('first_date', 'desc')
            ->get();

        $workshops = Workshop::orderBy('name', 'asc')->get();

        return view('entrenamientos.index', compact('months', 'workshops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month_year' => 'required', // Formato YYYY-MM desde el input tipo month
            'workshops' => 'required|array'
        ]);

        $date = Carbon::parse($request->month_year . '-01');
        $year = $date->year;
        $month = $date->month;

        $selectedWorkshops = Workshop::whereIn('id', $request->workshops)->get();

        foreach ($selectedWorkshops as $workshop) {
            
            if ($workshop->is_single_class) {
                // --- LÓGICA PARA CLASE ÚNICA ---
                $specificDate = Carbon::parse($workshop->specific_date);
                
                // Solo la creamos si la fecha única coincide con el mes/año que estamos planificando
                if ($specificDate->year == $year && $specificDate->month == $month) {
                    ClassSession::firstOrCreate([
                        'workshop_id' => $workshop->id,
                        'date' => $specificDate->toDateString(),
                        'start_time' => $workshop->start_time
                    ]);
                }

            } else {
                // --- LÓGICA PARA TALLER MENSUAL (RECURRENTE) ---
                $daysInMonth = $date->daysInMonth;
                
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $currentDay = Carbon::create($year, $month, $d);
                    
                    // Si el día de la semana coincide con el del taller (1=Lunes, 2=Martes...)
                    if ($currentDay->dayOfWeekIso == $workshop->repeat_day) {
                        ClassSession::firstOrCreate([
                            'workshop_id' => $workshop->id,
                            'date' => $currentDay->toDateString(),
                            'start_time' => $workshop->start_time
                        ]);
                    }
                }
            }
        }

        return redirect()->route('entrenamientos.show', $request->month_year)
                         ->with('success', 'Calendario generado exitosamente.');
    }

    public function show($monthId)
    {
        $monthDate = Carbon::parse($monthId . '-01');
        
        $sessions = ClassSession::with('workshop')
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->get();

        // Agrupamos por fecha para que la vista del calendario las dibuje fácilmente
        $sessionsByDate = $sessions->groupBy('date');

        return view('entrenamientos.show', compact('sessionsByDate', 'monthDate', 'monthId'));
    }

    public function destroyMonth($monthId)
    {
        $date = Carbon::parse($monthId . '-01');
        
        ClassSession::whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->delete();

        return redirect()->route('entrenamientos.index')->with('success', 'Mes eliminado correctamente.');
    }
}
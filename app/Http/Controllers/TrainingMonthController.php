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
        $months = ClassSession::selectRaw('strftime("%Y-%m", date) as month_id, MIN(date) as first_date')
            ->groupBy('month_id')
            ->orderBy('first_date', 'desc')
            ->get();

        // 1. SOLO enviamos los talleres MENSUALES al modal. Las únicas ya no estorban.
        $workshops = Workshop::where('is_single_class', false)->orderBy('name', 'asc')->get();

        return view('entrenamientos.index', compact('months', 'workshops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month_year' => 'required',
            'workshops' => 'required|array'
        ]);

        $date = \Carbon\Carbon::parse($request->month_year . '-01');
        $year = $date->year;
        $month = $date->month;

        $selectedWorkshops = \App\Models\Workshop::whereIn('id', $request->workshops)->get();

        foreach ($selectedWorkshops as $workshop) {
            $daysInMonth = $date->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                // Usar createFromDate previene errores de "horas arrastradas"
                $currentDay = \Carbon\Carbon::createFromDate($year, $month, $d);
                
                // CORRECCIÓN: Usamos dayOfWeek (0 a 6, donde 0 es Domingo).
                // Esto hace match perfecto con nuestro formulario de configuración.
                if ($currentDay->dayOfWeek == $workshop->repeat_day) {
                    \App\Models\ClassSession::firstOrCreate([
                        'workshop_id' => $workshop->id,
                        'date' => $currentDay->toDateString(),
                        'start_time' => $workshop->start_time
                    ]);
                }
            }
        }

        return redirect()->route('entrenamientos.show', $request->month_year)
                         ->with('success', 'Calendario mensual generado exitosamente.');
    }

    public function show($monthId)
    {
        $monthDate = \Carbon\Carbon::parse($monthId . '-01');
        
        $sessions = \App\Models\ClassSession::with('workshop')
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->get();

        // CORRECCIÓN: Agrupamos las sesiones forzando estrictamente el formato YYYY-MM-DD
        // Así evitamos que la clase del día 1 se esconda si la BD le añade horas.
        $sessionsByDate = $sessions->groupBy(function($session) {
            return \Carbon\Carbon::parse($session->date)->toDateString();
        });

        return view('entrenamientos.show', compact('sessionsByDate', 'monthDate', 'monthId'));
    }

    public function destroyMonth($monthId)
    {
        $date = Carbon::parse($monthId . '-01');
        
        // 3. PROTECCIÓN: Solo eliminamos las sesiones que pertenezcan a talleres mensuales.
        // Las clases únicas quedan intactas en el calendario.
        ClassSession::whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->whereHas('workshop', function($q) {
                $q->where('is_single_class', false);
            })
            ->delete();

        return redirect()->route('entrenamientos.index')->with('success', 'Mes eliminado correctamente. Las clases únicas se mantuvieron intactas.');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workshop;
use App\Models\ClassSession; // Agregamos esto

class WorkshopController extends Controller
{
    public function index()
    {
        $workshops = Workshop::orderBy('name', 'asc')->get();
        return view('classes.index', compact('workshops'));
    }

    public function store(Request $request)
    {
        $this->validateWorkshop($request);

        $data = $request->all();
        $data['is_single_class'] = $request->is_single_class == '1';

        if ($data['is_single_class']) {
            $data['repeat_day'] = null;
        } else {
            $data['specific_date'] = null;
        }

        $workshop = Workshop::create($data);

        // MAGIA AUTOMÁTICA: Si es clase única, creamos su sesión en el calendario al instante
        if ($workshop->is_single_class) {
            ClassSession::create([
                'workshop_id' => $workshop->id,
                'date' => $workshop->specific_date,
                'start_time' => $workshop->start_time
            ]);
        }

        return back()->with('success', 'Taller configurado correctamente.');
    }

    public function update(Request $request, Workshop $workshop)
    {
        $this->validateWorkshop($request);

        $data = $request->all();
        $data['is_single_class'] = $request->is_single_class == '1';

        if ($data['is_single_class']) {
            $data['repeat_day'] = null;
        } else {
            $data['specific_date'] = null;
        }

        $workshop->update($data);

        // MAGIA AUTOMÁTICA AL ACTUALIZAR
        if ($workshop->is_single_class) {
            // Si le cambió la fecha, actualizamos la sesión del calendario
            ClassSession::updateOrCreate(
                ['workshop_id' => $workshop->id],
                ['date' => $workshop->specific_date, 'start_time' => $workshop->start_time]
            );
        } else {
            // Si antes era única y la cambió a mensual, borramos su sesión suelta para evitar basura
            ClassSession::where('workshop_id', $workshop->id)->delete();
        }

        return back()->with('success', 'Taller actualizado.');
    }

    public function destroy(Workshop $workshop)
    {
        $workshop->delete();
        return back()->with('success', 'Taller eliminado.');
    }

    private function validateWorkshop(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'trainer' => 'nullable|string|max:255',
            'trainer_phone' => 'nullable|string|max:255',
            'start_time' => 'required',
            'payment_info' => 'nullable|string',
            'is_single_class' => 'required|in:0,1',
            'repeat_day' => 'required_if:is_single_class,0|nullable|integer|min:0|max:6',
            'specific_date' => 'required_if:is_single_class,1|nullable|date',
        ];

        $messages = [
            'name.required' => 'El nombre del taller es obligatorio.',
            'start_time.required' => 'Debes ingresar la hora de inicio.',
            'repeat_day.required_if' => 'Para un taller mensual, debes seleccionar un día.',
            'specific_date.required_if' => 'Para una clase única, debes seleccionar una fecha exacta.'
        ];

        $request->validate($rules, $messages);
    }
}
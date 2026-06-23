<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\Workshop;
use App\Models\WorkshopPrice;
use App\Models\Studio;


class PromotionController extends Controller
{
    public function index($subdomain)
    {
        // 1. Identificamos el estudio actual por el subdominio
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        // 2. Cargamos SOLO las promociones de este estudio
        $promotions = Promotion::where('studio_id', $studio->id)
                               ->with('workshopPrices.workshop')
                               ->get();
        
        // 3. Cargamos SOLO los talleres de este estudio
        $workshops = Workshop::where('studio_id', $studio->id)
                             ->with('prices')
                             ->orderBy('name', 'asc')
                             ->get();
        
        // 4. Extraemos los packs únicos creados SOLO en los talleres de este estudio
        $packTypes = WorkshopPrice::whereHas('workshop', function($q) use ($studio) {
                                        $q->where('studio_id', $studio->id);
                                    })
                                  ->select('class_count')
                                  ->distinct()
                                  ->orderBy('class_count', 'asc')
                                  ->pluck('class_count');
        
        return view('promotions.index', compact('promotions', 'workshops', 'packTypes'));
    }

    public function store(Request $request, $subdomain)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|in:specific_combo,additional_discount',
            
            // Reglas condicionales para Combo
            'total_price'        => 'required_if:type,specific_combo|nullable|numeric',
            'workshop_price_ids' => 'required_if:type,specific_combo|array',
            
            // Reglas condicionales para Taller Adicional
            'additional_price'   => 'required_if:type,additional_discount|nullable|numeric',
            'class_count'        => 'required_if:type,additional_discount|nullable|integer|min:1',
            
            // Nuevo campo booleano
            'is_monthly'         => 'nullable|boolean',
        ]);

        // Aseguramos que la promoción quede atada al estudio actual y capturamos el booleano
        $data = $request->only(['name', 'type', 'total_price', 'additional_price', 'class_count']);
        $data['studio_id'] = $studio->id; 
        $data['is_monthly'] = $request->boolean('is_monthly');

        $promotion = Promotion::create($data);

        if ($request->type === 'specific_combo') {
            $promotion->workshopPrices()->sync($request->workshop_price_ids);
        }

        return back()->with('success', 'Regla de promoción creada correctamente.');
    }

    /**
     * Actualiza una promoción existente en la base de datos.
     */
    public function update(Request $request, $subdomain, Promotion $promotion)
    {
        // 1. Validación estricta de los datos entrantes
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'type'                 => 'required|in:specific_combo,additional_discount',
            
            // Reglas condicionales para Combo
            'total_price'          => 'nullable|required_if:type,specific_combo|numeric|min:0',
            'workshop_price_ids'   => 'nullable|required_if:type,specific_combo|array',
            'workshop_price_ids.*' => 'exists:workshop_prices,id', // QA: Asegurar que el ID existe
            
            // Reglas condicionales para Taller Adicional
            'class_count'          => 'nullable|required_if:type,additional_discount|integer|min:1',
            'additional_price'     => 'nullable|required_if:type,additional_discount|numeric|min:0',
            
            // Nuevo campo booleano
            'is_monthly'           => 'nullable|boolean',
        ]);

        // Capturamos el booleano de forma segura
        $validated['is_monthly'] = $request->boolean('is_monthly');

        // 2. Limpieza de estado (Evitamos guardar datos cruzados si el usuario cambia de tipo de regla)
        if ($validated['type'] === 'specific_combo') {
            $validated['class_count'] = null;
            $validated['additional_price'] = null;
        } else {
            $validated['total_price'] = null;
        }

        // 3. Actualización de la entidad principal
        $promotion->update([
            'name'             => $validated['name'],
            'type'             => $validated['type'],
            'total_price'      => $validated['total_price'],
            'class_count'      => $validated['class_count'],
            'additional_price' => $validated['additional_price'],
            'is_monthly'       => $validated['is_monthly'],
        ]);

        // 4. Sincronización inteligente de la tabla pivote
        if ($validated['type'] === 'specific_combo' && !empty($validated['workshop_price_ids'])) {
            // sync() es magia: agrega los nuevos IDs, mantiene los que ya estaban, y elimina los desmarcados.
            $promotion->workshopPrices()->sync($validated['workshop_price_ids']);
        } else {
            // Si el usuario cambió la regla a "Descuento Adicional", destruimos las relaciones anteriores.
            $promotion->workshopPrices()->detach();
        }

        // 5. Redirección con mensaje de éxito
        return redirect()->route('promotions.index', ['subdomain' => $subdomain])
                         ->with('success', 'Regla de descuento actualizada correctamente.');
    }

    public function destroy($subdomain, Promotion $promotion)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        // Seguridad: Verificar que la promoción realmente pertenezca a este estudio antes de borrar
        if ($promotion->studio_id !== $studio->id) {
            abort(403, 'Acción no autorizada.');
        }

        $promotion->delete();
        return back()->with('success', 'Promoción eliminada.');
    }
}
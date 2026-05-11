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
            'name' => 'required|string|max:255',
            'type' => 'required|in:specific_combo,additional_discount',
            
            'total_price' => 'required_if:type,specific_combo|nullable|numeric',
            'workshop_price_ids' => 'required_if:type,specific_combo|array',
            
            'additional_price' => 'required_if:type,additional_discount|nullable|numeric',
            'class_count' => 'required_if:type,additional_discount|nullable|integer|min:1',
        ]);

        // Aseguramos que la promoción quede atada al estudio actual
        $data = $request->only(['name', 'type', 'total_price', 'additional_price', 'class_count']);
        $data['studio_id'] = $studio->id; 

        $promotion = Promotion::create($data);

        if ($request->type === 'specific_combo') {
            $promotion->workshopPrices()->sync($request->workshop_price_ids);
        }

        return back()->with('success', 'Regla de promoción creada correctamente.');
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
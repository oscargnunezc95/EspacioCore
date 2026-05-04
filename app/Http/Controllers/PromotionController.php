<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\Workshop;
use App\Models\WorkshopPrice;

class PromotionController extends Controller
{
    public function index($subdomain)
    {
        // 1. Cargamos las promociones existentes
        $promotions = Promotion::with('workshopPrices.workshop')->get();
        
        // 2. Cargamos los talleres y sus packs (Para combos específicos)
        $workshops = Workshop::with('prices')->orderBy('name', 'asc')->get();
        
        // 3. Extraemos de forma única (distinct) las cantidades de clases de los packs creados
        // Ej: devolverá [2, 4, 8, 12]
        $packTypes = WorkshopPrice::select('class_count')
                                  ->distinct()
                                  ->orderBy('class_count', 'asc')
                                  ->pluck('class_count');
        
        return view('promotions.index', compact('promotions', 'workshops', 'packTypes'));
    }

    public function store(Request $request, $subdomain)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:specific_combo,additional_discount',
            
            // Validaciones para Combo Específico
            'total_price' => 'required_if:type,specific_combo|nullable|numeric',
            'workshop_price_ids' => 'required_if:type,specific_combo|array',
            
            // Validaciones para Taller Adicional
            'additional_price' => 'required_if:type,additional_discount|nullable|numeric',
            'class_count' => 'required_if:type,additional_discount|nullable|integer|min:1',
        ]);

        $promotion = Promotion::create($request->only([
            'name', 
            'type', 
            'total_price', 
            'additional_price',
            'class_count'
        ]));

        if ($request->type === 'specific_combo') {
            $promotion->workshopPrices()->sync($request->workshop_price_ids);
        }

        return back()->with('success', 'Regla de promoción creada correctamente.');
    }

    public function destroy($subdomain, Promotion $promotion)
    {
        $promotion->delete();
        return back()->with('success', 'Promoción eliminada.');
    }
}
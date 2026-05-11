<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PricingService
{
    /**
     * Calcula el total y el desglose de un carrito para un estudio específico.
     */
    public function calculateCart(int $studioId, array $sessionIds): array
    {
        if (empty($sessionIds)) {
            return ['total' => 0, 'breakdown' => []];
        }

        // 1. Cargamos las sesiones (Ordenamos precios de mayor a menor para aplicar el mejor pack primero)
        $sessions = ClassSession::withoutGlobalScopes()
            ->with(['workshop.prices' => function($q) {
                $q->orderBy('class_count', 'desc');
            }])
            ->whereIn('id', $sessionIds)
            ->get();

        $total = 0;
        $breakdown = [];
        
        // Registro de cuántos packs de qué tipo está llevando el usuario (Útil para combos)
        // Estructura: [ workshop_price_id => cantidad_comprada ]
        $purchasedPacks = [];

        // =========================================================
        // 2. CÁLCULO DE PACKS POR TALLER (Matemática lineal)
        // =========================================================
        $groupedByWorkshop = $sessions->groupBy('workshop_id');

        foreach ($groupedByWorkshop as $workshopId => $workshopSessions) {
            $workshop = $workshopSessions->first()->workshop;
            $count = $workshopSessions->count();
            $remainingCount = $count;
            $workshopSubtotal = 0;
            $appliedBadges = [];

            // Evaluamos los packs de este taller en particular
            foreach ($workshop->prices as $priceTier) {
                if ($priceTier->class_count > 1 && $remainingCount >= $priceTier->class_count) {
                    $packsOfThisTier = intdiv($remainingCount, $priceTier->class_count);
                    
                    $workshopSubtotal += $packsOfThisTier * $priceTier->price;
                    $remainingCount %= $priceTier->class_count;

                    $appliedBadges[] = "Pack {$priceTier->class_count}x";
                    
                    // Registramos la compra de este pack para evaluarlo en las promociones globales
                    $purchasedPacks[$priceTier->id] = ($purchasedPacks[$priceTier->id] ?? 0) + $packsOfThisTier;
                }
            }

            // Calculamos las clases sueltas sobrantes
            if ($remainingCount > 0) {
                $dropInPrice = $workshop->prices->where('class_count', 1)->first()->price ?? 0;
                $workshopSubtotal += $remainingCount * $dropInPrice;
                
                // Opcional: Registrar la clase suelta si también aplica para combos
                $singlePriceId = $workshop->prices->where('class_count', 1)->first()->id ?? null;
                if ($singlePriceId) {
                    $purchasedPacks[$singlePriceId] = ($purchasedPacks[$singlePriceId] ?? 0) + $remainingCount;
                }
            }

            $total += $workshopSubtotal;

            $breakdown[] = [
                'name' => "{$count}x {$workshop->name}",
                'subtotal' => $workshopSubtotal,
                'badges' => $appliedBadges,
                'is_discount' => false // Bandera para la UI
            ];
        }

        // =========================================================
        // 3. CÁLCULO DE PROMOCIONES GLOBALES / COMBOS
        // =========================================================
        $promotions = Promotion::where('studio_id', $studioId)->with('workshopPrices')->get();

        foreach ($promotions as $promo) {
            
            // LÓGICA A: COMBO ESPECÍFICO (Ej: Pack 4 Yoga + Pack 4 Pilates)
            if ($promo->type === 'specific_combo') {
                $requiredPrices = $promo->workshopPrices->pluck('id')->toArray();
                
                // Verificamos si el carrito contiene TODOS los packs requeridos para este combo
                $hasAllRequired = true;
                foreach ($requiredPrices as $reqId) {
                    if (!isset($purchasedPacks[$reqId]) || $purchasedPacks[$reqId] < 1) {
                        $hasAllRequired = false;
                        break;
                    }
                }

                if ($hasAllRequired && !empty($requiredPrices)) {
                    // Calculamos cuánto costaban esos packs por separado originalmente
                    $originalComboCost = 0;
                    foreach ($promo->workshopPrices as $reqPrice) {
                        $originalComboCost += $reqPrice->price;
                    }

                    // El descuento es la diferencia entre el precio normal y el precio especial del combo
                    $discountAmount = $originalComboCost - $promo->total_price;

                    if ($discountAmount > 0) {
                        $total -= $discountAmount; // Restamos el descuento al total global
                        
                        // Agregamos la línea de descuento al desglose visual
                        $breakdown[] = [
                            'name' => "🌟 Combo: {$promo->name}",
                            'subtotal' => -$discountAmount,
                            'badges' => ['Promo Aplicada'],
                            'is_discount' => true
                        ];
                        
                        // Prevenimos que el mismo combo se aplique infinitas veces (Regla de negocio estándar)
                        // Restamos 1 uso de los packs registrados
                        foreach ($requiredPrices as $reqId) {
                            $purchasedPacks[$reqId]--;
                        }
                    }
                }
            }

            // LÓGICA B: DESCUENTO ADICIONAL POR VOLUMEN BRUTO (Independiente de los packs)
            if ($promo->type === 'additional_discount') {
                $totalClassesInCart = count($sessionIds);
                
                if ($totalClassesInCart >= $promo->class_count) {
                    // Restamos el descuento directo configurado
                    $total -= $promo->additional_price;
                    
                    $breakdown[] = [
                        'name' => "🔥 Descuento Volumen: {$promo->name}",
                        'subtotal' => -$promo->additional_price,
                        'badges' => ["+{$promo->class_count} clases"],
                        'is_discount' => true
                    ];
                }
            }
        }

        // Medida de seguridad: El total nunca puede ser negativo
        $total = max(0, $total);

        return [
            'total' => $total,
            'breakdown' => $breakdown
        ];
    }
}
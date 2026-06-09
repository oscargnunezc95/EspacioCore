<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calcula el total y el desglose de un carrito para un estudio específico.
     * Incorpora Delta Pricing (Upgrade retroactivo), validación de Alumno Nuevo,
     * agrupación dinámica (Mensual o Libre) y optimización absoluta de base de datos.
     */
    public function calculateCart(int $studioId, array $sessionIds, int $studentId = null): array
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
        
        // Registro de cuántos packs de qué tipo está llevando el usuario (Útil para combos globales)
        $purchasedPacks = [];

        // =========================================================================
        // OPTIMIZACIÓN DE RENDIMIENTO: Carga de historial en una sola consulta masiva
        // =========================================================================
        $historicalPayments = collect();
        if ($studentId) {
            $involvedWorkshopIds = $sessions->pluck('workshop_id')->unique()->toArray();
            
            // Traemos todos los cobros aprobados de este alumno para los talleres del carrito
            $historicalPayments = DB::table('class_session_student')
                ->join('class_sessions', 'class_session_student.class_session_id', '=', 'class_sessions.id')
                ->where('class_session_student.student_id', $studentId)
                ->where('class_session_student.payment_status', 'paid')
                ->whereIn('class_sessions.workshop_id', $involvedWorkshopIds)
                ->select('class_sessions.workshop_id', 'class_sessions.date')
                ->get();
        }

        // =========================================================================
        // 2. AGRUPACIÓN DINÁMICA (CORTE MENSUAL O PLAN LIBRE)
        // =========================================================================
        $groupedByWorkshop = $sessions->groupBy(function ($session) {
            $hasMonthlyPacks = $session->workshop->prices->where('is_monthly', true)->isNotEmpty();
            
            if ($hasMonthlyPacks) {
                // Es mensual: Agrupamos estrictamente por Año-Mes (Ej: "5-2026-06")
                return $session->workshop_id . '-' . Carbon::parse($session->date)->format('Y-m');
            } else {
                // Es libre/bolsa: Agrupamos todo el bloque de manera global
                return $session->workshop_id . '-global';
            }
        });

        foreach ($groupedByWorkshop as $groupKey => $workshopSessions) {
            $workshop = $workshopSessions->first()->workshop;
            $workshopId = $workshop->id;
            $cartCount = $workshopSessions->count();
            $appliedBadges = [];

            $hasMonthlyPacks = $workshop->prices->where('is_monthly', true)->isNotEmpty();
            
            if ($hasMonthlyPacks) {
                $firstDate = Carbon::parse($workshopSessions->first()->date);
                $periodName = ucfirst($firstDate->translatedFormat('F'));
            } else {
                $periodName = 'Plan Libre';
            }

            $pastCount = 0;
            $isEligibleForIntro = false;
            
            if ($studentId) {
                $firstDate = Carbon::parse($workshopSessions->first()->date);
                
                // Filtramos la colección cargada en memoria RAM para evitar consultas N+1
                $workshopHistory = $historicalPayments->where('workshop_id', $workshopId);
                
                if ($hasMonthlyPacks) {
                    // A. Clases ya pagadas dentro de este mismo mes calendario
                    $pastCount = $workshopHistory->filter(function($payment) use ($firstDate) {
                        $paymentDate = Carbon::parse($payment->date);
                        return $paymentDate->year === $firstDate->year && $paymentDate->month === $firstDate->month;
                    })->count();

                    // B. Clases pagadas históricamente antes de que iniciara este mes
                    $historicalPaid = $workshopHistory->filter(function($payment) use ($firstDate) {
                        return Carbon::parse($payment->date)->startOfDay()->lt($firstDate->copy()->startOfMonth());
                    })->count();
                } else {
                    // Si es plan libre, evaluamos el acumulado global de su vida útil
                    $pastCount = $workshopHistory->count();
                    $historicalPaid = $workshopHistory->count();
                }

                $isEligibleForIntro = ($historicalPaid === 0);
            }

            // Función anónima optimizada para calcular el costo en bruto (Evalúa Introductory Prices)
            $calculateRawPrice = function($count) use ($workshop, $isEligibleForIntro) {
                $price = 0;
                $rem = $count;
                
                foreach ($workshop->prices as $tier) {
                    if ($tier->class_count > 1 && $rem >= $tier->class_count) {
                        $packs = intdiv($rem, $tier->class_count);
                        
                        $tierPrice = $tier->price;
                        if ($isEligibleForIntro && $tier->is_introductory_active && $tier->introductory_price !== null) {
                            $tierPrice = $tier->introductory_price;
                        }
                        
                        $price += $packs * $tierPrice;
                        $rem %= $tier->class_count;
                    }
                }
                
                // Procesamiento de clases sueltas (Drop-in) remanentes
                if ($rem > 0) {
                    $dropInTier = $workshop->prices->where('class_count', 1)->first();
                    $dropInPrice = 0;
                    
                    if ($dropInTier) {
                        $dropInPrice = $dropInTier->price;
                        if ($isEligibleForIntro && $dropInTier->is_introductory_active && $dropInTier->introductory_price !== null) {
                            $dropInPrice = $dropInTier->introductory_price;
                        }
                    }
                    $price += $rem * $dropInPrice;
                }
                return $price;
            };

            $totalCount = $cartCount + $pastCount;

            // Delta Pricing: Calculamos la diferencia exacta a cobrar
            $priceForTotal = $calculateRawPrice($totalCount);
            $priceForPast = $calculateRawPrice($pastCount);

            $workshopSubtotal = max(0, $priceForTotal - $priceForPast);
            $total += $workshopSubtotal;

            // Registro de estructuras para Combos Globales
            $remCart = $cartCount;
            foreach ($workshop->prices as $tier) {
                if ($tier->class_count > 1 && $remCart >= $tier->class_count) {
                    $packs = intdiv($remCart, $tier->class_count);
                    
                    $purchasedPacks[$tier->id] = ($purchasedPacks[$tier->id] ?? 0) + $packs;
                    $remCart %= $tier->class_count;
                    
                    if ($pastCount == 0) {
                        $appliedBadges[] = "Pack {$tier->class_count}x";
                    }
                }
            }

            if ($remCart > 0) {
                $singlePriceId = $workshop->prices->where('class_count', 1)->first()->id ?? null;
                if ($singlePriceId) {
                    $purchasedPacks[$singlePriceId] = ($purchasedPacks[$singlePriceId] ?? 0) + $remCart;
                }
            }

            // Inyección de etiquetas informativas para la UI
            if ($isEligibleForIntro) {
                $appliedBadges[] = "Mes Introductorio";
            }
            if ($pastCount > 0 && $workshopSubtotal < $calculateRawPrice($cartCount)) {
                $appliedBadges[] = "Upgrade de Plan";
            }

            $breakdown[] = [
                'name' => "{$cartCount}x {$workshop->name} ({$periodName})",
                'subtotal' => $workshopSubtotal,
                'badges' => array_unique($appliedBadges),
                'is_discount' => false
            ];
        }

        // =========================================================================
        // 3. CÁLCULO DE PROMOCIONES GLOBALES (COMBOS MULTI-TALLER O VOLUMEN BRUTO)
        // =========================================================================
        $promotions = Promotion::where('studio_id', $studioId)->where('is_active', true)->with('workshopPrices')->get();

        foreach ($promotions as $promo) {
            
            // LÓGICA A: COMBOS ESPECÍFICOS (Ej: Pack 4 Vóley + Pack 4 Funcional)
            if ($promo->type === 'specific_combo') {
                $requiredPrices = $promo->workshopPrices->pluck('id')->toArray();
                
                $hasAllRequired = true;
                foreach ($requiredPrices as $reqId) {
                    if (!isset($purchasedPacks[$reqId]) || $purchasedPacks[$reqId] < 1) {
                        $hasAllRequired = false;
                        break;
                    }
                }

                if ($hasAllRequired && !empty($requiredPrices)) {
                    $originalComboCost = 0;
                    foreach ($promo->workshopPrices as $reqPrice) {
                        $originalComboCost += $reqPrice->price;
                    }

                    $discountAmount = $originalComboCost - $promo->total_price;

                    if ($discountAmount > 0) {
                        $total -= $discountAmount; 
                        
                        $breakdown[] = [
                            'name' => "🌟 Combo: {$promo->name}",
                            'subtotal' => -$discountAmount,
                            'badges' => ['Promo Aplicada'],
                            'is_discount' => true
                        ];
                        
                        // Consumimos los recursos del array para evitar duplicación del combo
                        foreach ($requiredPrices as $reqId) {
                            $purchasedPacks[$reqId]--;
                        }
                    }
                }
            }

            // LÓGICA B: DESCUENTOS DIRECTOS POR VOLUMEN BRUTO
            if ($promo->type === 'additional_discount') {
                $totalClassesInCart = count($sessionIds);
                
                if ($totalClassesInCart >= $promo->class_count) {
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

        // Red de seguridad contable invariable
        $total = max(0, $total);

        return [
            'total' => $total,
            'breakdown' => $breakdown
        ];
    }
}
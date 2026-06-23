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
     * y aislamiento temporal estricto (Mes Calendario) para Promociones.
     */
    public function calculateCart(int $studioId, array $sessionIds, int $studentId = null): array
    {
        if (empty($sessionIds)) {
            return ['total' => 0, 'breakdown' => []];
        }

        // =========================================================================
        // 1. CARGA BLINDADA DE SESIONES Y TALLERES
        // =========================================================================
        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => function($q) { 
                    $q->withoutGlobalScopes(); 
                },
                'workshop.prices' => function($q) {
                    $q->orderBy('class_count', 'desc');
                }
            ])
            ->whereIn('id', $sessionIds)
            ->get();

        $total = 0;
        $breakdown = [];
        
        // =========================================================================
        // 1.5. CONSTRUCCIÓN ESTRICTA DE BOLSAS MENSUALES PARA PROMOCIONES
        // =========================================================================
        $promoDataByPeriod = [];
        
        // Contamos las clases basándonos ÚNICAMENTE en su fecha real, ignorando cómo se configuren los precios.
        foreach ($sessions as $session) {
            $monthKey = Carbon::parse($session->date)->format('Y-m');
            if (!isset($promoDataByPeriod[$monthKey])) {
                $promoDataByPeriod[$monthKey] = ['classes_count' => 0, 'packs' => []];
            }
            $promoDataByPeriod[$monthKey]['classes_count']++;
        }

        $historicalPayments = collect();
        if ($studentId) {
            $involvedWorkshopIds = $sessions->pluck('workshop_id')->unique()->toArray();
            
            $historicalPayments = DB::table('class_session_student')
                ->join('class_sessions', 'class_session_student.class_session_id', '=', 'class_sessions.id')
                ->where('class_session_student.student_id', $studentId)
                ->where('class_session_student.payment_status', 'paid')
                ->whereIn('class_sessions.workshop_id', $involvedWorkshopIds)
                ->select('class_sessions.workshop_id', 'class_sessions.date')
                ->get();
        }

        // =========================================================================
        // 2. AGRUPACIÓN DINÁMICA Y CÁLCULO DE PRECIOS BASE
        // =========================================================================
        $groupedByWorkshop = $sessions->groupBy(function ($session) {
            $hasMonthlyPacks = $session->workshop->prices->where('is_monthly', true)->isNotEmpty();
            if ($hasMonthlyPacks) {
                return $session->workshop_id . '-' . Carbon::parse($session->date)->format('Y-m');
            } else {
                return $session->workshop_id . '-global';
            }
        });

        foreach ($groupedByWorkshop as $groupKey => $workshopSessions) {
            $workshop = $workshopSessions->first()->workshop;
            $workshopId = $workshop->id;
            $cartCount = $workshopSessions->count();
            $appliedBadges = [];

            $hasMonthlyPacks = $workshop->prices->where('is_monthly', true)->isNotEmpty();
            $firstDate = Carbon::parse($workshopSessions->first()->date);
            $monthKeyOfGroup = $firstDate->format('Y-m'); // Mes de la primera clase para asignar los packs
            
            if ($hasMonthlyPacks) {
                $periodName = ucfirst($firstDate->translatedFormat('F Y'));
            } else {
                $periodName = 'Plan Libre';
            }

            $pastCount = 0;
            $isEligibleForIntro = false;
            
            if ($studentId) {
                $workshopHistory = $historicalPayments->where('workshop_id', $workshopId);
                
                if ($hasMonthlyPacks) {
                    $pastCount = $workshopHistory->filter(function($payment) use ($firstDate) {
                        $paymentDate = Carbon::parse($payment->date);
                        return $paymentDate->year === $firstDate->year && $paymentDate->month === $firstDate->month;
                    })->count();

                    $historicalPaid = $workshopHistory->filter(function($payment) use ($firstDate) {
                        return Carbon::parse($payment->date)->startOfDay()->lt($firstDate->copy()->startOfMonth());
                    })->count();
                } else {
                    $pastCount = $workshopHistory->count();
                    $historicalPaid = $workshopHistory->count();
                }

                $isEligibleForIntro = ($historicalPaid === 0);
            }

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
            $priceForTotal = $calculateRawPrice($totalCount);
            $priceForPast = $calculateRawPrice($pastCount);

            $workshopSubtotal = max(0, $priceForTotal - $priceForPast);
            $total += $workshopSubtotal;

            // Inyectar los packs formados a la bolsa del mes correspondiente
            $remCart = $cartCount;
            foreach ($workshop->prices as $tier) {
                if ($tier->class_count > 1 && $remCart >= $tier->class_count) {
                    $packs = intdiv($remCart, $tier->class_count);
                    
                    $promoDataByPeriod[$monthKeyOfGroup]['packs'][$tier->id] = ($promoDataByPeriod[$monthKeyOfGroup]['packs'][$tier->id] ?? 0) + $packs;
                    $remCart %= $tier->class_count;
                    
                    if ($pastCount == 0) {
                        $appliedBadges[] = "Pack {$tier->class_count}x";
                    }
                }
            }

            if ($remCart > 0) {
                $singlePriceId = $workshop->prices->where('class_count', 1)->first()->id ?? null;
                if ($singlePriceId) {
                    $promoDataByPeriod[$monthKeyOfGroup]['packs'][$singlePriceId] = ($promoDataByPeriod[$monthKeyOfGroup]['packs'][$singlePriceId] ?? 0) + $remCart;
                }
            }

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
        // 3. CÁLCULO DE PROMOCIONES (DUAL: AISLADO POR MES O GLOBAL)
        // =========================================================================
        $promotions = Promotion::where('studio_id', $studioId)->where('is_active', true)->with('workshopPrices')->get();

        foreach ($promotions as $promo) {
            
            if ($promo->is_monthly) {
                // ---------------------------------------------------------
                // MODO RESTRINGIDO: Evalúa Bolsa por Bolsa (Estricto Mes Calendario)
                // ---------------------------------------------------------
                foreach ($promoDataByPeriod as $periodKey => &$periodData) {
                    $periodLabel = ucfirst(Carbon::createFromFormat('Y-m', $periodKey)->translatedFormat('F Y'));

                    if ($promo->type === 'specific_combo') {
                        $requiredPrices = $promo->workshopPrices->pluck('id')->toArray();
                        $hasAllRequired = true;
                        
                        foreach ($requiredPrices as $reqId) {
                            if (!isset($periodData['packs'][$reqId]) || $periodData['packs'][$reqId] < 1) {
                                $hasAllRequired = false;
                                break;
                            }
                        }

                        if ($hasAllRequired && !empty($requiredPrices)) {
                            $originalComboCost = $promo->workshopPrices->sum('price');
                            $discountAmount = $originalComboCost - $promo->total_price;

                            if ($discountAmount > 0) {
                                $total -= $discountAmount; 
                                $breakdown[] = [
                                    'name' => "🌟 Combo Mensual: {$promo->name} ({$periodLabel})",
                                    'subtotal' => -$discountAmount,
                                    'badges' => ['Promo Aplicada'],
                                    'is_discount' => true
                                ];
                                foreach ($requiredPrices as $reqId) {
                                    $periodData['packs'][$reqId]--;
                                }
                            }
                        }
                    }

                    if ($promo->type === 'additional_discount') {
                        if ($periodData['classes_count'] >= $promo->class_count) {
                            $total -= $promo->additional_price;
                            $breakdown[] = [
                                'name' => "🔥 Descuento Mensual: {$promo->name} ({$periodLabel})",
                                'subtotal' => -$promo->additional_price,
                                'badges' => ["+{$promo->class_count} clases"],
                                'is_discount' => true
                            ];
                            $periodData['classes_count'] = 0; 
                        }
                    }
                }
                unset($periodData);

            } else {
                // ---------------------------------------------------------
                // MODO GLOBAL: Agrupa todas las bolsas mensuales y evalúa el total
                // ---------------------------------------------------------
                $globalClasses = 0;
                $globalPacks = [];
                
                foreach ($promoDataByPeriod as $periodData) {
                    $globalClasses += $periodData['classes_count'];
                    foreach ($periodData['packs'] as $id => $qty) {
                        $globalPacks[$id] = ($globalPacks[$id] ?? 0) + $qty;
                    }
                }

                if ($promo->type === 'specific_combo') {
                    $requiredPrices = $promo->workshopPrices->pluck('id')->toArray();
                    $hasAllRequired = true;
                    
                    foreach ($requiredPrices as $reqId) {
                        if (!isset($globalPacks[$reqId]) || $globalPacks[$reqId] < 1) {
                            $hasAllRequired = false;
                            break;
                        }
                    }

                    if ($hasAllRequired && !empty($requiredPrices)) {
                        $originalComboCost = $promo->workshopPrices->sum('price');
                        $discountAmount = $originalComboCost - $promo->total_price;

                        if ($discountAmount > 0) {
                            $total -= $discountAmount; 
                            $breakdown[] = [
                                'name' => "🌟 Combo Global: {$promo->name}",
                                'subtotal' => -$discountAmount,
                                'badges' => ['Promo Aplicada'],
                                'is_discount' => true
                            ];
                            
                            foreach ($requiredPrices as $reqId) {
                                foreach ($promoDataByPeriod as $periodKey => &$periodData) {
                                    if (($periodData['packs'][$reqId] ?? 0) > 0) {
                                        $periodData['packs'][$reqId]--;
                                        break; 
                                    }
                                }
                                unset($periodData);
                            }
                        }
                    }
                }

                if ($promo->type === 'additional_discount') {
                    if ($globalClasses >= $promo->class_count) {
                        $total -= $promo->additional_price;
                        $breakdown[] = [
                            'name' => "🔥 Descuento Global: {$promo->name}",
                            'subtotal' => -$promo->additional_price,
                            'badges' => ["+{$promo->class_count} clases"],
                            'is_discount' => true
                        ];
                        
                        $classesToConsume = $promo->class_count;
                        foreach ($promoDataByPeriod as $periodKey => &$periodData) {
                            if ($periodData['classes_count'] > 0) {
                                if ($periodData['classes_count'] >= $classesToConsume) {
                                    $periodData['classes_count'] -= $classesToConsume;
                                    break;
                                } else {
                                    $classesToConsume -= $periodData['classes_count'];
                                    $periodData['classes_count'] = 0;
                                }
                            }
                        }
                        unset($periodData);
                    }
                }
            }
        }

        $total = max(0, $total);

        return [
            'total' => $total,
            'breakdown' => $breakdown
        ];
    }
}
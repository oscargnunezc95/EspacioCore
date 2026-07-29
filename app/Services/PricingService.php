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
     *
     * ARQUITECTURA: "Bolsas de Tiempo con Ventana Deslizante, Anclaje Inverso y Desglose Enriquecido".
     */
    public function calculateCart(int $studioId, array $sessionIds, ?int $studentId = null): array
    {
        if (empty($sessionIds)) {
            return ['total' => 0, 'breakdown' => []];
        }

        // =========================================================================
        // 1. CARGA BLINDADA DE SESIONES Y TALLERES
        // =========================================================================
        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => function ($q) {
                    $q->withoutGlobalScopes();
                },
                'workshop.prices' => function ($q) {
                    $q->orderBy('class_count', 'desc');
                },
            ])
            ->whereIn('id', $sessionIds)
            ->get();

        $total = 0;
        $breakdown = [];

        // =========================================================================
        // 1.5. CONSTRUCCIÓN DE BOLSAS POR WORKSHOP PARA PROMOCIONES
        // =========================================================================
        $promoDataByWorkshop = [];

        foreach ($sessions as $session) {
            $wsKey = $session->workshop_id;
            if (!isset($promoDataByWorkshop[$wsKey])) {
                $promoDataByWorkshop[$wsKey] = ['classes_count' => 0, 'packs' => []];
            }
            $promoDataByWorkshop[$wsKey]['classes_count']++;
        }

        // Carga de pagos históricos (incluyendo start_time para el desglose UI)
        $historicalPayments = collect();
        if ($studentId) {
            $involvedWorkshopIds = $sessions->pluck('workshop_id')->unique()->toArray();

            $historicalPayments = DB::table('class_session_student')
                ->join('class_sessions', 'class_session_student.class_session_id', '=', 'class_sessions.id')
                ->where('class_session_student.student_id', $studentId)
                ->where('class_session_student.payment_status', 'paid')
                ->whereIn('class_sessions.workshop_id', $involvedWorkshopIds)
                ->select('class_sessions.workshop_id', 'class_sessions.date', 'class_sessions.start_time')
                ->get();
        }

        // =========================================================================
        // 2. AGRUPACIÓN POR WORKSHOP
        // =========================================================================
        $groupedByWorkshop = $sessions->groupBy('workshop_id');

        foreach ($groupedByWorkshop as $workshopId => $workshopSessions) {
            $workshop = $workshopSessions->first()->workshop;
            $cartCount = $workshopSessions->count();
            $appliedBadges = [];

            // -----------------------------------------------------------
            // 2.1 ANCLAJE INVERSO: Ordenar DESCENDENTE (futuro → pasado)
            // -----------------------------------------------------------
            $sortedSessions = $workshopSessions->sortByDesc('date')->values();
            $anchorDate = Carbon::parse($sortedSessions->first()->date);   // más futura
            $oldestInCart = Carbon::parse($sortedSessions->last()->date);  // más antigua

            // -----------------------------------------------------------
            // 2.2 FILTRAR TIERS POR VENTANA DE VIGENCIA (hacia atrás)
            // -----------------------------------------------------------
            $validTiers = $workshop->prices->filter(function ($tier) use ($anchorDate, $oldestInCart) {
                $months = (int) $tier->validity_months;

                if ($months === 0) {
                    return true;
                }

                $minDate = $this->calculateMinDate(
                    $anchorDate,
                    $months,
                    $tier->validity_type
                );

                return $oldestInCart->gte($minDate);
            });

            // -----------------------------------------------------------
            // 2.3 RETROACTIVIDAD DINÁMICA POR TALLER Y CAPTURA DE SESIONES
            // -----------------------------------------------------------
            $pastSessions = collect();
            $pastCount = 0;
            $isEligibleForIntro = false;
            $activeTier = null;

            if ($studentId) {
                $workshopHistory = $historicalPayments->where('workshop_id', $workshopId);

                $maxMonths = $validTiers->max('validity_months') ?? 1;
                $maxType = $validTiers->first()->validity_type ?? 'calendar';

                if ($maxMonths > 0) {
                    $minDate = $this->calculateMinDate($anchorDate, (int) $maxMonths, $maxType);
                    $pastSessions = $workshopHistory->filter(function ($payment) use ($minDate, $anchorDate) {
                        $paymentDate = Carbon::parse($payment->date);
                        return $paymentDate->gte($minDate) && $paymentDate->lt($anchorDate);
                    });
                } else {
                    $pastSessions = $workshopHistory; // Vitalicio
                }

                $pastCount = $pastSessions->count();
                $isEligibleForIntro = $workshopHistory->isEmpty();
            }

            // -----------------------------------------------------------
            // DETERMINAR TIER PRINCIPAL ALCANZADO (Usando volumen TOTAL)
            // -----------------------------------------------------------
            $totalCount = $cartCount + $pastCount;

            $activeTier = $validTiers->first(function ($tier) use ($totalCount) {
                return $tier->class_count > 1 && $totalCount >= $tier->class_count;
            }) ?? $validTiers->where('class_count', 1)->first() ?? $validTiers->first();

            // -----------------------------------------------------------
            // 2.4 CÁLCULO DE PRECIOS BLINDADO (Cortafuegos Retroactivo)
            // -----------------------------------------------------------
            $calculateRawPrice = function ($count, bool $isUsingHistory = false) use ($validTiers, $isEligibleForIntro) {
                $price = 0;
                $rem = $count;

                foreach ($validTiers as $tier) {
                    if ($tier->class_count > 1 && $rem >= $tier->class_count) {
                        // Cortafuegos: Si usamos historial pero el Pack tiene desmarcado "Upgrade Retroactivo", se ignora.
                        if ($isUsingHistory && !$tier->allows_retroactive) {
                            continue;
                        }

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
                    $dropInTier = $validTiers->where('class_count', 1)->first();
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

            $priceForTotal = $calculateRawPrice($totalCount, true);
            $priceForPast = $calculateRawPrice($pastCount, false);

            // Clamping financiero a $0
            $workshopSubtotal = max(0, $priceForTotal - $priceForPast);
            $total += $workshopSubtotal;

            // -----------------------------------------------------------
            // 2.5 BADGES
            // -----------------------------------------------------------
            $rawCartPrice = $calculateRawPrice($cartCount, false);

            if ($pastCount > 0 && $workshopSubtotal === 0) {
                $appliedBadges[] = 'Upgrade 100% Cubierto';
            } elseif ($pastCount > 0 && $workshopSubtotal > 0 && $workshopSubtotal < $rawCartPrice) {
                $appliedBadges[] = 'Upgrade de Plan';
            }

            if ($isEligibleForIntro) {
                $appliedBadges[] = 'Mes Introductorio';
            }

            // -----------------------------------------------------------
            // 2.6 INYECTAR PACKS A LA BOLSA DEL WORKSHOP (para promociones)
            // -----------------------------------------------------------
            // Evaluamos los packs alcanzados con el volumen TOTAL ($totalCount)
            // para que las promociones y combos reconozcan los upgrades retroactivos.
            $remPacks = $totalCount;
            foreach ($validTiers as $tier) {
                if ($tier->class_count > 1 && $remPacks >= $tier->class_count) {
                    if ($pastCount > 0 && !$tier->allows_retroactive) {
                        continue;
                    }

                    $packs = intdiv($remPacks, $tier->class_count);

                    $promoDataByWorkshop[$workshopId]['packs'][$tier->id] =
                        ($promoDataByWorkshop[$workshopId]['packs'][$tier->id] ?? 0) + $packs;
                    $remPacks %= $tier->class_count;

                    $appliedBadges[] = "Pack {$tier->class_count}x";
                }
            }

            if ($remPacks > 0) {
                $singlePriceId = $validTiers->where('class_count', 1)->first()->id ?? null;
                if ($singlePriceId) {
                    $promoDataByWorkshop[$workshopId]['packs'][$singlePriceId] =
                        ($promoDataByWorkshop[$workshopId]['packs'][$singlePriceId] ?? 0) + $remPacks;
                }
            }

            // -----------------------------------------------------------
            // 2.7 CONSTRUCCIÓN DE ITEMS ENRIQUECIDOS PARA LA UI
            // -----------------------------------------------------------
            $sessionItems = [];

            // A) Sesiones en el carrito (por pagar ahora)
            foreach ($sortedSessions as $sess) {
                $sessionItems[] = [
                    'date_formatted' => ucfirst(Carbon::parse($sess->date)->translatedFormat('l d M, Y')),
                    'time_formatted' => Carbon::parse($sess->start_time)->format('H:i') . ' hrs',
                    'is_paid_history' => false,
                    'label' => 'Reserva en carrito'
                ];
            }

            // B) Sesiones históricas (ya pagadas y aplicadas al saldo retroactivo)
            if ($pastCount > 0) {
                foreach ($pastSessions as $pastSess) {
                    $timeStr = isset($pastSess->start_time) ? Carbon::parse($pastSess->start_time)->format('H:i') . ' hrs' : 'Histórico';
                    $sessionItems[] = [
                        'date_formatted' => ucfirst(Carbon::parse($pastSess->date)->translatedFormat('l d M, Y')),
                        'time_formatted' => $timeStr,
                        'is_paid_history' => true,
                        'label' => 'Clase ya pagada (considerada en pack)'
                    ];
                }
            }

            // Nombre descriptivo del paquete real alcanzado ($activeTier)
            $itemName = ($activeTier && $activeTier->class_count > 1)
                ? "Pack {$activeTier->class_count} Clases: {$workshop->name}"
                : "{$cartCount}x Clase Suelta: {$workshop->name}";

            $breakdown[] = [
                'name' => $itemName,
                'subtotal' => $workshopSubtotal,
                'badges' => array_values(array_unique($appliedBadges)),
                'is_discount' => false,
                'items' => $sessionItems,
            ];
        }

        // =========================================================================
        // 3. CÁLCULO DE PROMOCIONES (EVALUACIÓN GLOBAL CON FILTRO TEMPORAL)
        // =========================================================================
        $promotions = Promotion::where('studio_id', $studioId)
            ->where('is_active', true)
            ->with('workshopPrices')
            ->get();

        foreach ($promotions as $promo) {
            // 3.1 CONSTRUIR POOL GLOBAL
            $globalClasses = 0;
            $globalPacks = [];

            foreach ($promoDataByWorkshop as $wsData) {
                $globalClasses += $wsData['classes_count'];
                foreach ($wsData['packs'] as $id => $qty) {
                    $globalPacks[$id] = ($globalPacks[$id] ?? 0) + $qty;
                }
            }

            // 3.2 FILTRO DE INTERSECCIÓN TEMPORAL
            if ($promo->type === 'specific_combo') {
                $promoWorkshopIds = $promo->workshopPrices
                    ->pluck('workshop_id')
                    ->unique()
                    ->toArray();
            } else {
                $promoWorkshopIds = $sessions->pluck('workshop_id')->unique()->toArray();
            }

            $relevantSessions = $sessions->whereIn('workshop_id', $promoWorkshopIds);

            if ($relevantSessions->isNotEmpty() && $promo->validity_months !== 0) {
                $maxDate = Carbon::parse($relevantSessions->max('date'));
                $minDate = Carbon::parse($relevantSessions->min('date'));

                if ($promo->validity_type === 'calendar') {
                    $limit = $maxDate->copy()
                        ->subMonthsNoOverflow($promo->validity_months - 1)
                        ->startOfMonth();

                    if ($minDate->lt($limit)) {
                        continue;
                    }
                } else {
                    $limit = $maxDate->copy()
                        ->subMonthsNoOverflow($promo->validity_months);

                    if ($minDate->lt($limit)) {
                        continue;
                    }
                }
            }

            // 3.3 CONTROL DE RETROACTIVIDAD
            if (!$promo->allows_retroactive) {
                $hasHistoricalForPromo = $historicalPayments
                    ->whereIn('workshop_id', $promoWorkshopIds)
                    ->isNotEmpty();

                if ($hasHistoricalForPromo) {
                    continue;
                }
            }

            // 3.4 APLICAR PROMOCIÓN (pool global)
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
                            'name' => "🌟 Combo: {$promo->name}",
                            'subtotal' => -$discountAmount,
                            'badges' => ['Promo Aplicada'],
                            'is_discount' => true,
                            'items' => [],
                        ];

                        foreach ($requiredPrices as $reqId) {
                            foreach ($promoDataByWorkshop as $wsKey => &$wsData) {
                                if (($wsData['packs'][$reqId] ?? 0) > 0) {
                                    $wsData['packs'][$reqId]--;
                                    break;
                                }
                            }
                            unset($wsData);
                        }
                    }
                }
            }

            if ($promo->type === 'additional_discount') {
                if ($globalClasses >= $promo->class_count) {
                    $total -= $promo->additional_price;
                    $breakdown[] = [
                        'name' => "🔥 Descuento: {$promo->name}",
                        'subtotal' => -$promo->additional_price,
                        'badges' => ["+{$promo->class_count} clases"],
                        'is_discount' => true,
                        'items' => [],
                    ];

                    $classesToConsume = $promo->class_count;
                    foreach ($promoDataByWorkshop as $wsKey => &$wsData) {
                        if ($wsData['classes_count'] > 0) {
                            if ($wsData['classes_count'] >= $classesToConsume) {
                                $wsData['classes_count'] -= $classesToConsume;
                                break;
                            } else {
                                $classesToConsume -= $wsData['classes_count'];
                                $wsData['classes_count'] = 0;
                            }
                        }
                    }
                    unset($wsData);
                }
            }
        }

        $total = max(0, $total);

        return [
            'total' => $total,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calcula la fecha límite inferior ($minDate) de la ventana de vigencia.
     */
    private function calculateMinDate(Carbon $anchorDate, int $months, ?string $type = 'calendar'): Carbon
    {
        $type = $type ?? 'calendar';

        if ($type === 'calendar') {
            return $anchorDate->copy()
                ->subMonthsNoOverflow($months - 1)
                ->startOfMonth();
        }

        return $anchorDate->copy()->subMonthsNoOverflow($months);
    }
}
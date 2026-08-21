<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Promotion;
use App\Models\WorkshopPrice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PricingService
{
    public function calculateCart(int $studioId, array $sessionIds, ?int $studentId = null): array
    {
        if (empty($sessionIds)) {
            return ['total' => 0, 'breakdown' => []];
        }

        // =========================================================================
        // 0. VENTANA DINÁMICA DE MEMORIA Y CARGA DE DATOS
        // =========================================================================
        $maxWorkshopMonths = DB::table('workshop_prices')
            ->join('workshops', 'workshops.id', '=', 'workshop_prices.workshop_id')
            ->where('workshops.studio_id', $studioId)
            ->max('workshop_prices.validity_months') ?? 1;

        $maxPromoMonths = DB::table('promotions')
            ->where('studio_id', $studioId)
            ->where('is_active', 1)
            ->max('validity_months') ?? 1;

        $globalLookbackWindow = max((int)$maxWorkshopMonths, (int)$maxPromoMonths, 1) * 2;

        $sessions = ClassSession::withoutGlobalScopes()
            ->with(['workshop' => fn($q) => $q->withoutGlobalScopes(), 'workshop.prices' => fn($q) => $q->orderBy('class_count', 'desc')])
            ->whereIn('id', $sessionIds)
            ->get();

        $historicalPayments = collect();
        if ($studentId) {
            $historicalPayments = ClassSession::withoutGlobalScopes()
                ->join('class_session_student', 'class_session_student.class_session_id', '=', 'class_sessions.id')
                ->where('class_session_student.student_id', $studentId)
                ->where('class_session_student.payment_status', 'paid')
                ->where('class_sessions.date', '>=', Carbon::now()->subMonthsNoOverflow($globalLookbackWindow)->startOfMonth())
                ->with(['workshop' => fn($q) => $q->withoutGlobalScopes(), 'workshop.prices' => fn($q) => $q->orderBy('class_count', 'desc')])
                ->select('class_sessions.id', 'class_sessions.workshop_id', 'class_sessions.date', 'class_sessions.start_time', 'class_session_student.workshop_price_id')
                ->get();
        }

        // 🛡️ ESCUDO DETERMINISTA GLOBAL: Solo clases históricas "sueltas" son útiles para upgrades
        $usableHistoricalPayments = collect();
        foreach ($historicalPayments as $pastSess) {
            $tier = WorkshopPrice::find($pastSess->workshop_price_id);
            if (!$tier || $tier->class_count === 1) {
                $usableHistoricalPayments->push($pastSess);
            }
        }

        $total = 0;
        $finalBreakdown = [];

        // =========================================================================
        // FASE 1: ORQUESTADOR UNIFICADO DE PROMOCIONES (VOLUMEN SOBRE VARIEDAD)
        // =========================================================================
        $promotions = Promotion::where('studio_id', $studioId)
            ->where('is_active', true)
            ->with('workshopPrices.workshop')
            ->get();

        // ⚖️ CÁLCULO DE PESO: Ordenamos las promociones de mayor a menor cantidad de clases requeridas
        $promosSorted = $promotions->map(function($p) {
            $p->weight = $p->type === 'specific_combo' 
                ? $p->workshopPrices->sum('class_count') 
                : $p->class_count * 2; // Taller adicional exige mínimo 2 talleres (ej. 4 base + 4 extra = 8)
            return $p;
        })->sortByDesc('weight')->values();

        foreach ($promosSorted as $promo) {
            while (true) {
                $appliedThisIteration = false;

                // -----------------------------------------------------------------
                // LÓGICA A: COMBO ESPECÍFICO
                // -----------------------------------------------------------------
                if ($promo->type === 'specific_combo') {
                    $reqMap = [];
                    $promoWorkshopIds = [];
                    foreach ($promo->workshopPrices as $tier) {
                        $reqMap[$tier->workshop_id] = ($reqMap[$tier->workshop_id] ?? 0) + $tier->class_count;
                        $promoWorkshopIds[] = $tier->workshop_id;
                    }
                    $promoWorkshopIds = array_unique($promoWorkshopIds);

                    if ($sessions->whereIn('workshop_id', $promoWorkshopIds)->isEmpty()) break;

                    $presentCandidates = $sessions->whereIn('workshop_id', $promoWorkshopIds)->sortBy('date');
                    $pastCandidates = $promo->allows_retroactive ? $usableHistoricalPayments->whereIn('workshop_id', $promoWorkshopIds)->sortBy('date') : collect();

                    $cartAnchor = $presentCandidates->max('date');
                    if (!$cartAnchor) break;

                    $limit = null;
                    if ($promo->validity_months > 0) {
                        $limit = $promo->validity_type === 'calendar'
                            ? Carbon::parse($cartAnchor)->subMonthsNoOverflow($promo->validity_months - 1)->startOfMonth()
                            : Carbon::parse($cartAnchor)->subMonthsNoOverflow($promo->validity_months);
                    }

                    $filterFn = fn($s) => $limit ? Carbon::parse($s->date)->gte($limit) : true;
                    $validPresent = $presentCandidates->filter($filterFn);
                    $validPast = $pastCandidates->filter($filterFn);

                    $hasEnough = true;
                    $extractedPresent = collect();
                    $extractedPast = collect();

                    foreach ($reqMap as $wId => $needed) {
                        $wPres = $validPresent->where('workshop_id', $wId)->values();
                        $wPast = $validPast->where('workshop_id', $wId)->values();

                        if ($wPres->count() + $wPast->count() < $needed) {
                            $hasEnough = false; break;
                        }

                        $takePres = min($wPres->count(), $needed);
                        $extractedPresent = $extractedPresent->merge($wPres->take($takePres));
                        
                        $remainingNeeded = $needed - $takePres;
                        if ($remainingNeeded > 0) {
                            $extractedPast = $extractedPast->merge($wPast->take($remainingNeeded));
                        }
                    }

                    if ($hasEnough) {
                        $extPresIds = $extractedPresent->pluck('id')->toArray();
                        $extPastIds = $extractedPast->pluck('id')->toArray();
                        $sessions = $sessions->reject(fn($s) => in_array($s->id, $extPresIds));
                        $usableHistoricalPayments = $usableHistoricalPayments->reject(fn($s) => in_array($s->id, $extPastIds));

                        $pastValue = 0;
                        foreach ($extractedPast as $ep) {
                            $tier = WorkshopPrice::where('workshop_id', $ep->workshop_id)->where('class_count', 1)->first();
                            $pastValue += $tier ? $tier->price : 0;
                        }

                        $comboSubtotal = max(0, $promo->total_price - $pastValue);
                        $total += $comboSubtotal;

                        $groupedComboItems = [];
                        foreach ($extractedPresent->merge($extractedPast)->sortBy('date') as $sess) {
                            $wsName = \App\Models\Workshop::find($sess->workshop_id)->name ?? 'Taller';
                            $isPast = in_array($sess->id, $extPastIds);
                            $groupedComboItems["Clases de {$wsName}"][] = [
                                'id' => $sess->id,
                                'date_formatted' => ucfirst(Carbon::parse($sess->date)->translatedFormat('l d M., Y')),
                                'time_formatted' => Carbon::parse($sess->start_time)->format('H:i') . ' hrs',
                                'is_paid_history' => $isPast,
                                'label' => $isPast ? '✓ CLASE YA PAGADA' : 'A PAGAR'
                            ];
                        }

                        $finalBreakdown[] = [
                            'tier_id' => 'promo_' . $promo->id,
                            'name' => "🌟 Combo: {$promo->name}",
                            'subtotal' => $comboSubtotal,
                            'badges' => ['Promo Aplicada'],
                            'is_discount' => false,
                            'is_hidden' => $extractedPresent->isEmpty(),
                            'grouped_items' => $groupedComboItems
                        ];

                        $appliedThisIteration = true;
                    }
                }
                
                // -----------------------------------------------------------------
                // LÓGICA B: TALLER ADICIONAL
                // -----------------------------------------------------------------
                elseif ($promo->type === 'additional_discount') {
                    $baseCount = (int) $promo->class_count;
                    if ($baseCount <= 0) break;

                    $allWorkshopIds = $sessions->pluck('workshop_id')->merge($usableHistoricalPayments->pluck('workshop_id'))->unique();
                    $qualifyingWorkshops = [];
                    $cartAnchor = $sessions->max('date');

                    foreach ($allWorkshopIds as $wId) {
                        $wPres = $sessions->where('workshop_id', $wId)->sortBy('date')->values();
                        $wPast = $promo->allows_retroactive ? $usableHistoricalPayments->where('workshop_id', $wId)->sortBy('date')->values() : collect();

                        $merged = $wPres->merge($wPast)->sortBy('date')->values();
                        
                        if ($merged->count() >= $baseCount) {
                            $takePres = min($wPres->count(), $baseCount);
                            $chunk = $wPres->take($takePres)->merge($wPast->take($baseCount - $takePres))->sortBy('date')->values();

                            $limit = null;
                            if ($promo->validity_months > 0 && $cartAnchor) {
                                $limit = $promo->validity_type === 'calendar'
                                    ? Carbon::parse($cartAnchor)->subMonthsNoOverflow($promo->validity_months - 1)->startOfMonth()
                                    : Carbon::parse($cartAnchor)->subMonthsNoOverflow($promo->validity_months);
                            }

                            if (!$limit || Carbon::parse($chunk->min('date'))->gte($limit)) {
                                $tier = WorkshopPrice::where('workshop_id', $wId)->where('class_count', $baseCount)->first();
                                if ($tier) {
                                    $qualifyingWorkshops[] = [
                                        'workshop_id' => $wId,
                                        'sessions' => $chunk,
                                        'standard_price' => $tier->price,
                                        'has_present' => $takePres > 0
                                    ];
                                }
                            }
                        }
                    }

                    // Se exigen al menos 2 talleres, y al menos 1 debe venir del carrito actual
                    if (count($qualifyingWorkshops) >= 2 && collect($qualifyingWorkshops)->contains('has_present', true)) {
                        usort($qualifyingWorkshops, fn($a, $b) => $b['standard_price'] <=> $a['standard_price']);

                        $baseWs = array_shift($qualifyingWorkshops);
                        $totalPromoPrice = $baseWs['standard_price'];
                        $extractedSessions = $baseWs['sessions'];
                        $groupedComboItems = [];
                        $extPastIds = [];

                        $addPackToGroup = function($wsName, $chunk, $labelPrefix) use (&$groupedComboItems, &$extPastIds, $usableHistoricalPayments) {
                            foreach($chunk as $sess) {
                                $isPast = $usableHistoricalPayments->contains('id', $sess->id);
                                if($isPast) $extPastIds[] = $sess->id;
                                $groupedComboItems["Pack {$chunk->count()}: {$wsName}"][] = [
                                    'id' => $sess->id,
                                    'date_formatted' => ucfirst(Carbon::parse($sess->date)->translatedFormat('l d M., Y')),
                                    'time_formatted' => Carbon::parse($sess->start_time)->format('H:i') . ' hrs',
                                    'is_paid_history' => $isPast,
                                    'label' => $isPast ? '✓ CLASE YA PAGADA' : $labelPrefix
                                ];
                            }
                        };

                        $addPackToGroup(\App\Models\Workshop::find($baseWs['workshop_id'])->name ?? 'Taller', $baseWs['sessions'], 'BASE');

                        foreach ($qualifyingWorkshops as $extraWs) {
                            $totalPromoPrice += ($extraWs['standard_price'] > $promo->additional_price) ? $promo->additional_price : $extraWs['standard_price'];
                            $extractedSessions = $extractedSessions->merge($extraWs['sessions']);
                            $addPackToGroup(\App\Models\Workshop::find($extraWs['workshop_id'])->name ?? 'Taller', $extraWs['sessions'], 'EXTRA');
                        }

                        $extPresIds = $extractedSessions->pluck('id')->diff($extPastIds)->toArray();
                        $sessions = $sessions->reject(fn($s) => in_array($s->id, $extPresIds));
                        $usableHistoricalPayments = $usableHistoricalPayments->reject(fn($s) => in_array($s->id, $extPastIds));

                        $pastValue = 0;
                        foreach ($extractedSessions->filter(fn($s) => in_array($s->id, $extPastIds)) as $ep) {
                            $tier = WorkshopPrice::where('workshop_id', $ep->workshop_id)->where('class_count', 1)->first();
                            $pastValue += $tier ? $tier->price : 0;
                        }

                        $comboSubtotal = max(0, $totalPromoPrice - $pastValue);
                        $total += $comboSubtotal;

                        $finalBreakdown[] = [
                            'tier_id' => 'promo_' . $promo->id,
                            'name' => "🔥 Descuento: {$promo->name}",
                            'subtotal' => $comboSubtotal,
                            'badges' => ["+" . count($qualifyingWorkshops) . " taller(es) extra"],
                            'is_discount' => false,
                            'is_hidden' => false,
                            'grouped_items' => $groupedComboItems
                        ];

                        $appliedThisIteration = true;
                    }
                }

                if (!$appliedThisIteration) break;
            }
        }

        // =========================================================================
        // FASE 2: EMPAQUETAMIENTO VORAZ (Para las clases que sobraron de los Combos)
        // =========================================================================
        $allSessionsForGrouping = $sessions->concat($usableHistoricalPayments)->unique('id');

        foreach ($allSessionsForGrouping->groupBy('workshop_id') as $workshopId => $groupSessions) {
            $workshop = $groupSessions->first()->workshop;
            $workshopSessions = $sessions->where('workshop_id', $workshopId)->values();
            $workshopHistory = $usableHistoricalPayments->where('workshop_id', $workshopId)->values();
            
            $validTiers = $workshop->prices->sortByDesc('class_count');
            $pastSessions = collect();
            $isEligibleForIntro = $workshopHistory->isEmpty();

            if ($studentId && $workshopHistory->isNotEmpty()) {
                $maxMonths = $validTiers->max('validity_months') ?? 1;
                $minDate = Carbon::now()->subMonthsNoOverflow($maxMonths * 2)->startOfMonth();
                $pastSessions = $workshopHistory->filter(fn($p) => Carbon::parse($p->date)->gte($minDate));
                $isEligibleForIntro = $workshopHistory->isEmpty();
            } else if ($studentId) {
                $isEligibleForIntro = true;
            }

            $calcGranular = function ($sessCol, $isTotal) use ($validTiers, $isEligibleForIntro, $pastSessions) {
                $unassigned = $sessCol->sortBy('date')->values();
                $totPrice = 0; $clusters = []; 

                foreach ($validTiers as $tier) {
                    $tierSize = $tier->class_count;
                    if ($tierSize === 1) {
                        if ($unassigned->count() > 0) {
                            $price = ($isEligibleForIntro && $tier->is_introductory_active && $tier->introductory_price !== null) ? $tier->introductory_price : $tier->price;
                            $totPrice += ($price * $unassigned->count());
                            $clusters[] = ['tier_id' => $tier->id, 'class_count' => 1, 'is_grouped_dropin' => true, 'price' => ($price * $unassigned->count()), 'sessions' => $unassigned->values()];
                            $unassigned = collect(); 
                        }
                        continue;
                    }

                    $tierMonths = (int) $tier->validity_months;
                    while ($unassigned->count() >= $tierSize) {
                        $found = false;
                        for ($i = 0; $i <= $unassigned->count() - $tierSize; $i++) {
                            $pack = $unassigned->slice($i, $tierSize)->values();
                            $isValid = true;
                            
                            if ($tierMonths > 0) {
                                $maxAllowed = ($tier->validity_type === 'calendar') 
                                    ? Carbon::parse($pack->first()->date)->addMonthsNoOverflow($tierMonths - 1)->endOfMonth() 
                                    : Carbon::parse($pack->first()->date)->addMonthsNoOverflow($tierMonths);
                                if (Carbon::parse($pack->last()->date)->gt($maxAllowed)) $isValid = false;
                            }

                            if ($isValid && $isTotal && !$tier->allows_retroactive && $pack->contains(fn($s) => in_array($s->id, $pastSessions->pluck('id')->toArray()))) {
                                $isValid = false;
                            }

                            if ($isValid) {
                                $unassigned->splice($i, $tierSize);
                                $price = ($isEligibleForIntro && $tier->is_introductory_active && $tier->introductory_price !== null) ? $tier->introductory_price : $tier->price;
                                $totPrice += $price;
                                $clusters[] = ['tier_id' => $tier->id, 'class_count' => $tierSize, 'price' => $price, 'sessions' => $pack];
                                $found = true; break; 
                            }
                        }
                        if (!$found) break;
                    }
                }
                return ['total_price' => $totPrice, 'clusters' => $clusters];
            };

            $totRes = $calcGranular($workshopSessions->merge($pastSessions), true);
            $pastRes = $calcGranular($pastSessions, false);
            $total += max(0, $totRes['total_price'] - $pastRes['total_price']);

            foreach ($totRes['clusters'] as $cluster) {
                $cSess = $cluster['sessions'];
                if (!empty($cluster['is_grouped_dropin'])) {
                    $cSess = $cSess->reject(fn($s) => $pastSessions->contains('id', $s->id))->values();
                    if ($cSess->isEmpty()) continue;
                    $cluster['price'] = $cSess->count() * ($cluster['price'] / $cluster['sessions']->count());
                    $cluster['sessions'] = $cSess; 
                }

                $isPurelyPast = $cSess->every(fn($s) => $pastSessions->contains('id', $s->id));
                
                if ($isPurelyPast) continue; // Si no fue devorado por la Fase 1 y es puramente pasado, se oculta y descarta.

                $subtotal = $cluster['price'];
                $badges = [];
                $pastIds = $pastSessions->pluck('id')->toArray();

                if ($cSess->contains(fn($s) => in_array($s->id, $pastIds))) {
                    $fakePast = $calcGranular($cSess->filter(fn($s) => in_array($s->id, $pastIds)), false);
                    $subtotal = max(0, $cluster['price'] - $fakePast['total_price']);
                    $badges[] = 'Upgrade de Plan';
                }

                if ($isEligibleForIntro) $badges[] = 'Mes Introductorio';
                $qty = $cSess->count();
                $itemName = !empty($cluster['is_grouped_dropin']) ? "{$qty}x Clase Suelta: {$workshop->name}" : "Pack {$cluster['class_count']}: {$workshop->name}";

                $items = [];
                foreach ($cSess->sortBy('date') as $sess) {
                    $isPast = in_array($sess->id, $pastIds);
                    $items[] = [
                        'id' => $sess->id, 
                        'date_formatted' => ucfirst(Carbon::parse($sess->date)->translatedFormat('l d M., Y')),
                        'time_formatted' => Carbon::parse($sess->start_time)->format('H:i') . ' hrs',
                        'is_paid_history' => $isPast,
                        'label' => $isPast ? '✓ CLASE YA PAGADA' : 'A PAGAR'
                    ];
                }

                $finalBreakdown[] = [
                    'tier_id' => $cluster['tier_id'] ?? null, 
                    'name' => $itemName, 
                    'subtotal' => $subtotal, 
                    'badges' => array_values(array_unique($badges)),
                    'is_discount' => false, 
                    'is_hidden' => false,
                    'items' => $items,
                ];
            }
        }

        return ['total' => max(0, $total), 'breakdown' => array_values($finalBreakdown)];
    }
}
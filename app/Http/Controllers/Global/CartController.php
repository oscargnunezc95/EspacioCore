<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Promotion;
use App\Models\WorkshopPrice;
use Illuminate\Support\Facades\Auth;
use App\Services\PricingService; 

class CartController extends Controller
{
    public function index()
    {
        $groupedSessions = collect();
        $promotions = collect();
        $packs = collect();

        if (Auth::check()) {
            $user = Auth::user();
            
            // ─── PRINCIPIO: "Separar la Identidad de la Tutoría" ─────────────
            // El user_id en students es quien ASISTE. El apoderado gestiona
            // familiares vía user_dependents, NO vía user_id.

            // 1. Mis propias fichas de alumna
            $ownStudentIds = \App\Models\Student::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->pluck('id')
                ->toArray();

            // 2. Fichas de mis familiares/dependientes
            $dependentNationalIds = \App\Models\UserDependent::where('user_id', $user->id)
                ->pluck('national_id')
                ->toArray();

            $dependentStudentIds = [];
            if (!empty($dependentNationalIds)) {
                $dependentStudentIds = \App\Models\Student::withoutGlobalScopes()
                    ->whereIn('national_id', $dependentNationalIds)
                    ->where(function ($q) use ($user) {
                        $q->whereNull('user_id')
                          ->orWhere('user_id', '!=', $user->id);
                    })
                    ->pluck('id')
                    ->toArray();
            }

            $allStudentIds = array_unique(array_merge($ownStudentIds, $dependentStudentIds));

            if (!empty($allStudentIds)) {
                // 3. CONSULTA BLINDADA
                $dbSessions = ClassSession::withoutGlobalScopes()
                    ->with([
                        'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                        'workshop.studio', 
                        'workshop.prices',
                        // Cargar SOLO los estudiantes pendientes de este usuario
                        'students' => function ($q) use ($allStudentIds) {
                            $q->withoutGlobalScopes()
                              ->whereIn('students.id', $allStudentIds)
                              ->where('class_session_student.payment_status', 'pending');
                        }
                    ])
                    ->whereHas('students', function ($query) use ($allStudentIds) {
                        $query->withoutGlobalScopes()
                              ->whereIn('students.id', $allStudentIds)
                              ->where('class_session_student.payment_status', 'pending'); 
                    })
                    ->where('date', '>=', now()->toDateString())
                    ->orderBy('date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->get();

                // 4. Agrupamos por estudio
                $groupedSessions = $dbSessions->groupBy(function($session) {
                    return $session->workshop->studio_id ?? 0;
                });

                // 5. EXTRAEMOS LA DATA DE PROMOCIONES
                [$promotions, $packs] = $this->loadPromoData($groupedSessions->keys()->toArray());
            }
        }

        return view('cart.index', compact('groupedSessions', 'promotions', 'packs'));
    }

    public function getGuestSessions(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return response()->json(['html' => '']);

        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                'workshop.studio', 
                'workshop.prices'
            ])
            ->whereIn('id', $ids)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $groupedSessions = $sessions->groupBy(function($session) {
            return $session->workshop->studio_id;
        });

        [$promotions, $packs] = $this->loadPromoData($groupedSessions->keys()->toArray());

        $html = view('cart.partials.studio-groups', compact('groupedSessions', 'promotions', 'packs'))->render();
        return response()->json(['html' => $html]);
    }

    public function calculate(Request $request, PricingService $pricingService)
    {
        $request->validate([
            'studio_id' => 'required|integer',
            'selections' => 'required|array', 
            'selections.*.session_id' => 'required|integer',
            'selections.*.student_id' => 'required|integer',
        ]);

        try {
            $grandTotal = 0;
            $aggregatedBreakdown = [];

            // 1. Agrupamos las selecciones que llegaron por student_id
            $selectionsByStudent = collect($request->selections)->groupBy('student_id');

            // 2. Iteramos sobre cada grupo de estudiante
            foreach ($selectionsByStudent as $studentId => $items) {
                
                $student = \App\Models\Student::find($studentId);
                if (!$student) continue;

                // Sacamos solo los session_ids que el usuario chequeó para ESTE alumno
                $checkedSessionIds = $items->pluck('session_id')->toArray();

                if (!empty($checkedSessionIds)) {
                    // Calculamos el precio solo de las clases chequeadas para esta persona
                    $result = $pricingService->calculateCart($request->studio_id, $checkedSessionIds);
                    
                    $grandTotal += $result['total'];

                    if (!empty($result['breakdown'])) {
                        foreach ($result['breakdown'] as $item) {
                            $item['badges'][] = $student->first_name; 
                            $aggregatedBreakdown[] = $item;
                        }
                    }
                }
            }

            // 3. Renderizamos el HTML del carrito final
            $html = '';
            if (empty($aggregatedBreakdown)) {
                $html = "<span class='text-zinc-400'>0 clases seleccionadas</span>";
            } else {
                foreach ($aggregatedBreakdown as $item) {
                    $badgesHtml = '';
                    foreach ($item['badges'] as $index => $badge) {
                        // Si es el último badge (el nombre de la persona), lo pintamos de índigo para que resalte
                        $isNameBadge = ($index === count($item['badges']) - 1);
                        $colorClass = $isNameBadge ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700';
                        $badgesHtml .= "<span class='{$colorClass} text-[10px] px-1.5 py-0.5 rounded ml-2 font-black uppercase'>{$badge}</span>";
                    }

                    $formattedSubtotal = '$' . number_format($item['subtotal'], 0, ',', '.');
                    
                    $html .= "
                        <div class='flex justify-between items-center mt-2 text-sm border-b border-zinc-100 pb-2 last:border-0'>
                            <span class='text-zinc-600 font-medium'>{$item['name']} {$badgesHtml}</span>
                            <span class='font-black text-zinc-900'>{$formattedSubtotal}</span>
                        </div>
                    ";
                }
            }

            return response()->json([
                'total_raw' => $grandTotal,
                'total_formatted' => '$' . number_format($grandTotal, 0, ',', '.'),
                'breakdown_html' => $html
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en Pricing: ' . $e->getMessage() . ' Línea: ' . $e->getLine());
            return response()->json(['error' => 'Error al procesar los precios.'], 500);
        }
    }

    /**
     * =========================================================================
     * MÉTODO PRIVADO: Centraliza y optimiza la carga de Promociones y Packs
     * =========================================================================
     */
    private function loadPromoData(array $studioIds): array
    {
        if (empty($studioIds)) {
            return [collect(), collect()];
        }

        $promotions = Promotion::with('workshopPrices.workshop')
            ->whereIn('studio_id', $studioIds)
            ->get()
            ->groupBy('studio_id');

        $packs = WorkshopPrice::with('workshop')
            ->whereHas('workshop', function($q) use ($studioIds) {
                $q->whereIn('studio_id', $studioIds);
            })
            ->where('class_count', '>', 1)
            ->get()
            ->groupBy(function($price) {
                return $price->workshop->studio_id;
            })
            ->map(function ($studioPacks) {
                return $studioPacks->groupBy(function($pack) {
                    return $pack->workshop->name;
                });
            });

        return [$promotions, $packs];
    }
}
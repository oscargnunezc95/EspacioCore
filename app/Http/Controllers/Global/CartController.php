<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSession;
use Illuminate\Support\Facades\Auth;
use App\Services\PricingService; 

class CartController extends Controller
{
    public function index()
    {
        $groupedSessions = collect();

        if (Auth::check()) {
            $user = Auth::user();
            
            // 1. Obtenemos los IDs de las fichas de alumna exactas
            $studentIds = \App\Models\Student::withoutGlobalScopes()
                                             ->where('user_id', $user->id)
                                             ->pluck('id')
                                             ->toArray();

            if (!empty($studentIds)) {
                // 2. CONSULTA BLINDADA (Idéntica a la del contador de navegación)
                $dbSessions = ClassSession::withoutGlobalScopes()
                    ->with([
                        'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                        'workshop.studio', 
                        'workshop.prices'
                    ])
                    ->whereHas('students', function ($query) use ($studentIds) {
                        $query->withoutGlobalScopes()
                              // Filtramos directamente por el ID de estudiante en lugar del usuario
                              ->whereIn('students.id', $studentIds)
                              // Especificamos la tabla física para evitar que Laravel se pierda
                              ->where('class_session_student.payment_status', 'pending'); 
                    })
                    ->where('date', '>=', now()->toDateString())
                    ->orderBy('date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->get();

                // 3. Agrupamos por estudio (asegurando un fallback seguro)
                $groupedSessions = $dbSessions->groupBy(function($session) {
                    return $session->workshop->studio_id ?? 0;
                });
            }
        }

        return view('cart.index', compact('groupedSessions'));
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

        $html = view('cart.partials.studio-groups', compact('groupedSessions'))->render();
        return response()->json(['html' => $html]);
    }

    public function calculate(Request $request, PricingService $pricingService)
    {
        $request->validate([
            'studio_id' => 'required|integer',
            'session_ids' => 'present|array', 
            'session_ids.*' => 'integer'
        ]);

        try {
            $result = $pricingService->calculateCart($request->studio_id, $request->session_ids);

            $html = '';
            if (empty($result['breakdown'])) {
                $html = "<span class='text-zinc-400'>0 clases seleccionadas</span>";
            } else {
                foreach ($result['breakdown'] as $item) {
                    $badgesHtml = '';
                    foreach ($item['badges'] as $badge) {
                        $badgesHtml .= "<span class='bg-emerald-100 text-emerald-700 text-[10px] px-1.5 py-0.5 rounded ml-2 font-black uppercase'>{$badge}</span>";
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
                'total_raw' => $result['total'],
                'total_formatted' => '$' . number_format($result['total'], 0, ',', '.'),
                'breakdown_html' => $html
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en Pricing: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar los precios.'], 500);
        }
    }
}
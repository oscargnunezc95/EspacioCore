<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Agregado para usar DB facades de forma segura
use App\Services\PricingService; // Asegúrate de importar el servicio arriba

class CartController extends Controller
{
    public function index()
    {
        $groupedSessions = collect();

        if (Auth::check()) {
            $user = Auth::user();
            
            // 1. Obtenemos los IDs de los perfiles de estudiante ignorando scopes globales
            $studentIds = \App\Models\Student::withoutGlobalScopes()
                                             ->where('user_id', $user->id)
                                             ->pluck('id')
                                             ->toArray();

            if (!empty($studentIds)) {
                // 2. Consulta Global Blindada
                $dbSessions = ClassSession::withoutGlobalScopes()
                    ->with([
                        'workshop' => fn($q) => $q->withoutGlobalScopes(), 
                        'workshop.studio', 
                        'workshop.prices'
                    ])
                    ->whereHas('students', function ($query) use ($user) {
                        $query->withoutGlobalScopes()->where('students.user_id', $user->id); 
                    })
                    // Validamos contra la tabla de pagos cruda para evitar bloqueos por scopes
                    ->whereNotExists(function ($query) use ($studentIds) {
                        $query->select(DB::raw(1))
                              ->from('class_session_payment')
                              ->whereColumn('class_session_payment.class_session_id', 'class_sessions.id')
                              ->whereIn('class_session_payment.student_id', $studentIds);
                    })
                    ->where('date', '>=', now()->toDateString())
                    ->orderBy('date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->get();

                $groupedSessions = $dbSessions->groupBy(function($session) {
                    return $session->workshop->studio_id;
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
            'session_ids' => 'present|array', // present permite arreglos vacíos []
            'session_ids.*' => 'integer'
        ]);

        try {
            // Delegamos toda la carga matemática al Servicio
            $result = $pricingService->calculateCart($request->studio_id, $request->session_ids);

            // Construimos el HTML del desglose en el servidor para protegerlo
            $html = '';
            if (empty($result['breakdown'])) {
                $html = "<span class='text-zinc-400'>0 clases seleccionadas</span>";
            } else {
                foreach ($result['breakdown'] as $item) {
                    $badgesHtml = '';
                    foreach ($item['badges'] as $badge) {
                        $badgesHtml .= "<span class='bg-emerald-100 text-emerald-700 text-[10px] px-1.5 py-0.5 rounded ml-2 font-black uppercase'>{$badge}</span>";
                    }

                    // Formato CLP nativo en el backend
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
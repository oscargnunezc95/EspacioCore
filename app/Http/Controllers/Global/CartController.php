<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSession;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $groupedSessions = collect();

        if (Auth::check()) {
            $user = Auth::user();
            
            // 1. Obtenemos los IDs de los perfiles de estudiante de forma segura
            $studentIds = \App\Models\Student::where('user_id', $user->id)->pluck('id');

            // 2. Solo hacemos la consulta pesada si realmente es alumno de algún estudio
            if ($studentIds->isNotEmpty()) {
                $dbSessions = ClassSession::with(['workshop.studio', 'workshop.prices'])
                    ->whereHas('students', function ($query) use ($user) {
                        // Solucionado el problema de ambigüedad
                        $query->where('students.user_id', $user->id); 
                    })
                    ->whereDoesntHave('payments', function ($query) use ($studentIds) {
                        // Pasamos los IDs seguros en lugar de llamar a la relación
                        $query->whereIn('class_session_payment.student_id', $studentIds);
                    })
                    ->where('date', '>=', now()->toDateString())
                    ->orderBy('date', 'asc')
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

        $sessions = ClassSession::with(['workshop.studio', 'workshop.prices'])
            ->whereIn('id', $ids)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->get();

        $groupedSessions = $sessions->groupBy(function($session) {
            return $session->workshop->studio_id;
        });

        // Ojo al nuevo nombre de la carpeta: 'cart'
        $html = view('cart.partials.studio-groups', compact('groupedSessions'))->render();
        return response()->json(['html' => $html]);
    }
}
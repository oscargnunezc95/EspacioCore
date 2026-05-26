<?php

namespace App\Http\Controllers;

use App\Models\Studio;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
// 👇 NUEVAS IMPORTACIONES 👇
use Illuminate\Support\Facades\Mail;
use App\Mail\StudioMercadoPagoUnlinkedMail;

class AccountController extends Controller
{
    public function index($subdomain)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $payments = Payment::with('student')
            ->whereHas('workshop', function ($query) use ($studio) {
                $query->where('studio_id', $studio->id);
            })
            ->latest()
            ->paginate(15);

        return view('account.index', compact('studio', 'payments', 'subdomain'));
    }
    
    public function disconnectMercadoPago(Request $request)
    {
        $studio = auth()->user()->studios()->first();

        if ($studio) {
            $studio->update([
                'mp_access_token' => null,
                'mp_refresh_token' => null,
                'mp_user_id' => null,
            ]);

            Log::info("El estudio {$studio->name} ha desvinculado su cuenta de Mercado Pago.");

            try {
                if ($studio->user) {
                    // A) Envía el Correo
                    if ($studio->user->email) {
                        Mail::to($studio->user->email)->send(new StudioMercadoPagoUnlinkedMail($studio));
                    }
                    // B) 👇 NUEVA INYECCIÓN: Campanita para la Dueña 👇
                    $studio->user->notify(new \App\Notifications\StudioMPUnlinkedNotification($studio));
                }
            } catch (\Exception $e) {
                Log::error('Se desvinculó MP, pero fallaron las alertas: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Tu cuenta de Mercado Pago ha sido desvinculada correctamente.');
    }
}
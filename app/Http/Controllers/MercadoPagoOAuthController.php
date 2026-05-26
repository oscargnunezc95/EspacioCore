<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
// 👇 NUEVAS IMPORTACIONES 👇
use Illuminate\Support\Facades\Mail;
use App\Mail\StudioMercadoPagoLinkedMail;

class MercadoPagoOAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $studio = Studio::where('user_id', Auth::id())->firstOrFail();
        $appId = env('MERCADOPAGO_APP_ID');
        $redirectUri = env('MERCADOPAGO_REDIRECT_URI');
        
        $request->session()->put('oauth_studio_id', $studio->id);
        $url = "https://auth.mercadopago.cl/authorization?client_id={$appId}&response_type=code&platform_id=mp&redirect_uri={$redirectUri}";
        
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $studioId = $request->session()->pull('oauth_studio_id');
        
        if (!$studioId || !$request->has('code')) {
            Log::error('Fallo en OAuth de MP: Sesión perdida o código ausente.', $request->all());
            return redirect('/dashboard')->with('error', 'No se pudo vincular la cuenta. Por favor, intenta de nuevo.');
        }

        $studio = Studio::findOrFail($studioId);

        try {
            $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
                'client_id'     => env('MERCADOPAGO_APP_ID'),
                'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
                'grant_type'    => 'authorization_code',
                'code'          => $request->code,
                'redirect_uri'  => env('MERCADOPAGO_REDIRECT_URI'),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $studio->update([
                    'mp_access_token'  => $data['access_token'],
                    'mp_refresh_token' => $data['refresh_token'],
                    'mp_user_id'       => $data['user_id'],
                ]);

                try {
                    if ($studio->user) {
                        // A) Envía el Correo
                        if ($studio->user->email) {
                            Mail::to($studio->user->email)->send(new StudioMercadoPagoLinkedMail($studio));
                        }
                        // B) 👇 NUEVA INYECCIÓN: Campanita para la Dueña 👇
                        $studio->user->notify(new \App\Notifications\StudioMPLinkedNotification($studio));
                    }
                } catch (\Exception $e) {
                    Log::error('Se vinculó MP, pero fallaron las alertas: ' . $e->getMessage());
                }

                return redirect()->route('account.index', ['subdomain' => $studio->subdomain])
                                 ->with('success', '¡Cuenta de Mercado Pago vinculada exitosamente! Ya puedes recibir pagos directos.');
            }

            Log::error('MP OAuth Token Exchange Failed', $response->json());
            return redirect()->route('account.index', ['subdomain' => $studio->subdomain])
                             ->with('error', 'Hubo un error al autorizar la cuenta con Mercado Pago.');

        } catch (\Exception $e) {
            Log::error('Error crítico en MP OAuth: ' . $e->getMessage());
            return redirect()->route('account.index', ['subdomain' => $studio->subdomain])
                             ->with('error', 'Error de conexión con Mercado Pago.');
        }
    }
}
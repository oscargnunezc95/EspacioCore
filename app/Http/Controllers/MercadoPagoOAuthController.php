<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudioMercadoPagoLinkedMail;

class MercadoPagoOAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $appId = env('MERCADOPAGO_APP_ID');
        $redirectUri = env('MERCADOPAGO_REDIRECT_URI');

        // Si source=teacher (por ruta o query), el OAuth es para vincular la cuenta del profesor
        $isTeacher = $request->query('source') === 'teacher' || $request->route('source') === 'teacher';
        if ($isTeacher) {
            $request->session()->put('oauth_source', 'teacher');
            $request->session()->put('oauth_user_id', Auth::id());
        } else {
            // Flujo original: vincular cuenta del Studio
            $studio = Studio::where('user_id', Auth::id())->firstOrFail();
            $request->session()->put('oauth_source', 'studio');
            $request->session()->put('oauth_studio_id', $studio->id);
        }

        $url = "https://auth.mercadopago.cl/authorization?client_id={$appId}&response_type=code&platform_id=mp&redirect_uri={$redirectUri}";

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $source = $request->session()->pull('oauth_source', 'studio');

        if (!$request->has('code')) {
            Log::error('Fallo en OAuth de MP: código ausente.', $request->all());
            return redirect('/dashboard')->with('error', 'No se pudo vincular la cuenta. Por favor, intenta de nuevo.');
        }

        try {
            $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
                'client_id'     => env('MERCADOPAGO_APP_ID'),
                'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
                'grant_type'    => 'authorization_code',
                'code'          => $request->code,
                'redirect_uri'  => env('MERCADOPAGO_REDIRECT_URI'),
            ]);

            if (!$response->successful()) {
                Log::error('MP OAuth Token Exchange Failed', $response->json());
                return $this->redirectOnError($source, 'Hubo un error al autorizar la cuenta con Mercado Pago.');
            }

            $data = $response->json();

            if ($source === 'teacher') {
                return $this->handleTeacherCallback($data);
            }

            return $this->handleStudioCallback($data);

        } catch (\Exception $e) {
            Log::error('Error crítico en MP OAuth: ' . $e->getMessage());
            return $this->redirectOnError($source, 'Error de conexión con Mercado Pago.');
        }
    }

    /**
     * Flujo original: guarda tokens en el Studio.
     */
    private function handleStudioCallback(array $data)
    {
        $studioId = session('oauth_studio_id'); // usamos session() helper en vez de ->session()
        // Reintentamos obtener el ID si no está en la sesión actual
        if (!$studioId) {
            $studioId = request()->session()->get('oauth_studio_id');
        }
        // Si aún no está, lo sacamos del usuario autenticado
        if (!$studioId) {
            $studio = Studio::where('user_id', Auth::id())->first();
            $studioId = $studio?->id;
        }

        if (!$studioId) {
            Log::error('Fallo en OAuth de MP (studio): No se pudo determinar el studio.');
            return redirect('/dashboard')->with('error', 'No se pudo vincular la cuenta.');
        }

        $studio = Studio::findOrFail($studioId);
        $studio->update([
            'mp_access_token'  => $data['access_token'],
            'mp_refresh_token' => $data['refresh_token'],
            'mp_user_id'       => $data['user_id'],
        ]);

        $this->notifyStudioOwner($studio);

        return redirect()->route('account.index', ['subdomain' => $studio->subdomain])
                         ->with('success', '¡Cuenta de Mercado Pago vinculada exitosamente! Ya puedes recibir pagos directos.');
    }

    /**
     * Flujo profesor: guarda tokens en el User global.
     */
    private function handleTeacherCallback(array $data)
    {
        $userId = Auth::id();

        $user = User::findOrFail($userId);
        $user->update([
            'mp_access_token'  => $data['access_token'],
            'mp_refresh_token' => $data['refresh_token'],
            'mp_user_id'       => $data['user_id'],
        ]);

        Log::info("Profesor (User ID: {$userId}) vinculó su cuenta de Mercado Pago.", [
            'mp_user_id' => $data['user_id']
        ]);

        return redirect()->route('global.classes.teacher')
                         ->with('success', '¡Cuenta de Mercado Pago vinculada exitosamente! Ya puedes recibir pagos de los estudios.');
    }

    private function redirectOnError(string $source, string $message)
    {
        if ($source === 'teacher') {
            return redirect()->route('global.classes.teacher')->with('error', $message);
        }

        // studio fallback
        $studio = Studio::where('user_id', Auth::id())->first();
        if ($studio) {
            return redirect()->route('account.index', ['subdomain' => $studio->subdomain])
                             ->with('error', $message);
        }

        return redirect('/dashboard')->with('error', $message);
    }

    private function notifyStudioOwner(Studio $studio)
    {
        try {
            if ($studio->user) {
                if ($studio->user->email) {
                    Mail::to($studio->user->email)->send(new StudioMercadoPagoLinkedMail($studio));
                }
                $studio->user->notify(new \App\Notifications\StudioMPLinkedNotification($studio));
            }
        } catch (\Exception $e) {
            Log::error('Se vinculó MP, pero fallaron las alertas: ' . $e->getMessage());
        }
    }
}

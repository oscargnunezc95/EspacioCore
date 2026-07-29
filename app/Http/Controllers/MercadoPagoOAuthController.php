<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MercadoPagoOAuthController extends Controller
{ 
    public function redirect(Request $request)
    {
        $appId = config('services.mercadopago.app_id');
        $redirectUri = config('services.mercadopago.redirect_uri');
        $authBaseUrl = config('services.mercadopago.auth_url');

        // Si source=teacher (por ruta o query), el OAuth es para vincular la cuenta del profesor
        $isTeacher = $request->query('source') === 'teacher' || $request->route('source') === 'teacher';

        if ($isTeacher) {
            $state = 'teacher';
        } else {
            // Flujo estudio: recibe dinámicamente el ID por query param
            $requestedStudioId = $request->query('studio_id');

            if ($requestedStudioId) {
                // Validar que el estudio pertenece al usuario autenticado
                $studio = Studio::where('id', $requestedStudioId)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();
            } else {
                // Fallback: primer estudio del usuario (cuando no se especifica uno)
                $studio = Studio::where('user_id', Auth::id())->firstOrFail();
            }

            $state = 'studio_' . $studio->id;
        }

        // Scopes necesarios: read (lectura), write (escritura), offline_access (refresh tokens)
        $scopes = 'read%20write%20offline_access';
        $url = "{$authBaseUrl}?client_id={$appId}&response_type=code&platform_id=mp&redirect_uri={$redirectUri}&state={$state}&scope={$scopes}";

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        // Leer el state que Mercado Pago devuelve (flujo stateless)
        $state = $request->query('state');

        if (!$request->has('code')) {
            Log::error('Fallo en OAuth de MP: código ausente.', $request->all());
            return redirect('/dashboard')->with('error', 'No se pudo vincular la cuenta. Por favor, intenta de nuevo.');
        }

        try {
            // Base URL de la API también parametrizada
            $apiBaseUrl = env('MERCADOPAGO_API_URL', 'https://api.mercadopago.com');
            
            $response = Http::asForm()->post("{$apiBaseUrl}/oauth/token", [
                'client_id'     => env('MERCADOPAGO_APP_ID'),
                'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
                'grant_type'    => 'authorization_code',
                'code'          => $request->code,
                'redirect_uri'  => env('MERCADOPAGO_REDIRECT_URI'),
            ]);

            if (!$response->successful()) {
                Log::error('MP OAuth Token Exchange Failed', $response->json());
                return $this->redirectOnError($state, 'Hubo un error al autorizar la cuenta con Mercado Pago.');
            }

            $data = $response->json();

            if ($state === 'teacher') {
                return $this->handleTeacherCallback($data);
            }

            if (str_starts_with($state, 'studio_')) {
                $studioId = (int) substr($state, strlen('studio_'));
                return $this->handleStudioCallback($data, $studioId);
            }

            // State desconocido o corrupto — no podemos determinar el destino
            Log::error('Fallo en OAuth de MP: state desconocido.', ['state' => $state]);
            return redirect('/dashboard')->with('error', 'No se pudo determinar el destino de la vinculación. Por favor, intenta de nuevo.');

        } catch (\Exception $e) {
            Log::error('Error crítico en MP OAuth: ' . $e->getMessage());
            return $this->redirectOnError($state, 'Error de conexión con Mercado Pago.');
        }
    }

    /**
     * Flujo estudio: guarda tokens en el Studio específico.
     * El $stateStudioId viene del parámetro OAuth state, garantizando
     * vinculación exacta al estudio correcto (sin fallbacks peligrosos).
     */
    private function handleStudioCallback(array $data, $stateStudioId = null)
    {
        if (!$stateStudioId) {
            Log::error('Fallo en OAuth de MP (studio): No se recibió studio_id en el state.');
            return redirect('/dashboard')->with('error', 'No se pudo determinar el estudio a vincular.');
        }

        // Búsqueda estricta: solo el estudio cuyo ID viene en el state.
        // Sin fallback a ->first() que causaba vinculaciones accidentales.
        $studio = Studio::findOrFail($stateStudioId);

        $studio->update([
            'mp_access_token'  => $data['access_token'],
            'mp_refresh_token' => $data['refresh_token'],
            'mp_user_id'       => $data['user_id'],
        ]);

        // Auto-configurar QR estático (no bloquea el OAuth si falla)
        try {
            app(\App\Services\MercadoPagoService::class)->setupStaticQR($studio);
        } catch (\Exception $e) {
            Log::warning("Auto static-QR setup falló para el estudio {$studio->id}: " . $e->getMessage());
        }

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

    /**
     * Redirige al usuario en caso de error, interpretando el state
     * para devolverlo a la sección correcta.
     */
    private function redirectOnError(?string $state, string $message)
    {
        if ($state === 'teacher') {
            return redirect()->route('global.classes.teacher')->with('error', $message);
        }

        // Si el state contiene un studio_id, redirigimos a ese estudio
        if ($state && str_starts_with($state, 'studio_')) {
            $studioId = (int) substr($state, strlen('studio_'));
            $studio = Studio::find($studioId);
            if ($studio) {
                return redirect()->route('account.index', ['subdomain' => $studio->subdomain])
                                 ->with('error', $message);
            }
        }

        // Fallback último: si no hay state reconocible, al dashboard
        return redirect('/dashboard')->with('error', $message);
    }

    private function notifyStudioOwner(Studio $studio)
    {
        try {
            if ($studio->user) {
                // Se delega exclusivamente al sistema nativo de Notificaciones.
                // Si el canal ['mail'] está activo en la clase StudioMPLinkedNotification,
                // Laravel enviará un único correo.
                $studio->user->notify(new \App\Notifications\StudioMPLinkedNotification($studio));
            }
        } catch (\Exception $e) {
            Log::error('Se vinculó MP, pero fallaron las alertas: ' . $e->getMessage());
        }
    }
}
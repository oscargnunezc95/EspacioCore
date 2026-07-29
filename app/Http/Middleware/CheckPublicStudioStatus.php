<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Studio;

class CheckPublicStudioStatus
{
    /**
     * Bloquea el acceso público al subdominio del estudio si tiene
     * facturas vencidas (past_due), retornando HTTP 503.
     *
     * La consulta es ligera: solo verifica la existencia de al menos
     * una factura vencida mediante exists(), que ejecuta un
     * SELECT 1 … LIMIT 1 sobre el índice de la FK studio_id.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');

        if (!$subdomain) {
            return $next($request);
        }

        $studio = Studio::where('subdomain', $subdomain)->first();

        if (!$studio) {
            // Si el subdominio no corresponde a ningún estudio,
            // dejamos que la ruta siga su curso natural (el controlador
            // o IdentifyStudio se encargarán del 404/redirect).
            return $next($request);
        }

        // ─── VERIFICACIÓN LIGERA DE MORA ──────────────────────────
        // Solo necesitamos saber si EXISTE al menos una factura vencida.
        // Eloquent traduce esto a: SELECT 1 FROM studio_invoices
        // WHERE studio_id = ? AND status = 'past_due' LIMIT 1
        $isDelinquent = $studio->invoices()->where('status', 'past_due')->exists();

        if ($isDelinquent) {
            return response()->view('errors.studio-suspended', [
                'studio' => $studio,
            ], 503); // Service Unavailable — protege el SEO
        }

        return $next($request);
    }
}

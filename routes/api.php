<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Las rutas aquí no tienen protección CSRF, lo que permite recibir 
| peticiones automáticas de servidores externos (como Mercado Pago).
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/mercadopago', [WebhookController::class, 'handleMercadoPago']);
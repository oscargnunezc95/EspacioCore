<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*', 
        'sanctum/csrf-cookie', 
        '*/sessions/*/attendance/*' 
        // Eliminamos 'global/*' porque ya no necesita pasar por el firewall de CORS
    ],

    'allowed_methods' => ['*'],

    // CRÍTICO: Vacío para no chocar con las credenciales
    'allowed_origins' => [],

    // CRÍTICO: Vacío para no chocar con las credenciales
    'allowed_origins' => [],

    // EXPRESIÓN REGULAR VÁLIDA: Permite cualquier subdominio tuyo (http o https)
    'allowed_origins_patterns' => ['#^https?://.*\.estadoprisma\.test$#'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Mantenemos esto en true por si tu ruta de asistencia necesita leer al usuario logueado
    'supports_credentials' => true,

];
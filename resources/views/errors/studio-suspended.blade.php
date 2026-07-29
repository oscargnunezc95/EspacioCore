<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Servicio temporalmente inactivo — {{ $studio->name ?? 'Estudio' }}</title>

    {{-- SEO: indicamos a los motores que no indexen esta página de suspensión --}}
    <meta name="robots" content="noindex, nofollow">

    <link rel="icon" type="image/png" href="{{ asset('isotipo-estadoprisma.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-stone-800 antialiased bg-stone-50 selection:bg-stone-200 selection:text-stone-900">

    <main class="flex min-h-screen items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
        <div class="w-full max-w-lg text-center">

            {{-- Ícono de pausa / mantenimiento — limpio, sin connotaciones de alerta --}}
            <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-amber-50 ring-1 ring-amber-100">
                <svg class="h-12 w-12 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    {{-- Círculo exterior --}}
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" />
                    {{-- Barras de pausa --}}
                    <line x1="9" y1="8" x2="9" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <line x1="15" y1="8" x2="15" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </div>

            {{-- Título --}}
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">
                Servicio temporalmente inactivo
            </h1>

            {{-- Mensaje --}}
            <p class="mt-4 text-base leading-relaxed text-stone-500 sm:text-lg">
                La plataforma de reservas para este estudio se encuentra en mantenimiento.
                Por favor, comunícate directamente con la recepción del estudio para
                gestionar tus clases.
            </p>

            {{-- Botón secundario al Explorer global --}}
            <div class="mt-10">
                <a href="{{ route('explore') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-stone-300 bg-white px-6 py-3 text-sm font-medium text-stone-700 shadow-sm transition-all duration-200 hover:bg-stone-50 hover:text-stone-900 hover:shadow focus:outline-none focus:ring-2 focus:ring-stone-400 focus:ring-offset-2">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver a EstadoPrisma
                </a>
            </div>

            {{-- Detalle sutil del nombre del estudio --}}
            @if(!empty($studio->name))
                <p class="mt-8 text-xs text-stone-400">
                    {{ $studio->name }}
                </p>
            @endif
        </div>
    </main>

</body>
</html>

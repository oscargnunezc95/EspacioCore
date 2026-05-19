<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EstadoPrisma') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-zinc-800 antialiased bg-zinc-50 selection:bg-zinc-200 selection:text-zinc-900 flex flex-col min-h-screen">
    
    @include('layouts.navigation') 
    
    {{-- Cambiamos 'flex' por 'block' para que el contenido fluya de arriba a abajo y ocupe el 100% del ancho --}}
    <main class="flex-1 w-full block">
        {{ $slot }}
    </main>

</body>
</html>
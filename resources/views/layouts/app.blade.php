<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO — via atributos del componente o defaults --}}
    <title>{{ $metaTitle ?? 'EstadoPrisma' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Encuentra y reserva clases de circo, danza, acrobacia y mas.' }}">

    <meta property="og:title" content="{{ $metaTitle ?? 'EstadoPrisma' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Encuentra y reserva clases.' }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    <meta property="og:site_name" content="EstadoPrisma">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle ?? 'EstadoPrisma' }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? '' }}">

    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">

    <meta name="robots" content="{{ $metaRobots ?? 'index, follow' }}">

    {{-- Structured Data via named slot --}}
    {!! $structuredData ?? '' !!}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-zinc-800 antialiased bg-zinc-50 selection:bg-zinc-200 selection:text-zinc-900 flex flex-col min-h-screen">

    @include('layouts.navigation')

    @isset($header)
        <header class="bg-white border-b border-zinc-100">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main class="flex-1 w-full">
        {{ $slot }}
    </main>

</body>
</html>

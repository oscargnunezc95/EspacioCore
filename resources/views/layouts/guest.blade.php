<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO — via atributos del componente o defaults --}}
    <title>{{ $metaTitle ?? 'EstadoPrisma — Software de Gestion para Estudios y Academias' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Centraliza reservas, automatiza la cobranza y ofrece una experiencia impecable a tus alumnos. El sistema operativo para tu estudio de danza, circo o acrobacia.' }}">

    <meta property="og:title" content="{{ $metaTitle ?? 'EstadoPrisma — Software de Gestion para Estudios' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Centraliza reservas, automatiza la cobranza.' }}">
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

    <main class="flex-1 w-full block">
        {{ $slot }}
    </main>

</body>
</html>

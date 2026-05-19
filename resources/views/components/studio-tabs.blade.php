@php
    $subdomain = request()->route('subdomain');
    
    // ESTILOS ACTUALIZADOS A NEGRO (ZINC)
    $activeClass = 'text-zinc-900 bg-zinc-50 border-b-2 border-zinc-900'; 
    $inactiveClass = 'text-zinc-500 hover:text-zinc-900 hover:bg-zinc-50/50 border-b-2 border-transparent';

    // 1. Consultamos el Estudio
    $studio = \App\Models\Studio::where('subdomain', $subdomain)->first();
    $studioName = $studio ? $studio->name : 'Espacio';
    
    // 2. Extraemos la imagen
    $studioImage = $studio ? ($studio->logo_path ?? $studio->icon_path) : null;

    // 3. Preparamos URLs limpias
    $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
    $protocol = request()->secure() ? 'https://' : 'http://';
    $studioUrl = $protocol . $subdomain . '.' . $domain;
@endphp

{{-- ========================================== --}}
{{-- CONTENEDOR PRINCIPAL SINCRONIZADO POR CSS --}}
{{-- ========================================== --}}
<div x-data="{ 
        isHidden: false, 
        lastScrollY: 0 
     }" 
     @scroll.window="
        let currentScrollY = window.scrollY;
        if (currentScrollY > 80 && currentScrollY > lastScrollY) {
            isHidden = true;
        } else {
            isHidden = false;
        }
        lastScrollY = currentScrollY;
     "
     :class="isHidden ? '-translate-y-[81px]' : 'translate-y-0'"
     class="w-[100vw] ml-[calc(50%-50vw)] sticky top-[81px] z-30 transition-transform duration-300 ease-in-out transform flex flex-col shadow-sm border-b border-zinc-200 bg-white">
    
    {{-- ========================================== --}}
    {{-- ZONA SUPERIOR: BANNER --}}
    {{-- ========================================== --}}
    <div class="relative w-full h-auto min-h-[6rem] md:min-h-[8rem] bg-zinc-900 overflow-hidden">
        {{-- Imagen de Fondo --}}
        @if($studioImage)
            <img src="{{ asset('storage/' . $studioImage) }}" class="absolute inset-0 w-full h-full object-cover opacity-40 blur-[2px] scale-105" alt="Cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>

        {{-- BOTÓN COMPARTIR (Nativo con Alpine.js) --}}
        <button type="button"
                @click="
                    if (navigator.share) {
                        navigator.share({
                            title: '{{ $studioName }}',
                            text: 'Descubre y reserva clases en {{ $studioName }}',
                            url: '{{ $studioUrl }}'
                        }).catch(err => console.log('Error compartiendo:', err));
                    } else {
                        navigator.clipboard.writeText('{{ $studioUrl }}');
                        alert('¡Enlace del estudio copiado al portapapeles!');
                    }
                "
                class="absolute top-4 right-4 z-20 flex items-center gap-2 px-3 py-1.5 bg-black/40 hover:bg-black/60 backdrop-blur-md text-white text-xs font-bold rounded-lg border border-white/10 transition-all duration-200 active:scale-95 shadow-sm focus:outline-none focus:ring-2 focus:ring-white/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
            <span class="hidden sm:inline">Compartir</span>
        </button>

        {{-- Contenido del Banner --}}
        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-end pb-4 pt-8 gap-3 md:gap-5 h-full">
            
            {{-- Logo --}}
            <div class="shrink-0">
                @if($studioImage)
                    <img src="{{ asset('storage/' . $studioImage) }}" alt="Logo" class="h-16 w-16 md:h-20 md:w-20 rounded-xl object-cover border-2 md:border-4 border-white shadow-xl bg-white">
                @else
                    <div class="h-16 w-16 md:h-20 md:w-20 rounded-xl bg-zinc-900 border-2 md:border-4 border-white text-white flex items-center justify-center font-black text-2xl md:text-3xl shadow-xl">
                        {{ substr($studioName, 0, 1) }}
                    </div>
                @endif
            </div>

            {{-- Título y Enlaces Activos --}}
            <div class="flex-1 min-w-0 pb-1">
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight drop-shadow-md leading-none truncate mb-2">
                    {{ $studioName }}
                </h1>
                
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                    {{-- Enlace Web --}}
                    <a href="{{ $studioUrl }}" target="_blank" class="flex items-center gap-1.5 text-xs md:text-sm font-medium text-zinc-300 hover:text-white transition-colors drop-shadow w-fit group">
                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4 text-zinc-400 group-hover:text-indigo-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        <span class="truncate">{{ $subdomain }}.{{ $domain }}</span>
                    </a>

                    {{-- Enlace Maps (Condicional) --}}
                    @if($studio && $studio->address)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($studio->address) }}" target="_blank" class="flex items-center gap-1.5 text-xs md:text-sm font-medium text-zinc-300 hover:text-white transition-colors drop-shadow w-fit group">
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4 text-zinc-400 group-hover:text-rose-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="truncate max-w-[200px] md:max-w-xs">{{ $studio->address }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- ZONA INFERIOR: TABS (REPARTIDOS Y CENTRADOS) --}}
    {{-- ========================================== --}}
    <div class="w-full bg-white/95 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8"> 
            
            <nav class="flex w-full justify-between overflow-x-auto hide-scrollbar" aria-label="Tabs">
                
                <a href="{{ route('dashboard', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('dashboard') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="hidden md:inline">Panel General</span>
                </a>

                <a href="{{ route('account.index', $subdomain) }}" 
                    class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                           {{ request()->routeIs('account.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('account.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="hidden md:inline">Cuenta</span>
                </a>

                <a href="{{ route('students.index', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('students.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('students.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="hidden md:inline">Alumnas/os</span>
                </a>

                <a href="{{ route('teachers.index', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('teachers.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('teachers.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                    <span class="hidden md:inline">Profesores</span>
                </a>

                <a href="{{ route('workshops.index', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('workshops.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('workshops.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="hidden md:inline">Talleres</span>
                </a>

                <a href="{{ route('promotions.index', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('promotions.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('promotions.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <span class="hidden md:inline">Promociones</span>
                </a>

                <a href="{{ route('trainingmonth.index', $subdomain) }}" 
                    class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                        {{ request()->routeIs('trainingmonth.*', 'sessions.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('trainingmonth.*', 'sessions.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span class="hidden md:inline">Mensuales</span>
                </a>

            </nav>
        </div>
    </div>
</div>

<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
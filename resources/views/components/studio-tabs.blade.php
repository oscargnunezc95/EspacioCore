@php
    $subdomain = request()->route('subdomain');
    
    // ESTILOS ACTUALIZADOS A NEGRO (ZINC)
    $activeClass = 'text-red-600 font-black bg-red-50/50 border-b-2 border-red-600';
    $inactiveClass = 'text-stone-500 hover:text-stone-900 hover:bg-stone-50/50 border-b-2 border-transparent';

    // 1. Consultamos el Estudio
    $studio = \App\Models\Studio::where('subdomain', $subdomain)->first();
    $studioName = $studio ? $studio->name : 'Espacio';
    
    // 2. Extraemos las imágenes con lógica de Fallback (Portada -> Logo -> Icono)
    $coverImage = $studio ? ($studio->cover_path ?? $studio->logo_path ?? $studio->icon_path) : null;
    $avatarImage = $studio ? ($studio->icon_path ?? $studio->logo_path) : null;

    // 3. Preparamos URLs limpias
    $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
    $protocol = request()->secure() ? 'https://' : 'http://';
    $studioUrl = $protocol . $subdomain . '.' . $domain;
@endphp

{{-- ====================================================================== --}}
{{-- CONTENEDOR PRINCIPAL: SMART STICKY CON COLAPSO DINÁMICO DE BANNER --}}
{{-- ====================================================================== --}}
<div x-data="{ 
        isHidden: false, 
        lastScrollY: 0,
        bannerHeight: 0,
        updateHeight() {
            if (this.$refs.bannerZone) {
                this.bannerHeight = this.$refs.bannerZone.offsetHeight;
            }
        },
        init() {
            this.$nextTick(() => this.updateHeight());
            new ResizeObserver(() => this.updateHeight()).observe(this.$refs.bannerZone);
        }
     }" 
     @scroll.window="
        let currentScrollY = window.scrollY;
        if (currentScrollY > 50 && currentScrollY > lastScrollY) {
            isHidden = true;
        } else {
            isHidden = false;
        }
        lastScrollY = currentScrollY;
     "
     :style="isHidden ? `transform: translateY(-${81 + bannerHeight}px);` : 'transform: translateY(0px);'"
     class="w-[100vw] ml-[calc(50%-50vw)] sticky top-[81px] z-30 transition-transform duration-300 ease-in-out flex flex-col shadow-sm border-b border-stone-200 bg-white">
    
    {{-- ========================================== --}}
    {{-- ZONA SUPERIOR: BANNER (PORTADA + SOCIALS)  --}}
    {{-- ========================================== --}}
    <div x-ref="bannerZone" class="relative w-full h-auto min-h-[7rem] md:min-h-[9rem] bg-zinc-950 overflow-hidden shrink-0">
        {{-- Foto de Portada Horizontal (16:9) --}}
        @if($coverImage)
            <img src="{{ asset('storage/' . $coverImage) }}" class="absolute inset-0 w-full h-full object-cover opacity-40 blur-[1px] scale-105 transition-transform duration-700" alt="Cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/50 to-black/20"></div>

        {{-- BOTÓN COMPARTIR --}}
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
                class="absolute top-4 right-4 z-20 flex items-center gap-2 px-3 py-1.5 bg-black/40 hover:bg-black/60 backdrop-blur-md text-white text-xs font-bold rounded-xl border border-white/10 transition-all duration-200 active:scale-95 shadow-sm focus:outline-none focus:ring-2 focus:ring-white/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
            <span class="hidden sm:inline">Compartir</span>
        </button>

        {{-- Contenido del Banner --}}
        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-end pb-4 pt-8 gap-3 md:gap-5 h-full">
            
            {{-- Logo Cuadrado (Avatar 1:1) --}}
            <div class="shrink-0">
                @if($avatarImage)
                    <img src="{{ asset('storage/' . $avatarImage) }}" alt="Logo" class="h-16 w-16 md:h-20 md:w-20 rounded-2xl object-cover border-2 md:border-4 border-white shadow-2xl bg-white">
                @else
                    <div class="h-16 w-16 md:h-20 md:w-20 rounded-2xl bg-zinc-900 border-2 md:border-4 border-white text-white flex items-center justify-center font-black text-2xl md:text-3xl shadow-2xl">
                        {{ substr($studioName, 0, 1) }}
                    </div>
                @endif
            </div>

            {{-- Título, Badges, Enlaces Web y Redes Sociales --}}
            <div class="flex-1 min-w-0 pb-0.5">
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight drop-shadow-md leading-none truncate mb-2">
                    {{ $studioName }}
                </h1>

                {{-- Badge de Beneficio Founder o Cuenta --}}
                @php
                    $isFounder = $studio && $studio->isFounderActive();
                @endphp
                <a href="{{ route('account.billing', $subdomain) }}"
                   class="inline-flex self-start items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-offset-black/50 mb-2.5
                          @if($isFounder)
                              bg-emerald-700/60 text-emerald-100 border-emerald-400/30 hover:bg-emerald-600/80 focus:ring-emerald-400
                          @else
                              bg-zinc-800/80 text-zinc-200 border-zinc-500/30 hover:bg-zinc-700/80 focus:ring-zinc-400
                          @endif">
                    @if($isFounder)
                        <span>👑</span>
                        <span>Founder · {{ $studio->founder_cycles_remaining }} meses</span>
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        <span>Facturación por Uso</span>
                    @endif
                    <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>

                {{-- Contenedor Flex: Enlaces Web + Íconos Sociales --}}
                <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4 pr-2">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                        {{-- Enlace Web --}}
                        <a href="{{ $studioUrl }}" target="_blank" class="flex items-center gap-1.5 text-xs md:text-sm font-medium text-zinc-300 hover:text-white transition-colors drop-shadow w-fit group">
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4 text-stone-400 group-hover:text-rose-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            <span class="truncate">{{ $subdomain }}.{{ $domain }}</span>
                        </a>

                        {{-- Enlace Maps (Condicional) --}}
                        @if($studio && $studio->address)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($studio->address) }}" target="_blank" class="flex items-center gap-1.5 text-xs md:text-sm font-medium text-zinc-300 hover:text-white transition-colors drop-shadow w-fit group">
                                <svg class="w-3.5 h-3.5 md:w-4 md:h-4 text-stone-400 group-hover:text-rose-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="truncate max-w-[180px] md:max-w-xs">{{ $studio->address }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- BOTONERA DE REDES SOCIALES ACTIVAS --}}
                    @if($studio && ($studio->instagram_url || $studio->tiktok_url || $studio->youtube_url))
                        <div class="flex items-center gap-1.5">
                            @if($studio->instagram_url)
                                <a href="{{ $studio->instagram_url }}" target="_blank" 
                                   class="p-1.5 bg-white/10 hover:bg-gradient-to-tr hover:from-amber-500 hover:via-red-500 hover:to-purple-600 text-stone-300 hover:text-white rounded-lg backdrop-blur-md transition-all duration-200 active:scale-95 shadow-sm" title="Instagram">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            @endif
                            @if($studio->tiktok_url)
                                <a href="{{ $studio->tiktok_url }}" target="_blank" 
                                   class="p-1.5 bg-white/10 hover:bg-black text-stone-300 hover:text-white rounded-lg backdrop-blur-md transition-all duration-200 active:scale-95 shadow-sm" title="TikTok">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
                                </a>
                            @endif
                            @if($studio->youtube_url)
                                <a href="{{ $studio->youtube_url }}" target="_blank" 
                                   class="p-1.5 bg-white/10 hover:bg-red-600 text-stone-300 hover:text-white rounded-lg backdrop-blur-md transition-all duration-200 active:scale-95 shadow-sm" title="YouTube">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ZONA INFERIOR: TABS (PERMANECE ANCLADA EN TOP 0 AL BAJAR)   --}}
    {{-- ============================================================ --}}
    <div class="w-full bg-white/90 backdrop-blur-md shrink-0">
        <div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8"> 
            
            <nav class="flex w-full justify-between overflow-x-auto hide-scrollbar" aria-label="Tabs">
                
                <a href="{{ route('dashboard', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('dashboard') ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="hidden md:inline">Panel General</span>
                </a>

                <a href="{{ route('account.index', $subdomain) }}" 
                    class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                           {{ request()->routeIs('account.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('account.*') ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="hidden md:inline">Cuenta</span>
                </a>

                <a href="{{ route('students.index', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('students.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('students.*') ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="hidden md:inline">Alumnas/os</span>
                </a>

                <a href="{{ route('teachers.index', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('teachers.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('teachers.*') ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                    <span class="hidden md:inline">Profesores</span>
                </a>

                <a href="{{ route('workshops.index', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('workshops.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('workshops.*') ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="hidden md:inline">Talleres</span>
                </a>

                <a href="{{ route('promotions.index', $subdomain) }}" 
                   class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                          {{ request()->routeIs('promotions.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('promotions.*') ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <span class="hidden md:inline">Promociones</span>
                </a>

                <a href="{{ route('trainingmonth.index', $subdomain) }}" 
                    class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-bold text-[13px] md:text-sm transition-all duration-200 flex items-center justify-center gap-2
                        {{ request()->routeIs('trainingmonth.*', 'sessions.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 md:w-4 md:h-4 {{ request()->routeIs('trainingmonth.*', 'sessions.*') ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
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
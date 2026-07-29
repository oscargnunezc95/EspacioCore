{{-- ============================================================ --}}
{{-- COMPONENTE: _studio-nav — Hero + Submenú de Navegación     --}}
{{-- Fusiona hero.blade.php y tabs.blade.php en un solo archivo --}}
{{-- Recibe: $studio, $domain, $fullStudioUrl, $activeTab       --}}
{{-- ============================================================ --}}

{{-- 1. HERO: FOTO DE PORTADA / COVER --}}
<div class="bg-white w-full border-b border-stone-200">
    <div class="w-full max-w-6xl mx-auto">
        <div class="relative w-full h-48 md:h-[350px] bg-stone-100 md:rounded-b-3xl overflow-hidden shadow-sm border-x border-b border-stone-200/60">
            @if($studio->coverImageUrl)
                <img src="{{ asset('storage/' . $studio->coverImageUrl) }}"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 hover:scale-105"
                     alt="Portada de {{ $studio->name }}">
            @else
                <div class="absolute inset-0 bg-gradient-to-r from-stone-200 via-stone-100 to-stone-200"></div>
            @endif
        </div>
    </div>

    {{-- 2. INFORMACIÓN DEL ESTUDIO (Superpuesta) --}}
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center md:items-end md:justify-between pb-6 relative">

            {{-- Bloque Izquierdo: Avatar + Título y Datos --}}
            <div class="flex flex-col md:flex-row items-center md:items-end gap-4 md:gap-6 w-full md:w-auto">

                {{-- Foto de Perfil / Logo (Sube con margen negativo) --}}
                <div class="relative -mt-16 md:-mt-12 shrink-0 z-10">
                    @if($studio->avatarImageUrl)
                        <img src="{{ asset('storage/' . $studio->avatarImageUrl) }}" alt="Logo de {{ $studio->name }}"
                             class="w-32 h-32 md:w-[168px] md:h-[168px] rounded-[2rem] border-4 border-white shadow-md object-cover bg-white">
                    @else
                        <div class="w-32 h-32 md:w-[168px] md:h-[168px] rounded-[2rem] border-4 border-white shadow-md bg-stone-900 text-white flex items-center justify-center text-5xl md:text-7xl font-black">
                            {{ substr($studio->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                {{-- Título y Metadatos --}}
                <div class="text-center md:text-left mb-1 md:mb-3">
                    <h1 class="text-3xl md:text-4xl font-black ">
                        {{ $studio->name }}
                    </h1>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-y-1 gap-x-4 mt-2.5">
                        {{-- Indicador de Subdominio --}}
                        <a href="{{ $fullStudioUrl }}" class="flex items-center gap-1.5 text-stone-600 hover:text-red-600 font-bold text-sm transition-colors group">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="underline decoration-transparent group-hover:decoration-red-200 transition-all">{{ $studio->subdomain }}.{{ $domain }}</span>
                        </a>

                        {{-- Enlace a Google Maps (Si existe dirección) --}}
                        @if($studio->address)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $studio->latitude }},{{ $studio->longitude }}" target="_blank"
                               class="flex items-center gap-1 text-stone-500 hover:text-stone-900 font-medium text-xs md:text-sm transition-colors group">
                                <svg class="w-4 h-4 text-stone-400 group-hover:text-red-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="truncate max-w-[220px] sm:max-w-xs underline decoration-transparent group-hover:decoration-stone-300 transition-all">{{ $studio->address }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- 3. NAVEGACIÓN POR PESTAÑAS (Sticky al hacer scroll + Íconos responsivos) --}}
<div x-data="{ isNavSticky: false }"
     @scroll.window="isNavSticky = window.scrollY > 350"
     class="w-full bg-white border-b border-stone-200 z-40 transition-shadow duration-200"
     :class="isNavSticky ? 'sticky top-0 shadow-sm' : 'relative'">

    <div class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8">
        <nav class="flex w-full justify-around sm:justify-center gap-2 sm:gap-6 overflow-x-auto hide-scrollbar" aria-label="Navegación del estudio">

            {{-- Tab 1: Información --}}
            <a href="{{ url('/') }}"
               title="Información general"
               class="flex-1 sm:flex-initial flex items-center justify-center gap-2 py-4 px-3 sm:px-5 font-bold text-sm transition-all duration-200 outline-none whitespace-nowrap border-b-[3px] {{ $activeTab === 'perfil' ? 'text-red-600 border-red-600 bg-red-50/30' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800 border-transparent' }}">
                <svg class="w-5 h-5 shrink-0 {{ $activeTab === 'perfil' ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="hidden sm:inline">Información</span>
            </a>

            {{-- Tab 2: Talleres --}}
            <a href="{{ url('/talleres') }}"
               title="Catálogo de talleres"
               class="flex-1 sm:flex-initial flex items-center justify-center gap-2 py-4 px-3 sm:px-5 font-bold text-sm transition-all duration-200 outline-none whitespace-nowrap border-b-[3px] {{ $activeTab === 'talleres' ? 'text-red-600 border-red-600 bg-red-50/30' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800 border-transparent' }}">
                <svg class="w-5 h-5 shrink-0 {{ $activeTab === 'talleres' ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span class="hidden sm:inline">Talleres</span>
            </a>

            {{-- Tab 3: Promos y Packs --}}
            <a href="{{ url('/promos') }}"
               title="Promociones y packs de clases"
               class="flex-1 sm:flex-initial flex items-center justify-center gap-2 py-4 px-3 sm:px-5 font-bold text-sm transition-all duration-200 outline-none whitespace-nowrap border-b-[3px] {{ $activeTab === 'promos' ? 'text-red-600 border-red-600 bg-red-50/30' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800 border-transparent' }}">
                <svg class="w-5 h-5 shrink-0 {{ $activeTab === 'promos' ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <span class="hidden sm:inline">Promos y Packs</span>
            </a>

            {{-- Tab 4: Calendario --}}
            <a href="{{ url('/calendario') }}"
               title="Horarios y calendario"
               class="flex-1 sm:flex-initial flex items-center justify-center gap-2 py-4 px-3 sm:px-5 font-bold text-sm transition-all duration-200 outline-none whitespace-nowrap border-b-[3px] {{ $activeTab === 'clases' || $activeTab === 'calendario' ? 'text-red-600 border-red-600 bg-red-50/30' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800 border-transparent' }}">
                <svg class="w-5 h-5 shrink-0 {{ $activeTab === 'clases' || $activeTab === 'calendario' ? 'text-red-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                <span class="hidden sm:inline">Calendario</span>
            </a>

        </nav>
    </div>
</div>
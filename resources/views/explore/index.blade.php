<x-app-layout
    :metaTitle="$seo['title']"
    :metaDescription="$seo['description']"
    :canonicalUrl="$seo['canonical']"
    ogType="website"
    :metaRobots="$seo['page'] > 1 ? 'noindex, follow' : 'index, follow'"
>
    <x-slot name="structuredData">
        <script type="application/ld+json">
        {
        "@@context": "https://schema.org",
        "@@type": "ItemList",
        "itemListElement": [
            @foreach($sessions as $i => $session)
            {
            "@type": "ListItem",
            "position": {{ $i + 1 }},
            "item": {
                "@@type": "Event",
                "name": "{{ $session->workshop->name }}",
                "startDate": "{{ \Carbon\Carbon::parse($session->date)->toIso8601String() }}",
                "location": {
                "@@type": "Place",
                "name": "{{ $session->workshop->studio->name }}",
                "address": {
                    "@@type": "PostalAddress",
                    "addressLocality": "{{ $session->workshop->city ?? $session->workshop->studio->city }}",
                    "addressRegion": "{{ $session->workshop->region ?? $session->workshop->studio->region }}",
                    "addressCountry": "{{ $session->workshop->country ?? $session->workshop->studio->country }}"
                }
                },
                "performer": {
                "@@type": "Person",
                "name": "{{ $session->workshop->teacher ? trim($session->workshop->teacher->first_name . ' ' . $session->workshop->teacher->last_name) : 'Por asignar' }}"
                },
                "offers": {
                "@@type": "Offer",
                "price": "{{ $session->workshop->prices->where('class_count', 1)->first()->price ?? 0 }}",
                "priceCurrency": "CLP"
                }
            }
            }@if(!$loop->last),@endif
            @endforeach
        ]
        }
        </script>
    </x-slot>

    {{-- NUEVO LAYOUT FLEXBOX: Flujo natural sin overlaps --}}
    <div class="relative min-h-screen flex flex-col lg:flex-row w-full">

        {{-- ============================================================ --}}
        {{-- SIDEBAR STICKY (Desktop: lg+) — Integrado en el flujo Flex   --}}
        {{-- ============================================================ --}}
        <aside class="hidden lg:block w-72 shrink-0 bg-white border-r border-red-100/50 z-30 relative shadow-sm">
            <div class="sticky top-0 h-screen overflow-y-auto pt-24 pb-8 px-5 custom-scrollbar">
                <h2 class="text-lg font-black text-stone-900 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filtros
                </h2>
                @include('explore.partials._filters')
            </div>
        </aside>

        {{-- ============================================================ --}}
        {{-- CONTENEDOR PRINCIPAL: Ocupa el espacio restante del Flex     --}}
        {{-- ============================================================ --}}
        <div class="flex-1 min-w-0">
            <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">

                {{-- HERO: Vibrante, con personalidad --}}
                <div class="text-center mb-8 md:mb-12 relative">
                    <h1 class="text-3xl md:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                        <span>
                            @if($seo['city'] && $seo['discipline'])
                                Clases de {{ $seo['discipline'] }} en {{ $seo['city'] }}
                            @elseif($seo['city'] && $seo['area'])
                                Clases de {{ $seo['area'] }} en {{ $seo['city'] }}
                            @elseif($seo['city'])
                                Talleres y Clases en {{ $seo['city'] }}
                            @elseif($seo['discipline'])
                                Clases de {{ $seo['discipline'] }}
                            @elseif($seo['area'])
                                Clases de {{ $seo['area'] }}
                            @else
                                Descubre tu próxima clase
                            @endif
                        </span>
                    </h1>

                    <div class="flex items-center justify-center gap-2 mt-4 mb-5"></div>

                    <p class="text-base md:text-lg font-medium max-w-2xl mx-auto leading-relaxed
                              @if($seo['total'] > 0) text-stone-600 @else text-stone-500 @endif">
                        @if($seo['total'] > 0)
                            <span class="font-black text-red-700">{{ $seo['total'] }}</span>
                            {{ $seo['total'] == 1 ? 'clase encontrada' : 'clases encontradas' }}.
                            <span class="text-stone-500">Encuentra y reserva sesiones en los mejores estudios cerca de ti.</span>
                        @else
                            Intenta ajustando los filtros de búsqueda para ver más opciones.
                        @endif
                    </p>
                </div>

                {{-- BREADCRUMBS --}}
                @if(count($breadcrumbs) > 0)
                <nav class="mb-6" aria-label="Breadcrumb">
                    <ol class="flex flex-wrap items-center gap-1 text-xs font-medium text-stone-400" itemscope itemtype="https://schema.org/BreadcrumbList">
                        @foreach($breadcrumbs as $index => $crumb)
                            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="flex items-center">
                                @if(!$loop->last)
                                    <a itemprop="item" href="{{ $crumb['url'] }}" class="hover:text-red-600 transition-colors">
                                        <span itemprop="name">{{ $crumb['label'] }}</span>
                                    </a>
                                    <meta itemprop="position" content="{{ $index + 1 }}" />
                                    <span class="mx-1 text-stone-300">/</span>
                                @else
                                    <span itemprop="name" class="text-stone-700 font-bold" aria-current="page">{{ $crumb['label'] }}</span>
                                    <meta itemprop="position" content="{{ $index + 1 }}" />
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
                @endif

                {{-- ============================================================ --}}
                {{-- BOTÓN DE FILTROS MÓVIL + DRAWER OFF-CANVAS --}}
                {{-- ============================================================ --}}
                <div x-data="{ openFilters: false }" class="mb-6 lg:hidden">
                    <div class="flex justify-end mb-4">
                        <button @click="openFilters = true" type="button"
                            class="w-full bg-white border border-red-200 text-red-700 font-black py-3.5 px-4 rounded-2xl shadow-sm flex items-center justify-center gap-2 active:scale-95 transition-all duration-200 hover:bg-red-50">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filtros de Búsqueda
                        </button>
                    </div>

                    {{-- Overlay --}}
                    <div x-show="openFilters" x-transition.opacity.duration.300ms @click="openFilters = false"
                        class="fixed inset-0 bg-stone-900/60 z-[60]" style="display: none;"></div>

                    {{-- Drawer --}}
                    <div :class="openFilters ? 'translate-x-0' : 'translate-x-full'"
                        class="fixed inset-y-0 right-0 z-[70] w-[85%] max-w-sm bg-white shadow-2xl transition-transform duration-300 ease-in-out flex flex-col"
                        x-cloak>

                        <div class="flex items-center justify-between p-5 border-b border-red-50 shrink-0">
                            <h2 class="text-xl font-black text-stone-900">Filtros</h2>
                            <button type="button" @click="openFilters = false"
                                class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-50 rounded-full transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="p-5 overflow-y-auto flex-1 custom-scrollbar">
                            @include('explore.partials._filters')
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- MAPA CONTENEDOR LEAFLET (SIEMPRE VISIBLE E INTEGRADO) --}}
                {{-- ============================================================ --}}
                <div id="mapContainer" class="relative mb-10 w-full rounded-3xl overflow-hidden shadow-sm border border-stone-200 bg-white">
                    
                    {{-- Botón flotante integrado dentro del mapa --}}
                    <div class="absolute top-4 right-4 z-[69]">
                        <button type="button" onclick="toggleAllPopups()" id="btnToggleAllPopups"
                            class="text-xs font-black uppercase tracking-widest text-stone-800 bg-white/95 hover:bg-white backdrop-blur-md px-4 py-2.5 rounded-xl border border-stone-200 shadow-md hover:shadow-lg transition-all active:scale-95">
                            Cerrar Todas las Tarjetas
                        </button>
                    </div>

                    {{-- Lienzo del Mapa --}}
                    <div id="exploreMap" class="w-full h-[480px] z-10 bg-stone-100"></div>
                </div>

                {{-- ============================================================ --}}
                {{-- CONTADOR DE RESULTADOS --}}
                {{-- ============================================================ --}}
                <div class="flex items-center justify-between mb-6">
                    <p class="text-xs font-bold text-stone-400 uppercase tracking-wider">
                        @if($seo['total'] > 0)
                            Mostrando <span class="text-stone-700">{{ $sessions->count() }}</span> de <span class="text-stone-700">{{ $seo['total'] }}</span>
                        @endif
                    </p>
                </div>

                {{-- ============================================================ --}}
                {{-- GRID DE CLASES: Cards con alma artística --}}
                {{-- ============================================================ --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 md:gap-6">
                    @forelse($sessions as $session)
                        @php
                            $maxSpots     = $session->max_spots ?? 99;
                            $pendingCount = $session->pending_count ?? 0;
                            $available    = $session->available_spots ?? $maxSpots;
                            $isFull       = $available <= 0;
                            $almostFull   = $available <= 3 && $available > 0;
                        @endphp

                        <div class="relative bg-white/80 rounded-3xl overflow-hidden flex flex-col transform-gpu isolate transition-all duration-500 ease-out {{ $isFull ? 'bg-stone-50/80 opacity-75' : 'holo-white-card group/card cursor-pointer' }}">
                            @php
                                $imageUrl = $session->workshop->image_path
                                            ? asset('storage/' . $session->workshop->image_path)
                                            : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=dc2626&background=fef2f2&size=512';

                                $studioLogo = $session->workshop->studio->icon_path ?? $session->workshop->studio->logo_path ?? null;
                                $studioImageUrl = $studioLogo
                                            ? asset('storage/' . $studioLogo)
                                            : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->studio->name).'&color=ffffff&background=991b1b&size=128';

                                $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
                                $protocol = request()->secure() ? 'https://' : 'http://';
                                $studioUrl = $protocol . $session->workshop->studio->subdomain . '.' . $domain;

                                $mapUrl = (!empty($session->workshop->latitude) && !empty($session->workshop->longitude))
                                    ? "https://www.google.com/maps/search/?api=1&query={$session->workshop->latitude},{$session->workshop->longitude}"
                                    : (!empty($session->workshop->studio->latitude) && !empty($session->workshop->studio->longitude)
                                        ? "https://www.google.com/maps/search/?api=1&query={$session->workshop->studio->latitude},{$session->workshop->studio->longitude}"
                                        : "https://www.google.com/maps/search/?api=1&query=" . urlencode($session->workshop->address ?? $session->workshop->studio->address ?? ''));

                                $modalData = json_encode([
                                    'title'         => $session->workshop->name,
                                    'studio'        => $session->workshop->studio->name,
                                    'studio_url'    => $studioUrl,
                                    'teacher'       => $session->workshop->teacher ? trim($session->workshop->teacher->first_name . ' ' . $session->workshop->teacher->last_name) : 'Por asignar',
                                    'teacher_email' => $session->workshop->teacher->email ?? '',
                                    'date'          => \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F'),
                                    'time'          => \Carbon\Carbon::parse($session->start_time)->format('H:i'),
                                    'image'         => $imageUrl,
                                    'address'       => $session->workshop->address ?? $session->workshop->studio->address ?? 'Dirección no especificada',
                                    'description'   => $session->workshop->description ?? 'Sin descripción disponible.',
                                    'video_url'     => $session->workshop->embed_video_url,
                                    'map_url'       => $mapUrl,
                                ]);
                            @endphp

                            {{-- Imagen con overlay artístico --}}
                            <div class="h-44 bg-stone-100 relative overflow-hidden cursor-pointer transform-gpu" onclick="openDetailModal({{ $modalData }})">
                                <img src="{{ $imageUrl }}"
                                     alt="Clase"
                                     width="400"
                                     height="250"
                                     @if($loop->first)
                                         fetchpriority="high"
                                         decoding="sync"
                                     @else
                                         loading="lazy"
                                         decoding="async"
                                     @endif
                                     class="w-full h-full object-cover {{ $isFull ? 'opacity-50' : 'opacity-90' }} group-hover/card:opacity-100 group-hover/card:scale-110 transition-all duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-red-900/70 via-red-900/10 to-transparent {{ $isFull ? 'opacity-80' : 'opacity-50 group-hover/card:opacity-70' }} transition-opacity duration-500"></div>

                                {{-- Ribbon "Clase Llena" --}}
                                @if ($isFull)
                                <div class="absolute top-0 right-0 w-28 h-28 overflow-hidden z-10 pointer-events-none">
                                    <div class="absolute top-[13px] -right-[32px] w-40 bg-gradient-to-r from-rose-500 to-rose-600 text-white text-[9px] font-black uppercase tracking-[0.2em] py-1 text-center rotate-45 shadow-lg">
                                        Lleno
                                    </div>
                                </div>
                                @endif

                                {{-- Badge de categoría --}}
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="inline-block px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest
                                         {{ $isFull ? 'bg-white/60 text-stone-500' : 'bg-white/90 backdrop-blur-sm text-red-600 shadow-sm' }}
                                         border {{ $isFull ? 'border-white/30' : 'border-white/50' }}">
                                        {{ $session->workshop->discipline->area->name ?? 'Clase' }}
                                    </span>
                                </div>

                                {{-- Logo del estudio --}}
                                <div class="absolute bottom-3 right-3 {{ $isFull ? '' : 'group-hover/card:scale-110 group-hover/card:rotate-3' }} transition-all duration-500 z-10">
                                    <div class="relative w-11 h-11 rounded-2xl bg-white shadow-lg border-2 {{ $isFull ? 'border-stone-200 opacity-70' : 'border-white' }} overflow-hidden transform -rotate-2 group-hover/card:rotate-0 transition-transform duration-500" title="{{ $session->workshop->studio->name }}">
                                        <img src="{{ $studioImageUrl }}" alt="Logo Estudio" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>

                            {{-- Contenido de la card --}}
                            <div class="p-5 flex-1 flex flex-col relative z-20">
                                <div class="flex justify-between items-start mb-3 cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                                    <h3 class="text-base md:text-lg font-black text-stone-900 leading-tight group-hover/card:text-red-700 transition-colors duration-300 line-clamp-2">
                                        {{ $session->workshop->name }}
                                    </h3>
                                </div>

                                <div class="space-y-2.5 mt-auto cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                                    <div class="flex items-center gap-2.5 text-sm font-medium text-stone-500">
                                        <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <span class="font-bold text-stone-700">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('D d M') }} · {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
                                    </div>
                                    <div class="flex items-center gap-2.5 text-sm font-medium text-stone-500">
                                        <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <span class="font-bold text-stone-700 truncate">{{ $session->workshop->studio->name }}</span>
                                    </div>
                                </div>

                                {{-- Indicador de cupos --}}
                                <div class="mt-4 pt-4 border-t border-stone-100">
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            @if ($isFull)
                                                <span class="font-black text-rose-600 flex items-center gap-1.5 bg-rose-50 px-2.5 py-1.5 rounded-xl border border-rose-100">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                    Lleno
                                                </span>
                                            @elseif ($almostFull)
                                                <span class="font-black text-amber-600 bg-amber-50 px-2.5 py-1.5 rounded-xl border border-amber-100 flex items-center gap-1.5">
                                                    <span class="relative flex h-2 w-2">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                                    </span>
                                                    ¡Quedan {{ $available }}!
                                                </span>
                                            @else
                                                <span class="font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-100 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    {{ $available }} {{ $available === 1 ? 'cupo' : 'cupos' }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($pendingCount > 0)
                                            <span class="font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-lg text-[11px]">
                                                {{ $pendingCount }} {{ $pendingCount === 1 ? '❤️' : '❤️❤️' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Precio + Botón de acción --}}
                                @php
                                    $dbSelections = $dbSelectionsBySession[$session->id] ?? [];
                                    $enrolledCount = count($dbSelections);
                                    $isTitularPaid = isset($dbSelections['titular']) && $dbSelections['titular'] === 'paid';
                                    $hasDependents = auth()->check() && auth()->user()->dependents->count() > 0;
                                @endphp

                                <div class="mt-5 pt-4 border-t border-stone-100 flex items-center justify-between gap-3">
                                    <div class="shrink-0">
                                        @php
                                            $dropInPrice = $session->workshop->prices->where('class_count', 1)->first();
                                        @endphp
                                        @if($dropInPrice)
                                            <p class="text-[10px] text-stone-900 font-bold uppercase tracking-wider">Clase suelta</p>
                                            <p class="text-lg font-black text-stone-900">${{ number_format($dropInPrice->price, 0, ',', '.') }}</p>
                                        @else
                                            <p class="text-[10px] text-stone-400 font-bold uppercase tracking-wider">Desde</p>
                                            <p class="text-sm font-black text-stone-900">Ver Planes</p>
                                        @endif
                                    </div>

                                    @if($isFull)
                                        <button disabled class="flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 bg-stone-100 text-stone-400 cursor-not-allowed border border-stone-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 00-2-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Lleno
                                        </button>
                                    @elseif(auth()->check() && !$hasDependents && $isTitularPaid)
                                        <button disabled class="flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white cursor-not-allowed opacity-90 shadow-md border-0 transition-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Pagada ✓
                                        </button>
                                    @else
                                        <button type="button" onclick="handleInterestClick({{ $session->id }}, this)"
                                                data-db-selections="{{ json_encode($dbSelections) }}"
                                                class="interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 active:scale-95 flex items-center justify-center shadow-sm
                                                {{ $enrolledCount > 0 ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white border-0 hover:from-red-500 hover:to-rose-500 hover:shadow-md hover:shadow-red-200 group/btn' : 'bg-stone-100 text-stone-700 border border-stone-200 hover:bg-gradient-to-r hover:from-red-50 hover:to-orange-50 hover:border-red-200 hover:text-red-700 relative z-30' }}">
                                            @if($enrolledCount > 0)
                                                <div class="relative flex items-center justify-center w-full">
                                                    <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        {{ $enrolledCount === 1 ? ($hasDependents ? '1 en Portal' : 'En Portal') : $enrolledCount.' en Portal' }}
                                                    </span>
                                                    <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">
                                                        @if($hasDependents)
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.762z"></path></svg> Modificar
                                                        @else
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover
                                                        @endif
                                                    </span>
                                                </div>
                                            @else
                                                <span class="flex items-center gap-1.5">Me Interesa</span>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Estado vacío --}}
                        <div class="col-span-full py-20 text-center">
                            <div class="inline-flex flex-col items-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-red-100 to-rose-100 mb-6 shadow-inner">
                                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <h3 class="text-xl font-black text-stone-800 mb-2">¡No encontramos clases esta vez!</h3>
                                <p class="text-stone-500 max-w-md leading-relaxed">
                                    El ritmo no se detiene. Intenta ajustando los filtros o cambiando la ciudad para descubrir nuevas experiencias.
                                </p>
                                <a href="{{ route('explore') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold rounded-2xl shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 hover:from-red-500 hover:to-rose-500 transition-all duration-300 active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Limpiar filtros
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Paginación --}}
                <div class="mt-10">{{ $sessions->links() }}</div>

            </div>{{-- Fin max-w-7xl --}}
        </div>{{-- Fin Flex-1 min-w-0 --}}
    </div>{{-- Fin Nuevo Layout Flexbox --}}

    {{-- ============================================================ --}}
    {{-- BARRA FLOTANTE DE CONFIRMACIÓN --}}
    {{-- ============================================================ --}}
    <div id="floating-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 translate-y-24 opacity-0 transition-all duration-500 z-50 pointer-events-none">
        <div class="bg-gradient-to-r from-red-900 to-rose-900 text-white px-6 py-4 rounded-full shadow-2xl shadow-red-500/30 flex items-center gap-6 border border-white/10">
            <div class="flex items-center gap-3">
                <span id="selected-count" class="bg-emerald-400 text-red-900 font-black text-sm w-7 h-7 flex items-center justify-center rounded-full shadow-inner">0</span>
                <span class="font-bold text-sm">Cambios detectados</span>
            </div>
            <button onclick="confirmReservations()" id="floating-confirm-btn"
                class="bg-gradient-to-r from-emerald-400 to-teal-400 hover:from-emerald-300 hover:to-teal-300 text-red-900 px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-300 active:scale-95 flex items-center gap-2 pointer-events-auto shadow-lg shadow-emerald-500/30">
                Confirmar Cambios
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </button>
        </div>
    </div>

    @auth
        {{-- MINI CARRITO FLOTANTE --}}
        <div class="fixed bottom-6 right-6 z-[60]">
            <div id="miniCartPanel" class="hidden absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-red-100 overflow-hidden transition-all transform origin-bottom-right opacity-0 scale-95">
                <div class="p-5 bg-gradient-to-r from-red-700 to-rose-700 text-white flex justify-between items-center">
                    <div>
                        <h4 class="font-black text-lg leading-none">Tus Reservas</h4>
                        <p class="text-xs text-red-200 mt-1">Pendientes de pago</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->pending_reservations_count > 0)
                            <span class="text-sm bg-orange-400 text-white shadow-inner px-3 py-1 rounded-full font-black">
                                {{ auth()->user()->pending_reservations_count }}
                            </span>
                        @endif
                        <button onclick="toggleMiniCart()" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded-full transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 max-h-64 overflow-y-auto custom-scrollbar">
                    @if(auth()->user()->pending_reservations_count > 0)
                        <p class="text-sm text-stone-500 mb-4 leading-relaxed">
                            Tienes cupos reservados que aún no han sido pagados.
                            <strong class="text-stone-800">Asegura tu lugar antes de que se llenen los cupos.</strong>
                        </p>
                        <div class="bg-amber-50 border border-amber-100 p-3.5 rounded-2xl flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-xs text-amber-800 font-medium leading-relaxed">Agregar las clases a Mis Reservas hará que las puedas ver en tu portal de estudiante. Sin embargo, el cupo solo se asegurará al completar el pago.</p>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <span class="text-4xl block mb-3">🛒</span>
                            <p class="text-sm font-bold text-stone-400">No tienes reservas pendientes.</p>
                        </div>
                    @endif
                </div>

                <div class="p-4 border-t border-stone-100 bg-stone-50/50">
                    <a href="{{ route('cart.index') }}"
                        class="w-full {{ auth()->user()->pending_reservations_count > 0 ? 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 shadow-lg shadow-red-200' : 'bg-stone-200 pointer-events-none' }} text-white font-bold py-3.5 rounded-2xl transition-all duration-300 active:scale-95 text-sm flex items-center justify-center gap-2">
                        Ir a Pagar Mis Clases
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </div>

            <button onclick="toggleMiniCart()" id="btnMiniCart"
                class="relative bg-gradient-to-br from-red-600 to-rose-700 text-white p-4 rounded-full shadow-[0_10px_40px_-10px_rgba(220,38,38,0.5)] hover:shadow-[0_15px_50px_-10px_rgba(220,38,38,0.7)] hover:scale-110 transition-all duration-300 active:scale-95 border border-white/10 focus:outline-none focus:ring-4 focus:ring-red-300/50 group">
                <svg class="w-6 h-6 transform group-hover:-rotate-12 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                @if(auth()->user()->pending_reservations_count > 0)
                    <span class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-orange-400 border-2 border-white text-[11px] font-black text-white shadow-md animate-pulse">
                        {{ auth()->user()->pending_reservations_count }}
                    </span>
                @endif
            </button>
        </div>
    @endauth

    {{-- ============================================================ --}}
    {{-- MODAL DE DETALLES DEL TALLER --}}
    {{-- ============================================================ --}}
    <div id="detailModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-stone-900/80 backdrop-blur-sm transition-opacity" onclick="closeDetailModal()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md md:max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[95vh]" id="detailModalCard">
            <div class="h-40 sm:h-48 w-full bg-stone-200 relative shrink-0">
                <img id="m_image" src="" alt="Cover" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-red-900/70 via-red-900/10 to-transparent"></div>
                <button onclick="closeDetailModal()" class="absolute top-4 right-4 p-2 text-red-700 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full transition-colors focus:outline-none shadow-md z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 md:p-8 overflow-y-auto flex-1 custom-scrollbar">
                <div class="mb-6">
                    <a href="#" id="m_studio_link" target="_blank"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 transition-colors text-[10px] font-black rounded-lg tracking-widest uppercase mb-3">
                        <span id="m_studio">Estudio</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                    <h3 id="m_title" class="text-2xl font-black text-stone-900 leading-tight">Clase</h3>
                </div>

                <div id="m_video_container" class="hidden mb-6 rounded-2xl overflow-hidden shadow-md border border-red-100 bg-stone-900 relative group transition-all duration-300 mx-auto">
                    <iframe id="m_video_frame" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>

                <div id="m_description_container" class="hidden mb-8">
                    <h4 class="text-xs font-black text-red-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <span class="w-1 h-4 bg-gradient-to-b from-red-500 to-rose-500 rounded-full"></span>
                        Acerca de la clase
                    </h4>
                    <p id="m_description" class="text-sm text-stone-600 leading-relaxed whitespace-pre-line"></p>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center gap-3 text-stone-600 bg-red-50/50 p-3 rounded-2xl border border-red-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-red-100 text-red-500 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p id="m_date" class="text-sm font-bold text-stone-900 capitalize">Fecha</p>
                            <p id="m_time" class="text-xs font-medium text-stone-500">Hora</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-stone-600 bg-emerald-50/50 p-3 rounded-2xl border border-emerald-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-emerald-100 text-emerald-500 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-0.5">Profesor/a</p>
                            <p id="m_teacher" class="text-sm font-bold text-stone-900 leading-tight truncate">Nombre</p>
                            <a href="#" id="m_teacher_email" class="hidden text-[11px] font-medium text-red-600 hover:text-red-800 transition-colors mt-0.5 truncate"></a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 text-stone-600 bg-orange-50/50 p-3 rounded-2xl border border-orange-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-orange-100 text-orange-500 mt-1 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-0.5">Ubicación</p>
                            <p id="m_address" class="text-sm font-bold text-stone-900 mb-2 leading-tight">Dirección</p>
                            <a href="#" id="m_map_link" target="_blank" class="inline-flex items-center text-xs font-bold text-red-600 hover:text-red-800 transition-colors">
                                Cómo llegar en Maps <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL MULTI-SELECCIÓN FAMILIAR --}}
    {{-- ============================================================ --}}
    @auth
    <div id="familySelectionModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col" id="familySelectionCard">
            <div class="p-6 border-b border-red-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-stone-900 leading-tight">¿Quiénes asistirán?<br><span class="text-sm font-medium text-stone-500">Selecciona uno o más</span></h3>
                <button onclick="closeFamilyModal()" class="text-stone-400 hover:text-stone-600 bg-stone-50 p-2 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
                <button type="button" onclick="toggleModalSelection('titular')" id="modal_opt_titular"
                    class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-red-100 hover:border-red-300 transition-all group">
                    <div class="flex flex-col text-left">
                        <span class="font-bold text-stone-900 text-sm">Yo ({{ Auth::user()->name }})</span>
                        <span class="text-[10px] font-black text-red-600 uppercase tracking-widest mt-0.5">Titular</span>
                    </div>
                    <div class="w-6 h-6 rounded-full border-2 border-stone-200 flex items-center justify-center check-icon transition-colors">
                        <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest hidden"></span>
                </button>

                @foreach($activeDependents as $dependent)
                    <button type="button" onclick="toggleModalSelection({{ $dependent->id }})" id="modal_opt_{{ $dependent->id }}"
                        class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-red-100 hover:border-emerald-300 transition-all group">
                        <div class="flex flex-col text-left">
                            <span class="font-bold text-stone-900 text-sm">{{ $dependent->first_name }} {{ $dependent->last_name }}</span>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mt-0.5">{{ $dependent->relationship ?? 'Familiar' }}</span>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 border-stone-200 flex items-center justify-center check-icon transition-colors">
                            <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest hidden"></span>
                    </button>
                @endforeach
            </div>

            <div class="p-4 bg-white border-t border-red-50 flex flex-col gap-3">
                <button onclick="saveModalSelection()"
                    class="w-full bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold py-3.5 rounded-2xl shadow-md shadow-red-200 hover:from-red-500 hover:to-rose-500 transition-all duration-300 active:scale-95 text-sm">
                    Guardar Selección
                </button>
                <a href="{{ route('profile.family.index') }}" class="text-xs font-bold text-stone-500 hover:text-red-600 transition-colors flex items-center justify-center gap-1">
                    Administrar familia
                </a>
            </div>
        </div>
    </div>
    @endauth

    @php
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
        $protocol = request()->secure() ? 'https://' : 'http://';

        $mapLocationsData = $sessions->pluck('workshop.studio')
            ->filter()
            ->unique('id')
            ->filter(function($st) {
                return !empty($st->latitude) && !empty($st->longitude);
            })
            ->map(function($studio) use ($domain, $protocol) {
                $logo = $studio->icon_path ?? $studio->logo_path ?? null;
                $avatarUrl = $logo 
                    ? asset('storage/' . $logo) 
                    : 'https://ui-avatars.com/api/?name='.urlencode($studio->name).'&color=0f766e&background=ccfbf1&size=512';
                
                $studioUrl = $protocol . $studio->subdomain . '.' . $domain;

                return [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'address' => $studio->address ?? 'Dirección no registrada',
                    'lat' => (float) $studio->latitude,
                    'lng' => (float) $studio->longitude,
                    'image' => $avatarUrl,
                    'url' => $studioUrl
                ];
            })->values();
    @endphp

    <style>
        .holo-white-card::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(0deg, transparent, transparent 30%, rgba(255,255,255,0.5));
            transform: rotate(-45deg);
            transition: all 0.5s ease;
            opacity: 0;
            pointer-events: none;
            z-index: 50;
        }

        .holo-white-card:hover,
        .holo-white-card.is-animating {
            transform: scale(1.03) translateZ(0) !important;
            box-shadow: 0 0 25px rgba(255,255,255,0.7) !important;
        }

        .holo-white-card:hover::before,
        .holo-white-card.is-animating::before {
            opacity: 1;
            transform: rotate(-45deg) translateY(100%);
        }

        .holo-white-card img {
            will-change: transform;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        .holo-white-card:hover img:not(.opacity-50),
        .holo-white-card.is-animating img:not(.opacity-50) {
            transform: scale(1.1) translateZ(0) !important;
        }

        /* ESTILOS ADICIONALES PARA LEAFLET */
        .leaflet-popup-content-wrapper { padding: 0 !important; border-radius: 1.5rem !important; overflow: hidden !important; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1) !important; }
        .leaflet-popup-content { margin: 0 !important; line-height: 1.5 !important; }
        .leaflet-container { font-family: inherit !important; z-index: 10 !important; }
    </style>

    <script>

        // ==========================================
        // LÓGICA DEL MAPA LEAFLET (ESTUDIOS ÚNICOS)
        // ==========================================
        let mapInstance = null;
        let allPopupsOpen = false;
        let markersList = [];
        const mapLocations = @json($mapLocationsData);

        function initMap() {
            if (mapLocations.length === 0) return;
            const centerPos = [mapLocations[0].lat, mapLocations[0].lng];

            // 1. Inicializar mapa
            mapInstance = L.map('exploreMap').setView(centerPos, 13);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(mapInstance);

            // 2. Ícono personalizado de marca
            const customIcon = L.divIcon({
                className: 'custom-map-pin',
                html: `<div class="w-8 h-8 bg-red-600 border-2 border-white rounded-full shadow-md flex items-center justify-center text-white cursor-pointer hover:scale-110 transition-transform">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                       </div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -36]
            });

            markersList = [];

            // 3. Renderizar marcadores con su índice de posición (idx)
            mapLocations.forEach((studio, idx) => {
                if (studio.lat && studio.lng) {
                    const marker = L.marker([studio.lat, studio.lng], { icon: customIcon }).addTo(mapInstance);
                    
                    const popupCardHtml = `
                        <div class="w-64 bg-white flex flex-col font-sans cursor-pointer select-none" onclick="closeSingleCard(${idx});">
                            <div class="h-28 w-full relative bg-stone-100 overflow-hidden">
                                <img src="${studio.image}" alt="${studio.name}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-3 text-white font-black text-sm drop-shadow-sm truncate pr-4">
                                    ${studio.name}
                                </span>
                            </div>
                            
                            <div class="p-3 flex flex-col justify-between flex-1">
                                <p class="text-[11px] text-stone-500 font-medium flex items-center gap-1 mb-3 truncate">
                                    📍 ${studio.address}
                                </p>
                                
                                <a href="${studio.url}" target="_blank" onclick="event.stopPropagation();"
                                   class="w-full py-2 px-3 bg-stone-900 hover:bg-red-600 text-white text-xs font-bold rounded-xl text-center transition-colors shadow-2xs flex items-center justify-center gap-1">
                                    <span>Ver Estudio y Horarios</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupCardHtml, { 
                        autoClose: false, 
                        closeOnClick: false, 
                        closeButton: false 
                    });
                    
                    markersList.push(marker);
                }
            });

            // Abrir todas las tarjetas por defecto al iniciar
            toggleAllPopups(false);
        }

        function closeSingleCard(index) {
            if (markersList[index]) {
                markersList[index].closePopup();
            }
        }

        function toggleAllPopups(forceOpen = null) {
            if (markersList.length === 0) return;
            const btn = document.getElementById('btnToggleAllPopups');
            
            if (forceOpen !== null) {
                allPopupsOpen = forceOpen;
            } else {
                allPopupsOpen = !allPopupsOpen;
            }

            if (allPopupsOpen) {
                markersList.forEach(m => m.openPopup());
                if (btn) btn.innerText = 'Cerrar Todas las Tarjetas';
            } else {
                markersList.forEach(m => m.closePopup());
                if (btn) btn.innerText = 'Abrir Todas las Tarjetas';
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            initMap();
        });


        // ==========================================
        // MODALES DE DETALLE
        // ==========================================
        function openDetailModal(data) {
            document.getElementById('m_title').innerText = data.title;
            document.getElementById('m_studio').innerText = data.studio;
            document.getElementById('m_studio_link').href = data.studio_url;
            document.getElementById('m_image').src = data.image;
            document.getElementById('m_date').innerText = data.date;
            document.getElementById('m_time').innerText = data.time + ' hrs';
            document.getElementById('m_address').innerText = data.address;
            document.getElementById('m_teacher').innerText = data.teacher;

            const emailEl = document.getElementById('m_teacher_email');
            if (data.teacher_email) {
                emailEl.innerText = data.teacher_email;
                emailEl.href = 'mailto:' + data.teacher_email;
                emailEl.classList.remove('hidden');
            } else {
                emailEl.classList.add('hidden');
            }

            const descContainer = document.getElementById('m_description_container');
            const descText = document.getElementById('m_description');
            if (data.description && data.description.trim() !== '') {
                descText.innerText = data.description;
                descContainer.classList.remove('hidden');
            } else {
                descContainer.classList.add('hidden');
                descText.innerText = '';
            }

            const videoContainer = document.getElementById('m_video_container');
            const videoFrame = document.getElementById('m_video_frame');
            if (data.video_url) {
                videoFrame.src = data.video_url;
                videoContainer.classList.remove('hidden', 'aspect-video', 'aspect-[9/16]', 'w-full', 'w-[280px]', 'sm:w-[320px]', 'w-[340px]', 'sm:w-[380px]');
                if (data.video_url.includes('instagram.com')) {
                    videoContainer.classList.add('aspect-[9/16]','sm:w-[380px]');
                } else {
                    videoContainer.classList.add('aspect-video', 'w-full');
                }
            } else {
                videoContainer.classList.add('hidden');
                videoFrame.src = '';
            }

            const mapLinkEl = document.getElementById('m_map_link');
            if (mapLinkEl) {
                if (data.map_url) {
                    mapLinkEl.href = data.map_url;
                } else if (data.address) {
                    const encodedAddress = encodeURIComponent(data.address);
                    mapLinkEl.href = `https://www.google.com/maps/search/?api=1&query=${encodedAddress}`;
                }
            }

            const modal = document.getElementById('detailModal');
            const card = document.getElementById('detailModalCard');
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            const card = document.getElementById('detailModalCard');
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                document.getElementById('m_video_frame').src = '';
            }, 300);
        }

        // ==========================================
        // 2. LÓGICA DE MULTI-SELECCIÓN (ÓRDENES CLARAS)
        // ==========================================
        const isLoggedIn = @json(Auth::check());
        const hasDependents = @json(isset($activeDependents) ? $activeDependents->count() > 0 : false);

        let pendingClasses = new Map();
        let currentModalSessionId = null;
        let currentModalBtn = null;
        let currentModalSelections = new Set();

        function getParsedDbSet(btnElement) {
            const dbState = JSON.parse(btnElement.getAttribute('data-db-selections') || '{}');
            const dbSet = new Set();
            Object.keys(dbState).forEach(k => dbSet.add(k === 'titular' ? 'titular' : parseInt(k)));
            return { dbState, dbSet };
        }

        function handleInterestClick(sessionId, btnElement) {
            if (!isLoggedIn) {
                window.location.href = "{{ route('register') }}";
                return;
            }

            const { dbState, dbSet } = getParsedDbSet(btnElement);

            if (hasDependents) {
                currentModalSessionId = sessionId;
                currentModalBtn = btnElement;

                currentModalSelections = new Set(dbSet);
                const changes = pendingClasses.get(sessionId) || [];
                changes.forEach(change => {
                    if (change.action === 'add') currentModalSelections.add(change.id);
                    if (change.action === 'remove') currentModalSelections.delete(change.id);
                });

                renderModalUI();
                openFamilyModal();
            } else {
                let isEnrolled = dbSet.has('titular');
                const changes = pendingClasses.get(sessionId) || [];

                changes.forEach(c => {
                    if (c.action === 'add') isEnrolled = true;
                    if (c.action === 'remove') isEnrolled = false;
                });

                const newChanges = [];
                if (isEnrolled) {
                    if (dbSet.has('titular')) newChanges.push({id: 'titular', action: 'remove'});
                } else {
                    if (!dbSet.has('titular')) newChanges.push({id: 'titular', action: 'add'});
                }

                if (newChanges.length > 0) pendingClasses.set(sessionId, newChanges);
                else pendingClasses.delete(sessionId);

                updateButtonUI(sessionId, btnElement);
                toggleFloatingBar();
            }
        }

        function toggleModalSelection(id) {
            if (currentModalSelections.has(id)) currentModalSelections.delete(id);
            else currentModalSelections.add(id);
            renderModalUI();
        }

        function renderModalUI() {
            if (!currentModalBtn) return;
            const dbState = JSON.parse(currentModalBtn.getAttribute('data-db-selections') || '{}');

            document.querySelectorAll('[id^="modal_opt_"]').forEach(btn => {
                const isTitular = btn.id === 'modal_opt_titular';
                const idValue = isTitular ? 'titular' : parseInt(btn.id.replace('modal_opt_', ''));
                const checkCircle = btn.querySelector('.check-icon');
                const checkMark = checkCircle.querySelector('svg');
                const statusLabel = btn.querySelector('span:last-child');

                btn.classList.remove(
                    'border-blue-500', 'bg-blue-50', 'pointer-events-none',
                    'border-emerald-600', 'bg-emerald-50/50',
                    'border-red-100', 'hover:border-emerald-300', 'hover:border-red-300'
                );
                checkCircle.classList.remove(
                    'bg-blue-500', 'border-blue-500',
                    'bg-emerald-600', 'border-emerald-600',
                    'border-stone-200'
                );
                checkMark.classList.add('opacity-0');
                statusLabel.classList.add('hidden');
                statusLabel.classList.remove('text-blue-700', 'text-red-600', 'text-emerald-600');

                if (dbState[idValue] === 'paid') {
                    btn.classList.add('border-blue-500', 'bg-blue-50', 'pointer-events-none');
                    statusLabel.innerText = "YA PAGADO";
                    statusLabel.classList.add('text-blue-700');
                    statusLabel.classList.remove('hidden');
                    
                    checkCircle.classList.add('bg-blue-500', 'border-blue-500');
                    checkMark.classList.remove('opacity-0');
                    return; 
                }

                if (currentModalSelections.has(idValue)) {
                    btn.classList.add('border-emerald-600', 'bg-emerald-50/50');
                    checkCircle.classList.add('bg-emerald-600', 'border-emerald-600');
                    checkMark.classList.remove('opacity-0');
                } else {
                    btn.classList.add('border-red-100', isTitular ? 'hover:border-red-300' : 'hover:border-emerald-300');
                    checkCircle.classList.add('border-stone-200');
                }
            });
        }

        function saveModalSelection() {
            const { dbState, dbSet } = getParsedDbSet(currentModalBtn);
            const changes = [];

            currentModalSelections.forEach(val => {
                if (!dbSet.has(val)) changes.push({id: val, action: 'add'});
            });
            dbSet.forEach(val => {
                if (!currentModalSelections.has(val) && dbState[val] !== 'paid') {
                    changes.push({id: val, action: 'remove'});
                }
            });

            if (changes.length > 0) pendingClasses.set(currentModalSessionId, changes);
            else pendingClasses.delete(currentModalSessionId);

            updateButtonUI(currentModalSessionId, currentModalBtn);
            toggleFloatingBar();
            closeFamilyModal();
        }

        function openFamilyModal() {
            const modal = document.getElementById('familySelectionModal');
            const card = document.getElementById('familySelectionCard');
            modal.classList.remove('hidden');
            setTimeout(() => { card.classList.remove('scale-95', 'opacity-0'); card.classList.add('scale-100', 'opacity-100'); }, 10);
        }

        function closeFamilyModal() {
            const modal = document.getElementById('familySelectionModal');
            const card = document.getElementById('familySelectionCard');
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        function updateButtonUI(sessionId, btnElement) {
            const { dbSet } = getParsedDbSet(btnElement);
            const changes = pendingClasses.get(sessionId) || [];

            let finalCount = dbSet.size;
            changes.forEach(c => {
                if (c.action === 'add') finalCount++;
                if (c.action === 'remove') finalCount--;
            });

            if (finalCount > 0) {
                const countText = finalCount === 1 ? (hasDependents ? '1 Seleccionado' : 'En Portal') : `${finalCount} en Portal`;
                const actionIcon = hasDependents
                    ? `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.762z"></path></svg> Modificar`
                    : `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover`;

                btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 active:scale-95 flex items-center justify-center shadow-sm bg-gradient-to-r from-red-600 to-rose-600 text-white border-0 hover:from-red-500 hover:to-rose-500 hover:shadow-md hover:shadow-red-200 group/btn relative z-30";
                btnElement.innerHTML = `
                    <div class="relative flex items-center justify-center w-full">
                        <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ${countText}
                        </span>
                        <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">${actionIcon}</span>
                    </div>`;
            } else {
                if (dbSet.size > 0 && finalCount === 0) {
                    btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center bg-stone-500 text-white border border-stone-600 hover:bg-stone-600 relative z-30";
                    btnElement.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover Todo`;
                } else {
                    btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center bg-stone-100 text-stone-700 border border-stone-200 hover:bg-gradient-to-r hover:from-red-50 hover:to-orange-50 hover:border-red-200 hover:text-red-700 relative z-30";
                    btnElement.innerHTML = `<span class="flex items-center gap-1.5">Me Interesa</span>`;
                }
            }
        }

        // ==========================================
        // 3. ENVÍO AL BACKEND
        // ==========================================
        function toggleFloatingBar() {
            const bar = document.getElementById('floating-bar');
            const countLabel = document.getElementById('selected-count');

            let totalCount = 0;
            pendingClasses.forEach(changes => { totalCount += changes.length; });

            countLabel.innerText = totalCount;
            if (totalCount > 0) {
                bar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                bar.classList.add('translate-y-0', 'opacity-100');
            } else {
                bar.classList.remove('translate-y-0', 'opacity-100');
                bar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
            }
        }

        async function confirmReservations() {
            const btn = document.getElementById('floating-confirm-btn');

            btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-red-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
            btn.disabled = true;

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const formattedSessions = [];
            pendingClasses.forEach((changes, sessionId) => {
                changes.forEach(c => {
                    formattedSessions.push({
                        session_id: sessionId,
                        dependent_id: c.id === 'titular' ? null : c.id,
                        action: c.action
                    });
                });
            });

            const payload = { enrollments: formattedSessions };

            try {
                const response = await fetch("{{ route('global.student.enroll.bulk') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok && response.status === 422) {
                    const data = await response.json();
                    showToast(data.message || 'Algunas clases ya no tienen cupos disponibles.', 'error');
                    resetConfirmButton(btn);
                    return;
                }

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text);
                }

                const data = await response.json();

                if (data.error) {
                    showToast(data.message, 'error');
                    resetConfirmButton(btn);
                } else {
                    pendingClasses.clear();
                    sessionStorage.setItem('cart_auto_open', 'true');
                    window.location.reload();
                }
            } catch (error) {
                console.error("Error en bulk:", error);
                showToast("Hubo un error de conexión al procesar tus reservas.", 'error');
                resetConfirmButton(btn);
            }
        }

        function resetConfirmButton(btn) {
            btn.innerHTML = `Confirmar Cambios <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
            btn.disabled = false;
        }

        // ==========================================
        // 4. LÓGICA DEL MINI-CARRITO FLOTANTE
        // ==========================================
        function toggleMiniCart() {
            const panel = document.getElementById('miniCartPanel');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                setTimeout(() => {
                    panel.classList.remove('opacity-0', 'scale-95');
                    panel.classList.add('opacity-100', 'scale-100');
                }, 10);
            } else {
                panel.classList.remove('opacity-100', 'scale-100');
                panel.classList.add('opacity-0', 'scale-95');
                setTimeout(() => { panel.classList.add('hidden'); }, 300);
            }
        }

        document.addEventListener('click', function(event) {
            const panel = document.getElementById('miniCartPanel');
            const btn = document.getElementById('btnMiniCart');
            if (panel && !panel.classList.contains('hidden') && !panel.contains(event.target) && !btn.contains(event.target)) {
                toggleMiniCart();
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            if (sessionStorage.getItem('cart_auto_open') === 'true') {
                sessionStorage.removeItem('cart_auto_open');
                setTimeout(() => { if (document.getElementById('miniCartPanel')) toggleMiniCart(); }, 600);
            }
        });

        // ==========================================
        // 5. SISTEMA DE TOASTS
        // ==========================================
        function createToastContainer() {
            const div = document.createElement('div');
            div.id = 'toast-container';
            div.className = 'fixed top-6 right-6 z-[200] flex flex-col gap-3';
            document.body.appendChild(div);
            return div;
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container') || createToastContainer();
            const toast = document.createElement('div');

            const bgClass = type === 'error'
                ? 'bg-gradient-to-r from-rose-600 to-red-500'
                : 'bg-gradient-to-r from-emerald-500 to-teal-500';

            const iconPath = type === 'error'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';

            toast.className = `${bgClass} text-white px-5 py-3 rounded-2xl font-bold text-sm transition-all duration-300 transform translate-x-full opacity-0 flex items-center gap-2.5 min-w-[280px] max-w-[420px]`;

            toast.innerHTML = `
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">${iconPath}</svg>
                <span class="flex-1">${escapeHtml(message)}</span>
                <button onclick="this.parentElement.remove()" class="shrink-0 hover:opacity-70 transition-opacity duration-150">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            });

            const dismissTimeout = setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300);
            }, 4000);

            toast.addEventListener('click', (e) => {
                if (e.target.tagName === 'BUTTON') return;
                clearTimeout(dismissTimeout);
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300);
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.holo-white-card');
            
            // 1. Precargamos el sonido en memoria
            const hoverSound = new Audio('{{ asset("audio/hover-pop.mp3") }}');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    let soundClone = hoverSound.cloneNode();
                    soundClone.volume = 0.15;
                    soundClone.play().catch(error => {});

                    if (!this.classList.contains('is-animating')) {
                        this.classList.add('is-animating');
                        setTimeout(() => {
                            this.classList.remove('is-animating');
                        }, 500);
                    }
                });
            });
        });
    </script>
</x-app-layout>
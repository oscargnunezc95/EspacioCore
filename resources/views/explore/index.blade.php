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
      "@@type": "ListItem",
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

    {{-- FONDO DECORATIVO: Gradiente artístico sutil + blob --}}
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-b from-violet-50/50 via-white to-amber-50/30">
        {{-- Blobs decorativos de fondo --}}
        <div class="absolute top-0 -left-32 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/3 -right-32 w-96 h-96 bg-rose-200/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-64 h-64 bg-amber-200/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative py-8 md:py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">

            {{-- ============================================================ --}}
            {{-- HERO: Vibrante, artístico, con personalidad                    --}}
            {{-- ============================================================ --}}
            <div class="text-center mb-10 md:mb-14 relative">
                {{-- Ícono decorativo danza/música --}}
                <div class="inline-flex items-center justify-center mb-5 animate-bounce-slow">
                    <span class="text-4xl md:text-5xl">🎭</span>
                </div>

                <h1 class="text-3xl md:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    <span class="bg-gradient-to-r from-purple-700 via-violet-600 to-fuchsia-600 bg-clip-text text-transparent">
                        @if($seo['city'] && $seo['area'])
                            Clases de {{ $seo['area'] }} en {{ $seo['city'] }}
                        @elseif($seo['city'])
                            Talleres y Clases en {{ $seo['city'] }}
                        @elseif($seo['area'])
                            Clases de {{ $seo['area'] }}
                        @else
                            Descubre tu próxima clase
                        @endif
                    </span>
                </h1>

                {{-- Línea decorativa --}}
                <div class="flex items-center justify-center gap-2 mt-4 mb-5">
                    <div class="h-0.5 w-8 rounded-full bg-gradient-to-r from-transparent to-purple-400"></div>
                    <div class="h-2 w-2 rounded-full bg-fuchsia-500"></div>
                    <div class="h-0.5 w-8 rounded-full bg-gradient-to-l from-transparent to-purple-400"></div>
                </div>

                <p class="text-base md:text-lg font-medium max-w-2xl mx-auto leading-relaxed
                          @if($seo['total'] > 0) text-stone-600 @else text-stone-500 @endif">
                    @if($seo['total'] > 0)
                        <span class="font-black text-purple-700">{{ $seo['total'] }}</span>
                        {{ $seo['total'] == 1 ? 'clase encontrada' : 'clases encontradas' }}.
                        <span class="text-stone-500">Encuentra y reserva sesiones en los mejores estudios cerca de ti.</span>
                    @else
                        Intenta ajustando los filtros de búsqueda para ver más opciones.
                    @endif
                </p>

                {{-- Chips de disciplinas populares (decorativos, no funcionales) --}}
                <div class="flex flex-wrap items-center justify-center gap-2 mt-5">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full border border-purple-200/50">
                        🎪 Circo
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-fuchsia-100 text-fuchsia-700 text-xs font-bold rounded-full border border-fuchsia-200/50">
                        💃 Danza
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full border border-amber-200/50">
                        🎵 Música
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded-full border border-rose-200/50">
                        🎨 Arte
                    </span>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- BREADCRUMBS --}}
            {{-- ============================================================ --}}
            @if(count($breadcrumbs) > 0)
            <nav class="mb-4" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-1 text-xs font-medium text-stone-400" itemscope itemtype="https://schema.org/BreadcrumbList">
                    @foreach($breadcrumbs as $index => $crumb)
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="flex items-center">
                            @if(!$loop->last)
                                <a itemprop="item" href="{{ $crumb['url'] }}" class="hover:text-purple-600 transition-colors">
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
            {{-- FILTROS: Diseño cálido, vidrio esmerilado                       --}}
            {{-- ============================================================ --}}
            <div x-data="{ openFilters: false }" class="mb-6">
                <div class="md:hidden flex justify-end mb-4">
                    <button @click="openFilters = true" type="button"
                        class="w-full bg-white/80 backdrop-blur-sm border border-purple-200 text-purple-700 font-black py-3.5 px-4 rounded-2xl shadow-sm flex items-center justify-center gap-2 active:scale-95 transition-all duration-200 hover:bg-purple-50">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filtros de Búsqueda
                    </button>
                </div>

                <form action="{{ route('explore') }}" method="GET">
                    <div x-show="openFilters" x-transition.opacity.duration.300ms @click="openFilters = false" class="fixed inset-0 bg-stone-900/60 z-[60] md:hidden" style="display: none;"></div>

                    <div :class="openFilters ? 'translate-x-0' : 'translate-x-full'"
                        class="fixed inset-y-0 right-0 z-[70] w-[85%] max-w-sm bg-white shadow-2xl transition-transform duration-300 ease-in-out flex flex-col md:static md:translate-x-0 md:z-auto md:w-full md:max-w-none md:bg-white/70 md:backdrop-blur-md md:p-5 md:rounded-3xl md:shadow-sm md:border md:border-purple-100/50 translate-x-full"
                        x-cloak>
                        <div class="flex items-center justify-between p-5 border-b border-purple-50 md:hidden shrink-0">
                            <h2 class="text-xl font-black text-stone-900">Filtros</h2>
                            <button type="button" @click="openFilters = false" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-50 rounded-full transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="p-5 overflow-y-auto flex-1 md:p-0 md:overflow-visible md:flex md:flex-row md:gap-4 md:items-end w-full space-y-5 md:space-y-0">
                            <div class="w-full md:w-1/5">
                                <label class="flex items-center gap-1.5 text-[11px] font-black text-purple-400 uppercase tracking-widest mb-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                                    Ciudad
                                </label>
                                <select name="city" class="w-full rounded-xl border border-purple-100 bg-white/80 px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer transition-all hover:border-purple-300">
                                    <option value="">Todas</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full md:w-1/5">
                                <label class="flex items-center gap-1.5 text-[11px] font-black text-purple-400 uppercase tracking-widest mb-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    Categoría
                                </label>
                                <select name="area" class="w-full rounded-xl border border-purple-100 bg-white/80 px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer transition-all hover:border-purple-300">
                                    <option value="">Todas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->name }}" {{ request('area') == $area->name ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full md:w-1/5">
                                <label class="flex items-center gap-1.5 text-[11px] font-black text-purple-400 uppercase tracking-widest mb-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Desde
                                </label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}"
                                    class="w-full rounded-xl border border-purple-100 bg-white/80 px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer transition-all hover:border-purple-300">
                            </div>

                            <div class="w-full md:w-1/5">
                                <label class="flex items-center gap-1.5 text-[11px] font-black text-purple-400 uppercase tracking-widest mb-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Hasta
                                </label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}"
                                    class="w-full rounded-xl border border-purple-100 bg-white/80 px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer transition-all hover:border-purple-300">
                            </div>

                            <div class="w-full md:w-1/5 flex gap-2 pt-6 md:pt-0 border-t border-purple-50 md:border-t-0 mt-auto md:mt-0">
                                <a href="{{ route('explore') }}" class="flex-1 flex items-center justify-center bg-stone-100 text-stone-600 font-bold py-3 md:py-3.5 rounded-xl hover:bg-stone-200 transition-all duration-200 text-sm active:scale-95"
                                    title="Limpiar Filtros">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </a>
                                <button type="submit"
                                    class="flex-[3] bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white font-bold py-3 md:py-3.5 rounded-xl shadow-md shadow-purple-200 hover:shadow-lg hover:shadow-purple-300 hover:from-purple-500 hover:to-fuchsia-500 transition-all duration-300 active:scale-95 text-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ============================================================ --}}
            {{-- TOGGLE MAPA + CONTADOR DE RESULTADOS --}}
            {{-- ============================================================ --}}
            <div class="flex items-center justify-between mb-6">
                <p class="text-xs font-bold text-stone-400 uppercase tracking-wider">
                    @if($seo['total'] > 0)
                        Mostrando <span class="text-stone-700">{{ $sessions->count() }}</span> de <span class="text-stone-700">{{ $seo['total'] }}</span>
                    @endif
                </p>
                <button onclick="toggleMap()" id="btnToggleMap"
                    class="flex items-center gap-2 text-sm font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 px-5 py-2.5 rounded-2xl transition-all duration-200 border border-purple-100 hover:border-purple-200 active:scale-95 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    <span>Ver en Mapa</span>
                </button>
            </div>

            {{-- MAPA --}}
            <div id="mapContainer" class="hidden mb-10 w-full h-[500px] rounded-3xl overflow-hidden shadow-lg shadow-purple-100 border border-purple-100">
                <div id="exploreMap" class="w-full h-full bg-stone-100"></div>
            </div>

            {{-- ============================================================ --}}
            {{-- GRID DE CLASES: Cards con alma artística                        --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 md:gap-6">
                @forelse($sessions as $session)
                    @php
                        $maxSpots     = $session->max_spots ?? 99;
                        $pendingCount = $session->pending_count ?? 0;
                        $available    = $session->available_spots ?? $maxSpots;
                        $isFull       = $available <= 0;
                        $almostFull   = $available <= 3 && $available > 0;
                    @endphp

                    <div class="relative group/card bg-white border {{ $isFull ? 'border-stone-200 bg-stone-50/80' : 'border-purple-100/80 hover:border-purple-200' }} rounded-3xl overflow-hidden {{ $isFull ? '' : 'hover:shadow-2xl hover:shadow-purple-100/50 hover:-translate-y-1.5' }} transition-all duration-500 flex flex-col transform-gpu isolate {{ $isFull ? 'opacity-75' : '' }}">
                        @php
                            $imageUrl = $session->workshop->image_path
                                            ? asset('storage/' . $session->workshop->image_path)
                                            : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=7c3aed&background=ede9fe&size=512';

                            $studioLogo = $session->workshop->studio->icon_path ?? $session->workshop->studio->logo_path ?? null;
                            $studioImageUrl = $studioLogo
                                            ? asset('storage/' . $studioLogo)
                                            : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->studio->name).'&color=ffffff&background=4c1d95&size=128';

                            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
                            $protocol = request()->secure() ? 'https://' : 'http://';
                            $studioUrl = $protocol . $session->workshop->studio->subdomain . '.' . $domain;

                            $modalData = json_encode([
                                'title'         => $session->workshop->name,
                                'studio'        => $session->workshop->studio->name,
                                'studio_url'    => $studioUrl,
                                'teacher' => $session->workshop->teacher ? trim($session->workshop->teacher->first_name . ' ' . $session->workshop->teacher->last_name) : 'Por asignar',
                                'teacher_email' => $session->workshop->teacher->email ?? '',
                                'date'          => \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F'),
                                'time'          => \Carbon\Carbon::parse($session->start_time)->format('H:i'),
                                'image'         => $imageUrl,
                                'address'       => $session->workshop->address ?? 'Dirección no especificada',
                                'description'   => $session->workshop->description ?? 'Sin descripción disponible.',
                                'video_url'     => $session->workshop->embed_video_url,
                            ]);
                        @endphp

                        {{-- Imagen con overlay artístico --}}
                        <div class="h-44 bg-stone-100 relative overflow-hidden cursor-pointer transform-gpu" onclick="openDetailModal({{ $modalData }})">
                            <img src="{{ $imageUrl }}" alt="Clase" class="w-full h-full object-cover {{ $isFull ? 'opacity-50' : 'opacity-90' }} {{ $isFull ? '' : 'group-hover/card:opacity-100 group-hover/card:scale-110' }} transition-all duration-700 ease-out">
                            {{-- Overlay gradiente artístico --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-purple-900/70 via-purple-900/10 to-transparent {{ $isFull ? 'opacity-80' : 'opacity-50 group-hover/card:opacity-70' }} transition-opacity duration-500"></div>

                            {{-- Ribbon "Clase Llena" más estilizado --}}
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
                                    {{ $isFull ? 'bg-white/60 text-stone-500' : 'bg-white/90 backdrop-blur-sm text-purple-600 shadow-sm' }}
                                    border {{ $isFull ? 'border-white/30' : 'border-white/50' }}">
                                    {{ $session->workshop->discipline->area->name ?? 'Clase' }}
                                </span>
                            </div>

                            {{-- Logo del estudio (efecto polaroid) --}}
                            <div class="absolute bottom-3 right-3 {{ $isFull ? '' : 'group-hover/card:scale-110 group-hover/card:rotate-3' }} transition-all duration-500 z-10">
                                <div class="relative w-11 h-11 rounded-2xl bg-white shadow-lg border-2 {{ $isFull ? 'border-stone-200 opacity-70' : 'border-white' }} overflow-hidden transform -rotate-2 group-hover/card:rotate-0 transition-transform duration-500" title="{{ $session->workshop->studio->name }}">
                                    <img src="{{ $studioImageUrl }}" alt="Logo Estudio" class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>

                        {{-- Contenido de la card --}}
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-3 cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                                <h3 class="text-base md:text-lg font-black text-stone-900 leading-tight group-hover/card:text-purple-700 transition-colors duration-300 line-clamp-2">
                                    {{ $session->workshop->name }}
                                </h3>
                            </div>

                            <div class="space-y-2.5 mt-auto cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                                <div class="flex items-center gap-2.5 text-sm font-medium text-stone-500">
                                    <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="font-bold text-stone-700">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('D d M') }} · {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-sm font-medium text-stone-500">
                                    <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
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
                                            {{ $pendingCount }} {{ $pendingCount === 1 ? '💜' : '💜💜' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- MAGIA BLADE: DETECCIÓN EXACTA DE FAMILIARES --}}
                            @php
                                $dbSelections = $dbSelectionsBySession[$session->id] ?? [];
                                $enrolledCount = count($dbSelections);
                                $isTitularPaid = isset($dbSelections['titular']) && $dbSelections['titular'] === 'paid';
                                $hasDependents = auth()->check() && auth()->user()->dependents->count() > 0;
                            @endphp

                            {{-- Precio + Botón de acción --}}
                            <div class="mt-5 pt-4 border-t border-stone-100 flex items-center justify-between gap-3">
                                <div class="shrink-0">
                                    @php
                                        $dropInPrice = $session->workshop->prices->where('class_count', 1)->first();
                                    @endphp
                                    @if($dropInPrice)
                                        <p class="text-[10px] text-stone-400 font-bold uppercase tracking-wider">Clase suelta</p>
                                        <p class="text-lg font-black text-stone-900">${{ number_format($dropInPrice->price, 0, ',', '.') }}</p>
                                    @else
                                        <p class="text-[10px] text-stone-400 font-bold uppercase tracking-wider">Desde</p>
                                        <p class="text-sm font-black text-stone-900">Ver Planes</p>
                                    @endif
                                </div>

                                @if($isFull)
                                    <button disabled class="flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 bg-stone-100 text-stone-400 cursor-not-allowed border border-stone-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Lleno
                                    </button>
                                @elseif(auth()->check() && !$hasDependents && $isTitularPaid)
                                    <button disabled class="flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white cursor-not-allowed opacity-90 shadow-md border-0 transition-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Pagada ✓
                                    </button>
                                @else
                                    <button onclick="handleInterestClick({{ $session->id }}, this)"
                                            data-db-selections="{{ json_encode($dbSelections) }}"
                                            class="interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 active:scale-95 flex items-center justify-center shadow-sm
                                            {{ $enrolledCount > 0 ? 'bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white border-0 hover:from-purple-500 hover:to-fuchsia-500 hover:shadow-md hover:shadow-purple-200 group/btn' : 'bg-stone-100 text-stone-700 border border-stone-200 hover:bg-gradient-to-r hover:from-purple-50 hover:to-fuchsia-50 hover:border-purple-200 hover:text-purple-700' }}">
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
                                            <span class="flex items-center gap-1.5">✨ Me Interesa</span>
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Estado vacío con personalidad artística --}}
                    <div class="col-span-full py-20 text-center">
                        <div class="inline-flex flex-col items-center">
                            <div class="text-7xl mb-6 animate-bounce-slow">🎭</div>
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-purple-100 to-fuchsia-100 mb-6 shadow-inner">
                                <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-stone-800 mb-2">¡No encontramos clases esta vez!</h3>
                            <p class="text-stone-500 max-w-md leading-relaxed">
                                El ritmo no se detiene. Intenta ajustando los filtros o cambiando la ciudad para descubrir nuevas experiencias.
                            </p>
                            <a href="{{ route('explore') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white font-bold rounded-2xl shadow-lg shadow-purple-200 hover:shadow-xl hover:shadow-purple-300 hover:from-purple-500 hover:to-fuchsia-500 transition-all duration-300 active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Limpiar filtros
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            <div class="mt-10">{{ $sessions->links() }}</div>
        </div>

        {{-- ============================================================ --}}
        {{-- BARRA FLOTANTE DE CONFIRMACIÓN --}}
        {{-- ============================================================ --}}
        <div id="floating-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 translate-y-24 opacity-0 transition-all duration-500 z-50 pointer-events-none">
            <div class="bg-gradient-to-r from-purple-900 to-fuchsia-900 text-white px-6 py-4 rounded-full shadow-2xl shadow-purple-500/30 flex items-center gap-6 border border-white/10">
                <div class="flex items-center gap-3">
                    <span id="selected-count" class="bg-emerald-400 text-purple-900 font-black text-sm w-7 h-7 flex items-center justify-center rounded-full shadow-inner">0</span>
                    <span class="font-bold text-sm">Cambios detectados</span>
                </div>
                <button onclick="confirmReservations()" id="floating-confirm-btn"
                    class="bg-gradient-to-r from-emerald-400 to-teal-400 hover:from-emerald-300 hover:to-teal-300 text-purple-900 px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-300 active:scale-95 flex items-center gap-2 pointer-events-auto shadow-lg shadow-emerald-500/30">
                    Confirmar Cambios
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        </div>

        @auth
            {{-- ============================================================ --}}
            {{-- MINI CARRITO FLOTANTE --}}
            {{-- ============================================================ --}}
            <div class="fixed bottom-6 right-6 z-[60]">
                <div id="miniCartPanel" class="hidden absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-purple-100 overflow-hidden transition-all transform origin-bottom-right opacity-0 scale-95">
                    <div class="p-5 bg-gradient-to-r from-purple-700 to-fuchsia-700 text-white flex justify-between items-center">
                        <div>
                            <h4 class="font-black text-lg leading-none">Tus Reservas</h4>
                            <p class="text-xs text-purple-200 mt-1">Pendientes de pago</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @if(auth()->user()->pending_reservations_count > 0)
                                <span class="text-sm bg-rose-400 text-white shadow-inner px-3 py-1 rounded-full font-black">
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
                            class="w-full {{ auth()->user()->pending_reservations_count > 0 ? 'bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 shadow-lg shadow-purple-200' : 'bg-stone-200 pointer-events-none' }} text-white font-bold py-3.5 rounded-2xl transition-all duration-300 active:scale-95 text-sm flex items-center justify-center gap-2">
                            Ir a Pagar Mis Clases
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>

                <button onclick="toggleMiniCart()" id="btnMiniCart"
                    class="relative bg-gradient-to-br from-purple-600 to-fuchsia-700 text-white p-4 rounded-full shadow-[0_10px_40px_-10px_rgba(147,51,234,0.5)] hover:shadow-[0_15px_50px_-10px_rgba(147,51,234,0.7)] hover:scale-110 transition-all duration-300 active:scale-95 border border-white/10 focus:outline-none focus:ring-4 focus:ring-purple-300/50 group">
                    <svg class="w-6 h-6 transform group-hover:-rotate-12 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    @if(auth()->user()->pending_reservations_count > 0)
                        <span class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-rose-400 border-2 border-white text-[11px] font-black text-white shadow-md animate-pulse">
                            {{ auth()->user()->pending_reservations_count }}
                        </span>
                    @endif
                </button>
            </div>
        @endauth

        {{-- ============================================================ --}}
        {{-- MODAL DE DETALLES DEL TALLER (con alma artística)             --}}
        {{-- ============================================================ --}}
        <div id="detailModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-stone-900/80 backdrop-blur-sm transition-opacity" onclick="closeDetailModal()"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md md:max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[95vh]" id="detailModalCard">
                <div class="h-40 sm:h-48 w-full bg-stone-200 relative shrink-0">
                    <img id="m_image" src="" alt="Cover" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-purple-900/70 via-purple-900/10 to-transparent"></div>
                    <button onclick="closeDetailModal()" class="absolute top-4 right-4 p-2 text-purple-700 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full transition-colors focus:outline-none shadow-md z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 md:p-8 overflow-y-auto flex-1 custom-scrollbar">
                    <div class="mb-6">
                        <a href="#" id="m_studio_link" target="_blank"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-600 transition-colors text-[10px] font-black rounded-lg tracking-widest uppercase mb-3">
                            <span id="m_studio">Estudio</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                        <h3 id="m_title" class="text-2xl font-black text-stone-900 leading-tight">Clase</h3>
                    </div>

                    <div id="m_video_container" class="hidden mb-6 rounded-2xl overflow-hidden shadow-md border border-purple-100 bg-stone-900 relative group transition-all duration-300 mx-auto">
                        <iframe id="m_video_frame" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>

                    <div id="m_description_container" class="hidden mb-8">
                        <h4 class="text-xs font-black text-purple-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1 h-4 bg-gradient-to-b from-purple-500 to-fuchsia-500 rounded-full"></span>
                            Acerca de la clase
                        </h4>
                        <p id="m_description" class="text-sm text-stone-600 leading-relaxed whitespace-pre-line"></p>
                    </div>

                    <div class="space-y-3 mb-4">
                        <div class="flex items-center gap-3 text-stone-600 bg-purple-50/50 p-3 rounded-2xl border border-purple-100">
                            <div class="bg-white p-2.5 rounded-xl shadow-sm border border-purple-100 text-purple-500 shrink-0">
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
                                <a href="#" id="m_teacher_email" class="hidden text-[11px] font-medium text-purple-600 hover:text-purple-800 transition-colors mt-0.5 truncate"></a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 text-stone-600 bg-rose-50/50 p-3 rounded-2xl border border-rose-100">
                            <div class="bg-white p-2.5 rounded-xl shadow-sm border border-rose-100 text-rose-500 mt-1 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-0.5">Ubicación</p>
                                <p id="m_address" class="text-sm font-bold text-stone-900 mb-2 leading-tight">Dirección</p>
                                <a href="#" id="m_map_link" target="_blank" class="inline-flex items-center text-xs font-bold text-purple-600 hover:text-purple-800 transition-colors">
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
                <div class="p-6 border-b border-purple-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-stone-900 leading-tight">¿Quiénes asistirán?<br><span class="text-sm font-medium text-stone-500">Selecciona uno o más</span></h3>
                    <button onclick="closeFamilyModal()" class="text-stone-400 hover:text-stone-600 bg-stone-50 p-2 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
                    <button type="button" onclick="toggleModalSelection('titular')" id="modal_opt_titular"
                        class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-purple-100 hover:border-purple-300 transition-all group">
                        <div class="flex flex-col text-left">
                            <span class="font-bold text-stone-900 text-sm">Yo ({{ Auth::user()->name }})</span>
                            <span class="text-[10px] font-black text-purple-600 uppercase tracking-widest mt-0.5">Titular</span>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 border-stone-200 flex items-center justify-center check-icon transition-colors">
                            <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest hidden"></span>
                    </button>

                    @foreach($activeDependents as $dependent)
                        <button type="button" onclick="toggleModalSelection({{ $dependent->id }})" id="modal_opt_{{ $dependent->id }}"
                            class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-purple-100 hover:border-emerald-300 transition-all group">
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

                <div class="p-4 bg-white border-t border-purple-50 flex flex-col gap-3">
                    <button onclick="saveModalSelection()"
                        class="w-full bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white font-bold py-3.5 rounded-2xl shadow-md shadow-purple-200 hover:from-purple-500 hover:to-fuchsia-500 transition-all duration-300 active:scale-95 text-sm">
                        Guardar Selección
                    </button>
                    <a href="{{ route('profile.family.index') }}" class="text-xs font-bold text-stone-500 hover:text-purple-600 transition-colors flex items-center justify-center gap-1">
                        Administrar familia
                    </a>
                </div>
            </div>
        </div>
        @endauth

        @php
            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
            $protocol = request()->secure() ? 'https://' : 'http://';

            $mapLocationsData = $sessions->map(function($s) use ($domain, $protocol) {
                $imageUrl = $s->workshop->image_path
                                ? asset('storage/' . $s->workshop->image_path)
                                : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=7c3aed&background=ede9fe&size=512';
                $studioUrl = $protocol . $s->workshop->studio->subdomain . '.' . $domain;

                return [
                    'title' => $s->workshop->name,
                    'studio' => $s->workshop->studio->name,
                    'lat' => (float) ($s->workshop->latitude ?? $s->workshop->studio->latitude),
                    'lng' => (float) ($s->workshop->longitude ?? $s->workshop->studio->longitude),
                    'image' => $imageUrl,
                    'url' => $studioUrl
                ];
            })->filter(function($l) { return $l['lat'] !== 0.0; })->values();
        @endphp

        <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.key') }}"></script>

        <script>
            // ==========================================
            // 1. LÓGICA DEL MAPA Y MODALES DE DETALLE
            // ==========================================
            let mapInstance = null;
            let mapIsVisible = false;
            const mapLocations = @json($mapLocationsData);

            function toggleMap() {
                const container = document.getElementById('mapContainer');
                const btn = document.getElementById('btnToggleMap');
                const btnText = btn.querySelector('span');
                mapIsVisible = !mapIsVisible;

                if (mapIsVisible) {
                    container.classList.remove('hidden');
                    btn.classList.replace('bg-purple-50', 'bg-purple-600');
                    btn.classList.replace('text-purple-600', 'text-white');
                    btn.classList.replace('border-purple-100', 'border-purple-600');
                    btn.classList.replace('hover:bg-purple-100', 'hover:bg-purple-700');
                    btnText.innerText = 'Ocultar Mapa';
                    if (!mapInstance) initMap();
                } else {
                    container.classList.add('hidden');
                    btn.classList.replace('bg-purple-600', 'bg-purple-50');
                    btn.classList.replace('text-white', 'text-purple-600');
                    btn.classList.replace('border-purple-600', 'border-purple-100');
                    btn.classList.replace('hover:bg-purple-700', 'hover:bg-purple-100');
                    btnText.innerText = 'Ver en Mapa';
                }
            }

            function initMap() {
                if (mapLocations.length === 0) return;
                const centerPos = { lat: mapLocations[0].lat, lng: mapLocations[0].lng };

                mapInstance = new google.maps.Map(document.getElementById('exploreMap'), {
                    center: centerPos, zoom: 13, mapTypeControl: false, streetViewControl: false,
                    styles: [{ "featureType": "poi", "stylers": [{ "visibility": "off" }] }]
                });

                const infoWindow = new google.maps.InfoWindow();

                mapLocations.forEach(loc => {
                    if (loc.lat && loc.lng) {
                        const marker = new google.maps.Marker({
                            position: { lat: loc.lat, lng: loc.lng }, map: mapInstance, title: loc.title, animation: google.maps.Animation.DROP
                        });

                        marker.addListener('click', () => {
                            const content = `
                                <a href="${loc.url}" target="_blank" class="block p-1.5 max-w-[200px] cursor-pointer group" style="text-decoration: none; color: inherit;">
                                    <div class="relative w-full h-24 mb-2 rounded-lg overflow-hidden bg-stone-100">
                                        <img src="${loc.image}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    </div>
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-[10px] font-black text-purple-600 uppercase tracking-widest">${loc.studio}</p>
                                        <svg class="w-3 h-3 text-stone-300 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </div>
                                    <h4 class="font-bold text-sm text-stone-900 leading-tight group-hover:text-purple-600 transition-colors">${loc.title}</h4>
                                </a>
                            `;
                            infoWindow.setContent(content);
                            infoWindow.open(mapInstance, marker);
                        });
                    }
                });
            }

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

                const encodedAddress = encodeURIComponent(data.address);
                document.getElementById('m_map_link').href = `https://www.google.com/maps/search/?api=1&query=$${encodedAddress}`;

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

            // HELPER PARA EVITAR EL ERROR DE TIPOS (STRING VS NUMBER)
            function getParsedDbSet(btnElement) {
                const dbState = JSON.parse(btnElement.getAttribute('data-db-selections') || '{}');
                const dbSet = new Set();
                // Convertimos las llaves que son "1", "2" de vuelta a números enteros 1, 2
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
                const dbState = JSON.parse(currentModalBtn.getAttribute('data-db-selections') || '{}');

                document.querySelectorAll('[id^="modal_opt_"]').forEach(btn => {
                    const isTitular = btn.id === 'modal_opt_titular';
                    const idValue = isTitular ? 'titular' : parseInt(btn.id.replace('modal_opt_', ''));
                    const checkCircle = btn.querySelector('.check-icon');
                    const checkMark = checkCircle.querySelector('svg');
                    const statusLabel = btn.querySelector('span:last-child');

                    if (dbState[idValue] === 'paid') {
                        btn.classList.add('border-blue-500', 'bg-blue-50', 'pointer-events-none');
                        btn.classList.remove('border-purple-100', 'hover:border-emerald-300');
                        statusLabel.innerText = "YA PAGADO";
                        statusLabel.classList.replace('text-purple-600', 'text-blue-700');
                        statusLabel.classList.replace('text-emerald-600', 'text-blue-700');
                        statusLabel.classList.remove('hidden');
                        checkCircle.classList.add('bg-blue-500', 'border-blue-500');
                        checkMark.classList.remove('opacity-0');
                        return;
                    } else {
                        statusLabel.classList.add('hidden');
                    }

                    if (currentModalSelections.has(idValue)) {
                        btn.classList.add('border-emerald-600', 'bg-emerald-50/50');
                        btn.classList.remove('border-purple-100');
                        checkCircle.classList.add('bg-emerald-600', 'border-emerald-600');
                        checkCircle.classList.remove('border-stone-200');
                        checkMark.classList.remove('opacity-0');
                    } else {
                        btn.classList.remove('border-emerald-600', 'bg-emerald-50/50');
                        btn.classList.add('border-purple-100');
                        checkCircle.classList.remove('bg-emerald-600', 'border-emerald-600');
                        checkCircle.classList.add('border-stone-200');
                        checkMark.classList.add('opacity-0');
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

                    btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 active:scale-95 flex items-center justify-center shadow-sm bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white border-0 hover:from-purple-500 hover:to-fuchsia-500 hover:shadow-md hover:shadow-purple-200 group/btn";
                    btnElement.innerHTML = `
                        <div class="relative flex items-center justify-center w-full">
                            <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ${countText}
                            </span>
                            <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">${actionIcon}</span>
                        </div>`;
                } else {
                    if (dbSet.size > 0 && finalCount === 0) {
                        btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center bg-rose-500 text-white border border-rose-600 hover:bg-rose-600";
                        btnElement.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover Todo`;
                    } else {
                        btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center bg-stone-100 text-stone-700 border border-stone-200 hover:bg-gradient-to-r hover:from-purple-50 hover:to-fuchsia-50 hover:border-purple-200 hover:text-purple-700";
                        btnElement.innerHTML = `<span class="flex items-center gap-1.5">✨ Me Interesa</span>`;
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

                btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-purple-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
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
            // 5. SISTEMA DE TOASTS (Notificaciones elegantes)
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

                // Slide in
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-x-full', 'opacity-0');
                    toast.classList.add('translate-x-0', 'opacity-100');
                });

                // Auto-dismiss
                const dismissTimeout = setTimeout(() => {
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300);
                }, 4000);

                // Click to dismiss early
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
        </script>
    </div>
</x-app-layout>

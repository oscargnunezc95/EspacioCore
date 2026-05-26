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

    <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">

        {{-- Breadcrumbs semanticos --}}
        @if(count($breadcrumbs) > 1)
        <nav class="mb-4" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1 text-xs font-medium text-zinc-400">
                @foreach($breadcrumbs as $crumb)
                    @if(!$loop->last)
                        <li><a href="{{ $crumb['url'] }}" class="hover:text-indigo-600 transition-colors">{{ $crumb['label'] }}</a></li>
                        <li class="mx-1">/</li>
                    @else
                        <li class="text-zinc-700 font-bold">{{ $crumb['label'] }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
        @endif
        
        <div class="text-center mb-10 md:mb-14">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">
                @if($seo['city'] && $seo['area'])
                    Clases de {{ $seo['area'] }} en {{ $seo['city'] }}
                @elseif($seo['city'])
                    Talleres y Clases en {{ $seo['city'] }}
                @elseif($seo['area'])
                    Clases de {{ $seo['area'] }}
                @else
                    Descubre tu proxima clase
                @endif
            </h1>
            <p class="mt-3 text-zinc-500 font-medium text-base md:text-lg">
                @if($seo['total'] > 0)
                    {{ $seo['total'] }} {{ $seo['total'] == 1 ? 'clase encontrada' : 'clases encontradas' }}. Encuentra y reserva sesiones en los mejores estudios cerca de ti.
                @else
                    Intenta ajustando los filtros de busqueda para ver mas opciones.
                @endif
            </p>
        </div>

        <div x-data="{ openFilters: false }" class="mb-8">
            <div class="md:hidden flex justify-end mb-4">
                <button @click="openFilters = true" type="button" class="w-full bg-white border border-zinc-200 text-zinc-900 font-black py-3.5 px-4 rounded-2xl shadow-sm flex items-center justify-center gap-2 active:scale-95 transition-transform">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filtros de Búsqueda
                </button>
            </div>

            <form action="{{ route('explore') }}" method="GET">
                <div x-show="openFilters" x-transition.opacity.duration.300ms @click="openFilters = false" class="fixed inset-0 bg-zinc-900/60 z-[60] md:hidden" style="display: none;"></div>

                <div :class="openFilters ? 'translate-x-0' : 'translate-x-full'" class="fixed inset-y-0 right-0 z-[70] w-[85%] max-w-sm bg-white shadow-2xl transition-transform duration-300 ease-in-out flex flex-col md:static md:translate-x-0 md:z-auto md:w-full md:max-w-none md:bg-white md:p-6 md:rounded-2xl md:shadow-sm md:border md:border-zinc-200" x-cloak>
                    <div class="flex items-center justify-between p-5 border-b border-zinc-100 md:hidden shrink-0">
                        <h2 class="text-xl font-black text-zinc-900">Filtros</h2>
                        <button type="button" @click="openFilters = false" class="p-2 text-zinc-400 hover:text-zinc-700 hover:bg-zinc-50 rounded-full transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-5 overflow-y-auto flex-1 md:p-0 md:overflow-visible md:flex md:flex-row md:gap-4 md:items-end w-full space-y-5 md:space-y-0">
                        <div class="w-full md:w-1/5">
                            <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Ciudad</label>
                            <select name="city" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                                <option value="">Todas</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-1/5">
                            <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Categoría</label>
                            <select name="area" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                                <option value="">Todas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->name }}" {{ request('area') == $area->name ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-1/5">
                            <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Desde</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                        </div>

                        <div class="w-full md:w-1/5">
                            <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Hasta</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                        </div>

                        <div class="w-full md:w-1/5 flex gap-2 pt-6 md:pt-0 border-t border-zinc-100 md:border-t-0 mt-auto md:mt-0">
                            <a href="{{ route('explore') }}" class="flex-1 flex items-center justify-center bg-zinc-100 text-zinc-600 font-bold py-3 md:py-3.5 rounded-xl hover:bg-zinc-200 transition-colors text-sm" title="Limpiar Filtros">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </a>
                            <button type="submit" class="flex-[3] bg-zinc-900 text-white font-bold py-3 md:py-3.5 rounded-xl shadow-sm hover:bg-zinc-800 transition-all active:scale-95 text-sm flex items-center justify-center gap-2">
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex justify-end mb-6">
            <button onclick="toggleMap()" id="btnToggleMap" class="flex items-center gap-2 text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-colors border border-indigo-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                <span>Ver en Mapa</span>
            </button>
        </div>

        <div id="mapContainer" class="hidden mb-10 w-full h-[500px] rounded-3xl overflow-hidden shadow-sm border border-zinc-200">
            <div id="exploreMap" class="w-full h-full bg-zinc-100"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($sessions as $session)

                <div class="bg-white border border-zinc-200 rounded-2xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group transform-gpu isolate">                    
                    @php
                        $imageUrl = $session->workshop->image_path 
                                        ? asset('storage/' . $session->workshop->image_path) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=4f46e5&background=e0e7ff&size=512';
                        
                        $studioLogo = $session->workshop->studio->icon_path ?? $session->workshop->studio->logo_path ?? null;
                        $studioImageUrl = $studioLogo 
                                        ? asset('storage/' . $studioLogo) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->studio->name).'&color=ffffff&background=18181b&size=128';

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

                    <div class="h-44 bg-zinc-100 relative overflow-hidden cursor-pointer transform-gpu" onclick="openDetailModal({{ $modalData }})">
                        <img src="{{ $imageUrl }}" alt="Clase" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/60 to-transparent opacity-60"></div>
                        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg shadow-sm border border-white/20">
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ $session->workshop->discipline->area->name ?? 'Clase' }}</span>
                        </div>
                        <div class="absolute bottom-3 right-3 group-hover:scale-110 transition-transform duration-300">
                            <div class="relative w-10 h-10 rounded-xl bg-white shadow-lg border-2 border-white overflow-hidden" title="{{ $session->workshop->studio->name }}">
                                <img src="{{ $studioImageUrl }}" alt="Logo Estudio" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-3 cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                            <h3 class="text-lg font-black text-zinc-900 leading-tight hover:text-indigo-600 transition-colors">{{ $session->workshop->name }}</h3>
                        </div>
                        
                        <div class="space-y-2 mt-auto cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                            <div class="flex items-center gap-2 text-sm font-medium text-zinc-600">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ \Carbon\Carbon::parse($session->date)->translatedFormat('D d M') }} • {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm font-medium text-zinc-600">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                                <span class="truncate">{{ $session->workshop->studio->name }}</span>
                            </div>
                        </div>

                        {{-- MAGIA BLADE: DETECCIÓN EXACTA DE FAMILIARES --}}
                        @php
                            $dbSelections = $dbSelectionsBySession[$session->id] ?? [];
                            $enrolledCount = count($dbSelections);
                            $isTitularPaid = isset($dbSelections['titular']) && $dbSelections['titular'] === 'paid';
                            $hasDependents = auth()->check() && auth()->user()->dependents->count() > 0;
                        @endphp
                        
                        <div class="mt-5 pt-4 border-t border-zinc-100 flex items-center justify-between gap-3">
                            <div class="shrink-0">
                                @php
                                    $dropInPrice = $session->workshop->prices->where('class_count', 1)->first();
                                @endphp
                                @if($dropInPrice)
                                    <p class="text-xs text-zinc-500 font-medium">Clase suelta</p>
                                    <p class="text-base font-black text-zinc-900">${{ number_format($dropInPrice->price, 0, ',', '.') }}</p>
                                @else
                                    <p class="text-xs text-zinc-500 font-medium">Desde</p>
                                    <p class="text-base font-black text-zinc-900">Ver Planes</p>
                                @endif
                            </div>
                            
                            @if(auth()->check() && !$hasDependents && $isTitularPaid)
                                <button disabled class="flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 bg-blue-500 text-white cursor-not-allowed opacity-90 shadow-sm border border-blue-600 transition-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Pagada
                                </button>
                            @else
                                <button onclick="handleInterestClick({{ $session->id }}, this)" 
                                        data-db-selections="{{ json_encode($dbSelections) }}"
                                        class="interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center
                                        {{ $enrolledCount > 0 ? 'bg-emerald-500 text-white border-emerald-600 hover:bg-emerald-600 group/btn' : 'bg-zinc-100 text-zinc-900 border-transparent hover:bg-zinc-200' }}">
                                    @if($enrolledCount > 0)
                                        <div class="relative flex items-center justify-center w-full">
                                            <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 
                                                {{ $enrolledCount === 1 ? ($hasDependents ? '1 Seleccionado' : 'En Portal') : $enrolledCount.' en Portal' }}
                                            </span>
                                            <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">
                                                @if($hasDependents)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Modificar
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover
                                                @endif
                                            </span>
                                        </div>
                                    @else
                                        <span>Me Interesa</span>
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 mb-4">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900">No encontramos clases</h3>
                    <p class="text-zinc-500 mt-1">Intenta ajustando los filtros de búsqueda para ver más opciones.</p>
                </div>
            @endforelse
        </div>
        
        <div class="mt-10">{{ $sessions->links() }}</div>
    </div>

    {{-- BARRA FLOTANTE DE CONFIRMACIÓN --}}
    <div id="floating-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 translate-y-24 opacity-0 transition-all duration-300 z-50 pointer-events-none">
        <div class="bg-zinc-900 text-white px-6 py-4 rounded-full shadow-2xl flex items-center gap-6 border border-zinc-700">
            <div class="flex items-center gap-3">
                <span id="selected-count" class="bg-emerald-500 text-white font-black text-sm w-7 h-7 flex items-center justify-center rounded-full shadow-inner">0</span>
                <span class="font-bold text-sm">Cambios detectados</span>
            </div>
            <button onclick="confirmReservations()" id="floating-confirm-btn" class="bg-emerald-500 hover:bg-emerald-400 text-white px-5 py-2.5 rounded-full font-bold text-sm transition-colors active:scale-95 flex items-center gap-2 pointer-events-auto shadow-sm">
                Confirmar Cambios
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </button>
        </div>
    </div>

    @auth
        <div class="fixed bottom-6 right-6 z-[60]">
            <div id="miniCartPanel" class="hidden absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-zinc-200 overflow-hidden transition-all transform origin-bottom-right opacity-0 scale-95">
                <div class="p-5 bg-zinc-900 text-white flex justify-between items-center">
                    <div>
                        <h4 class="font-black text-lg leading-none">Tus Reservas</h4>
                        <p class="text-xs text-zinc-400 mt-1">Pendientes de pago</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->pending_reservations_count > 0)
                            <span class="text-sm bg-rose-500 text-white shadow-inner px-3 py-1 rounded-full font-black">
                                {{ auth()->user()->pending_reservations_count }}
                            </span>
                        @endif
                        <button onclick="toggleMiniCart()" class="bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white p-1.5 rounded-full transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-5 max-h-64 overflow-y-auto custom-scrollbar">
                    @if(auth()->user()->pending_reservations_count > 0)
                        <p class="text-sm text-zinc-500 mb-4 leading-relaxed">
                            Tienes cupos reservados que aún no han sido pagados. 
                            <strong class="text-zinc-800">Asegura tu lugar antes de que se llenen los cupos.</strong>
                        </p>
                        <div class="bg-rose-50 border border-rose-100 p-3.5 rounded-2xl flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-xs text-rose-800 font-medium leading-relaxed">Agregar las clases a Mis Reservas hará que las puedas ver en tu portal de estudiante. Sin embargo, el cupo solo se asegurará al completar el pago.</p>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="w-12 h-12 text-zinc-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="text-sm font-bold text-zinc-500">No tienes reservas pendientes.</p>
                        </div>
                    @endif
                </div>
                
                <div class="p-4 border-t border-zinc-100 bg-zinc-50">
                    <a href="{{ route('cart.index') }}" class="w-full {{ auth()->user()->pending_reservations_count > 0 ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-zinc-300 pointer-events-none' }} text-white font-bold py-3.5 rounded-xl shadow-sm transition-all active:scale-95 text-sm flex items-center justify-center gap-2">
                        Ir a Pagar Mis Clases
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </div>

            <button onclick="toggleMiniCart()" id="btnMiniCart" class="relative bg-zinc-900 text-white p-4 rounded-full shadow-[0_10px_40px_-10px_rgba(0,0,0,0.5)] hover:bg-zinc-800 hover:scale-105 transition-all duration-300 active:scale-95 border border-zinc-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 group">
                <svg class="w-6 h-6 transform group-hover:-rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                @if(auth()->user()->pending_reservations_count > 0)
                    <span class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-rose-500 border-2 border-zinc-900 text-[11px] font-black text-white shadow-sm">
                        {{ auth()->user()->pending_reservations_count }}
                    </span>
                @endif
            </button>
        </div>
    @endauth

    {{-- MODAL DE DETALLES DEL TALLER --}}
    <div id="detailModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-zinc-900/70 backdrop-blur-sm transition-opacity" onclick="closeDetailModal()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md md:max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[95vh]" id="detailModalCard">
            <div class="h-40 sm:h-48 w-full bg-zinc-200 relative shrink-0">
                <img id="m_image" src="" alt="Cover" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/60 to-transparent"></div>
                <button onclick="closeDetailModal()" class="absolute top-4 right-4 p-2 text-zinc-700 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full transition-colors focus:outline-none shadow-sm z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-6 md:p-8 overflow-y-auto flex-1 custom-scrollbar">
                <div class="mb-6">
                    <a href="#" id="m_studio_link" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition-colors text-[10px] font-black rounded-md tracking-widest uppercase mb-3">
                        <span id="m_studio">Estudio</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                    <h3 id="m_title" class="text-2xl font-black text-zinc-900 leading-tight">Clase</h3>
                </div>

                <div id="m_video_container" class="hidden mb-6 rounded-2xl overflow-hidden shadow-sm border border-zinc-200 bg-zinc-900 relative group transition-all duration-300 mx-auto">
                    <iframe id="m_video_frame" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>

                <div id="m_description_container" class="hidden mb-8">
                    <h4 class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-2">Acerca de la clase</h4>
                    <p id="m_description" class="text-sm text-zinc-600 leading-relaxed whitespace-pre-line"></p>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-indigo-500 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p id="m_date" class="text-sm font-bold text-zinc-900 capitalize">Fecha</p>
                            <p id="m_time" class="text-xs font-medium text-zinc-500">Hora</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-emerald-500 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Profesor/a</p>
                            <p id="m_teacher" class="text-sm font-bold text-zinc-900 leading-tight truncate">Nombre</p>
                            <a href="#" id="m_teacher_email" class="hidden text-[11px] font-medium text-indigo-600 hover:text-indigo-800 transition-colors mt-0.5 truncate"></a>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-rose-500 mt-1 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Ubicación</p>
                            <p id="m_address" class="text-sm font-bold text-zinc-900 mb-2 leading-tight">Dirección</p>
                            <a href="#" id="m_map_link" target="_blank" class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                Cómo llegar en Maps <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL MULTI-SELECCIÓN FAMILIAR --}}
    @auth
    <div id="familySelectionModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col" id="familySelectionCard">
            <div class="p-6 border-b border-zinc-100 flex justify-between items-center">
                <h3 class="text-lg font-black text-zinc-900 leading-tight">¿Quiénes asistirán?<br><span class="text-sm font-medium text-zinc-500">Selecciona uno o más</span></h3>
                <button onclick="closeFamilyModal()" class="text-zinc-400 hover:text-zinc-600 bg-zinc-50 p-2 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
                <button type="button" onclick="toggleModalSelection('titular')" id="modal_opt_titular" class="w-full flex items-center justify-between p-4 rounded-xl border-2 border-zinc-100 hover:border-indigo-200 transition-all group">
                    <div class="flex flex-col text-left">
                        <span class="font-bold text-zinc-900 text-sm">Yo ({{ Auth::user()->name }})</span>
                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mt-0.5">Titular</span>
                    </div>
                    <div class="w-6 h-6 rounded-full border-2 border-zinc-200 flex items-center justify-center check-icon transition-colors">
                        <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest hidden"></span>
                </button>
                
                @foreach(Auth::user()->dependents as $dependent)
                    <button type="button" onclick="toggleModalSelection({{ $dependent->id }})" id="modal_opt_{{ $dependent->id }}" class="w-full flex items-center justify-between p-4 rounded-xl border-2 border-zinc-100 hover:border-emerald-200 transition-all group">
                        <div class="flex flex-col text-left">
                            <span class="font-bold text-zinc-900 text-sm">{{ $dependent->first_name }} {{ $dependent->last_name }}</span>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mt-0.5">{{ $dependent->relationship ?? 'Familiar' }}</span>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 border-zinc-200 flex items-center justify-center check-icon transition-colors">
                            <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest hidden"></span>
                    </button>
                @endforeach
            </div>

            <div class="p-4 bg-white border-t border-zinc-100 flex flex-col gap-3">
                <button onclick="saveModalSelection()" class="w-full bg-zinc-900 text-white font-bold py-3.5 rounded-xl shadow-sm hover:bg-zinc-800 transition-all active:scale-95 text-sm">
                    Guardar Selección
                </button>
                <a href="{{ route('profile.family.index') }}" class="text-xs font-bold text-zinc-500 hover:text-indigo-600 transition-colors flex items-center justify-center gap-1">
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
                            : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=512';
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
                btn.classList.replace('bg-indigo-50', 'bg-indigo-600');
                btn.classList.replace('text-indigo-600', 'text-white');
                btnText.innerText = 'Ocultar Mapa';
                if (!mapInstance) initMap();
            } else {
                container.classList.add('hidden');
                btn.classList.replace('bg-indigo-600', 'bg-indigo-50');
                btn.classList.replace('text-white', 'text-indigo-600');
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
                                <div class="relative w-full h-24 mb-2 rounded-lg overflow-hidden bg-zinc-100">
                                    <img src="${loc.image}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                </div>
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">${loc.studio}</p>
                                    <svg class="w-3 h-3 text-zinc-300 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </div>
                                <h4 class="font-bold text-sm text-zinc-900 leading-tight group-hover:text-indigo-600 transition-colors">${loc.title}</h4>
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
        const hasDependents = @json(Auth::check() ? Auth::user()->dependents->count() > 0 : false);
        
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
                    btn.classList.remove('border-zinc-100', 'hover:border-emerald-200');
                    statusLabel.innerText = "YA PAGADO";
                    statusLabel.classList.replace('text-indigo-600', 'text-blue-700');
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
                    btn.classList.remove('border-zinc-100');
                    checkCircle.classList.add('bg-emerald-600', 'border-emerald-600');
                    checkCircle.classList.remove('border-zinc-200');
                    checkMark.classList.remove('opacity-0');
                } else {
                    btn.classList.remove('border-emerald-600', 'bg-emerald-50/50');
                    btn.classList.add('border-zinc-100');
                    checkCircle.classList.remove('bg-emerald-600', 'border-emerald-600');
                    checkCircle.classList.add('border-zinc-200');
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
                    ? `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Modificar`
                    : `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover`;

                btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center bg-emerald-500 text-white border border-emerald-600 hover:bg-emerald-600 group/btn";
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
                    btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[140px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center bg-zinc-100 text-zinc-900 border border-transparent hover:bg-zinc-200";
                    btnElement.innerHTML = `Me Interesa`;
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
            const originalBtnHTML = btn.innerHTML; // Guardamos el estado del botón
            
            btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
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

            // ==========================================
            // 🔥 ZONA DE DEBUG: INTERCEPCIÓN DEL PAYLOAD
            // ==========================================
            console.group("🚀 DEBUG: ENVIANDO AL BACKEND");
            console.log("Estructura original de pendingClasses (Map):", pendingClasses);
            console.log("Arreglo formateado que procesará el foreach:");
            console.table(formattedSessions);
            console.log("JSON exacto que viaja en el body:", JSON.stringify(payload, null, 2));
            console.groupEnd();

            // Pausamos la ejecución para que leas la consola
            const proceed = confirm("👀 ¡Debug activado! Revisa la consola (F12) para ver la tabla de datos.\n\n¿Los datos son correctos? Presiona 'Aceptar' para enviarlos al servidor, o 'Cancelar' para abortar.");
            
            if (!proceed) {
                console.log("🛑 Envío abortado por el usuario.");
                btn.innerHTML = originalBtnHTML;
                btn.disabled = false;
                return; // Matamos la función aquí, el fetch nunca se ejecuta
            }
            // ==========================================

            fetch("{{ route('global.student.enroll.bulk') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) { 
                    alert(data.message); 
                    resetConfirmButton(btn); 
                } else {
                    pendingClasses.clear();
                    sessionStorage.setItem('cart_auto_open', 'true');
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error("Error en bulk:", error);
                alert("Hubo un error de conexión al procesar tus reservas.");
                resetConfirmButton(btn);
            });
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
    </script>
</x-app-layout>
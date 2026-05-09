<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-24 relative">
        
        {{-- Encabezado del Marketplace --}}
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-black text-zinc-900 tracking-tight">Descubre tu próxima clase</h1>
            <p class="mt-4 text-zinc-500 text-lg">Encuentra y reserva sesiones en los mejores estudios cerca de ti.</p>
        </div>

        {{-- BARRA DE FILTROS --}}
        <div class="bg-white p-4 md:p-6 rounded-3xl shadow-sm border border-zinc-200 mb-8">
            <form action="{{ route('explore') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                
                {{-- Filtro Ciudad --}}
                <div class="w-full md:w-1/5">
                    <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Ciudad</label>
                    <select name="city" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                        <option value="">Todas</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro Área --}}
                <div class="w-full md:w-1/5">
                    <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Categoría</label>
                    <select name="area" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                        <option value="">Todas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->name }}" {{ request('area') == $area->name ? 'selected' : '' }}>{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Rango de Fechas (Desde) --}}
                <div class="w-full md:w-1/5">
                    <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" 
                           class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                </div>

                {{-- Rango de Fechas (Hasta) --}}
                <div class="w-full md:w-1/5">
                    <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" 
                           class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                </div>

                {{-- Botones de Acción --}}
                <div class="w-full md:w-1/5 flex gap-2">
                    <a href="{{ route('explore') }}" class="flex-1 flex items-center justify-center bg-zinc-100 text-zinc-600 font-bold py-3 rounded-xl hover:bg-zinc-200 transition-colors text-sm" title="Limpiar Filtros">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </a>
                    <button type="submit" class="flex-[3] bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition-all active:scale-95 text-sm flex items-center justify-center gap-2">
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        {{-- CONTROLES DE VISTA (Toggle Mapa) --}}
        <div class="flex justify-end mb-6">
            <button onclick="toggleMap()" id="btnToggleMap" class="flex items-center gap-2 text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-colors border border-indigo-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                <span>Ver en Mapa</span>
            </button>
        </div>

        {{-- CONTENEDOR DEL MAPA (Oculto por defecto) --}}
        <div id="mapContainer" class="hidden mb-10 w-full h-[500px] rounded-3xl overflow-hidden shadow-sm border border-zinc-200">
            <div id="exploreMap" class="w-full h-full bg-zinc-100"></div>
        </div>

        {{-- GRILLA DE RESULTADOS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($sessions as $session)
                <div class="bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group">
                    
                    {{-- Cabecera / Imagen --}}
                    @php
                        // 1. Imagen del Taller (Fondo)
                        $imageUrl = $session->workshop->image_path 
                                        ? asset('storage/' . $session->workshop->image_path) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=4f46e5&background=e0e7ff&size=512';
                        
                        // 2. Imagen del Estudio (Miniatura)
                        // Asumimos que se llama logo_path o image_path. Si no, genera iniciales premium oscuras.
                        $studioLogo = $session->workshop->studio->logo_path ?? $session->workshop->studio->image_path ?? null;
                        $studioImageUrl = $studioLogo 
                                        ? asset('storage/' . $studioLogo) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->studio->name).'&color=ffffff&background=18181b&size=128';
                    @endphp

                    <div class="h-44 bg-zinc-100 relative overflow-hidden cursor-pointer" 
                         onclick="openDetailModal({{ json_encode([
                             'title' => $session->workshop->name,
                             'studio' => $session->workshop->studio->name,
                             'teacher' => $session->workshop->teacher->name ?? 'Por asignar',
                             'date' => \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F'),
                             'time' => \Carbon\Carbon::parse($session->start_time)->format('H:i'),
                             'image' => $imageUrl,
                             'address' => $session->workshop->address ?? 'Dirección no especificada'
                         ]) }})">
                        
                        {{-- Foto del Taller (Principal) --}}
                        <img src="{{ $imageUrl }}" alt="Clase" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/60 to-transparent opacity-60"></div>

                        {{-- Badge de Disciplina (Arriba Izquierda) --}}
                        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg shadow-sm border border-white/20">
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ $session->workshop->discipline->area->name ?? 'Clase' }}</span>
                        </div>

                        {{-- NUEVO: Miniatura del Estudio (Abajo Derecha) --}}
                        <div class="absolute bottom-3 right-3 group-hover:scale-110 transition-transform duration-300">
                            <div class="relative w-10 h-10 rounded-xl bg-white shadow-lg border-2 border-white overflow-hidden" title="{{ $session->workshop->studio->name }}">
                                <img src="{{ $studioImageUrl }}" alt="Logo Estudio" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>

                    {{-- Contenido Principal --}}
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-3 cursor-pointer" 
                             onclick="openDetailModal({{ json_encode([
                                 'title' => $session->workshop->name,
                                 'studio' => $session->workshop->studio->name,
                                 'teacher' => $session->workshop->teacher->name ?? 'Por asignar',
                                 'date' => \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F'),
                                 'time' => \Carbon\Carbon::parse($session->start_time)->format('H:i'),
                                 'image' => $imageUrl,
                                 'address' => $session->workshop->address ?? 'Dirección no especificada'
                             ]) }})">
                            <h3 class="text-lg font-black text-zinc-900 leading-tight hover:text-indigo-600 transition-colors">{{ $session->workshop->name }}</h3>
                        </div>
                        
                        <div class="space-y-2 mt-auto">
                            <div class="flex items-center gap-2 text-sm font-medium text-zinc-600">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ \Carbon\Carbon::parse($session->date)->translatedFormat('D d M') }} • {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm font-medium text-zinc-600">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                                <span class="truncate">{{ $session->workshop->studio->name }}</span>
                            </div>
                        </div>

                        {{-- FOOTER DE COMPRA CON CONFIRMACIÓN --}}
                        @php
                            $isEnrolled = in_array($session->id, $enrolledSessionIds ?? []);
                            $isPaid = in_array($session->id, $paidSessionIds ?? []);
                        @endphp
                        
                        <div class="mt-5 pt-4 border-t border-zinc-100 flex items-center justify-between">
                            <div>
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
                            
                            @if($isPaid)
                                {{-- ESTADO: PAGADA (Azul, Inmutable) --}}
                                <button disabled class="px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 bg-blue-500 text-white cursor-not-allowed opacity-90 shadow-sm border border-blue-600 transition-none w-full sm:w-[140px] justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Pagada
                                </button>
                            @else
                                {{-- ESTADOS INTERACTIVOS (Clickeables) --}}
                                <button onclick="toggleSelection({{ $session->id }}, this)" 
                                        data-initial-state="{{ $isEnrolled ? 'enrolled' : 'unenrolled' }}"
                                        class="interest-btn px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center w-full sm:w-[140px]
                                        {{ $isEnrolled ? 'bg-emerald-500 text-white border-emerald-600 hover:bg-emerald-600 group/btn' : 'bg-zinc-100 text-zinc-900 border-transparent hover:bg-zinc-200' }}">
                                    @if($isEnrolled)
                                        <div class="relative flex items-center justify-center w-full">
                                            <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 
                                                En mi Portal
                                            </span>
                                            <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> 
                                                Remover
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
        
        <div class="mt-10">
            {{ $sessions->links() }}
        </div>
    </div>

    {{-- BARRA FLOTANTE DE CONFIRMACIÓN --}}
    <div id="floating-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 translate-y-24 opacity-0 transition-all duration-300 z-50 pointer-events-none">
        <div class="bg-zinc-900 text-white px-6 py-4 rounded-full shadow-2xl flex items-center gap-6 border border-zinc-700">
            <div class="flex items-center gap-3">
                <span id="selected-count" class="bg-emerald-500 text-white font-black text-sm w-7 h-7 flex items-center justify-center rounded-full shadow-inner">0</span>
                <span class="font-bold text-sm">Cambios seleccionados</span>
            </div>
            <button onclick="confirmReservations()" id="floating-confirm-btn" class="bg-emerald-500 hover:bg-emerald-400 text-white px-5 py-2.5 rounded-full font-bold text-sm transition-colors active:scale-95 flex items-center gap-2 pointer-events-auto shadow-sm">
                Confirmar Cambios
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </button>
        </div>
    </div>

    {{-- MODAL DE DETALLES DEL TALLER --}}
    <div id="detailModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" onclick="closeDetailModal()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="detailModalCard">
            
            <div class="h-40 sm:h-48 w-full bg-zinc-200 relative">
                <img id="m_image" src="" alt="Cover" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/50 to-transparent"></div>
                <button onclick="closeDetailModal()" class="absolute top-4 right-4 p-2 text-zinc-700 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full transition-colors focus:outline-none shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 md:p-8 overflow-y-auto flex-1">
                <div class="mb-6">
                    <span id="m_studio" class="px-2.5 py-1 bg-zinc-100 text-zinc-600 text-[10px] font-black rounded-md tracking-widest uppercase mb-3 inline-block">Estudio</span>
                    <h3 id="m_title" class="text-2xl font-black text-zinc-900 leading-tight">Clase</h3>
                </div>
                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p id="m_date" class="text-sm font-bold text-zinc-900 capitalize">Fecha</p>
                            <p id="m_time" class="text-xs font-medium text-zinc-500">Hora</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-emerald-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Profesor/a</p>
                            <p id="m_teacher" class="text-sm font-bold text-zinc-900">Nombre</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-rose-500 mt-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                        </div>
                        <div class="flex-1">
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

    {{-- PREPARAMOS LOS DATOS EN PHP PARA EL MAPA --}}
    @php
        $mapLocationsData = $sessions->map(function($s) {
            $imageUrl = $s->workshop->image_path 
                            ? asset('storage/' . $s->workshop->image_path) 
                            : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=512';
            return [
                'title' => $s->workshop->name,
                'studio' => $s->workshop->studio->name,
                'lat' => (float) ($s->workshop->latitude ?? $s->workshop->studio->latitude),
                'lng' => (float) ($s->workshop->longitude ?? $s->workshop->studio->longitude),
                'image' => $imageUrl
            ];
        })->filter(function($l) { return $l['lat'] !== 0.0; })->values(); 
    @endphp

    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.key') }}"></script>

    {{-- SCRIPTS PRINCIPALES --}}
    <script>
        // ==========================================
        // 1. LÓGICA DEL MAPA Y MODALES
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
                center: centerPos, zoom: 13, mapTypeControl: false, streetViewControl: false
            });
            const infoWindow = new google.maps.InfoWindow();

            mapLocations.forEach(loc => {
                if (loc.lat && loc.lng) {
                    const marker = new google.maps.Marker({
                        position: { lat: loc.lat, lng: loc.lng }, map: mapInstance, title: loc.title, animation: google.maps.Animation.DROP
                    });
                    marker.addListener('click', () => {
                        const content = `
                            <div class="p-2 max-w-[200px]">
                                <img src="${loc.image}" class="w-full h-24 object-cover rounded-lg mb-2">
                                <p class="text-[10px] font-black text-indigo-600 uppercase mb-1">${loc.studio}</p>
                                <h4 class="font-bold text-sm text-zinc-900 leading-tight">${loc.title}</h4>
                            </div>
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
            document.getElementById('m_teacher').innerText = data.teacher;
            document.getElementById('m_date').innerText = data.date;
            document.getElementById('m_time').innerText = data.time + ' hrs';
            document.getElementById('m_address').innerText = data.address;
            document.getElementById('m_image').src = data.image;
            document.getElementById('m_map_link').href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(data.address)}`;

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
            }, 300);
        }

        // ==========================================
        // 2. LÓGICA DEL CARRITO (Confirmación Requerida)
        // ==========================================
        const isLoggedIn = @json(Auth::check());
        let pendingClasses = new Set(); 

        function toggleSelection(sessionId, btnElement) {
            const initialState = btnElement.getAttribute('data-initial-state');
            
            if (pendingClasses.has(sessionId)) {
                pendingClasses.delete(sessionId);
                
                if (initialState === 'enrolled') {
                    btnElement.className = "interest-btn px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center w-full sm:w-[140px] bg-emerald-500 text-white border border-emerald-600 hover:bg-emerald-600 group/btn";
                    btnElement.innerHTML = `
                        <div class="relative flex items-center justify-center w-full">
                            <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 
                                En mi Portal
                            </span>
                            <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> 
                                Remover
                            </span>
                        </div>
                    `;
                } else {
                    btnElement.className = "interest-btn px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center w-full sm:w-[140px] bg-zinc-100 text-zinc-900 border border-transparent hover:bg-zinc-200";
                    btnElement.innerHTML = `Me Interesa`;
                }
            } else {
                pendingClasses.add(sessionId);
                
                if (initialState === 'enrolled') {
                    btnElement.className = "interest-btn px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center w-full sm:w-[140px] bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100";
                    btnElement.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover`;
                } else {
                    btnElement.className = "interest-btn px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center w-full sm:w-[140px] bg-emerald-500 text-white border border-emerald-600 hover:bg-emerald-600 group/btn";
                    btnElement.innerHTML = `
                        <div class="relative flex items-center justify-center w-full">
                            <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 
                                Seleccionada
                            </span>
                            <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> 
                                Desmarcar
                            </span>
                        </div>
                    `;
                }
            }
            
            toggleFloatingBar();
        }

        function toggleFloatingBar() {
            const bar = document.getElementById('floating-bar');
            const countLabel = document.getElementById('selected-count');
            const count = pendingClasses.size;
            
            countLabel.innerText = count;

            if (count > 0) {
                bar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                bar.classList.add('translate-y-0', 'opacity-100');
            } else {
                bar.classList.remove('translate-y-0', 'opacity-100');
                bar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
            }
        }

        async function confirmReservations() {
            if (!isLoggedIn) {
                localStorage.setItem('espaciocore_cart', JSON.stringify(Array.from(pendingClasses)));
                window.location.href = "{{ route('register') }}"; 
                return;
            }

            const btn = document.getElementById('floating-confirm-btn');
            btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
            btn.disabled = true;

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const payload = {
                session_ids: Array.from(pendingClasses)
            };

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
                    btn.innerHTML = `Confirmar Cambios <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
                    btn.disabled = false;
                } else {
                    pendingClasses.clear();
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error("Error en bulk:", error);
                alert("Hubo un error de conexión al procesar tus reservas.");
                btn.innerHTML = `Confirmar Cambios <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
                btn.disabled = false;
            });
        }
    </script>
</x-app-layout>
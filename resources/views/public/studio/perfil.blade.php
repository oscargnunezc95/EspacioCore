{{-- ============================================================ --}}
{{-- Vista: Perfil del Estudio (Información, Contacto, Mapa)    --}}
{{-- Controlador: StudioPublicController@perfil                 --}}
{{-- Refinada con design-taste-frontend                         --}}
{{-- ============================================================ --}}
<x-guest-layout>
    <div class="min-h-screen bg-transparent relative z-10">

        @include('public.studio._studio-nav', ['activeTab' => 'perfil'])

        <div class="w-full pt-10 pb-32 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 md:px-8">

                {{-- ========================================== --}}
                {{-- CONTENIDO: Perfil del Estudio              --}}
                {{-- ========================================== --}}
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-col gap-8 max-w-4xl mx-auto">

                        {{-- TARJETA 1: IDENTIDAD DEL ESTUDIO --}}
                        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden flex flex-col">
                            @php
                                $studioImage = $studio->cover_path ?? null;
                                $studioImageUrl = $studioImage
                                    ? asset('storage/' . $studioImage)
                                    : 'https://ui-avatars.com/api/?name='.urlencode($studio->name).'&color=dc2626&background=fee2e2&size=512';
                            @endphp
                            <div class="w-full h-48 sm:h-64 md:h-80 bg-stone-100 relative shrink-0">
                                <img src="{{ $studioImageUrl }}" alt="Foto de {{ $studio->name }}" class="w-full h-full object-cover">
                            </div>

                            <div class="p-6 md:p-8 flex-1 flex flex-col items-center text-center sm:items-start sm:text-left">
                                <div class="w-full">
                                    @if($studio->description)
                                        <p class="text-stone-600 text-sm md:text-base leading-relaxed max-w-[65ch]">
                                            {{ $studio->description }}
                                        </p>
                                    @else
                                        <p class="text-stone-400 text-sm">No hay descripción disponible para este espacio por el momento.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- TARJETA 2: CONTACTO Y REDES --}}
                        @if($studio->email || $studio->whatsapp || $studio->instagram_url || $studio->tiktok_url || $studio->youtube_url)
                            <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden flex flex-col p-6 md:p-8">

                                @if($studio->email || $studio->whatsapp)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @if($studio->email)
                                            <a href="mailto:{{ $studio->email }}"
                                               class="flex items-center gap-4 p-4 rounded-xl bg-stone-50 hover:bg-red-50 border border-stone-100 hover:border-red-200 transition-all duration-200 group">
                                                <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0 group-hover:bg-red-200 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Correo Electrónico</p>
                                                    <p class="text-sm font-semibold text-stone-700 group-hover:text-red-700 truncate transition-colors">{{ $studio->email }}</p>
                                                </div>
                                                <svg class="w-4 h-4 text-stone-300 group-hover:text-red-400 shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        @endif

                                        @if($studio->whatsapp)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $studio->whatsapp) }}"
                                               target="_blank"
                                               class="flex items-center gap-4 p-4 rounded-xl bg-stone-50 hover:bg-red-50 border border-stone-100 hover:border-red-200 transition-all duration-200 group">
                                                <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0 group-hover:bg-red-200 transition-colors">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">WhatsApp</p>
                                                    <p class="text-sm font-semibold text-stone-700 group-hover:text-red-700 truncate transition-colors">{{ $studio->whatsapp }}</p>
                                                </div>
                                                <svg class="w-4 h-4 text-stone-300 group-hover:text-red-400 shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                {{-- Redes Sociales --}}
                                @if($studio->instagram_url || $studio->tiktok_url || $studio->youtube_url)
                                    <div class="flex items-center justify-center gap-3 {{ ($studio->email || $studio->whatsapp) ? 'mt-6 pt-6 border-t border-stone-100' : '' }}">
                                        <span class="text-xs font-semibold text-stone-400 uppercase tracking-wider hidden sm:inline">Redes</span>
                                        @if($studio->instagram_url)
                                            <a href="{{ $studio->instagram_url }}" target="_blank"
                                               class="p-3 bg-stone-100 hover:bg-gradient-to-tr hover:from-amber-500 hover:via-red-500 hover:to-purple-600 text-stone-500 hover:text-white rounded-xl transition-all duration-200 active:scale-95" title="Instagram">
                                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                            </a>
                                        @endif
                                        @if($studio->tiktok_url)
                                            <a href="{{ $studio->tiktok_url }}" target="_blank"
                                               class="p-3 bg-stone-100 hover:bg-black text-stone-500 hover:text-white rounded-xl transition-all duration-200 active:scale-95" title="TikTok">
                                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
                                            </a>
                                        @endif
                                        @if($studio->youtube_url)
                                            <a href="{{ $studio->youtube_url }}" target="_blank"
                                               class="p-3 bg-stone-100 hover:bg-red-600 text-stone-500 hover:text-white rounded-xl transition-all duration-200 active:scale-95" title="YouTube">
                                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

{{-- TARJETA 3: UBICACIÓN Y MAPA --}}
                        @if($studio->latitude && $studio->longitude)
                            <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden flex flex-col">
                                <div class="w-full h-[350px] md:h-[450px] bg-stone-100 relative z-0">
                                    <div id="studioLocationMap" class="absolute inset-0 w-full h-full"></div>
                                </div>
                                <div class="p-6 md:p-8 flex flex-col sm:flex-row items-center sm:items-start justify-between gap-4 shrink-0 bg-white text-center sm:text-left">
                                    <div>
                                        <h3 class="text-lg font-bold text-stone-900 mb-1.5">Nuestra Ubicación</h3>
                                        <p class="text-sm text-stone-600 leading-relaxed">{{ $studio->address }}</p>
                                    </div>
                                    
                                    {{-- 🚀 CORREGIDO: Búsqueda infalible por coordenadas exactas en Google Maps --}}
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $studio->latitude }},{{ $studio->longitude }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl transition-all duration-200 active:scale-[0.98] font-semibold text-sm shadow-sm">
                                        <span>Cómo llegar</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </a>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- GRILLA DE POSTS DESTACADOS --}}
                    @if(!empty($studio->featured_posts))
                        <div class="mt-16 max-w-5xl mx-auto">
                            <h3 class="text-xl font-bold text-stone-900 mb-8 text-center">Nuestra Vibra en Redes</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                                @foreach($studio->featured_posts as $postUrl)
                                    @php
                                        $cleanUrl = strtok($postUrl, '?');
                                        $embedUrl = rtrim($cleanUrl, '/') . '/embed';
                                    @endphp
                                    <div class="aspect-[9/16] rounded-2xl overflow-hidden border border-stone-200 bg-stone-50 w-full max-w-[340px] mx-auto hover:border-stone-300 transition-all duration-300">
                                        <iframe src="{{ $embedUrl }}" class="w-full h-full" frameborder="0" scrolling="no" allowtransparency="true"></iframe>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- SCRIPT PARA EL MAPA (Leaflet + OpenStreetMap) --}}
                @if($studio->latitude && $studio->longitude)
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const mapContainer = document.getElementById('studioLocationMap');
                        if (!mapContainer || mapContainer.hasAttribute('data-loaded')) return;

                        const studioPos = [{{ (float)$studio->latitude }}, {{ (float)$studio->longitude }}];

                        const map = L.map(mapContainer, {
                            center: studioPos,
                            zoom: 16,
                            zoomControl: true,
                            attributionControl: true
                        });

                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }).addTo(map);

                        const customIcon = L.divIcon({
                            className: 'custom-studio-pin',
                            html: `<div class="w-10 h-10 bg-red-600 border-2 border-white rounded-full shadow-lg flex items-center justify-center">
                                     <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                   </div>`,
                            iconSize: [40, 40],
                            iconAnchor: [20, 40],
                            popupAnchor: [0, -44]
                        });

                        // 🚀 CORREGIDO: Uso del helper moderno Js::from() inmune a errores de compilación
                        const studioName = {{ Js::from($studio->name) }};

                        L.marker(studioPos, { icon: customIcon })
                            .addTo(map)
                            .bindPopup(`
                                <div class="text-center font-sans p-1">
                                    <p class="font-bold text-stone-900 text-sm">${studioName}</p>
                                </div>
                            `, { closeButton: false, autoClose: true });

                        mapContainer.setAttribute('data-loaded', 'true');
                    });
                </script>
                @endif

            </div>
        </div>

        @include('public.studio._mini-cart')

    </div>

</x-guest-layout>
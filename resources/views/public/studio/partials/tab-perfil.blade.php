<div class="max-w-7xl mx-auto">
    
    {{-- Contenedor Principal: Columna única, apilada y centrada --}}
    <div class="flex flex-col gap-8 max-w-4xl mx-auto">
        
        {{-- ========================================== --}}
        {{-- TARJETA 1: IDENTIDAD DEL ESTUDIO --}}
        {{-- ========================================== --}}
        <div class="bg-white border border-zinc-200 rounded-3xl overflow-hidden shadow-sm flex flex-col transform-gpu isolate">
            
            {{-- 1. Foto del Estudio (Estilo Banner) --}}
            @php
                $studioImage = $studio->logo_path ?? $studio->icon_path ?? null;
                $studioImageUrl = $studioImage 
                    ? asset('storage/' . $studioImage) 
                    : 'https://ui-avatars.com/api/?name='.urlencode($studio->name).'&color=4f46e5&background=e0e7ff&size=512';
            @endphp
            <div class="w-full h-48 sm:h-64 md:h-80 bg-zinc-100 relative shrink-0 border-b border-zinc-100">
                <img src="{{ $studioImageUrl }}" alt="Foto de {{ $studio->name }}" class="w-full h-full object-cover">
            </div>

            {{-- Contenedor interior de Texto (Con padding) --}}
            <div class="p-6 md:p-8 flex-1 flex flex-col items-center text-center sm:items-start sm:text-left">
                
                {{-- 2. Red Social --}}
                @if($studio->social_link)
                    <div class="mb-6">
                        <a href="{{ $studio->social_link }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"></path>
                            </svg>
                            Instagram Oficial
                        </a>
                    </div>
                @endif

                {{-- 3. Descripción --}}
                <div class="w-full">
                    @if($studio->description)
                        <p class="text-zinc-600 text-sm md:text-base whitespace-pre-line leading-relaxed">
                            {{ $studio->description }}
                        </p>
                    @else
                        <p class="text-zinc-500 italic text-sm">No hay descripción disponible para este espacio por el momento.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- TARJETA 2: UBICACIÓN Y MAPA --}}
        {{-- ========================================== --}}
        @if($studio->latitude && $studio->longitude)
            <div class="bg-white border border-zinc-200 rounded-3xl overflow-hidden shadow-sm flex flex-col transform-gpu isolate">
                
                {{-- Cabecera Operativa (Dirección y Botón) --}}
                <div class="p-6 md:p-8 border-b border-zinc-100 flex flex-col sm:flex-row items-center sm:items-start justify-between gap-4 shrink-0 bg-white z-10 text-center sm:text-left">
                    <div>
                        <h3 class="text-lg font-black text-zinc-900 mb-1.5">Nuestra Ubicación</h3>
                        <p class="text-sm font-medium text-zinc-600 leading-tight">{{ $studio->address }}</p>
                    </div>
                    <a href="https://maps.google.com/?q={{ urlencode($studio->address) }}" 
                       target="_blank" 
                       class="w-full sm:w-auto shrink-0 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800 px-6 py-3 rounded-xl transition-colors flex items-center justify-center gap-2 font-bold text-sm">
                        <span>¿Cómo llegar?</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </a>
                </div>
                
                {{-- Contenedor del Mapa (Más alto en este diseño) --}}
                <div class="w-full h-[350px] md:h-[450px] bg-zinc-100 relative z-0">
                    <div id="studioLocationMap" class="absolute inset-0 w-full h-full"></div>
                </div>
            </div>
        @endif

    </div> {{-- Fin de la columna apilada --}}

    {{-- ========================================== --}}
    {{-- GRILLA DE POSTS DESTACADOS --}}
    {{-- ========================================== --}}
    @if(!empty($studio->featured_posts))
        <div class="mt-16 max-w-5xl mx-auto">
            <h3 class="text-xl font-black text-zinc-900 mb-8 text-center uppercase tracking-widest">Nuestra Vibra en Redes</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($studio->featured_posts as $postUrl)
                    @php
                        $cleanUrl = strtok($postUrl, '?');
                        $embedUrl = rtrim($cleanUrl, '/') . '/embed';
                    @endphp
                    <div class="aspect-[9/16] rounded-3xl overflow-hidden border border-zinc-200 shadow-sm bg-zinc-50 w-full max-w-[340px] mx-auto hover:shadow-xl transition-all duration-300 transform-gpu isolate">
                        <iframe src="{{ $embedUrl }}" class="w-full h-full" frameborder="0" scrolling="no" allowtransparency="true"></iframe>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- SCRIPT PARA EL MAPA (Con protector de Race Condition) --}}
@if($studio->latitude && $studio->longitude)
<script>
    function initStudioProfileMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            setTimeout(initStudioProfileMap, 100);
            return;
        }

        const mapContainer = document.getElementById('studioLocationMap');
        
        if (mapContainer && !mapContainer.hasAttribute('data-loaded')) {
            const studioPos = { 
                lat: {{ (float)$studio->latitude }}, 
                lng: {{ (float)$studio->longitude }} 
            };

            const map = new google.maps.Map(mapContainer, {
                center: studioPos,
                zoom: 16,
                disableDefaultUI: true,
                zoomControl: true,
                styles: [
                    { "featureType": "poi", "stylers": [{ "visibility": "off" }] } // Mapa limpio
                ]
            });

            new google.maps.Marker({
                position: studioPos,
                map: map,
                title: "{{ $studio->name }}",
                animation: google.maps.Animation.DROP
            });

            mapContainer.setAttribute('data-loaded', 'true');
        }
    }

    document.addEventListener('DOMContentLoaded', initStudioProfileMap);
</script>
@endif
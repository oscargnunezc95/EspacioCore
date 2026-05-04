<x-app-layout>
    <div class="py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-zinc-900 tracking-tight">Mis Espacios</h1>
            <p class="mt-3 text-zinc-500 font-light text-lg">Selecciona el estudio que deseas administrar o registra una nueva sucursal.</p>
        </div>

        @if (session('success'))
            <div class="mb-8 p-4 bg-emerald-50 text-emerald-700 rounded-xl font-medium border border-emerald-200 text-center flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Lista de Estudios Creados --}}
            @forelse($studios as $studio)
                <a href="{{ route('dashboard', ['subdomain' => $studio->subdomain]) }}" class="group bg-white rounded-3xl p-8 border border-zinc-200 shadow-sm hover:shadow-xl hover:border-zinc-300 transition-all duration-300 flex flex-col justify-between min-h-[200px]">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            {{-- Lógica visual: Si hay logo lo muestra, si no, muestra la inicial --}}
                            @if($studio->logo_path)
                                <img src="{{ asset('storage/' . $studio->logo_path) }}" alt="Logo {{ $studio->name }}" class="h-12 w-12 rounded-xl object-cover border border-zinc-100 shadow-sm">
                            @else
                                <div class="h-12 w-12 rounded-xl bg-zinc-900 text-white flex items-center justify-center font-bold text-xl shadow-sm">
                                    {{ substr($studio->name, 0, 1) }}
                                </div>
                            @endif
                            
                            <span class="bg-zinc-100 text-zinc-600 text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full border border-zinc-200">
                                Activo
                            </span>
                        </div>
                        <h2 class="text-2xl font-bold text-zinc-900 group-hover:text-zinc-600 transition">{{ $studio->name }}</h2>
                        <p class="text-sm font-medium text-zinc-400 mt-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            {{ $studio->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'espaciocore.test' }}
                        </p>
                        @if($studio->city)
                            <p class="text-xs font-medium text-zinc-400 mt-1 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ $studio->city }}, {{ $studio->country }}
                            </p>
                        @endif
                    </div>
                    <div class="mt-6 flex items-center text-sm font-bold text-zinc-900 uppercase tracking-wide group-hover:translate-x-2 transition-transform">
                        Entrar al Panel <span class="ml-2">&rarr;</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 bg-white border border-dashed border-zinc-300 rounded-3xl text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <h3 class="text-lg font-medium text-zinc-900">Aún no tienes estudios</h3>
                    <p class="mt-1 text-sm text-zinc-500">Comienza registrando tu primer local comercial.</p>
                </div>
            @endforelse

            {{-- Botón para Crear Nuevo Estudio --}}
            <button onclick="document.getElementById('studioModal').classList.remove('hidden')" class="bg-zinc-50 rounded-3xl p-8 border-2 border-dashed border-zinc-300 hover:border-zinc-400 hover:bg-zinc-100 transition-all duration-300 flex flex-col items-center justify-center text-zinc-500 hover:text-zinc-800 min-h-[200px] group">
                <div class="h-12 w-12 rounded-full bg-white border border-zinc-200 shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-lg font-bold tracking-tight">Registrar Nueva Sucursal</span>
            </button>
        </div>
    </div>

    {{-- MODAL CREAR ESTUDIO AVANZADO --}}
    <div id="studioModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-2xl w-full shadow-2xl border border-zinc-100 my-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-zinc-900 tracking-tight">Nuevo Espacio</h3>
                    <p class="text-sm text-zinc-500 mt-1">Configura los datos maestros de tu sede principal.</p>
                </div>
                <button type="button" onclick="document.getElementById('studioModal').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-600 bg-zinc-100 hover:bg-zinc-200 p-2 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            {{-- IMPORTANTE: enctype requerido para subir archivos --}}
            <form action="{{ route('studios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- COLUMNA IZQUIERDA: Datos Básicos --}}
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre Comercial</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej: Gravedad Zero" 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none bg-zinc-50 focus:bg-white" required>
                            @error('name') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Logo del Estudio <span class="text-zinc-400 font-normal">(Opcional)</span></label>
                            <input type="file" name="logo" accept="image/*"
                                   class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 transition-all cursor-pointer border border-zinc-200 rounded-xl p-2 bg-zinc-50">
                            @error('logo') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA: Ubicación --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Sede Principal <span class="text-zinc-400 font-normal">(Mapa)</span></label>
                            <input type="text" name="address" id="s_address" placeholder="Busca tu dirección..." 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none bg-zinc-50 focus:bg-white relative z-10">
                            
                            {{-- Campos Ocultos para Base de Datos --}}
                            <input type="hidden" name="latitude" id="s_latitude">
                            <input type="hidden" name="longitude" id="s_longitude">
                            <input type="hidden" name="city" id="s_city">
                            <input type="hidden" name="region" id="s_region">
                            <input type="hidden" name="country" id="s_country">
                            
                            {{-- Contenedor del Mapa --}}
                            <div id="studio_map" class="w-full h-40 mt-3 rounded-xl border border-zinc-300 shadow-inner bg-zinc-100 hidden relative z-0"></div>
                            <p id="studio_map_helper" class="text-[10px] text-zinc-500 mt-1.5 font-medium hidden">Arrastra el pin para ubicación exacta.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-zinc-100 flex gap-3">
                    <button type="button" onclick="document.getElementById('studioModal').classList.add('hidden')" class="w-1/3 font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition text-sm">Cancelar</button>
                    <button type="submit" class="w-2/3 bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 active:scale-95 transition-all text-sm">Crear Espacio y Comenzar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById('studioModal').classList.remove('hidden');
            });
        @endif

        // Variables del mapa de la sucursal
        let sMap;
        let sMarker;

        function initStudioMapAutocomplete() {
            const addressInput = document.getElementById('s_address');
            const mapContainer = document.getElementById('studio_map');
            const mapHelper = document.getElementById('studio_map_helper');
            
            if(!addressInput) return;

            const defaultPos = { lat: -33.4489, lng: -70.6693 }; // Centro genérico

            sMap = new google.maps.Map(mapContainer, {
                center: defaultPos,
                zoom: 15,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false
            });

            sMarker = new google.maps.Marker({
                map: sMap,
                position: defaultPos,
                draggable: true,
                animation: google.maps.Animation.DROP
            });

            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                componentRestrictions: { country: "cl" },
                fields: ["formatted_address", "geometry", "name", "address_components"],
                types: ["geocode", "establishment"]
            });

            // Cuando eligen una dirección
            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                
                if (!place.geometry) {
                    mapContainer.classList.add('hidden');
                    mapHelper.classList.add('hidden');
                    return;
                }

                mapContainer.classList.remove('hidden');
                mapHelper.classList.remove('hidden');

                // Necesario para evitar bugs visuales al mostrar un mapa oculto
                google.maps.event.trigger(sMap, 'resize');
                
                sMap.setCenter(place.geometry.location);
                sMap.setZoom(17);
                sMarker.setPosition(place.geometry.location);

                document.getElementById('s_latitude').value = place.geometry.location.lat();
                document.getElementById('s_longitude').value = place.geometry.location.lng();

                let city = '', region = '', country = '';
                if(place.address_components) {
                    for (const component of place.address_components) {
                        const type = component.types[0];
                        if(type === "locality") city = component.long_name;
                        if(type === "administrative_area_level_1") region = component.long_name;
                        if(type === "country") country = component.long_name;
                    }
                }
                document.getElementById('s_city').value = city;
                document.getElementById('s_region').value = region;
                document.getElementById('s_country').value = country;

                addressInput.value = place.formatted_address;
            });

            // Cuando arrastran el pin
            sMarker.addListener('dragend', function() {
                const newPos = sMarker.getPosition();
                document.getElementById('s_latitude').value = newPos.lat();
                document.getElementById('s_longitude').value = newPos.lng();
            });
        }
    </script>

    {{-- Script de Google Maps apuntando al callback de esta vista --}}
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.key') }}&libraries=places&callback=initStudioMapAutocomplete"></script>
</x-app-layout>
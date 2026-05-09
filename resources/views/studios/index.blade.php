<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-24">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-zinc-900 tracking-tight">Mis Espacios</h1>
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
                <div class="group bg-white rounded-3xl border border-zinc-200 shadow-sm hover:shadow-xl hover:border-zinc-300 transition-all duration-300 flex flex-col overflow-hidden min-h-[250px] relative">
                    
                    {{-- Botón Editar Flotante --}}
                    <button onclick="openEditStudioModal({{ json_encode($studio) }})" class="absolute top-4 right-4 z-20 bg-white/90 backdrop-blur-sm text-zinc-500 hover:text-indigo-600 p-2.5 rounded-full shadow-sm hover:shadow transition-all" title="Editar Espacio">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>

                    {{-- Banner/Cover del Estudio --}}
                    <div class="h-28 w-full bg-gradient-to-br from-zinc-800 to-zinc-900 relative overflow-hidden">
                        @if($studio->logo_path)
                            <img src="{{ asset('storage/' . $studio->logo_path) }}" class="w-full h-full object-cover opacity-40 blur-sm scale-105 group-hover:scale-100 transition-transform duration-500" alt="Cover">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    </div>

                    {{-- Logo y Contenido (Superpuesto al banner) --}}
                    <div class="px-8 pb-8 flex-1 flex flex-col mt-[-32px] relative z-10">
                        <div class="flex justify-between items-end mb-4">
                            @if($studio->logo_path)
                                <img src="{{ asset('storage/' . $studio->logo_path) }}" alt="Logo" class="h-16 w-16 rounded-2xl object-cover border-4 border-white shadow-md bg-white">
                            @else
                                <div class="h-16 w-16 rounded-2xl bg-zinc-900 border-4 border-white text-white flex items-center justify-center font-black text-2xl shadow-md">
                                    {{ substr($studio->name, 0, 1) }}
                                </div>
                            @endif
                            
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-emerald-200 mb-2">
                                Activo
                            </span>
                        </div>
                        
                        <h2 class="text-2xl font-black text-zinc-900 leading-tight">{{ $studio->name }}</h2>
                        
                        <div class="space-y-1 mt-2">
                            <p class="text-sm font-medium text-zinc-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                {{ $studio->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'espaciocore.test' }}
                            </p>
                            @if($studio->city)
                                <p class="text-xs font-medium text-zinc-500 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                                    {{ $studio->city }}, {{ $studio->country }}
                                </p>
                            @endif
                        </div>

                        {{-- Botón de Acción Principal --}}
                        <div class="mt-auto pt-6">
                            <a href="{{ route('dashboard', ['subdomain' => $studio->subdomain]) }}" class="flex items-center justify-center w-full bg-zinc-50 hover:bg-zinc-900 text-zinc-700 hover:text-white border border-zinc-200 hover:border-zinc-900 px-4 py-3.5 rounded-xl text-sm font-bold transition-colors group/btn">
                                Entrar al Panel 
                                <svg class="w-4 h-4 ml-2 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 bg-white border border-dashed border-zinc-300 rounded-3xl text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <h3 class="text-lg font-medium text-zinc-900">Aún no tienes estudios</h3>
                    <p class="mt-1 text-sm text-zinc-500">Comienza registrando tu primer local comercial.</p>
                </div>
            @endforelse

            {{-- Botón para Crear Nuevo Estudio --}}
            <button onclick="document.getElementById('studioModal').classList.remove('hidden')" class="bg-zinc-50 rounded-3xl p-8 border-2 border-dashed border-zinc-300 hover:border-zinc-400 hover:bg-zinc-100 transition-all duration-300 flex flex-col items-center justify-center text-zinc-500 hover:text-zinc-800 min-h-[250px] group">
                <div class="h-14 w-14 rounded-full bg-white border border-zinc-200 shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 group-hover:shadow transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-lg font-bold tracking-tight">Registrar Nueva Sucursal</span>
            </button>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL CREAR ESTUDIO AVANZADO --}}
    {{-- ========================================== --}}
    <div id="studioModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-2xl w-full shadow-2xl border border-zinc-100 my-auto transform transition-all">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-zinc-900 tracking-tight">Nuevo Espacio</h3>
                    <p class="text-sm text-zinc-500 mt-1">Configura los datos maestros de tu sede principal.</p>
                </div>
                <button type="button" onclick="document.getElementById('studioModal').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-600 bg-zinc-100 hover:bg-zinc-200 p-2 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="{{ route('studios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre Comercial</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej: Gravedad Zero" 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none bg-zinc-50 focus:bg-white" required>
                            @error('name') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Logo del Estudio <span class="text-zinc-400 font-normal">(Opcional)</span></label>
                            <input type="file" name="logo" id="c_logo" accept="image/*"
                                   class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 transition-all cursor-pointer border border-zinc-200 rounded-xl p-2 bg-zinc-50">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Sede Principal <span class="text-zinc-400 font-normal">(Mapa)</span></label>
                            <input type="text" name="address" id="s_address" placeholder="Busca tu dirección..." 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none bg-zinc-50 focus:bg-white relative z-10">
                            
                            <input type="hidden" name="latitude" id="s_latitude">
                            <input type="hidden" name="longitude" id="s_longitude">
                            <input type="hidden" name="city" id="s_city">
                            <input type="hidden" name="region" id="s_region">
                            <input type="hidden" name="country" id="s_country">
                            
                            <div id="studio_map" class="w-full h-40 mt-3 rounded-xl border border-zinc-300 shadow-inner bg-zinc-100 hidden relative z-0"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-zinc-100 flex gap-3">
                    <button type="button" onclick="document.getElementById('studioModal').classList.add('hidden')" class="w-1/3 font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition text-sm">Cancelar</button>
                    <button type="submit" class="w-2/3 bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 active:scale-95 transition-all text-sm">Crear Espacio</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL EDITAR ESTUDIO --}}
    {{-- ========================================== --}}
    <div id="editStudioModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-2xl w-full shadow-2xl border border-zinc-100 my-auto transform transition-all">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-zinc-900 tracking-tight">Editar Espacio</h3>
                    <p class="text-sm text-zinc-500 mt-1">Actualiza la información de tu estudio.</p>
                </div>
                <button type="button" onclick="document.getElementById('editStudioModal').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-600 bg-zinc-100 hover:bg-zinc-200 p-2 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="editStudioForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre Comercial</label>
                            <input type="text" name="name" id="e_name" required 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none bg-zinc-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Actualizar Logo <span class="text-zinc-400 font-normal">(Opcional)</span></label>
                            <input type="file" name="logo" id="e_logo" accept="image/*"
                                   class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 transition-all cursor-pointer border border-zinc-200 rounded-xl p-2 bg-zinc-50">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Sede Principal</label>
                            <input type="text" name="address" id="e_address" placeholder="Busca tu nueva dirección..." 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none bg-zinc-50 focus:bg-white relative z-10">
                            
                            <input type="hidden" name="latitude" id="e_latitude">
                            <input type="hidden" name="longitude" id="e_longitude">
                            <input type="hidden" name="city" id="e_city">
                            <input type="hidden" name="region" id="e_region">
                            <input type="hidden" name="country" id="e_country">
                            
                            <div id="edit_studio_map" class="w-full h-40 mt-3 rounded-xl border border-zinc-300 shadow-inner bg-zinc-100 hidden relative z-0"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-zinc-100 flex gap-3">
                    <button type="button" onclick="document.getElementById('editStudioModal').classList.add('hidden')" class="w-1/3 font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition text-sm">Cancelar</button>
                    <button type="submit" class="w-2/3 bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-indigo-700 active:scale-95 transition-all text-sm">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        @if($errors->any() && !old('_method'))
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById('studioModal').classList.remove('hidden');
            });
        @endif

        // ==========================================
        // VALIDACIÓN DE TAMAÑO DE IMAGEN (8MB)
        // ==========================================
        function validateFileSize(inputElement) {
            if (inputElement && inputElement.files.length > 0) {
                const file = inputElement.files[0];
                if (file.size > 15728640) { // 15MB en bytes
                    alert('¡Oops! La imagen supera el límite de 15MB. Por favor, selecciona un archivo un poco más liviano.');
                    inputElement.value = ''; 
                }
            }
        }
        document.getElementById('c_logo')?.addEventListener('change', function() { validateFileSize(this); });
        document.getElementById('e_logo')?.addEventListener('change', function() { validateFileSize(this); });

        // ==========================================
        // LÓGICA DE MODALES Y MAPAS
        // ==========================================
        let cMap, cMarker; // Mapa de Creación
        let eMap, eMarker; // Mapa de Edición

        function initAllMaps() {
            initCreateMap();
            initEditMap();
        }

        function initCreateMap() {
            const addressInput = document.getElementById('s_address');
            const mapContainer = document.getElementById('studio_map');
            if(!addressInput) return;

            const defaultPos = { lat: -33.4489, lng: -70.6693 };
            cMap = new google.maps.Map(mapContainer, { center: defaultPos, zoom: 15, mapTypeControl: false, streetViewControl: false });
            cMarker = new google.maps.Marker({ map: cMap, position: defaultPos, draggable: true, animation: google.maps.Animation.DROP });

            const autocomplete = new google.maps.places.Autocomplete(addressInput, { componentRestrictions: { country: "cl" } });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) { mapContainer.classList.add('hidden'); return; }
                mapContainer.classList.remove('hidden');
                google.maps.event.trigger(cMap, 'resize');
                cMap.setCenter(place.geometry.location);
                cMap.setZoom(17);
                cMarker.setPosition(place.geometry.location);
                
                document.getElementById('s_latitude').value = place.geometry.location.lat();
                document.getElementById('s_longitude').value = place.geometry.location.lng();
                extractLocationData(place, 's_city', 's_region', 's_country');
                addressInput.value = place.formatted_address;
            });

            cMarker.addListener('dragend', function() {
                document.getElementById('s_latitude').value = cMarker.getPosition().lat();
                document.getElementById('s_longitude').value = cMarker.getPosition().lng();
            });
        }

        function initEditMap() {
            const addressInput = document.getElementById('e_address');
            const mapContainer = document.getElementById('edit_studio_map');
            if(!addressInput) return;

            const defaultPos = { lat: -33.4489, lng: -70.6693 };
            eMap = new google.maps.Map(mapContainer, { center: defaultPos, zoom: 15, mapTypeControl: false, streetViewControl: false });
            eMarker = new google.maps.Marker({ map: eMap, position: defaultPos, draggable: true, animation: google.maps.Animation.DROP });

            const autocomplete = new google.maps.places.Autocomplete(addressInput, { componentRestrictions: { country: "cl" } });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) { mapContainer.classList.add('hidden'); return; }
                mapContainer.classList.remove('hidden');
                google.maps.event.trigger(eMap, 'resize');
                eMap.setCenter(place.geometry.location);
                eMap.setZoom(17);
                eMarker.setPosition(place.geometry.location);
                
                document.getElementById('e_latitude').value = place.geometry.location.lat();
                document.getElementById('e_longitude').value = place.geometry.location.lng();
                extractLocationData(place, 'e_city', 'e_region', 'e_country');
                addressInput.value = place.formatted_address;
            });

            eMarker.addListener('dragend', function() {
                document.getElementById('e_latitude').value = eMarker.getPosition().lat();
                document.getElementById('e_longitude').value = eMarker.getPosition().lng();
            });
        }

        function extractLocationData(place, cityId, regionId, countryId) {
            let city = '', region = '', country = '';
            if(place.address_components) {
                for (const component of place.address_components) {
                    const type = component.types[0];
                    if(type === "locality") city = component.long_name;
                    if(type === "administrative_area_level_1") region = component.long_name;
                    if(type === "country") country = component.long_name;
                }
            }
            document.getElementById(cityId).value = city;
            document.getElementById(regionId).value = region;
            document.getElementById(countryId).value = country;
        }

        function openEditStudioModal(studio) {
            // 1. Generamos la ruta base con Laravel usando un comodín genérico ':id'
            let updateUrl = "{{ route('studios.update', ':id') }}";
            
            // 2. Reemplazamos el comodín con el ID real del estudio usando JavaScript
            updateUrl = updateUrl.replace(':id', studio.id);
            
            // 3. Asignamos la ruta dinámica y segura al formulario
            document.getElementById('editStudioForm').action = updateUrl;

            // Llenamos el resto de los campos
            document.getElementById('e_name').value = studio.name;
            document.getElementById('e_address').value = studio.address || '';
            document.getElementById('e_latitude').value = studio.latitude || '';
            document.getElementById('e_longitude').value = studio.longitude || '';
            document.getElementById('e_city').value = studio.city || '';
            document.getElementById('e_region').value = studio.region || '';
            document.getElementById('e_country').value = studio.country || '';

            const mapContainer = document.getElementById('edit_studio_map');
            
            if (studio.latitude && studio.longitude) {
                mapContainer.classList.remove('hidden');
                const pos = { lat: parseFloat(studio.latitude), lng: parseFloat(studio.longitude) };
                
                // Darle tiempo al modal para renderizarse antes de ajustar el mapa
                setTimeout(() => {
                    google.maps.event.trigger(eMap, 'resize');
                    eMap.setCenter(pos);
                    eMarker.setPosition(pos);
                }, 100);
            } else {
                mapContainer.classList.add('hidden');
            }

            document.getElementById('editStudioModal').classList.remove('hidden');
        }
    </script>

    {{-- Script unificado de Google Maps apuntando a initAllMaps --}}
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.key') }}&libraries=places&callback=initAllMaps"></script>
</x-app-layout>
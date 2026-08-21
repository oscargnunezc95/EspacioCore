<x-app-layout>
    @php
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
        $protocol = request()->secure() ? 'https://' : 'http://';
    @endphp

    <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        
        <div class="text-center mb-10 md:mb-14">
            <h1 class="text-3xl md:text-4xl font-black tracking-tight">Mis Estudios</h1>
            <p class="mt-3 text-stone-500 font-medium text-base md:text-lg">Selecciona el estudio que deseas administrar o registra una nueva sucursal.</p>
        </div>

        {{-- ALERTAS DE ÉXITO Y ERROR --}}
        @if (session('success'))
            <div class="mb-8 p-4 bg-emerald-50 text-emerald-700 rounded-xl font-medium border border-emerald-200 text-center flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-8 p-4 bg-rose-50 text-rose-700 rounded-xl font-medium border border-rose-200 text-center flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- GRILLA PRINCIPAL DE ESTUDIOS               --}}
        {{-- ========================================== --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($studios as $studio)
                @php
                    $fullStudioUrl = $protocol . $studio->subdomain . '.' . $domain;
                    $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($studio->address . ' ' . $studio->city);
                    $bgImage = $studio->cover_path ?? $studio->logo_path;
                    
                    // Cálculo de Deuda para Seguridad
                    $hasDebt = $studio->hasUnpaidPlatformInvoices() || $studio->currentMonthPendingDebt() > 0;
                    $debtAmount = $studio->currentMonthPendingDebt();
                @endphp

                <div class="group bg-white rounded-3xl border border-stone-200 shadow-sm hover:shadow-xl hover:border-stone-300 hover:-translate-y-1.5 transition-all duration-300 flex flex-col overflow-hidden min-h-[280px] relative">                    
                    
                    {{-- Acciones Flotantes (Compartir y Editar) --}}
                    <div class="absolute top-4 right-4 z-20 flex gap-2">
                        <button type="button" 
                                x-data 
                                @click="
                                    if (navigator.share) {
                                        navigator.share({
                                            title: '{{ $studio->name }}',
                                            text: 'Gestiona tus clases en {{ $studio->name }}',
                                            url: '{{ $fullStudioUrl }}'
                                        }).catch(err => console.log('Error:', err));
                                    } else {
                                        navigator.clipboard.writeText('{{ $fullStudioUrl }}');
                                        alert('¡Enlace copiado!');
                                    }
                                "
                                class="bg-white/90 backdrop-blur-sm text-stone-500 hover:text-emerald-600 p-2.5 rounded-full shadow-sm transition-all" title="Compartir Enlace">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"></path></svg>
                        </button>
                        
                        <button onclick='openEditStudioModal(@json($studio), {{ $hasDebt ? "true" : "false" }}, {{ $debtAmount }})' class="bg-white/90 backdrop-blur-sm text-stone-500 hover:text-red-600 p-2.5 rounded-full shadow-sm transition-all" title="Editar Estudio">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 1 1 3.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                    </div>

                    {{-- Banner/Cover de Tarjeta --}}
                    <div class="h-36 w-full bg-gradient-to-br from-zinc-800 to-zinc-900 relative overflow-hidden shrink-0">
                        @if($bgImage)
                            <img src="{{ asset('storage/' . $bgImage) }}" class="w-full h-full object-cover opacity-60 scale-100 group-hover:scale-105 transition-transform duration-700 ease-out" alt="Cover">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                    </div>

                    {{-- Contenido --}}
                    <div class="px-8 pb-8 flex-1 flex flex-col mt-[-36px] relative z-10">
                        <div class="flex justify-between items-end mb-4">
                            @if($studio->icon_path)
                                <img src="{{ asset('storage/' . $studio->icon_path) }}" alt="Logo" class="h-16 w-16 rounded-2xl object-cover border-4 border-white shadow-md bg-white group-hover:-translate-y-1 transition-transform duration-300">
                            @elseif($studio->logo_path)
                                <img src="{{ asset('storage/' . $studio->logo_path) }}" alt="Logo" class="h-16 w-16 rounded-2xl object-cover border-4 border-white shadow-md bg-white group-hover:-translate-y-1 transition-transform duration-300">
                            @else
                                <div class="h-16 w-16 rounded-2xl bg-zinc-900 border-4 border-white text-white flex items-center justify-center font-black text-2xl shadow-md group-hover:-translate-y-1 transition-transform duration-300">
                                    {{ substr($studio->name, 0, 1) }}
                                </div>
                            @endif
                            
                            <div class="flex items-center gap-1.5 mb-2">
                                @if($studio->instagram_url)
                                    <a href="{{ $studio->instagram_url }}" target="_blank" class="p-1.5 bg-stone-100 hover:bg-gradient-to-tr hover:from-amber-500 hover:via-red-500 hover:to-purple-500 text-stone-600 hover:text-white rounded-lg transition-all" title="Instagram">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    </a>
                                @endif
                                @if($studio->tiktok_url)
                                    <a href="{{ $studio->tiktok_url }}" target="_blank" class="p-1.5 bg-stone-100 hover:bg-black text-stone-600 hover:text-white rounded-lg transition-all" title="TikTok">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
                                    </a>
                                @endif
                                @if($studio->youtube_url)
                                    <a href="{{ $studio->youtube_url }}" target="_blank" class="p-1.5 bg-stone-100 hover:bg-red-600 text-stone-600 hover:text-white rounded-lg transition-all" title="YouTube">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                        
                        <h2 class="text-2xl font-black text-stone-900 leading-tight">{{ $studio->name }}</h2>
                        
                        <div class="space-y-2 mt-3">
                            <a href="{{ $fullStudioUrl }}" target="_blank" class="group/link flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-red-600 transition-colors w-fit">
                                <svg class="w-4 h-4 text-stone-400 group-hover/link:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                <span class="underline decoration-transparent group-hover/link:decoration-red-200 transition-all">{{ $studio->subdomain }}.{{ $domain }}</span>
                            </a>

                            @if($studio->address)
                                <a href="{{ $googleMapsUrl }}" target="_blank" class="group/link flex items-center gap-2 text-xs font-medium text-stone-500 hover:text-rose-600 transition-colors w-fit">
                                    <svg class="w-4 h-4 text-stone-400 group-hover/link:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="truncate max-w-[250px] underline decoration-transparent group-hover/link:decoration-rose-200 transition-all">
                                        {{ $studio->address }}{{ $studio->city ? ', ' . $studio->city : '' }}
                                    </span>
                                </a>
                            @endif
                        </div>

                        <div class="mt-auto pt-6">
                            <a href="{{ route('dashboard', ['subdomain' => $studio->subdomain]) }}" class="flex items-center justify-center w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 group/btn text-sm">
                                Entrar al Panel 
                                <svg class="w-4 h-4 ml-2 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 bg-white border border-dashed border-stone-300 rounded-3xl text-center">
                    <svg class="mx-auto h-12 w-12 text-stone-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 0 0 -2 -2H7a2 2 0 0 0 -2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v5m-4 0h4"></path></svg>
                    <h3 class="text-lg font-medium text-stone-900">Aún no tienes estudios</h3>
                    <p class="mt-1 text-sm text-stone-500">Comienza registrando tu primer local comercial.</p>
                </div>
            @endforelse

            {{-- Botón para Crear Nuevo Estudio --}}
            <button onclick="document.getElementById('studioModal').classList.remove('hidden'); setTimeout(() => { if(cMap) cMap.invalidateSize(); }, 200);" class="bg-stone-50 rounded-3xl p-8 border-2 border-dashed border-stone-300 hover:border-stone-400 hover:bg-stone-100 transition-all duration-300 flex flex-col items-center justify-center text-stone-500 hover:text-stone-800 min-h-[280px] group">
                <div class="h-14 w-14 rounded-full bg-white border border-stone-200 shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 group-hover:shadow transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-lg font-bold tracking-tight">Registrar Nueva Sucursal</span>
            </button>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL CREAR ESTUDIO AVANZADO               --}}
    {{-- ========================================== --}}
    <div id="studioModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-3xl w-full shadow-2xl border border-stone-100 my-auto transform transition-all max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-stone-900 tracking-tight">Nuevo Espacio</h3>
                    <p class="text-sm text-stone-500 mt-1">Configura los datos maestros de tu sede principal.</p>
                </div>
                <button type="button" onclick="document.getElementById('studioModal').classList.add('hidden')" class="text-stone-400 hover:text-stone-600 bg-stone-100 hover:bg-stone-200 border border-stone-200 p-2 rounded-full transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="{{ route('studios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-wider text-red-600">Identidad Comercial</h4>
                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1">Nombre Comercial</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej: Gravedad Zero" 
                                   class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white" required>
                            @error('name') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1">Descripción <span class="text-stone-400 font-normal">(Opc.)</span></label>
                            <textarea name="description" rows="2" placeholder="Ej: Academia especializada en..." 
                                      class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">{{ old('description') }}</textarea>
                            @error('description') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1">Logo / Ícono <span class="text-stone-400 font-normal">(Cuadrado 1:1)</span></label>
                            <input type="file" name="logo" id="c_logo" accept="image/*"
                                   class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer border border-stone-200 rounded-xl p-1.5 bg-stone-50">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1">Foto de Portada / Card <span class="text-stone-400 font-normal">(Horizontal 16:9)</span></label>
                            <input type="file" name="cover" id="c_cover" accept="image/*"
                                   class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer border border-stone-200 rounded-xl p-1.5 bg-stone-50">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-wider text-red-600">Contacto y Redes</h4>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">📧 Correo Electrónico</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="contacto@tuestudio.com"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">📱 WhatsApp</label>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="+56 9 1234 5678"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                        <div class="pt-2 border-t border-stone-100">
                            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">Redes Sociales</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Instagram URL</label>
                            <input type="url" name="instagram_url" value="{{ old('instagram_url') }}" placeholder="https://instagram.com/tuestudio"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">TikTok URL</label>
                            <input type="url" name="tiktok_url" value="{{ old('tiktok_url') }}" placeholder="https://tiktok.com/@tuestudio"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">YouTube URL</label>
                            <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" placeholder="https://youtube.com/@tuestudio"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN DE UBICACIÓN Y MAPA SINCRONIZADO --}}
                <div class="border-t border-stone-100 pt-6">
                    <h4 class="text-xs font-black uppercase tracking-wider text-red-600 mb-3">Ubicación y Sede</h4>
                    <div>
                        <label class="block text-sm font-bold text-stone-700 mb-1">Dirección Principal</label>
                        <p class="text-xs text-stone-500 mb-2">Escribe tu dirección o arrastra el pin en el mapa para fijar las coordenadas exactas.</p>
                        <div class="relative">
                            <input type="text" name="address" id="s_address" placeholder="Busca tu dirección..." autocomplete="off"
                                   class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white relative z-10">
                            <ul id="s_address_results" class="absolute z-50 w-full mt-1 bg-white border border-stone-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden py-1 custom-scrollbar"></ul>
                        </div>

                        <input type="hidden" name="latitude" id="s_latitude">
                        <input type="hidden" name="longitude" id="s_longitude">
                        <input type="hidden" name="city" id="s_city">
                        <input type="hidden" name="region" id="s_region">
                        <input type="hidden" name="country" id="s_country">

                        <div id="studio_map" class="w-full h-64 mt-3 rounded-xl border border-stone-300 shadow-inner bg-stone-100 relative z-0"></div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-stone-100 flex gap-3">
                    <button type="button" onclick="document.getElementById('studioModal').classList.add('hidden')" class="w-1/3 bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95 text-sm">Cancelar</button>
                    <button type="submit" class="w-2/3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 text-sm">Crear Espacio</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL EDITAR ESTUDIO                       --}}
    {{-- ========================================== --}}
    <div id="editStudioModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-3xl w-full shadow-2xl border border-stone-100 my-auto transform transition-all max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-stone-900 tracking-tight">Editar Espacio</h3>
                    <p class="text-sm text-stone-500 mt-1">Actualiza la información comercial y visual de tu estudio.</p>
                </div>
                <button type="button" onclick="document.getElementById('editStudioModal').classList.add('hidden')" class="text-stone-400 hover:text-stone-600 bg-stone-100 hover:bg-stone-200 border border-stone-200 p-2 rounded-full transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="editStudioForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-wider text-red-600">Identidad Comercial</h4>
                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1">Nombre Comercial</label>
                            <input type="text" name="name" id="e_name" required 
                                   class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1">Descripción <span class="text-stone-400 font-normal">(Opc.)</span></label>
                            <textarea name="description" id="e_description" rows="2" placeholder="Ej: Academia enfocada en..." 
                                      class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1">Actualizar Logo <span class="text-stone-400 font-normal">(Cuadrado 1:1)</span></label>
                            <input type="file" name="logo" id="e_logo" accept="image/*"
                                   class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer border border-stone-200 rounded-xl p-1.5 bg-stone-50">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1">Actualizar Portada / Card <span class="text-stone-400 font-normal">(Horizontal 16:9)</span></label>
                            <input type="file" name="cover" id="e_cover" accept="image/*"
                                   class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer border border-stone-200 rounded-xl p-1.5 bg-stone-50">
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-wider text-red-600">Contacto y Redes</h4>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">📧 Correo Electrónico</label>
                            <input type="email" name="email" id="e_email" placeholder="contacto@tuestudio.com"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">📱 WhatsApp</label>
                            <input type="text" name="whatsapp" id="e_whatsapp" placeholder="+56 9 1234 5678"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Instagram URL</label>
                            <input type="url" name="instagram_url" id="e_instagram_url" placeholder="https://instagram.com/tuestudio"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">TikTok URL</label>
                            <input type="url" name="tiktok_url" id="e_tiktok_url" placeholder="https://tiktok.com/@tuestudio"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">YouTube URL</label>
                            <input type="url" name="youtube_url" id="e_youtube_url" placeholder="https://youtube.com/@tuestudio"
                                   class="w-full rounded-xl border border-stone-300 px-3 py-2 text-xs focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN DE UBICACIÓN Y MAPA SINCRONIZADO --}}
                <div class="border-t border-stone-100 pt-6">
                    <h4 class="text-xs font-black uppercase tracking-wider text-red-600 mb-3">Ubicación y Sede</h4>
                    <div>
                        <label class="block text-sm font-bold text-stone-700 mb-1">Sede Principal</label>
                        <p class="text-xs text-stone-500 mb-2">Escribe tu dirección o arrastra el pin en el mapa para ajustar la posición exacta.</p>
                        <div class="relative">
                            <input type="text" name="address" id="e_address" placeholder="Busca tu nueva dirección..." autocomplete="off"
                                   class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 outline-none bg-stone-50 focus:bg-white relative z-10">
                            <ul id="e_address_results" class="absolute z-50 w-full mt-1 bg-white border border-stone-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden py-1 custom-scrollbar"></ul>
                        </div>

                        <input type="hidden" name="latitude" id="e_latitude">
                        <input type="hidden" name="longitude" id="e_longitude">
                        <input type="hidden" name="city" id="e_city">
                        <input type="hidden" name="region" id="e_region">
                        <input type="hidden" name="country" id="e_country">

                        <div id="edit_studio_map" class="w-full h-64 mt-3 rounded-xl border border-stone-300 shadow-inner bg-stone-100 relative z-0"></div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-stone-100 flex gap-3">
                    <button type="button" onclick="document.getElementById('editStudioModal').classList.add('hidden')" class="w-1/3 bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95 text-sm">Cancelar</button>
                    <button type="submit" class="w-2/3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 text-sm">Guardar Cambios</button>
                </div>
            </form>

            {{-- ========================================== --}}
            {{-- ZONA DE PELIGRO (Fuera del form de edición)--}}
            {{-- ========================================== --}}
            <div class="mt-8 pt-6 border-t border-rose-100">
                <h4 class="text-xs font-black uppercase tracking-wider text-rose-600 mb-4">Zona de Peligro</h4>
                
                {{-- Alerta de Deuda (Oculta por defecto) --}}
                <div id="e_danger_debt" class="hidden p-4 bg-rose-50 border border-rose-200 rounded-xl mb-4 text-sm text-rose-700">
                    <p class="font-bold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        No puedes eliminar este espacio.
                    </p>
                    <p class="mt-1">Tienes una deuda pendiente de <span id="e_debt_amount" class="font-black"></span> por el uso de la plataforma. Paga tu saldo para habilitar el cierre de cuenta.</p>
                </div>

                {{-- Formulario de Eliminación --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-stone-50 p-4 rounded-2xl border border-stone-200">
                    <p class="text-xs text-stone-500 max-w-sm">Al eliminar el estudio, este se ocultará de la plataforma pero mantendrá su historial financiero de forma segura.</p>
                    
                    <form id="deleteStudioForm" method="POST" onsubmit="return confirm('¿Estás absolutamente seguro de que deseas cerrar este espacio? Esta acción ocultará el estudio y no podrás acceder a su panel.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" id="e_delete_btn" class="w-full sm:w-auto px-4 py-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white font-bold rounded-xl transition-colors text-sm shrink-0">
                            Eliminar Espacio
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPTS DE GEOLOCALIZACIÓN BIDIRECCIONAL Y MODALES --}}
    <script>
        @if($errors->any() && !old('_method'))
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById('studioModal').classList.remove('hidden');
            });
        @endif

        function validateFileSize(inputElement) {
            if (inputElement && inputElement.files.length > 0) {
                const file = inputElement.files[0];
                if (file.size > 15728640) { // 15MB
                    alert('¡Oops! La imagen supera el límite de 15MB. Por favor, selecciona un archivo un poco más liviano.');
                    inputElement.value = ''; 
                }
            }
        }
        document.getElementById('c_logo')?.addEventListener('change', function() { validateFileSize(this); });
        document.getElementById('c_cover')?.addEventListener('change', function() { validateFileSize(this); });
        document.getElementById('e_logo')?.addEventListener('change', function() { validateFileSize(this); });
        document.getElementById('e_cover')?.addEventListener('change', function() { validateFileSize(this); });

        // ==========================================
        // MAPAS BIDIRECCIONALES: Leaflet + Nominatim
        // ==========================================
        let cMap, cMarker, eMap, eMarker;

        async function nominatimSearch(query) {
            if (!query || query.length < 3) return [];
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=cl&q=${encodeURIComponent(query)}`);
                return await res.json();
            } catch (e) { return []; }
        }

        async function nominatimReverse(lat, lng) {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                return await res.json();
            } catch (e) { return null; }
        }

        function showDropdown(listEl, results, onSelect) { 
            listEl.innerHTML = '';
            if (!results || results.length === 0) {
                listEl.classList.add('hidden');
                return;
            }
            results.forEach((place) => {
                const li = document.createElement('li');
                li.className = 'px-4 py-2.5 hover:bg-stone-100 cursor-pointer text-stone-700 text-sm transition-colors border-b border-stone-100 last:border-0';
                li.textContent = place.display_name;
                li.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    onSelect(place);
                });
                listEl.appendChild(li);
            });
            listEl.classList.remove('hidden');
        }

        function extractNominatimAddress(place, cityId, regionId, countryId) {
            const addr = place.address || {};
            const city = addr.city || addr.town || addr.village || addr.municipality || '';
            const region = addr.state || '';
            const country = addr.country || '';
            document.getElementById(cityId).value = city;
            document.getElementById(regionId).value = region;
            document.getElementById(countryId).value = country;
        }

        function createLeafletMap(mapContainerId) {
            const container = document.getElementById(mapContainerId);
            if (!container) return null;

            const defaultPos = [-33.4489, -70.6693];
            const map = L.map(container, {
                center: defaultPos,
                zoom: 13,
                zoomControl: true,
                attributionControl: true
            });

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const icon = L.divIcon({
                className: 'custom-map-pin',
                html: `<div class="w-8 h-8 bg-red-600 border-2 border-white rounded-full shadow-md flex items-center justify-center text-white cursor-pointer hover:scale-110 transition-transform">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                       </div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            const marker = L.marker(defaultPos, { icon, draggable: true }).addTo(map);
            return { map, marker };
        }

        function setupBidirectionalSync(addressInputId, resultsListId, mapObj, markerObj, latId, lngId, cityId, regionId, countryId, mapContainerId) {
            const input = document.getElementById(addressInputId);
            const listEl = document.getElementById(resultsListId);
            const mapContainer = document.getElementById(mapContainerId);
            if (!input || !listEl) return;

            let timeout = null;

            input.addEventListener('input', function () {
                clearTimeout(timeout);
                const query = this.value.trim();
                if (query.length < 3) {
                    listEl.classList.add('hidden');
                    return;
                }
                timeout = setTimeout(async () => {
                    const results = await nominatimSearch(query);
                    showDropdown(listEl, results, (place) => {
                        input.value = place.display_name;
                        listEl.classList.add('hidden');

                        const lat = parseFloat(place.lat);
                        const lng = parseFloat(place.lon);
                        const pos = [lat, lng];

                        document.getElementById(latId).value = lat.toFixed(8);
                        document.getElementById(lngId).value = lng.toFixed(8);
                        extractNominatimAddress(place, cityId, regionId, countryId);

                        mapContainer.classList.remove('hidden');
                        setTimeout(() => {
                            mapObj.invalidateSize();
                            mapObj.setView(pos, 17);
                            markerObj.setLatLng(pos);
                        }, 100);
                    });
                }, 350);
            });

            input.addEventListener('blur', () => {
                setTimeout(() => listEl.classList.add('hidden'), 200);
            });

            async function syncCoordsToAddress(pos) {
                document.getElementById(latId).value = pos.lat.toFixed(8);
                document.getElementById(lngId).value = pos.lng.toFixed(8);
                
                input.value = "Obteniendo dirección exacta...";
                const place = await nominatimReverse(pos.lat, pos.lng);
                if (place && place.display_name) {
                    input.value = place.display_name;
                    extractNominatimAddress(place, cityId, regionId, countryId);
                } else {
                    input.value = `${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`;
                }
            }

            markerObj.on('dragend', function () {
                syncCoordsToAddress(markerObj.getLatLng());
            });

            mapObj.on('click', function (e) {
                markerObj.setLatLng(e.latlng);
                syncCoordsToAddress(e.latlng);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const createMap = createLeafletMap('studio_map');
            if (createMap) {
                cMap = createMap.map;
                cMarker = createMap.marker;
                setupBidirectionalSync('s_address', 's_address_results', cMap, cMarker,
                    's_latitude', 's_longitude', 's_city', 's_region', 's_country', 'studio_map');
            }

            const editMap = createLeafletMap('edit_studio_map');
            if (editMap) {
                eMap = editMap.map;
                eMarker = editMap.marker;
                setupBidirectionalSync('e_address', 'e_address_results', eMap, eMarker,
                    'e_latitude', 'e_longitude', 'e_city', 'e_region', 'e_country', 'edit_studio_map');
            }
        });

        // 🚀 NUEVA LÓGICA DE EDICIÓN Y SEGURIDAD FINANCIERA
        function openEditStudioModal(studio, hasDebt, debtAmount) {
            let updateUrl = "{{ route('studios.update', ':id') }}";
            updateUrl = updateUrl.replace(':id', studio.id);
            document.getElementById('editStudioForm').action = updateUrl;

            // Ruta para eliminar
            let deleteUrl = "{{ route('studios.destroy', ':id') }}";
            document.getElementById('deleteStudioForm').action = deleteUrl.replace(':id', studio.id);

            const debtContainer = document.getElementById('e_danger_debt');
            const deleteBtn = document.getElementById('e_delete_btn');

            if (hasDebt) {
                debtContainer.classList.remove('hidden');
                document.getElementById('e_debt_amount').innerText = '$' + Math.round(debtAmount).toLocaleString('es-CL');
                
                deleteBtn.disabled = true;
                deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
                deleteBtn.classList.remove('hover:bg-rose-600', 'hover:text-white');
            } else {
                debtContainer.classList.add('hidden');
                deleteBtn.disabled = false;
                deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                deleteBtn.classList.add('hover:bg-rose-600', 'hover:text-white');
            }

            document.getElementById('e_name').value = studio.name;
            document.getElementById('e_description').value = studio.description || '';
            document.getElementById('e_instagram_url').value = studio.instagram_url || '';
            document.getElementById('e_tiktok_url').value = studio.tiktok_url || '';
            document.getElementById('e_youtube_url').value = studio.youtube_url || '';
            document.getElementById('e_email').value = studio.email || '';
            document.getElementById('e_whatsapp').value = studio.whatsapp || '';
            document.getElementById('e_address').value = studio.address || '';
            document.getElementById('e_latitude').value = studio.latitude || '';
            document.getElementById('e_longitude').value = studio.longitude || '';
            document.getElementById('e_city').value = studio.city || '';
            document.getElementById('e_region').value = studio.region || '';
            document.getElementById('e_country').value = studio.country || '';

            const mapContainer = document.getElementById('edit_studio_map');
            
            if (studio.latitude && studio.longitude) {
                mapContainer.classList.remove('hidden');
                const pos = [parseFloat(studio.latitude), parseFloat(studio.longitude)];

                setTimeout(() => {
                    eMap.invalidateSize();
                    eMap.setView(pos, 17);
                    eMarker.setLatLng(pos);
                }, 100);
            } else {
                mapContainer.classList.remove('hidden');
                setTimeout(() => { eMap.invalidateSize(); }, 100);
            }

            document.getElementById('editStudioModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
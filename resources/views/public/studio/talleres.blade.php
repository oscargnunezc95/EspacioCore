{{-- ============================================================ --}}
{{-- Vista: Talleres del Estudio                                --}}
{{-- Controlador: StudioPublicController@talleres               --}}
{{-- ============================================================ --}}
<x-guest-layout>
    <div class="min-h-screen bg-transparent relative z-10">

        @include('public.studio._studio-nav', ['activeTab' => 'talleres'])

        <div class="w-full pt-8 pb-32 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 md:px-8">

                {{-- ========================================== --}}
                {{-- CONTENIDO: Talleres del Estudio            --}}
                {{-- ========================================== --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($studio->workshops as $workshop)
                        @php
                            $imageUrl = $workshop->image_path
                                ? asset('storage/' . $workshop->image_path)
                                : 'https://ui-avatars.com/api/?name='.urlencode($workshop->name).'&color=dc2626&background=fef2f2&size=512';

                            // 1. Extraer dirección en texto para mostrar en UI
                            $address = $workshop->address ?? $studio->address;
                            $displayAddress = $address ?? 'Dirección no especificada';

                            // 2. Extraer coordenadas exactas (Prioridad: Taller -> Estudio)
                            $lat = $workshop->latitude ?? $studio->latitude;
                            $lng = $workshop->longitude ?? $studio->longitude;

                            // 3. Construir el Link de Maps
                            if ($lat && $lng) {
                                // Formato exacto solicitado con coordenadas
                                $mapLink = "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
                            } elseif ($address) {
                                // Fallback de seguridad: si no hay coordenadas pero sí hay texto
                                $mapLink = "https://www.google.com/maps/search/?api=1&query=" . urlencode($address);
                            } else {
                                $mapLink = null;
                            }
                        @endphp

                        <div class="bg-white border border-stone-200 rounded-3xl overflow-hidden shadow-sm flex flex-col">

                            {{-- Imagen de Cabecera --}}
                            <div class="h-40 sm:h-48 w-full bg-zinc-200 relative shrink-0">
                                <img src="{{ $imageUrl }}" alt="{{ $workshop->name }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-red-900/70 via-red-900/10 to-transparent"></div>
                            </div>

                            {{-- Cuerpo de la Tarjeta --}}
                            <div class="p-6 md:p-8 flex-1 flex flex-col">

                                {{-- Título y Etiquetas --}}
                                <div class="mb-6">
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @if($workshop->discipline)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-600 border border-red-100 text-[10px] font-black rounded-md tracking-widest uppercase shadow-sm">
                                                {{ $workshop->discipline->name }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-stone-100 text-stone-600 border border-stone-200 text-[10px] font-black rounded-md tracking-widest uppercase shadow-sm">
                                            @switch($workshop->target_audience)
                                                @case('kids') Niñas/os @break
                                                @case('teens') Adolescentes @break
                                                @case('adults') Adultos @break
                                                @default Todas las edades
                                            @endswitch
                                        </span>
                                    </div>
                                    <h3 class="text-2xl font-black text-stone-900 leading-tight">{{ $workshop->name }}</h3>
                                </div>

                                {{-- Video Promocional --}}
                                @if($workshop->embed_video_url)
                                    @php
                                        $isInstagram = str_contains($workshop->embed_video_url, 'instagram.com');
                                        $videoClass = $isInstagram ? 'aspect-[9/16] w-full max-w-[380px] mx-auto' : 'aspect-video w-full';
                                    @endphp
                                    <div class="mb-6 rounded-2xl overflow-hidden shadow-sm border border-stone-200 bg-zinc-900 relative {{ $videoClass }}">
                                        <iframe class="absolute top-0 left-0 w-full h-full" src="{{ $workshop->embed_video_url }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                @endif

                                {{-- Descripción --}}
                                @if($workshop->description)
                                    <div class="mb-6">
                                        <h4 class="text-xs font-black text-stone-400 uppercase tracking-widest mb-2">Acerca del Taller</h4>
                                        <p class="text-sm text-stone-600 leading-relaxed whitespace-pre-line">{{ $workshop->description }}</p>
                                    </div>
                                @endif

                                {{-- Detalles Operativos --}}
                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center gap-3 text-stone-600 bg-stone-50 p-3 rounded-2xl border border-stone-100">
                                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-stone-100 text-emerald-500 shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-0.5">Profesor/a a cargo</p>
                                            <p class="text-sm font-bold text-stone-900 leading-tight truncate">
                                                {{ $workshop->teacher ? $workshop->teacher->first_name . ' ' . $workshop->teacher->last_name : 'Staff del Estudio' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3 text-stone-600 bg-stone-50 p-3 rounded-2xl border border-stone-100">
                                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-stone-100 text-rose-500 mt-1 shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-0.5">Ubicación</p>
                                            <p class="text-sm font-bold text-stone-900 mb-2 leading-tight">{{ $displayAddress }}</p>
                                            
                                            {{-- Renderiza el botón SOLO si pudimos construir el link (coordenadas o fallback) --}}
                                            @if($mapLink)
                                                <a href="{{ $mapLink }}" target="_blank" class="inline-flex items-center text-xs font-bold text-red-600 hover:text-red-800 transition-colors">
                                                    Cómo llegar en Maps <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Precios --}}
                                @if($workshop->prices->count() > 0)
                                    <div class="mt-auto pt-5 border-t border-stone-100">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-stone-400 mb-3">Planes Disponibles</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($workshop->prices as $price)
                                                <div class="bg-stone-50 border border-stone-200 px-3 py-2 rounded-xl flex items-center gap-2 shadow-sm">
                                                    <span class="font-black text-stone-900 text-sm">{{ $price->class_count }} Clases</span>
                                                    <span class="text-stone-300">|</span>
                                                    <span class="font-bold text-red-600 text-sm">${{ number_format($price->price, 0, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-20 bg-stone-50 border border-stone-200 rounded-3xl">
                            <p class="text-stone-500 font-bold">Este estudio no ha publicado sus talleres aún.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

        @include('public.studio._mini-cart')

    </div>
</x-guest-layout>
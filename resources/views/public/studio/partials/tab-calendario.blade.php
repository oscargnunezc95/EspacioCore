<div class="space-y-8 max-w-7xl mx-auto mb-24">
    
    {{-- ========================================== --}}
    {{-- BARRA DE FILTROS DEL ESTUDIO --}}
    {{-- ========================================== --}}
    <div id="calendario" class="bg-white p-4 md:p-6 rounded-3xl shadow-sm border border-zinc-200 mb-8">
        <form id="filterForm" action="{{ url()->current() }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            
            {{-- Filtro Taller --}}
            <div class="w-full md:w-1/3">
                <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Taller</label>
                <select name="workshop" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                    <option value="">Todos los talleres</option>
                    {{-- Asumimos que desde tu controlador envías la variable $workshops (o $studio->workshops) --}}
                    @foreach($workshops ?? [] as $workshopItem)
                        <option value="{{ $workshopItem->id }}" {{ request('workshop') == $workshopItem->id ? 'selected' : '' }}>
                            {{ $workshopItem->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filtro Día de la Semana --}}
            <div class="w-full md:w-1/3">
                <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1.5">Día de la semana</label>
                <select name="day" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                    <option value="">Cualquier día</option>
                    {{-- Usamos valores numéricos estándar (1=Lunes, 7=Domingo) para facilitar la consulta en el backend --}}
                    <option value="1" {{ request('day') == '1' ? 'selected' : '' }}>Lunes</option>
                    <option value="2" {{ request('day') == '2' ? 'selected' : '' }}>Martes</option>
                    <option value="3" {{ request('day') == '3' ? 'selected' : '' }}>Miércoles</option>
                    <option value="4" {{ request('day') == '4' ? 'selected' : '' }}>Jueves</option>
                    <option value="5" {{ request('day') == '5' ? 'selected' : '' }}>Viernes</option>
                    <option value="6" {{ request('day') == '6' ? 'selected' : '' }}>Sábado</option>
                    <option value="7" {{ request('day') == '7' ? 'selected' : '' }}>Domingo</option>
                </select>
            </div>

            {{-- Botones de Acción --}}
            <div class="w-full md:w-1/3 flex gap-2">
                <a href="{{ url()->current() }}" class="flex-1 flex items-center justify-center bg-zinc-100 text-zinc-600 font-bold py-3 rounded-xl hover:bg-zinc-200 transition-colors text-sm" title="Limpiar Filtros">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
                <button type="submit" class="flex-[3] bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition-all active:scale-95 text-sm flex items-center justify-center gap-2">
                    Filtrar Clases
                </button>
            </div>
        </form>
    </div>

    {{-- ========================================== --}}
    {{-- LISTADO DE CLASES (Agrupadas por Día) --}}
    {{-- ========================================== --}}
    @php
        // Agrupamos las sesiones por fecha para mostrar cabeceras de día
        $groupedSessions = $studio->classSessions->groupBy(function($session) {
            return \Carbon\Carbon::parse($session->date)->format('Y-m-d');
        });
    @endphp

    @forelse($groupedSessions as $date => $sessions)
        <div>
            {{-- Cabecera del Día --}}
            <div class="mb-6 px-2">
                <h4 class="text-xl md:text-2xl font-black text-zinc-900 capitalize flex items-center gap-2.5 border-b border-zinc-200 pb-3">
                    <svg class="w-6 h-6 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l d \d\e F') }}
                </h4>
            </div>

            {{-- GRILLA DE RESULTADOS (Máximo 4 columnas) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($sessions as $session)
                    @php
                        // Imagen de Taller
                        $imageUrl = $session->workshop->image_path 
                                        ? asset('storage/' . $session->workshop->image_path) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=4f46e5&background=e0e7ff&size=512';
                        
                        $modalData = json_encode([
                            'title'         => $session->workshop->name,
                            'studio'        => $session->workshop->studio->name,
                            'studio_url'    => '#', // Opcional dentro del mismo estudio
                            'teacher' => $session->workshop->teacher 
                                ? trim($session->workshop->teacher->first_name . ' ' . $session->workshop->teacher->last_name) 
                                : 'Staff',
                            'teacher_email' => $session->workshop->teacher->email ?? '',
                            'date'          => \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F'),
                            'time'          => \Carbon\Carbon::parse($session->start_time)->format('H:i'),
                            'image'         => $imageUrl,
                            'address'       => $session->workshop->address ?? 'Dirección no especificada',
                            'description'   => $session->workshop->description ?? 'Sin descripción disponible.',
                            'video_url'     => $session->workshop->embed_video_url,
                        ]);

                        $isEnrolled = in_array($session->id, $enrolledSessionIds ?? []);
                        $isPaid = in_array($session->id, $paidSessionIds ?? []);
                    @endphp

                    {{-- TARJETA CLÁSICA (Separada: Imagen arriba, info abajo) --}}
                    <div class="bg-white border border-zinc-200 rounded-2xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group transform-gpu isolate">                        
                        {{-- 1. Mitad Superior: Imagen y Etiquetas --}}
                        <div class="h-44 bg-zinc-100 relative overflow-hidden cursor-pointer transform-gpu" onclick="openDetailModal({{ $modalData }})">
                            <img src="{{ $imageUrl }}" alt="Clase" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/40 to-transparent opacity-50"></div>

                            {{-- Etiqueta Categoría --}}
                            <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-3 py-1 rounded-lg shadow-sm border border-white/20">
                                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ $session->workshop->discipline->name ?? 'Clase' }}</span>
                            </div>
                        </div>

                        {{-- 2. Mitad Inferior: Contenido --}}
                        <div class="p-5 flex-1 flex flex-col bg-white relative">
                            {{-- Título --}}
                            <div class="mb-3 cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                                <h3 class="text-lg font-black text-zinc-900 leading-tight hover:text-indigo-600 transition-colors">{{ $session->workshop->name }}</h3>
                            </div>
                            
                            {{-- Info (Hora y Profesor) --}}
                            <div class="space-y-1.5 mt-auto cursor-pointer mb-5" onclick="openDetailModal({{ $modalData }})">
                                <div class="flex items-center gap-2 text-sm font-medium text-zinc-600">
                                    <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="font-black text-zinc-800">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm font-medium text-zinc-600">
                                    <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span class="truncate">Prof. {{ $session->workshop->teacher ? $session->workshop->teacher->first_name . ' ' . $session->workshop->teacher->last_name : 'Staff' }}</span>
                                </div>
                            </div>

                            {{-- Footer: Precio y Acción --}}
                            <div class="pt-4 border-t border-zinc-100 flex items-center justify-between gap-3">
                                <div class="shrink-0">
                                    @php $dropInPrice = $session->workshop->prices->where('class_count', 1)->first(); @endphp
                                    @if($dropInPrice)
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest">Clase suelta</p>
                                        <p class="text-base font-black text-zinc-900">${{ number_format($dropInPrice->price, 0, ',', '.') }}</p>
                                    @else
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest">Desde</p>
                                        <p class="text-base font-black text-zinc-900">Ver Planes</p>
                                    @endif
                                </div>

                                @if($isPaid)
                                    <button disabled class="flex-1 sm:flex-none sm:w-[130px] px-3 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-1.5 bg-blue-50 text-blue-600 cursor-not-allowed border border-blue-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Pagada
                                    </button>
                                @else
                                    <button onclick="toggleSelection({{ $session->id }}, this)" 
                                            data-initial-state="{{ $isEnrolled ? 'enrolled' : 'unenrolled' }}"
                                            class="interest-btn flex-1 sm:flex-none sm:w-[130px] px-3 py-2.5 rounded-xl text-sm font-black transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center
                                            {{ $isEnrolled ? 'bg-emerald-500 text-white border border-emerald-600 hover:bg-emerald-600 group/btn' : 'bg-zinc-100 text-zinc-900 border border-transparent hover:bg-zinc-200' }}">
                                        @if($isEnrolled)
                                            <div class="relative flex items-center justify-center w-full">
                                                <span class="flex items-center gap-1 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 
                                                    En Portal
                                                </span>
                                                <span class="absolute inset-0 flex items-center justify-center gap-1 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> 
                                                    Quitar
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
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-20 bg-zinc-50 border border-zinc-200 rounded-3xl">
            <svg class="w-12 h-12 text-zinc-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p class="text-zinc-500 font-bold text-lg">No encontramos clases.</p>
            <p class="text-zinc-400 text-sm mt-1">Ajusta los filtros o intenta con otra fecha.</p>
        </div>
    @endforelse
</div>
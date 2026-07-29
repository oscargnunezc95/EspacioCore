<style>
    /* Degradado diagonal que genera el barrido de brillo */
    .holo-white-card::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%;
        width: 200%; height: 200%;
        background: linear-gradient(0deg, transparent, transparent 30%, rgba(255, 255, 255, 0.6));
        transform: rotate(-45deg);
        transition: all 0.5s ease;
        opacity: 0;
        pointer-events: none;
        z-index: 30;
    }

    /* Escalado de la tarjeta y sombra brillante al hacer hover o animar */
    .holo-white-card:hover,
    .holo-white-card.is-animating {
        transform: scale(1.03) translateZ(0) !important;
        box-shadow: 0 15px 30px -5px rgba(239, 68, 68, 0.15), 0 0 20px rgba(255, 255, 255, 0.8) !important;
    }

    /* Movimiento del haz de luz hacia abajo */
    .holo-white-card:hover::before,
    .holo-white-card.is-animating::before {
        opacity: 1;
        transform: rotate(-45deg) translateY(100%);
    }

    /* Optimización de GPU para la imagen interna */
    .holo-white-card img {
        will-change: transform;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        transform: translateZ(0);
    }

    /* Zoom de la imagen (excluye tarjetas llenas con opacidad reducida) */
    .holo-white-card:hover img:not(.opacity-50),
    .holo-white-card.is-animating img:not(.opacity-50) {
        transform: scale(1.1) translateZ(0) !important;
    }
</style>
<x-app-layout :metaTitle="$seo['title']" :metaDescription="$seo['description']" :canonicalUrl="$seo['canonical']">
    <div class="min-h-screen bg-transparent relative z-10">
        @include('public.studio._studio-nav', ['activeTab' => 'clases'])
        
        <div class="w-full pt-8 pb-32 min-h-screen">
            <div class="mx-auto px-4 md:px-8">
                <div x-data="{ viewMode: 'day' }" class="space-y-8 w-full">

                    {{-- NAVEGADOR POR MES --}}
                    @php
                        $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
                        $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
                    @endphp

                   

                    {{-- BARRA DE FILTROS Y SELECTOR DE VISTA (1 Fila en Escritorio / Íconos en Móvil) --}}
                    <div id="calendario-filtros" class="bg-white p-5 md:p-6 rounded-3xl shadow-sm border border-stone-200 space-y-5 md:space-y-6">
    
                        {{-- CABECERA: Navegación del Mes (Separada limpiamente con space-y del contenedor) --}}
                        <div class="flex justify-between items-center gap-4">
                            <a href="{{ request()->fullUrlWithQuery(['month' => $prevMonth]) }}" 
                            class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-1.5 transition-all duration-200 active:scale-95 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                <span class="hidden sm:inline">Anterior</span>
                            </a>
                            
                            <h2 class="text-lg sm:text-xl md:text-2xl font-black text-stone-900 capitalize text-center truncate">
                                🗓️ {{ $monthDate->translatedFormat('F Y') }}
                            </h2>
                            
                            <a href="{{ request()->fullUrlWithQuery(['month' => $nextMonth]) }}" 
                            class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-1.5 transition-all duration-200 active:scale-95 shrink-0">
                                <span class="hidden sm:inline">Siguiente</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>

                        {{-- BARRA DE HERRAMIENTAS: Fondo tenue (stone-50) y alineación inferior milimétrica (items-end) --}}
                        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 bg-stone-50/60 p-4 sm:p-5 rounded-2xl border border-stone-200/80">
                                                    
                            {{-- SWITCHER DE VISTAS (Con etiqueta real para alinear con los selects) --}}
                            <div class="w-full lg:w-auto shrink-0">
                                <label class="block text-[11px] font-black text-stone-400 uppercase tracking-widest mb-1.5">Vista</label>
                                <div class="w-full sm:w-auto flex items-center bg-stone-200/60 p-1 rounded-xl border border-stone-200/80">
                                    <button type="button" @click="viewMode = 'day'" 
                                            :class="viewMode === 'day' ? 'bg-white text-stone-900 shadow-sm font-black' : 'text-stone-500 hover:text-stone-900 font-bold'" 
                                            title="Por Día"
                                            class="flex-1 sm:flex-initial h-[34px] px-3.5 sm:px-4 rounded-lg text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <span>☀️</span>
                                        <span class="hidden sm:inline">Por Día</span>
                                    </button>
                                    
                                    <button type="button" @click="viewMode = 'week'" 
                                            :class="viewMode === 'week' ? 'bg-white text-stone-900 shadow-sm font-black' : 'text-stone-500 hover:text-stone-900 font-bold'" 
                                            title="Horario Semanal"
                                            class="flex-1 sm:flex-initial h-[34px] px-3.5 sm:px-4 rounded-lg text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <span>📅</span>
                                        <span class="hidden sm:inline">Semanal</span>
                                    </button>
                                    
                                    <button type="button" @click="viewMode = 'month'" 
                                            :class="viewMode === 'month' ? 'bg-white text-stone-900 shadow-sm font-black' : 'text-stone-500 hover:text-stone-900 font-bold'" 
                                            title="Horario Mensual"
                                            class="flex-1 sm:flex-initial h-[34px] px-3.5 sm:px-4 rounded-lg text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <span>🗓️</span>
                                        <span class="hidden sm:inline">Mensual</span>
                                    </button>
                                </div>
                            </div>

                            {{-- FORMULARIO DE FILTROS --}}
                            <form id="filterForm" action="{{ url()->current() }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-3 sm:gap-4 items-stretch sm:items-end justify-end">
                                <input type="hidden" name="month" value="{{ $monthDate->format('Y-m') }}">

                                {{-- Filtro Taller --}}
                                <div class="flex-1 sm:max-w-[220px]">
                                    <label class="block text-[11px] font-black text-stone-400 uppercase tracking-widest mb-1.5">Taller</label>
                                    <select name="workshop" class="w-full h-[42px] rounded-xl border border-stone-200 bg-white px-3.5 text-sm font-medium text-stone-700 focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900/20 outline-none transition-all duration-200 cursor-pointer shadow-2xs">
                                        <option value="">Todos los talleres</option>
                                        @foreach($workshops as $workshopItem)
                                            <option value="{{ $workshopItem->id }}" {{ request('workshop') == $workshopItem->id ? 'selected' : '' }}>
                                                {{ $workshopItem->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro Día de la semana --}}
                                <div class="flex-1 sm:max-w-[160px]">
                                    <label class="block text-[11px] font-black text-stone-400 uppercase tracking-widest mb-1.5">Día</label>
                                    <select name="day" class="w-full h-[42px] rounded-xl border border-stone-200 bg-white px-3.5 text-sm font-medium text-stone-700 focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900/20 outline-none transition-all duration-200 cursor-pointer shadow-2xs">
                                        <option value="">Cualquier día</option>
                                        <option value="1" {{ request('day') == '1' ? 'selected' : '' }}>Lunes</option>
                                        <option value="2" {{ request('day') == '2' ? 'selected' : '' }}>Martes</option>
                                        <option value="3" {{ request('day') == '3' ? 'selected' : '' }}>Miércoles</option>
                                        <option value="4" {{ request('day') == '4' ? 'selected' : '' }}>Jueves</option>
                                        <option value="5" {{ request('day') == '5' ? 'selected' : '' }}>Viernes</option>
                                        <option value="6" {{ request('day') == '6' ? 'selected' : '' }}>Sábado</option>
                                        <option value="7" {{ request('day') == '7' ? 'selected' : '' }}>Domingo</option>
                                    </select>
                                </div>

                                {{-- Botones de acción (Alineados a 42px con estados activos) --}}
                                <div class="flex gap-2 shrink-0 pt-1 sm:pt-0">
                                    <a href="{{ request()->url() }}?month={{ $monthDate->format('Y-m') }}" 
                                    class="h-[42px] px-3.5 bg-stone-200/60 text-stone-600 font-bold rounded-xl hover:bg-stone-300/70 hover:text-stone-900 transition-all duration-200 text-sm flex items-center justify-center shrink-0 active:scale-95" 
                                    title="Limpiar Filtros">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </a>
                                    <button type="submit" class="flex-1 sm:flex-initial h-[42px] px-6 bg-zinc-900 hover:bg-zinc-800 text-white font-bold rounded-xl shadow-sm transition-all duration-200 active:scale-95 text-sm flex items-center justify-center gap-2 cursor-pointer">
                                        <span>Filtrar</span>
                                    </button>
                                </div>
                            </form>
                                                    
                        </div>
                    </div>

                    @php
                        $allSessions = $studio->classSessions;
                        $hasDependents = auth()->check() && $activeDependents->count() > 0;
                    @endphp

                    {{-- ========================================== --}}
                    {{-- VISTA 1: POR DÍA (6 Tarjetas por Fila)     --}}
                    {{-- ========================================== --}}
                    <div x-show="viewMode === 'day'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        @php
                            $groupedSessions = $studio->classSessions->groupBy(fn($s) => \Carbon\Carbon::parse($s->date)->format('Y-m-d'));
                        @endphp

                        @forelse($groupedSessions as $date => $sessions)
                            <div>
                                <div class="mb-6 px-2">
                                    <h4 class="text-xl md:text-2xl font-black text-amber-600 capitalize flex items-center gap-2.5">
                                        <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l d \d\e F') }}
                                    </h4>
                                </div>

                                {{-- 🚀 GRILLA MODIFICADA A 6 COLUMNAS EN PANTALLAS ANCHAS (2xl:grid-cols-6) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-4 md:gap-5 mb-10">
                                    @foreach($sessions as $session)
                                        @php
                                            $maxSpots     = $session->max_spots ?? 99;
                                            $pendingCount = $session->pending_count ?? 0;
                                            $available    = $session->available_spots ?? $maxSpots;
                                            $isFull       = $available <= 0;
                                            $almostFull   = $available <= 3 && $available > 0;

                                            $imageUrl = $session->workshop->image_path
                                                            ? asset('storage/' . $session->workshop->image_path)
                                                            : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=dc2626&background=fef2f2&size=512';


                                            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
                                            $protocol = request()->secure() ? 'https://' : 'http://';
                                            $studioUrl = $protocol . $session->workshop->studio->subdomain . '.' . $domain;

                                            $modalData = json_encode([
                                                'title'         => $session->workshop->name,
                                                'studio'        => $session->workshop->studio->name,
                                                'studio_url'    => $studioUrl,
                                                'teacher'       => $session->workshop->teacher ? trim($session->workshop->teacher->first_name . ' ' . $session->workshop->teacher->last_name) : 'Por asignar',
                                                'teacher_email' => $session->workshop->teacher->email ?? '',
                                                'date'          => \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F'),
                                                'time'          => \Carbon\Carbon::parse($session->start_time)->format('H:i'),
                                                'image'         => $imageUrl,
                                                'address'       => $session->workshop->address ?? 'Dirección no especificada',
                                                'description'   => $session->workshop->description ?? 'Sin descripción disponible.',
                                                'video_url'     => $session->workshop->embed_video_url,
                                            ]);

                                            $dbSelections = $dbSelectionsBySession[$session->id] ?? [];
                                            $enrolledCount = count($dbSelections);
                                            $isTitularPaid = isset($dbSelections['titular']) && $dbSelections['titular'] === 'paid';
                                            $hasDependents = auth()->check() && auth()->user()->dependents->count() > 0;
                                        @endphp

                                        <div class="relative group/card bg-white {{ $isFull ? 'border-stone-900 bg-stone-50/80 opacity-75' : 'holo-white-card border-stone-200 hover:border-red-200 cursor-pointer' }} rounded-2xl md:rounded-3xl overflow-hidden transition-all duration-500 flex flex-col transform-gpu isolate">
                                            
                                            {{-- Imagen de Portada --}}
                                            <div class="h-36 bg-stone-100 relative overflow-hidden transform-gpu" onclick="openDetailModal({{ $modalData }})">
                                                <img src="{{ $imageUrl }}" alt="Clase" class="w-full h-full object-cover {{ $isFull ? 'opacity-50' : 'opacity-90' }} transition-all duration-700 ease-out">
                                                <div class="absolute inset-0 bg-gradient-to-t from-red-900/70 via-red-900/10 to-transparent {{ $isFull ? 'opacity-80' : 'opacity-50 group-hover/card:opacity-70' }} transition-opacity duration-500"></div>

                                                @if ($isFull)
                                                <div class="absolute top-0 right-0 w-24 h-24 overflow-hidden z-10 pointer-events-none">
                                                    <div class="absolute top-[10px] -right-[30px] w-36 bg-gradient-to-r from-rose-500 to-rose-600 text-white text-[8px] font-black uppercase tracking-[0.2em] py-1 text-center rotate-45 shadow-lg">Lleno</div>
                                                </div>
                                                @endif

                                                <div class="absolute top-2.5 left-2.5 z-10">
                                                    <span class="inline-block px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $isFull ? 'bg-white/60 text-stone-500' : 'bg-white/90 backdrop-blur-sm text-red-600 shadow-sm' }} border {{ $isFull ? 'border-white/30' : 'border-white/50' }} truncate max-w-[120px]">
                                                        {{ $session->workshop->discipline->area->name ?? $session->workshop->discipline->name ?? 'Clase' }}
                                                    </span>
                                                </div>


                                            </div>

                                            {{-- Contenido de la Tarjeta --}}
                                            <div class="p-4 flex-1 flex flex-col justify-between relative z-20">
                                                <div>
                                                    <div class="flex justify-between items-start mb-2" onclick="openDetailModal({{ $modalData }})">
                                                        <h3 class="text-sm md:text-base font-black text-stone-900 leading-tight group-hover/card:text-red-700 transition-colors duration-300 line-clamp-2" title="{{ $session->workshop->name }}">
                                                            {{ $session->workshop->name }}
                                                        </h3>
                                                    </div>

                                                    <div class="space-y-1.5 mt-2" onclick="openDetailModal({{ $modalData }})">
                                                        <div class="flex items-center gap-2 text-xs font-medium text-stone-500">
                                                            <div class="w-6 h-6 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                                                                <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                                                            </div>
                                                            <span class="font-bold text-stone-700">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
                                                        </div>
                                                        <div class="flex items-center gap-2 text-xs font-medium text-stone-500">
                                                            <div class="w-6 h-6 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                                                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                            </div>
                                                            <span class="truncate">Prof. {{ $session->workshop->teacher ? $session->workshop->teacher->first_name : 'Staff' }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 pt-3 border-t border-stone-100">
                                                        <div class="flex items-center justify-between text-[11px]">
                                                            <div class="flex items-center gap-1.5">
                                                                @if ($isFull)
                                                                    <span class="font-black text-rose-600 bg-rose-50 px-2 py-1 rounded-lg border border-rose-100">Lleno</span>
                                                                @elseif ($almostFull)
                                                                    <span class="font-black text-amber-600 bg-amber-50 px-2 py-1 rounded-lg border border-amber-100 flex items-center gap-1">
                                                                        <span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span></span>
                                                                        ¡Quedan {{ $available }}!
                                                                    </span>
                                                                @else
                                                                    <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">{{ $available }} {{ $available === 1 ? 'cupo' : 'cupos' }}</span>
                                                                @endif
                                                            </div>
                                                            @if ($pendingCount > 0)
                                                                <span class="font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded text-[10px]">{{ $pendingCount }} ❤️</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Botones de Acción (Con z-30 para evitar colisiones del click con la tarjeta) --}}
                                                <div class="mt-3 pt-3 border-t border-stone-100 flex items-center justify-between gap-2 relative z-30">
                                                    <div class="shrink-0">
                                                        @php $dropInPrice = $session->workshop->prices->where('class_count', 1)->first(); @endphp
                                                        @if($dropInPrice)
                                                            <p class="text-[9px] text-stone-400 font-bold uppercase tracking-wider">Suelta</p>
                                                            <p class="text-sm font-black text-stone-900">${{ number_format($dropInPrice->price, 0, ',', '.') }}</p>
                                                        @else
                                                            <p class="text-[9px] text-stone-400 font-bold uppercase tracking-wider">Desde</p>
                                                            <p class="text-xs font-black text-stone-900">Ver Planes</p>
                                                        @endif
                                                    </div>

                                                    <div class="flex-1">
                                                        @if($isFull)
                                                            <button disabled class="w-full py-2 rounded-xl text-xs font-bold bg-stone-100 text-stone-400 cursor-not-allowed border border-stone-200 text-center">Lleno</button>
                                                        @elseif(auth()->check() && !$hasDependents && $isTitularPaid)
                                                            <button disabled class="w-full py-2 rounded-xl text-xs font-bold bg-emerald-500 text-white cursor-not-allowed opacity-90 shadow-sm border-0 transition-none text-center">Pagada ✓</button>
                                                        @else
                                                            <button type="button" onclick="handleInterestClick({{ $session->id }}, this); event.stopPropagation();"
                                                                    data-session-id="{{ $session->id }}"
                                                                    data-db-selections="{{ json_encode($dbSelections) }}"
                                                                    class="interest-btn w-full py-2 rounded-xl text-xs font-bold transition-all duration-300 active:scale-95 flex items-center justify-center shadow-sm
                                                                    {{ $enrolledCount > 0 ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white border-0 hover:from-red-500 hover:to-rose-500 hover:shadow-md group/btn' : 'bg-stone-100 text-stone-700 border border-stone-200 hover:bg-red-50 hover:border-red-200 hover:text-red-700' }}">
                                                                @if($enrolledCount > 0)
                                                                    <div class="relative flex items-center justify-center w-full">
                                                                        <span class="flex items-center gap-1.5 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0 truncate px-1">
                                                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                            {{ $enrolledCount === 1 ? ($hasDependents ? '1 en Portal' : 'En Portal') : $enrolledCount.' en Portal' }}
                                                                        </span>
                                                                        <span class="absolute inset-0 flex items-center justify-center gap-1 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100 bg-red-600 rounded-xl">
                                                                            @if($hasDependents) Modificar @else Remover @endif
                                                                        </span>
                                                                    </div>
                                                                @else
                                                                    <span class="flex items-center gap-1">+ Agregar</span>
                                                                @endif
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 bg-stone-50 border border-stone-200 rounded-3xl">
                                <p class="text-stone-500 font-bold text-lg">No encontramos clases.</p>
                                <p class="text-stone-400 text-sm mt-1">Ajusta los filtros o intenta con otra fecha.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- ========================================== --}}
                    {{-- VISTA 2: HORARIO SEMANAL                   --}}
                    {{-- ========================================== --}}
                    <div x-show="viewMode === 'week'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        @php
                            $weeklyMatrix = [];
                            $timeSlotsMap = [];
                            foreach ($allSessions as $session) {
                                $startFormatted = \Carbon\Carbon::parse($session->start_time)->format('H:i');
                                $endFormatted = $session->end_time ? \Carbon\Carbon::parse($session->end_time)->format('H:i') : '';
                                $timeLabel = $startFormatted . ($endFormatted ? ' - ' . $endFormatted : ' hrs');
                                
                                $timeSlotsMap[$timeLabel] = $session->start_time;
                                $dayIso = \Carbon\Carbon::parse($session->date)->dayOfWeekIso;
                                $workshopId = $session->workshop_id;

                                if (!isset($weeklyMatrix[$timeLabel][$dayIso][$workshopId])) {
                                    $weeklyMatrix[$timeLabel][$dayIso][$workshopId] = ['workshop' => $session->workshop, 'sessions' => collect()];
                                }
                                $weeklyMatrix[$timeLabel][$dayIso][$workshopId]['sessions']->push($session);
                            }
                            asort($timeSlotsMap);
                        @endphp

                        <div class="bg-white border border-stone-200 rounded-3xl p-4 md:p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-6 border-b border-stone-100 pb-4">
                                <h4 class="text-xl md:text-2xl font-black text-stone-900 tracking-tight">📅 Horario Semanal del Estudio</h4>
                                <span class="text-xs font-bold text-stone-400">Haz clic en cualquier clase para ver y seleccionar fechas</span>
                            </div>

                            <div class="w-full overflow-x-auto hide-scrollbar pb-36">
                                <table class="w-full border-collapse min-w-[750px]">
                                    <thead>
                                        <tr class="border-b-2 border-stone-800 text-left">
                                            <th class="py-4 px-3 text-xs font-black uppercase tracking-widest text-stone-500 w-28 bg-stone-50 rounded-tl-2xl">Hora</th>
                                            @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                                                <th class="py-4 px-2 text-xs font-black uppercase tracking-widest text-stone-800 text-center">{{ $d }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-200">
                                        @forelse($timeSlotsMap as $timeLabel => $rawTime)
                                            <tr class="hover:bg-stone-50/40 transition-colors">
                                                <td class="py-4 px-3 text-xs font-black text-stone-700 whitespace-nowrap align-top border-r border-stone-100 bg-stone-50/30">{{ $timeLabel }}</td>
                                                @for($day = 1; $day <= 7; $day++)
                                                    <td class="py-3 px-2 text-center align-top relative">
                                                        @if(isset($weeklyMatrix[$timeLabel][$day]))
                                                            <div class="flex flex-col gap-2">
                                                                @foreach($weeklyMatrix[$timeLabel][$day] as $workshopId => $cellData)
                                                                    @php
                                                                        $workshop = $cellData['workshop'];
                                                                        $cellSessions = $cellData['sessions']->sortBy('date');
                                                                        $disciplineName = $workshop->discipline->name ?? 'Taller';
                                                                    @endphp
                                                                    
                                                                    <div x-data="{ openDropdown: false }" class="relative inline-block text-left w-full">
                                                                        <button type="button" @click="openDropdown = !openDropdown" @click.outside="openDropdown = false"
                                                                                class="w-full p-2.5 rounded-2xl border border-stone-200 bg-white hover:border-red-500 hover:shadow-md transition-all text-left flex flex-col items-center justify-center group active:scale-95">
                                                                            <span class="text-[9px] font-black uppercase tracking-widest text-red-600 bg-red-50 px-2 py-0.5 rounded-md mb-1 group-hover:bg-red-600 group-hover:text-white transition-colors">{{ $disciplineName }}</span>
                                                                            <span class="text-xs font-black text-stone-900 text-center leading-tight">{{ $workshop->name }}</span>
                                                                            <span class="text-[10px] font-bold text-stone-400 mt-1.5 flex items-center gap-1">
                                                                                <span>📅</span> {{ $cellSessions->count() }} {{ $cellSessions->count() === 1 ? 'fecha' : 'fechas' }}
                                                                                <svg class="w-3 h-3 transition-transform" :class="openDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                            </span>
                                                                        </button>

                                                                        <div x-show="openDropdown" x-cloak
                                                                             x-transition:enter="transition ease-out duration-200"
                                                                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                                                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                                                             class="absolute z-50 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-stone-200 p-3 text-left {{ $day >= 5 ? 'right-0 left-auto' : 'left-0 right-auto' }}">
                                                                            
                                                                            <div class="flex items-center justify-between border-b border-stone-100 pb-2 mb-2">
                                                                                <span class="text-[11px] font-black text-stone-800 uppercase tracking-wider">Fechas Disponibles</span>
                                                                                <button @click="openDropdown = false" type="button" class="text-stone-400 hover:text-stone-600 text-xs font-bold">✕</button>
                                                                            </div>

                                                                            <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar pr-1">
                                                                                @foreach($cellSessions as $session)
                                                                                    @php
                                                                                        $dbSelections = $dbSelectionsBySession[$session->id] ?? [];
                                                                                        $enrolledCount = count($dbSelections);
                                                                                        $isTitularPaid = isset($dbSelections['titular']) && $dbSelections['titular'] === 'paid';
                                                                                        $available = $session->available_spots ?? ($session->max_spots ?? 99);
                                                                                        $isFull = $available <= 0;
                                                                                    @endphp
                                                                                    <div class="p-2 rounded-xl border border-stone-100 bg-stone-50/60 hover:bg-stone-50 flex items-center justify-between gap-2">
                                                                                        <div>
                                                                                            <p class="text-xs font-black text-stone-900 capitalize">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('d \d\e F') }}</p>
                                                                                            <p class="text-[10px] font-bold text-stone-500">Prof. {{ $session->workshop->teacher ? $session->workshop->teacher->first_name : 'Staff' }} · <span class="{{ $isFull ? 'text-rose-600 font-bold' : 'text-emerald-600' }}">{{ $isFull ? 'Lleno' : ($available . ' cupos') }}</span></p>
                                                                                        </div>

                                                                                        <div class="shrink-0">
                                                                                            @if($isFull)
                                                                                                <button disabled class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-stone-200 text-stone-400 cursor-not-allowed">Lleno</button>
                                                                                            @elseif(auth()->check() && !$hasDependents && $isTitularPaid)
                                                                                                <button disabled class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-500 text-white cursor-not-allowed">Pagada ✓</button>
                                                                                            @else
                                                                                                {{-- 🚀 BLINDAJE: data-session-id y data-db-selections agregados --}}
                                                                                                <button type="button" onclick="handleInterestClick({{ $session->id }}, this)"
                                                                                                        data-session-id="{{ $session->id }}"
                                                                                                        data-db-selections="{{ json_encode($dbSelections) }}"
                                                                                                        class="interest-btn px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all active:scale-95 shadow-2xs {{ $enrolledCount > 0 ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white' : 'bg-white text-stone-700 hover:bg-red-50 hover:text-red-700 border border-stone-200' }}">
                                                                                                    {{ $enrolledCount > 0 ? ($enrolledCount === 1 ? ($hasDependents ? '1 en Portal' : 'En Portal') : $enrolledCount.' en Portal') : '+ Agregar' }}
                                                                                                </button>
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endfor
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-16 text-stone-400 font-bold">No hay clases programadas para esta semana.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- VISTA 3: HORARIO MENSUAL                   --}}
                    {{-- ========================================== --}}
                    <div x-show="viewMode === 'month'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        @php
                            $calendarMonth = $monthDate->copy()->startOfMonth();
                            $daysInMonth   = $calendarMonth->daysInMonth;
                            $emptyCells    = $calendarMonth->dayOfWeekIso - 1; 
                            $sessionsByDate = $allSessions->groupBy(fn($s) => \Carbon\Carbon::parse($s->date)->format('Y-m-d'));
                        @endphp

                        <div class="bg-white border border-stone-200 rounded-3xl p-4 md:p-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6 border-b border-stone-100 pb-4">
                                <h4 class="text-xl md:text-2xl font-black text-stone-900 capitalize flex items-center gap-2"><span>🗓️</span> {{ $calendarMonth->translatedFormat('F Y') }}</h4>
                                <div class="flex items-center gap-4 text-xs font-bold text-stone-500">
                                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-600"></span> Hoy</span>
                                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Cupos disponibles</span>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden relative">
                                <div class="absolute top-0 right-0 bottom-0 w-8 bg-gradient-to-l from-white/95 to-transparent pointer-events-none md:hidden z-20"></div>

                                <div class="overflow-x-auto custom-scrollbar">
                                    <div class="min-w-[1300px] w-full">
                                        <div class="grid grid-cols-7 border-b border-stone-200 bg-stone-50">
                                            @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $dayName)
                                                <div class="py-3 text-center text-xs font-black text-stone-500 uppercase tracking-wider border-r border-stone-200 last:border-0">{{ $dayName }}</div>
                                            @endforeach
                                        </div>

                                        <div class="grid grid-cols-7 gap-px bg-stone-200">
                                            @for ($i = 0; $i < $emptyCells; $i++) <div class="bg-stone-50/50 min-h-[160px]"></div> @endfor

                                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                                @php
                                                    $currentDateStr = $calendarMonth->copy()->day($day)->format('Y-m-d');
                                                    $daySessions = $sessionsByDate->get($currentDateStr, collect());
                                                    $isToday = $currentDateStr === \Carbon\Carbon::today()->format('Y-m-d');
                                                @endphp

                                                <div class="bg-white min-h-[160px] p-2 transition flex flex-col justify-start {{ $isToday ? 'ring-2 ring-inset ring-red-600 bg-stone-50/30' : '' }}">
                                                    <div class="flex justify-between items-center mb-2 px-1">
                                                        <span class="text-sm font-black flex items-center justify-center h-7 w-7 rounded-full {{ $isToday ? 'bg-red-600 text-white shadow-sm' : 'text-stone-700' }}">{{ $day }}</span>
                                                        @if($daySessions->count() > 0)
                                                            <span class="text-[10px] font-extrabold text-stone-400 bg-stone-100 px-1.5 py-0.5 rounded-md">{{ $daySessions->count() }} {{ $daySessions->count() === 1 ? 'clase' : 'clases' }}</span>
                                                        @endif
                                                    </div>

                                                    <div class="space-y-2 overflow-y-auto max-h-[320px] custom-scrollbar pr-0.5">
                                                        @foreach($daySessions as $session)
                                                            @php
                                                                $dbSelections = $dbSelectionsBySession[$session->id] ?? [];
                                                                $enrolledCount = count($dbSelections);
                                                                $isTitularPaid = isset($dbSelections['titular']) && $dbSelections['titular'] === 'paid';
                                                                $available = $session->available_spots ?? ($session->max_spots ?? 99);
                                                                $isFull = $available <= 0;
                                                                $imageUrl = $session->workshop->image_path ? asset('storage/' . $session->workshop->image_path) : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=dc2626&background=fef2f2&size=128';
                                                                
                                                                $dateFormatted = \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F');
                                                                $timeFormatted = \Carbon\Carbon::parse($session->start_time)->format('H:i');
                                                            @endphp

                                                            <div class="relative w-full p-2 bg-white border {{ $enrolledCount > 0 ? 'border-red-500 ring-1 ring-red-500/20 bg-red-50/10' : ($isFull ? 'border-stone-200 bg-stone-50 opacity-75' : 'border-stone-200 hover:border-red-300') }} rounded-xl shadow-xs hover:shadow-md transition-all duration-200 flex flex-col gap-1.5 group/card overflow-hidden">
                                                                <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isFull ? 'bg-stone-300' : 'bg-red-600' }}"></div>

                                                                <div onclick="openDetailModal({{ $session->workshop_id }}, '{{ $dateFormatted }}', '{{ $timeFormatted }}')" class="pl-1.5 cursor-pointer flex items-center gap-2">
                                                                    <img src="{{ $imageUrl }}" class="w-8 h-8 rounded-lg object-cover shadow-2xs border border-stone-100 shrink-0">
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="flex justify-between items-start">
                                                                            <span class="text-[11px] font-black text-stone-900 leading-none">{{ $timeFormatted }}</span>
                                                                            <span class="text-[9px] font-bold {{ $isFull ? 'text-rose-600' : 'text-emerald-600' }}">{{ $isFull ? 'Lleno' : ($available . ' cupos') }}</span>
                                                                        </div>
                                                                        <div class="text-[11px] font-extrabold text-stone-800 truncate mt-0.5 group-hover/card:text-red-600 transition-colors">{{ $session->workshop->name }}</div>
                                                                    </div>
                                                                </div>

                                                                <div class="pl-1.5 pt-1 border-t border-stone-100 flex items-center justify-between gap-1">
                                                                    <span class="text-[10px] font-bold text-stone-500 truncate">Prof. {{ $session->workshop->teacher->first_name ?? 'Staff' }}</span>
                                                                    <div class="shrink-0" onclick="event.stopPropagation();">
                                                                        @if($isFull)
                                                                            <button disabled class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-stone-100 text-stone-400 cursor-not-allowed">Lleno</button>
                                                                        @elseif(auth()->check() && !$hasDependents && $isTitularPaid)
                                                                            <button disabled class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-500 text-white cursor-not-allowed">Pagada ✓</button>
                                                                        @else
                                                                            {{-- 🚀 BLINDAJE: data-session-id y data-db-selections agregados --}}
                                                                            <button type="button" onclick="handleInterestClick({{ $session->id }}, this)"
                                                                                    data-session-id="{{ $session->id }}"
                                                                                    data-db-selections="{{ json_encode($dbSelections) }}"
                                                                                    class="interest-btn px-2 py-0.5 rounded-md text-[9px] font-black transition-all active:scale-95 shadow-2xs {{ $enrolledCount > 0 ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white' : 'bg-stone-100 text-stone-700 hover:bg-red-50 hover:text-red-700 border border-stone-200' }}">
                                                                                {{ $enrolledCount > 0 ? ($enrolledCount === 1 ? ($hasDependents ? '1 en Portal' : 'En Portal') : $enrolledCount.' en Portal') : '+ Agregar' }}
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endfor

                                            @php
                                                $totalRendered = $emptyCells + $daysInMonth;
                                                $rem = (7 - ($totalRendered % 7)) % 7;
                                            @endphp
                                            @for ($i = 0; $i < $rem; $i++) <div class="bg-stone-50/50 min-h-[160px]"></div> @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @include('public.studio._calendario-modals')
    @include('public.studio._mini-cart')
    
</x-app-layout>
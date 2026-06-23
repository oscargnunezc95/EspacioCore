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

            {{-- GRILLA DE RESULTADOS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($sessions as $session)
                    @php
                        $maxSpots     = $session->max_spots ?? 99;
                        $pendingCount = $session->pending_count ?? 0;
                        $available    = $session->available_spots ?? $maxSpots;
                        $isFull       = $available <= 0;
                        $almostFull   = $available <= 3 && $available > 0;

                        $imageUrl = $session->workshop->image_path
                                        ? asset('storage/' . $session->workshop->image_path)
                                        : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=4f46e5&background=e0e7ff&size=512';

                        $studioLogo = $session->workshop->studio->icon_path ?? $session->workshop->studio->logo_path ?? null;
                        $studioImageUrl = $studioLogo
                                        ? asset('storage/' . $studioLogo)
                                        : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->studio->name).'&color=ffffff&background=4c1d95&size=128';

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

                        // MAGIA BLADE: DETECCIÓN EXACTA DE FAMILIARES
                        $dbSelections = $dbSelectionsBySession[$session->id] ?? [];
                        $enrolledCount = count($dbSelections);
                        $isTitularPaid = isset($dbSelections['titular']) && $dbSelections['titular'] === 'paid';
                        $hasDependents = auth()->check() && auth()->user()->dependents->count() > 0;
                    @endphp

                    <div class="relative group/card bg-white border {{ $isFull ? 'border-stone-200 bg-stone-50/80' : 'border-zinc-200 hover:border-indigo-200' }} rounded-3xl overflow-hidden {{ $isFull ? '' : 'hover:shadow-2xl hover:shadow-indigo-100/50 hover:-translate-y-1.5' }} transition-all duration-500 flex flex-col transform-gpu isolate {{ $isFull ? 'opacity-75' : '' }}">

                        {{-- Imagen con overlay --}}
                        <div class="h-44 bg-zinc-100 relative overflow-hidden cursor-pointer transform-gpu" onclick="openDetailModal({{ $modalData }})">
                            <img src="{{ $imageUrl }}" alt="Clase" class="w-full h-full object-cover {{ $isFull ? 'opacity-50' : 'opacity-90' }} {{ $isFull ? '' : 'group-hover/card:opacity-100 group-hover/card:scale-110' }} transition-all duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-indigo-900/70 via-indigo-900/10 to-transparent {{ $isFull ? 'opacity-80' : 'opacity-50 group-hover/card:opacity-70' }} transition-opacity duration-500"></div>

                            {{-- Ribbon "Clase Llena" --}}
                            @if ($isFull)
                            <div class="absolute top-0 right-0 w-28 h-28 overflow-hidden z-10 pointer-events-none">
                                <div class="absolute top-[13px] -right-[32px] w-40 bg-gradient-to-r from-rose-500 to-rose-600 text-white text-[9px] font-black uppercase tracking-[0.2em] py-1 text-center rotate-45 shadow-lg">
                                    Lleno
                                </div>
                            </div>
                            @endif

                            {{-- Badge de categoría --}}
                            <div class="absolute top-3 left-3 z-10">
                                <span class="inline-block px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest
                                    {{ $isFull ? 'bg-white/60 text-stone-500' : 'bg-white/90 backdrop-blur-sm text-indigo-600 shadow-sm' }}
                                    border {{ $isFull ? 'border-white/30' : 'border-white/50' }}">
                                    {{ $session->workshop->discipline->area->name ?? $session->workshop->discipline->name ?? 'Clase' }}
                                </span>
                            </div>

                            {{-- Logo del estudio --}}
                            <div class="absolute bottom-3 right-3 {{ $isFull ? '' : 'group-hover/card:scale-110 group-hover/card:rotate-3' }} transition-all duration-500 z-10">
                                <div class="relative w-11 h-11 rounded-2xl bg-white shadow-lg border-2 {{ $isFull ? 'border-stone-200 opacity-70' : 'border-white' }} overflow-hidden transform -rotate-2 group-hover/card:rotate-0 transition-transform duration-500" title="{{ $session->workshop->studio->name }}">
                                    <img src="{{ $studioImageUrl }}" alt="Logo Estudio" class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>

                        {{-- Contenido de la card --}}
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-3 cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                                <h3 class="text-base md:text-lg font-black text-zinc-900 leading-tight group-hover/card:text-indigo-700 transition-colors duration-300 line-clamp-2">
                                    {{ $session->workshop->name }}
                                </h3>
                            </div>

                            <div class="space-y-2.5 mt-auto cursor-pointer" onclick="openDetailModal({{ $modalData }})">
                                <div class="flex items-center gap-2.5 text-sm font-medium text-zinc-500">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="font-bold text-zinc-700">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-sm font-medium text-zinc-500">
                                    <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <span class="truncate">Prof. {{ $session->workshop->teacher ? $session->workshop->teacher->first_name . ' ' . $session->workshop->teacher->last_name : 'Staff' }}</span>
                                </div>
                            </div>

                            {{-- Indicador de cupos --}}
                            <div class="mt-4 pt-4 border-t border-zinc-100">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        @if ($isFull)
                                            <span class="font-black text-rose-600 flex items-center gap-1.5 bg-rose-50 px-2.5 py-1.5 rounded-xl border border-rose-100">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                Lleno
                                            </span>
                                        @elseif ($almostFull)
                                            <span class="font-black text-amber-600 bg-amber-50 px-2.5 py-1.5 rounded-xl border border-amber-100 flex items-center gap-1.5">
                                                <span class="relative flex h-2 w-2">
                                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                                </span>
                                                ¡Quedan {{ $available }}!
                                            </span>
                                        @else
                                            <span class="font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-100 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                {{ $available }} {{ $available === 1 ? 'cupo' : 'cupos' }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($pendingCount > 0)
                                        <span class="font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-lg text-[11px]">
                                            {{ $pendingCount }} {{ $pendingCount === 1 ? '💜' : '💜💜' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Precio + Botón de acción --}}
                            <div class="mt-5 pt-4 border-t border-zinc-100 flex items-center justify-between gap-3">
                                <div class="shrink-0">
                                    @php
                                        $dropInPrice = $session->workshop->prices->where('class_count', 1)->first();
                                    @endphp
                                    @if($dropInPrice)
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">Clase suelta</p>
                                        <p class="text-lg font-black text-zinc-900">${{ number_format($dropInPrice->price, 0, ',', '.') }}</p>
                                    @else
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">Desde</p>
                                        <p class="text-sm font-black text-zinc-900">Ver Planes</p>
                                    @endif
                                </div>

                                @if($isFull)
                                    <button disabled class="flex-1 sm:flex-none sm:w-[130px] px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 bg-stone-100 text-stone-400 cursor-not-allowed border border-stone-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Lleno
                                    </button>
                                @elseif(auth()->check() && !$hasDependents && $isTitularPaid)
                                    <button disabled class="flex-1 sm:flex-none sm:w-[130px] px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white cursor-not-allowed opacity-90 shadow-md border-0 transition-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Pagada ✓
                                    </button>
                                @else
                                    <button onclick="handleInterestClick({{ $session->id }}, this)"
                                            data-db-selections="{{ json_encode($dbSelections) }}"
                                            class="interest-btn flex-1 sm:flex-none sm:w-[130px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 active:scale-95 flex items-center justify-center shadow-sm
                                            {{ $enrolledCount > 0 ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white border-0 hover:from-indigo-500 hover:to-purple-500 hover:shadow-md hover:shadow-indigo-200 group/btn' : 'bg-zinc-100 text-zinc-700 border border-zinc-200 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:border-indigo-200 hover:text-indigo-700' }}">
                                        @if($enrolledCount > 0)
                                            <div class="relative flex items-center justify-center w-full">
                                                <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    {{ $enrolledCount === 1 ? ($hasDependents ? '1 en Portal' : 'En Portal') : $enrolledCount.' en Portal' }}
                                                </span>
                                                <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">
                                                    @if($hasDependents)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.762z"></path></svg> Modificar
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover
                                                    @endif
                                                </span>
                                            </div>
                                        @else
                                            <span class="flex items-center gap-1.5">✨ Me Interesa</span>
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

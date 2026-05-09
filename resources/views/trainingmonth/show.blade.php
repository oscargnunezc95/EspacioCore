<x-app-layout>
    <x-slot name="header">
        <!-- 1. Pestañas de Navegación -->
        <x-studio-tabs />

        <!-- 2. Cabecera en Cascada -->
        <div class="mt-8">
            <x-studio-header 
                title="{{ ucfirst($monthDate->translatedFormat('F Y')) }}" 
                :breadcrumbs="[
                    ['name' => 'Planificación', 'url' => route('trainingmonth.index')],
                    ['name' => ucfirst($monthDate->translatedFormat('F'))]
                ]"
            >
            </x-studio-header>
        </div>
    </x-slot>

    <!-- 3. Contenido Principal: CALENDARIO -->
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            
            {{-- Días de la semana --}}
            <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50">
                @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $d)
                    <div class="py-3 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider border-r border-zinc-200 last:border-0">
                        <span class="hidden sm:inline">{{ $d }}</span>
                        <span class="sm:hidden">{{ substr($d, 0, 3) }}</span> {{-- Abreviado en móviles --}}
                    </div>
                @endforeach
            </div>

            {{-- Cuadrícula del mes --}}
            <div class="grid grid-cols-7 gap-px bg-zinc-200">
                @php
                    $start = $monthDate->copy()->startOfMonth();
                    $empty = $start->dayOfWeekIso - 1;
                    $days = $monthDate->daysInMonth;
                @endphp

                {{-- Días vacíos iniciales --}}
                @for ($i = 0; $i < $empty; $i++) 
                    <div class="bg-zinc-50/50 min-h-[120px] md:min-h-[160px]"></div> 
                @endfor

                {{-- Días del mes --}}
                @for ($day = 1; $day <= $days; $day++)
                    @php
                        $cur = $monthDate->copy()->day($day)->toDateString();
                        $sessionsInDay = $sessionsByDate->get($cur, collect());
                        $isToday = \Carbon\Carbon::parse($cur)->isToday();
                    @endphp
                    
                    <div class="bg-white min-h-[120px] md:min-h-[160px] p-2 md:p-3 transition-colors hover:bg-zinc-50 {{ $isToday ? 'ring-2 ring-inset ring-zinc-900 bg-zinc-50/30' : '' }}">
                        
                        {{-- Cabecera del día --}}
                        <div class="flex justify-between items-start mb-2 md:mb-3">
                            <span class="text-sm font-bold flex items-center justify-center h-7 w-7 md:h-8 md:w-8 rounded-full {{ $isToday ? 'bg-zinc-900 text-white shadow-sm' : 'text-zinc-700' }}">
                                {{ $day }}
                            </span>
                            @if($sessionsInDay->count() > 0)
                                <span class="hidden md:inline-block text-xs font-bold text-zinc-400 mt-1">{{ $sessionsInDay->count() }} clases</span>
                                <span class="md:hidden text-xs font-bold text-zinc-400 mt-1">{{ $sessionsInDay->count() }}</span>
                            @endif
                        </div>
                        
                        {{-- Lista de clases --}}
                        <div class="space-y-1.5 md:space-y-2">
                            @foreach($sessionsInDay as $s)
                                @php 
                                    $colorTaller = $s->workshop->color ?? 'blue'; 
                                    $bgClass = match($colorTaller) {
                                        'emerald' => 'bg-emerald-500', 'rose' => 'bg-rose-500', 'purple' => 'bg-purple-500',
                                        'amber' => 'bg-amber-500', 'indigo' => 'bg-indigo-500', 'teal' => 'bg-teal-500',
                                        'cyan' => 'bg-cyan-500', 'fuchsia' => 'bg-fuchsia-500', 'slate' => 'bg-slate-500',
                                        default => 'bg-blue-500',
                                    };
                                @endphp
                                                                
                                <a href="{{ route('sessions.show', ['subdomain' => request()->route('subdomain'), 'session' => $s->id]) }}" 
                                class="relative block p-2 pl-3 md:p-2.5 md:pl-3.5 bg-white border border-zinc-200 rounded-lg shadow-sm hover:shadow-md hover:border-zinc-400 transition-all duration-200 group overflow-hidden">
                                                                    
                                    {{-- Barra de Color Identificadora (A prueba de Tailwind Purge) --}}
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $bgClass }}"></div>
                                    
                                    {{-- Hora --}}
                                    <div class="text-[10px] md:text-xs font-extrabold text-zinc-900 group-hover:text-zinc-600 transition-colors">
                                        {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                    </div>
                                    
                                    {{-- Nombre del Taller --}}
                                    <div class="text-[10px] md:text-xs font-medium text-zinc-500 leading-tight mt-0.5 line-clamp-1 md:line-clamp-2">
                                        {{ $s->workshop->name }}
                                    </div>

                                    {{-- Indicador de Cancelación --}}
                                    @if($s->is_cancelled)
                                        <div class="absolute right-1.5 top-1.5">
                                            <span class="flex h-2 w-2 rounded-full bg-red-500 shadow-sm" title="Clase Cancelada"></span>
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        </div>

                    </div>
                @endfor

                {{-- Relleno final para mantener la grilla cuadrada --}}
                @php
                    $totalCells = $empty + $days;
                    $remainingCells = 7 - ($totalCells % 7);
                    if ($remainingCells == 7) $remainingCells = 0;
                @endphp
                @for ($i = 0; $i < $remainingCells; $i++)
                    <div class="bg-zinc-50/50 min-h-[120px] md:min-h-[160px]"></div>
                @endfor
                
            </div>
        </div>
    </div>
</x-app-layout>
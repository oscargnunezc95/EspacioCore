<x-app-layout>
    
    {{-- 1. NAVEGACIÓN DEL ESTUDIO (Libre de paddings, pegado arriba) --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO (Contenedor maestro acoplado a la nueva arquitectura) --}}
    <div class="pt-6 pb-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Cabecera Unificada del Calendario Planificado --}}
        <div class="mt-2 mb-8 p-1">

            {{-- Breadcrumbs con navegación funcional --}}
            <nav class="flex text-xs font-bold text-zinc-500 mb-3 gap-2 items-center">
                <a href="{{ route('trainingmonth.index', ['subdomain' => request()->route('subdomain')]) }}" class="hover:text-zinc-900 transition-colors">Planificación</a>
                <span>/</span>
                <span class="text-zinc-900 capitalize">{{ $monthDate->translatedFormat('F Y') }}</span>
            </nav>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">
                
                {{-- Título --}}
                <h1 class="text-2xl md:text-3xl font-black text-zinc-900 truncate flex-1 min-w-0">
                    Calendario Planificado
                </h1>

                {{-- Botón Responsivo (Estilo Secundario para "Volver") --}}
                <a href="{{ route('trainingmonth.index', ['subdomain' => request()->route('subdomain')]) }}" 
                   class="shrink-0 ml-auto bg-zinc-100 text-zinc-700 border border-zinc-200 px-3 sm:px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-zinc-200 hover:text-zinc-900 focus:ring-2 focus:ring-zinc-200 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2">
                    
                    {{-- Icono de Flecha hacia atrás --}}
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    
                    {{-- Texto oculto en móviles --}}
                    <span class="hidden sm:inline">Volver a los Ciclos</span>
                </a>
            </div>
        </div>

        {{-- A partir de aquí sigue intacto el resto de tu código (calendario, sesiones, etc.) --}}
        
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-black text-zinc-900 capitalize tracking-tight">{{ $monthDate->translatedFormat('F Y') }}</h2>
            <p class="mt-2 text-zinc-500 font-medium">Revisa las clases generadas para este ciclo.</p>
        </div>

        {{-- Calendario Maestro --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50">
                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                    <div class="py-3 text-center text-[10px] md:text-xs font-bold text-zinc-500 uppercase tracking-wider border-r border-zinc-200 last:border-0">{{ $d }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-px bg-zinc-200">
                @php
                    $start = $monthDate->copy()->startOfMonth();
                    $empty = $start->dayOfWeekIso - 1;
                    $days = $monthDate->daysInMonth;
                    $subdomain = request()->route('subdomain');
                @endphp

                @for ($i = 0; $i < $empty; $i++) <div class="bg-zinc-50/50 min-h-[140px]"></div> @endfor

                @for ($day = 1; $day <= $days; $day++)
                    @php
                        $cur = $monthDate->copy()->day($day)->toDateString();
                        $sessionsInDay = $sessionsByDate->get($cur, collect());
                        $isToday = \Carbon\Carbon::parse($cur)->isToday();
                        $classCount = $sessionsInDay->count();
                    @endphp
                    
                    <div class="bg-white min-h-[140px] p-1.5 md:p-2 flex flex-col relative transition-all {{ $isToday ? 'ring-2 ring-inset ring-zinc-900 bg-zinc-50/30' : '' }}">
                        
                        <div class="flex justify-between items-start mb-1.5">
                            <span class="text-sm font-bold flex items-center justify-center h-6 w-6 md:h-7 md:w-7 rounded-full {{ $isToday ? 'bg-zinc-900 text-white' : 'text-zinc-500' }}">{{ $day }}</span>
                        </div>
                        
                        @if($classCount > 0)
                            @php
                                // Preparamos la data del Modal para TODOS los casos
                                $dayData = $sessionsInDay->map(function($s) use ($subdomain) {
                                    $imageUrl = $s->workshop->image_path 
                                        ? asset('storage/' . $s->workshop->image_path) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=128';
                                    
                                    return [
                                        'id' => $s->id,
                                        'time' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                                        'name' => $s->workshop->name,
                                        'url' => route('sessions.show', ['subdomain' => $subdomain, 'session' => $s->id]),
                                        'image' => $imageUrl,
                                        'is_cancelled' => $s->is_cancelled ?? false
                                    ];
                                })->toJson();
                                $formattedDate = \Carbon\Carbon::parse($cur)->translatedFormat('l d \d\e F');
                            @endphp

                            {{-- LÓGICA DE ESCRITORIO/TABLET: Muestra la lista con fotos si son 3 o menos --}}
                            @if($classCount <= 3)
                                <div class="hidden md:block space-y-1.5 flex-1">
                                    @foreach($sessionsInDay as $s)
                                        @php
                                            $c = $s->workshop->color ?? 'indigo';
                                            $bgClass = match($c) {
                                                'emerald' => 'bg-emerald-500', 'rose' => 'bg-rose-500', 'purple' => 'bg-purple-500',
                                                'amber' => 'bg-amber-500', 'indigo' => 'bg-indigo-500', 'teal' => 'bg-teal-500',
                                                'cyan' => 'bg-cyan-500', 'fuchsia' => 'bg-fuchsia-500', 'slate' => 'bg-slate-500',
                                                default => 'bg-indigo-500',
                                            };
                                            $sessionUrl = route('sessions.show', ['subdomain' => $subdomain, 'session' => $s->id]);
                                            $isCancelled = $s->is_cancelled ?? false;
                                            
                                            // Imagen para la vista inline
                                            $imageUrl = $s->workshop->image_path 
                                                ? asset('storage/' . $s->workshop->image_path) 
                                                : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=128';
                                        @endphp

                                        <a href="{{ $sessionUrl }}" class="relative block p-1.5 md:p-2 pl-2.5 bg-zinc-50 border border-zinc-100 rounded-lg hover:border-indigo-300 hover:shadow-sm transition-all duration-200 group {{ $isCancelled ? 'opacity-60 grayscale' : '' }}">
                                            <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-lg {{ $isCancelled ? 'bg-zinc-400' : $bgClass }}"></div>
                                            
                                            <div class="flex items-center gap-2">
                                                <img src="{{ $imageUrl }}" class="w-6 h-6 md:w-7 md:h-7 rounded-md object-cover border border-zinc-200 shrink-0">
                                                
                                                <div class="flex flex-col min-w-0 flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-[10px] font-black leading-none {{ $isCancelled ? 'text-zinc-500 line-through' : 'text-zinc-900' }}">
                                                            {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                                        </span>
                                                        @if($isCancelled)
                                                            <span class="text-[7px] font-black uppercase tracking-widest text-rose-600 bg-rose-100 px-1 rounded shrink-0 ml-1">Anulada</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-[9px] md:text-[10px] font-bold text-zinc-500 truncate mt-0.5 group-hover:text-indigo-600 transition-colors">
                                                        {{ $s->workshop->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- LÓGICA MÓVIL (Y ESCRITORIO > 3): Botón agrupador --}}
                            {{-- En móviles (sm) siempre se ve. En Desktop (md) solo se ve si hay >3 clases --}}
                            <button onclick="openDayClasses('{{ $formattedDate }}', {{ $dayData }})" 
                                    class="mt-auto mb-auto mx-1 py-2 md:py-3 px-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-xl flex flex-col items-center justify-center gap-1 group transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $classCount <= 3 ? 'md:hidden' : '' }}">
                                <span class="text-lg md:text-xl font-black text-indigo-600 leading-none group-hover:scale-110 transition-transform">{{ $classCount }}</span>
                                <span class="text-[9px] md:text-[10px] font-bold text-indigo-700 uppercase tracking-widest">Clases</span>
                            </button>

                        @endif
                    </div>
                @endfor

                @php
                    $remainingCells = 7 - (($empty + $days) % 7);
                    if ($remainingCells == 7) $remainingCells = 0;
                @endphp
                @for ($i = 0; $i < $remainingCells; $i++) <div class="bg-zinc-50/50 min-h-[140px]"></div> @endfor
            </div>
        </div>
    </div>

    {{-- MODAL PARA DÍAS SATURADOS (>3 CLASES) --}}
    <div id="dayClassesModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" onclick="closeDayClasses()"></div>
        
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="dayModalCard">
            
            <div class="px-6 py-5 border-b border-zinc-100 flex justify-between items-center bg-zinc-50">
                <div>
                    <h3 class="text-xl font-black text-zinc-900 tracking-tight">Clases del Día</h3>
                    <p id="modalDayDate" class="text-xs font-bold text-zinc-500 capitalize mt-0.5">Fecha</p>
                </div>
                <button onclick="closeDayClasses()" class="p-2 text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-4 md:p-6 overflow-y-auto flex-1 space-y-3" id="modalClassesList">
                </div>
        </div>
    </div>

    <script>
        const dayModal = document.getElementById('dayClassesModal');
        const dayModalCard = document.getElementById('dayModalCard');
        const listContainer = document.getElementById('modalClassesList');

        function openDayClasses(dateStr, classesArray) {
            document.getElementById('modalDayDate').innerText = dateStr;
            listContainer.innerHTML = '';

            classesArray.forEach(cls => {
                const link = document.createElement('a');
                link.href = cls.url;
                
                // Estilos para canceladas vs normales
                link.className = cls.is_cancelled 
                    ? "block bg-zinc-50 border border-zinc-200 rounded-2xl p-3 opacity-70 grayscale transition-all group"
                    : "block bg-white border border-zinc-200 hover:border-indigo-300 hover:shadow-md rounded-2xl p-3 transition-all duration-200 group";
                
                const titleClass = cls.is_cancelled ? "text-zinc-500 line-through" : "text-zinc-900 group-hover:text-indigo-600 transition-colors";
                const badgeHtml = cls.is_cancelled 
                    ? `<span class="bg-rose-100 text-rose-700 text-[9px] font-black px-2 py-0.5 rounded border border-rose-200 uppercase tracking-widest mt-1.5 inline-block">Anulada</span>` 
                    : '';

                link.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="${cls.image}" class="w-10 h-10 rounded-xl object-cover border border-zinc-100 shadow-sm shrink-0">
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold leading-tight truncate ${titleClass}">${cls.name}</h4>
                                ${badgeHtml}
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 ml-2">
                            <div class="bg-zinc-100 text-zinc-700 font-black text-xs px-2.5 py-1.5 rounded-lg border border-zinc-200">
                                ${cls.time}
                            </div>
                            <svg class="w-5 h-5 text-zinc-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                `;
                listContainer.appendChild(link);
            });

            dayModal.classList.remove('hidden');
            setTimeout(() => {
                dayModalCard.classList.remove('scale-95', 'opacity-0');
                dayModalCard.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeDayClasses() {
            dayModalCard.classList.remove('scale-100', 'opacity-100');
            dayModalCard.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                dayModal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape" && !dayModal.classList.contains('hidden')) {
                closeDayClasses();
            }
        });
    </script>
</x-app-layout>
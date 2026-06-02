<x-app-layout>
    <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        
        <div class="text-center mb-10 md:mb-14">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Mis clases a dictar</h1>
            <p class="mt-3 text-zinc-500 font-medium text-base md:text-lg">Tu agenda unificada como Profesor/a.</p>
        </div>

        @if(!$mpLinked)
            <div class="mb-6 text-center">
                <p class="text-xs text-amber-600 font-medium">
                    ⚠ Recuerda vincular tu cuenta de Mercado Pago para recibir los pagos del estudio.
                    <a href="{{ route('global.payments.index') }}" class="underline font-bold hover:text-amber-800">Ir a Pagos</a>
                </p>
            </div>
        @endif

        {{-- Controles de Navegación del Mes --}}
        @php
            $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
            $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
        @endphp

        <div class="flex justify-between items-center mb-6 bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-zinc-200">
            
            {{-- Botón Anterior --}}
            <a href="{{ route('global.classes.teacher', ['month' => $prevMonth]) }}" 
               class="px-3 sm:px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm flex items-center gap-1.5 sm:gap-2 active:scale-95">
                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                <span class="hidden sm:inline">Anterior</span>
            </a>
            
            {{-- Título del Mes --}}
            <h2 class="text-lg sm:text-xl md:text-2xl font-black text-zinc-800 capitalize truncate px-2 text-center">
                {{ $monthDate->translatedFormat('F Y') }}
            </h2>
            
            {{-- Botón Siguiente --}}
            <a href="{{ route('global.classes.teacher', ['month' => $nextMonth]) }}" 
               class="px-3 sm:px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm flex items-center gap-1.5 sm:gap-2 active:scale-95">
                <span class="hidden sm:inline">Siguiente</span>
                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </a>
            
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
                @endphp

                @for ($i = 0; $i < $empty; $i++) <div class="bg-zinc-50/50 min-h-[120px] md:min-h-[140px]"></div> @endfor

                @for ($day = 1; $day <= $days; $day++)
                    @php
                        $cur = $monthDate->copy()->day($day)->toDateString();
                        $sessionsInDay = $sessionsByDate->get($cur, collect());
                        $isToday = \Carbon\Carbon::parse($cur)->isToday();
                        $classCount = $sessionsInDay->count();
                    @endphp
                    
                    <div class="bg-white min-h-[120px] md:min-h-[140px] p-1.5 md:p-2 flex flex-col relative transition-all {{ $isToday ? 'ring-2 ring-inset ring-zinc-900 bg-zinc-50/30' : '' }}">
                        <div class="flex justify-between items-start mb-1.5">
                            <span class="text-sm font-bold flex items-center justify-center h-6 w-6 md:h-7 md:w-7 rounded-full {{ $isToday ? 'bg-zinc-900 text-white' : 'text-zinc-500' }}">{{ $day }}</span>
                        </div>
                        
                        @if($classCount > 0)
                            @php
                                // Preparamos la data del Modal para TODOS los casos
                                $dayData = $sessionsInDay->map(function($s) {
                                    $imageUrl = $s->workshop->image_path 
                                        ? asset('storage/' . $s->workshop->image_path) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=128';
                                    
                                    $studioLogo = $s->workshop->studio->icon_path ?? $s->workshop->studio->logo_path ?? null;
                                    $studioAvatar = $studioLogo 
                                        ? asset('storage/' . $studioLogo) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->studio->name).'&color=ffffff&background=18181b&size=128';

                                    $isCancelled = $s->is_cancelled ?? ($s->status === 'cancelled') ?? false;

                                    return [
                                        'id' => $s->id,
                                        'time' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                                        'name' => $s->workshop->name,
                                        'studio' => $s->workshop->studio->name,
                                        'studio_avatar' => $studioAvatar,
                                        'url' => route('global.classes.teacher.session', $s->id),
                                        'image' => $imageUrl,
                                        'is_cancelled' => (bool) $isCancelled
                                    ];
                                })->toJson();
                                $formattedDate = \Carbon\Carbon::parse($cur)->translatedFormat('l d \d\e F');
                            @endphp

                            {{-- LÓGICA DE ESCRITORIO/TABLET: Muestra la lista con el diseño unificado del Alumno --}}
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
                                            $sessionUrl = route('global.classes.teacher.session', $s->id);
                                            $isCancelled = $s->is_cancelled ?? ($s->status === 'cancelled') ?? false;
                                            
                                            // Imagen
                                            $imageUrl = $s->workshop->image_path 
                                                ? asset('storage/' . $s->workshop->image_path) 
                                                : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=128';

                                            // Estados simplificados (Sin pagos, solo Activo o Cancelado)
                                            if ($isCancelled) {
                                                $cardClasses = 'border-zinc-200 bg-zinc-50 opacity-75 grayscale';
                                            } else {
                                                $cardClasses = 'border-zinc-200 bg-white hover:border-zinc-400';
                                            }
                                        @endphp

                                        <a href="{{ $sessionUrl }}" class="relative w-full block text-left p-1.5 md:p-2 pl-3 border {{ $cardClasses }} rounded-xl shadow-sm overflow-hidden flex items-center gap-2 transition-all duration-200 group">
                                            
                                            {{-- Barra Izquierda de Color --}}
                                            <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isCancelled ? 'bg-zinc-400' : $bgClass }}"></div>

                                            {{-- Miniatura de Imagen del Taller --}}
                                            <img src="{{ $imageUrl }}" class="w-8 h-8 rounded-lg object-cover shadow-sm border border-zinc-100 shrink-0">

                                            {{-- Contenido --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    {{-- Hora --}}
                                                    <div class="text-[10px] md:text-xs font-extrabold leading-none {{ $isCancelled ? 'text-zinc-500 line-through' : 'text-zinc-900' }}">
                                                        {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                                    </div>
                                                    
                                                    {{-- Indicador Cancelado --}}
                                                    @if($isCancelled)
                                                        <span class="bg-rose-100 text-rose-700 text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest shrink-0">Anulada</span>
                                                    @endif
                                                </div>

                                                {{-- Textos (Título y Sede) --}}
                                                <div class="text-[10px] font-bold mt-0.5 truncate {{ $isCancelled ? 'text-zinc-500' : 'text-zinc-700' }}">
                                                    {{ $s->workshop->name }}
                                                </div>
                                                <div class="text-[8px] font-black uppercase tracking-wider text-zinc-400 mt-0.5 truncate group-hover:text-zinc-600 transition-colors">
                                                    {{ $s->workshop->studio->name }}
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- LÓGICA MÓVIL (Y ESCRITORIO > 3): Botón agrupador --}}
                            <button onclick="openDayClasses('{{ $formattedDate }}', {{ $dayData }})" 
                                    class="mt-auto mb-auto mx-1 py-2 md:py-3 px-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-xl flex flex-col items-center justify-center gap-1 group transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $classCount <= 3 ? 'md:hidden' : '' }}">
                                
                                <span class="text-lg md:text-xl font-black text-indigo-600 leading-none group-hover:scale-110 transition-transform">{{ $classCount }}</span>
                                <span class="text-[9px] md:text-[10px] font-bold text-indigo-700 uppercase tracking-widest">{{ $classCount === 1 ? 'Clase' : 'Clases' }}</span>
                                
                            </button>
                        @endif
                    </div>
                @endfor

                @php
                    $remainingCells = 7 - (($empty + $days) % 7);
                    if ($remainingCells == 7) $remainingCells = 0;
                @endphp
                @for ($i = 0; $i < $remainingCells; $i++) <div class="bg-zinc-50/50 min-h-[120px] md:min-h-[140px]"></div> @endfor
            </div>
        </div>
    </div>

    {{-- MODAL CON LA LISTA DE CLASES DEL DÍA --}}
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
                
                link.className = cls.is_cancelled 
                    ? "block bg-zinc-50 border border-zinc-200 rounded-2xl p-3 md:p-4 opacity-75 grayscale-[30%] cursor-not-allowed"
                    : "block bg-white border border-zinc-200 hover:border-indigo-300 hover:shadow-md rounded-2xl p-3 md:p-4 transition-all duration-200 group";
                
                const titleClass = cls.is_cancelled ? "text-zinc-500 line-through" : "text-zinc-900 group-hover:text-indigo-600 transition-colors";
                const timeBadgeClass = cls.is_cancelled ? "bg-zinc-200 text-zinc-500 border-zinc-300" : "bg-indigo-50 text-indigo-600 border-indigo-100";
                
                const cancelledBadge = cls.is_cancelled 
                    ? `<span class="bg-rose-100 text-rose-700 text-[9px] font-black px-2 py-0.5 rounded border border-rose-200 uppercase tracking-widest ml-2">Cancelada</span>` 
                    : ``;

                link.innerHTML = `
                    <div class="flex justify-between items-center relative">
                        <div class="flex items-center gap-4 min-w-0">
                            <img src="${cls.image}" alt="cover" class="w-12 h-12 rounded-xl object-cover border border-zinc-100 shadow-sm shrink-0">
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold leading-tight truncate flex items-center ${titleClass}">
                                    ${cls.name}
                                </h4>
                                <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                    <img src="${cls.studio_avatar}" class="w-4 h-4 rounded-[5px] object-cover bg-zinc-900 shadow-sm border border-zinc-100 shrink-0">
                                    <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest truncate">${cls.studio}</p>
                                    ${cancelledBadge}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 ml-2">
                            <div class="font-black text-sm px-3 py-1.5 rounded-lg border ${timeBadgeClass}">
                                ${cls.time}
                            </div>
                            ${!cls.is_cancelled ? `<svg class="w-5 h-5 text-zinc-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>` : ''}
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
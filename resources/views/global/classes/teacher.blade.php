<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-24">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-zinc-900 tracking-tight">Mis clases a dictar</h1>
            <p class="mt-4 text-zinc-500 text-lg">Tu agenda unificada como Profesor/a.</p>
        </div>

        {{-- Controles de Navegación del Mes --}}
        @php
            $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
            $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
        @endphp

        <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-2xl shadow-sm border border-zinc-200">
            <a href="{{ route('global.classes.teacher', ['month' => $prevMonth]) }}" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm">← Anterior</a>
            <h2 class="text-xl md:text-2xl font-black text-zinc-800 capitalize">{{ $monthDate->translatedFormat('F Y') }}</h2>
            <a href="{{ route('global.classes.teacher', ['month' => $nextMonth]) }}" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm">Siguiente →</a>
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
                        <div class="flex justify-center mb-2">
                            <span class="text-sm font-bold flex items-center justify-center h-6 w-6 md:h-7 md:w-7 rounded-full {{ $isToday ? 'bg-zinc-900 text-white' : 'text-zinc-500' }}">{{ $day }}</span>
                        </div>
                        
                        @if($classCount > 0)
                            @php
                                // Preparamos la data para el JS inyectando la imagen
                                $dayData = $sessionsInDay->map(function($s) {
                                    $imageUrl = $s->workshop->image_path 
                                        ? asset('storage/' . $s->workshop->image_path) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=128';
                                        
                                    return [
                                        'id' => $s->id,
                                        'time' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                                        'name' => $s->workshop->name,
                                        'studio' => $s->workshop->studio->name,
                                        'url' => route('global.classes.teacher.session', $s->id),
                                        'image' => $imageUrl
                                    ];
                                })->toJson();
                                $formattedDate = \Carbon\Carbon::parse($cur)->translatedFormat('l d \d\e F');
                            @endphp

                            <button onclick="openDayClasses('{{ $formattedDate }}', {{ $dayData }})" 
                                    class="mt-auto mb-auto mx-1 py-3 px-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-xl flex flex-col items-center justify-center gap-1 group transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                
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
            
            // Limpiamos el contenedor
            listContainer.innerHTML = '';

            // Pintamos las clases (AHORA CON IMAGEN)
            classesArray.forEach(cls => {
                const link = document.createElement('a');
                link.href = cls.url;
                link.className = "block bg-white border border-zinc-200 hover:border-indigo-300 hover:shadow-md rounded-2xl p-3 md:p-4 transition-all duration-200 group";
                
                link.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <img src="${cls.image}" alt="cover" class="w-12 h-12 rounded-xl object-cover border border-zinc-100 shadow-sm">
                            <div>
                                <h4 class="text-sm font-bold text-zinc-900 group-hover:text-indigo-600 transition-colors leading-tight">${cls.name}</h4>
                                <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mt-1 line-clamp-1">${cls.studio}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-indigo-50 text-indigo-600 font-black text-sm px-3 py-1.5 rounded-lg border border-indigo-100">
                                ${cls.time}
                            </div>
                            <svg class="w-5 h-5 text-zinc-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
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
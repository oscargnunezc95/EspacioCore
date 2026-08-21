<x-app-layout>
    
    {{-- 1. NAVEGACIÓN DEL ESTUDIO --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO --}}
    <div class="pt-6 pb-24 w-full mx-auto relative">

        {{-- Cabecera Unificada --}}
        <div class="mt-2 mb-8 p-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex text-xs font-bold text-amber-600 mb-3 gap-2 items-center">
                <a href="{{ route('trainingmonth.index', ['subdomain' => request()->route('subdomain')]) }}" class="hover:text-stone-900 transition-colors">Planificación</a>
                <span>/</span>
                <span class="text-amber-600 capitalize">{{ $monthDate->translatedFormat('F Y') }}</span>
            </nav>

            <div class="flex flex-row items-center justify-between gap-4 w-full">
                <h1 class="text-2xl md:text-3xl font-black truncate flex-1 min-w-0">
                    Calendario Planificado
                </h1>

                <a href="{{ route('trainingmonth.index', ['subdomain' => request()->route('subdomain')]) }}" 
                   class="shrink-0 ml-auto bg-stone-100 text-stone-700 border border-stone-200 px-3 sm:px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-stone-200 hover:text-stone-900 focus:ring-2 focus:ring-stone-200 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2">
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="hidden sm:inline">Volver a los Ciclos</span>
                </a>
            </div>
        </div>
        
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="text-center mb-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-black text-amber-600 capitalize tracking-tight">{{ $monthDate->translatedFormat('F Y') }}</h2>
            <p class="mt-2 text-amber-600 font-medium">Revisa y administra las clases generadas para este ciclo.</p>
        </div>

        {{-- Calendario Maestro --}}
        <div class="px-2 sm:px-4 md:px-6 lg:px-8 w-full mx-auto">
            <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden relative">
                
                <div class="grid grid-cols-7 border-b border-stone-200 bg-stone-50">
                    @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                        <div class="py-3 text-center text-[10px] md:text-xs font-bold text-stone-500 uppercase tracking-wider border-r border-stone-200 last:border-0">{{ $d }}</div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 gap-px bg-stone-200">
                    @php
                        $start = $monthDate->copy()->startOfMonth();
                        $empty = $start->dayOfWeekIso - 1;
                        $days = $monthDate->daysInMonth;
                        $subdomain = request()->route('subdomain');
                    @endphp

                    @for ($i = 0; $i < $empty; $i++) <div class="bg-stone-50/50 min-h-[140px]"></div> @endfor

                    @for ($day = 1; $day <= $days; $day++)
                        @php
                            $cur = $monthDate->copy()->day($day)->toDateString();
                            $sessionsInDay = $sessionsByDate->get($cur, collect());
                            $isToday = \Carbon\Carbon::parse($cur)->isToday();
                            $classCount = $sessionsInDay->count();
                        @endphp
                        
                        <div class="bg-white min-h-[140px] p-1.5 md:p-2 flex flex-col relative transition-all {{ $isToday ? 'ring-2 ring-inset ring-red-600 bg-stone-50/30' : '' }}">
                            
                            <div class="flex justify-between items-start mb-1.5">
                                <span class="text-sm font-bold flex items-center justify-center h-6 w-6 md:h-7 md:w-7 rounded-full {{ $isToday ? 'bg-zinc-900 text-white' : 'text-stone-500' }}">{{ $day }}</span>
                            </div>
                            
                            @if($classCount > 0)
                                @php
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

                                {{-- VISTA INLINE (Escritorio <= 3 clases) --}}
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
                                                
                                                $imageUrl = $s->workshop->image_path 
                                                    ? asset('storage/' . $s->workshop->image_path) 
                                                    : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=128';
                                            @endphp

                                            {{-- Wrapper Relativo: Soporta elemento absoluto (Checkbox) --}}
                                            <div class="relative block bg-stone-50 border border-stone-100 rounded-lg hover:border-red-300 hover:shadow-sm transition-all duration-200 group {{ $isCancelled ? 'opacity-60 grayscale' : '' }}">
                                                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-lg {{ $isCancelled ? 'bg-stone-400' : $bgClass }}"></div>
                                                
                                                {{-- 🚀 Checkbox Esquina Superior Derecha --}}
                                                <input type="checkbox" value="{{ $s->id }}" 
                                                       class="session-checkbox absolute top-1.5 right-1.5 md:top-2 md:right-2 w-3.5 h-3.5 md:w-4 md:h-4 text-red-600 border-stone-300 rounded focus:ring-red-500 cursor-pointer transition-all z-20 shadow-sm" 
                                                       onchange="toggleSession({{ $s->id }}, this.checked)">

                                                {{-- Área del Enlace (El pr-7 evita que los textos choquen con el checkbox) --}}
                                                <a href="{{ $sessionUrl }}" class="block p-1.5 md:p-2 pl-2.5 pr-7 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <img src="{{ $imageUrl }}" class="w-6 h-6 md:w-7 md:h-7 rounded-md object-cover border border-stone-200 shrink-0">
                                                        
                                                        <div class="flex flex-col min-w-0 flex-1">
                                                            <span class="text-[10px] font-black leading-none {{ $isCancelled ? 'text-stone-500 line-through' : 'text-stone-900' }}">
                                                                {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                                            </span>
                                                            <span class="text-[9px] md:text-[10px] font-bold text-stone-500 truncate mt-0.5 group-hover:text-red-600 transition-colors">
                                                                {{ $s->workshop->name }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @if($isCancelled)
                                                        <div class="mt-1">
                                                            <span class="text-[7px] font-black uppercase tracking-widest text-rose-600 bg-rose-100 px-1 rounded inline-block">Anulada</span>
                                                        </div>
                                                    @endif
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- BOTÓN AGRUPADOR (Móviles o > 3 clases) --}}
                                <button onclick="openDayClasses('{{ $formattedDate }}', {{ $dayData }})" 
                                        class="mt-auto mb-auto mx-1 py-2 md:py-3 px-2 bg-red-50 hover:bg-red-100 border border-red-100 rounded-xl flex flex-col items-center justify-center gap-1 group transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 {{ $classCount <= 3 ? 'md:hidden' : '' }}">
                                    <span class="text-lg md:text-xl font-black text-red-600 leading-none group-hover:scale-110 transition-transform">{{ $classCount }}</span>
                                    <span class="text-[9px] md:text-[10px] font-bold text-red-700 uppercase tracking-widest">Clases</span>
                                </button>

                            @endif
                        </div>
                    @endfor

                    @php
                        $remainingCells = 7 - (($empty + $days) % 7);
                        if ($remainingCells == 7) $remainingCells = 0;
                    @endphp
                    @for ($i = 0; $i < $remainingCells; $i++) <div class="bg-stone-50/50 min-h-[140px]"></div> @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- BARRA FLOTANTE DE ACCIÓN EN BLOQUE --}}
    <div id="bulkDeleteBar" class="fixed bottom-0 left-0 right-0 bg-white border-t border-stone-200 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)] p-4 sm:p-5 flex justify-between items-center z-[50] transform translate-y-full transition-transform duration-300 ease-out">
        <div class="w-full flex flex-col sm:flex-row justify-between items-center px-4 sm:px-6 lg:px-8 gap-4 sm:gap-0">
            
            <div class="flex items-center gap-3">
                <span class="bg-red-100 text-red-700 font-black px-3 py-1 rounded-lg text-lg" id="selectedCount">0</span>
                <span class="font-bold text-stone-700">Clases seleccionadas</span>
            </div>
            
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <button type="button" onclick="clearSelection()" class="flex-1 sm:flex-none text-sm font-bold text-stone-500 hover:text-stone-800 transition-colors py-2 text-center">
                    Cancelar
                </button>
                <form action="{{ route('trainingmonth.bulk_destroy', ['subdomain' => request()->route('subdomain')]) }}" method="POST" id="bulkDeleteForm" class="flex-1 sm:flex-none">
                    @csrf
                    @method('DELETE')
                    <!-- Inputs ocultos inyectados por JS -->
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition-all active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Eliminar Selección
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- MODAL PARA DÍAS SATURADOS (>3 CLASES) --}}
    <div id="dayClassesModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" onclick="closeDayClasses()"></div>
        
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="dayModalCard">
            
            <div class="px-6 py-5 border-b border-stone-100 flex justify-between items-center bg-stone-50">
                <div>
                    <h3 class="text-xl font-black text-stone-900 tracking-tight">Clases del Día</h3>
                    <p id="modalDayDate" class="text-xs font-bold text-stone-500 capitalize mt-0.5">Fecha</p>
                </div>
                <button onclick="closeDayClasses()" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-200 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-4 md:p-6 overflow-y-auto flex-1 space-y-3" id="modalClassesList">
                {{-- Inyectado via JS --}}
            </div>
        </div>
    </div>

    <script>
        // ---------------------------------------------------
        // LÓGICA DE SELECCIÓN Y ELIMINACIÓN MASIVA
        // ---------------------------------------------------
        const selectedSessions = new Set();
        const bulkDeleteBar = document.getElementById('bulkDeleteBar');
        const selectedCountEl = document.getElementById('selectedCount');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        function toggleSession(id, isChecked) {
            if (isChecked) {
                selectedSessions.add(id);
            } else {
                selectedSessions.delete(id);
            }
            
            // Sincronizar todos los checkboxes del DOM que tengan este ID
            document.querySelectorAll(`.session-checkbox[value="${id}"]`).forEach(cb => {
                cb.checked = isChecked;
            });

            updateBulkUI();
        }

        function clearSelection() {
            selectedSessions.clear();
            document.querySelectorAll('.session-checkbox').forEach(cb => cb.checked = false);
            updateBulkUI();
        }

        function updateBulkUI() {
            const count = selectedSessions.size;
            selectedCountEl.innerText = count;
            
            if (count > 0) {
                bulkDeleteBar.classList.remove('translate-y-full');
            } else {
                bulkDeleteBar.classList.add('translate-y-full');
            }

            bulkDeleteForm.querySelectorAll('.hidden-session-id').forEach(el => el.remove());
            selectedSessions.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'session_ids[]';
                input.value = id;
                input.className = 'hidden-session-id';
                bulkDeleteForm.appendChild(input);
            });
        }

        // ---------------------------------------------------
        // LÓGICA DEL MODAL
        // ---------------------------------------------------
        const dayModal = document.getElementById('dayClassesModal');
        const dayModalCard = document.getElementById('dayModalCard');
        const listContainer = document.getElementById('modalClassesList');

        function openDayClasses(dateStr, classesArray) {
            document.getElementById('modalDayDate').innerText = dateStr;
            listContainer.innerHTML = '';

            classesArray.forEach(cls => {
                const wrapper = document.createElement('div');
                
                wrapper.className = cls.is_cancelled 
                    ? "relative block bg-stone-50 border border-stone-200 rounded-2xl opacity-70 grayscale transition-all group"
                    : "relative block bg-white border border-stone-200 hover:border-red-300 hover:shadow-md rounded-2xl transition-all duration-200 group";
                
                const titleClass = cls.is_cancelled ? "text-stone-500 line-through" : "text-stone-900 group-hover:text-red-600 transition-colors";
                const badgeHtml = cls.is_cancelled 
                    ? `<span class="bg-rose-100 text-rose-700 text-[9px] font-black px-2 py-0.5 rounded border border-rose-200 uppercase tracking-widest inline-block">Anulada</span>` 
                    : '';
                
                const isChecked = selectedSessions.has(cls.id) ? 'checked' : '';

                // 🚀 Estructura de Modal rediseñada: Checkbox Top-Right
                wrapper.innerHTML = `
                    <input type="checkbox" value="${cls.id}" ${isChecked} 
                           class="session-checkbox absolute top-3.5 right-3.5 w-5 h-5 text-red-600 border-stone-300 rounded focus:ring-red-500 cursor-pointer transition-all z-20 shadow-sm" 
                           onchange="toggleSession(${cls.id}, this.checked)">

                    <a href="${cls.url}" class="block p-3 pr-11 min-w-0">
                        <div class="flex items-center justify-between gap-3 min-w-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="${cls.image}" class="w-10 h-10 rounded-xl object-cover border border-stone-100 shadow-sm shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold leading-tight truncate ${titleClass}">${cls.name}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[11px] font-black text-stone-500">${cls.time}</span>
                                        ${badgeHtml}
                                    </div>
                                </div>
                            </div>
                            <svg class="w-5 h-5 shrink-0 text-stone-300 group-hover:text-red-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </a>
                `;
                listContainer.appendChild(wrapper);
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
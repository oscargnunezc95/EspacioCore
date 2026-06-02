<x-app-layout>
    <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        
        <div class="text-center mb-10 md:mb-14">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Mi Horario</h1>
            <p class="mt-3 text-zinc-500 font-medium text-base md:text-lg">Tu calendario unificado de clases en todos los estudios.</p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Controles de Navegación del Mes --}}
        @php
            $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
            $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
        @endphp

        <div class="flex justify-between items-center mb-6 bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-zinc-200">
            
            {{-- Botón Anterior --}}
            <a href="{{ route('global.classes.student', ['month' => $prevMonth]) }}" 
               class="px-3 sm:px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm flex items-center gap-1.5 sm:gap-2 active:scale-95">
                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                <span class="hidden sm:inline">Anterior</span>
            </a>
            
            {{-- Título del Mes --}}
            <h2 class="text-lg sm:text-xl md:text-2xl font-black text-zinc-800 capitalize truncate px-2 text-center">
                {{ $monthDate->translatedFormat('F Y') }}
            </h2>
            
            {{-- Botón Siguiente --}}
            <a href="{{ route('global.classes.student', ['month' => $nextMonth]) }}" 
               class="px-3 sm:px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm flex items-center gap-1.5 sm:gap-2 active:scale-95">
                <span class="hidden sm:inline">Siguiente</span>
                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </a>
            
        </div>

        @if($sessionsByDate->isEmpty() && $monthDate->isCurrentMonth())
            <div class="bg-white rounded-3xl border border-zinc-200 py-24 px-6 text-center shadow-sm mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-zinc-50 border border-zinc-100 mb-6">
                    <svg class="w-10 h-10 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900 mb-2">Tu Horario está vacío</h3>
                <p class="text-zinc-500 max-w-md mx-auto text-sm leading-relaxed mb-8">
                    Aún no estás inscrita/o en ninguna clase para este mes. Explora el catálogo para añadir nuevas sesiones.
                </p>
                <a href="{{ route('explore') }}" class="inline-flex items-center justify-center px-6 py-3 font-bold rounded-xl text-white bg-zinc-900 hover:bg-zinc-800 transition-all duration-200 shadow-sm active:scale-95">
                    Explorar Catálogo
                </a>
            </div>
        @endif

        {{-- Calendario Maestro --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden relative">
            
            {{-- Sombra lateral para indicar al usuario que puede hacer scroll en móviles --}}
            <div class="absolute top-0 right-0 bottom-0 w-8 bg-gradient-to-l from-white/90 to-transparent pointer-events-none md:hidden z-10"></div>

            {{-- Contenedor con Scroll Horizontal (La magia ocurre aquí) --}}
            <div class="overflow-x-auto custom-scrollbar">
                
                {{-- Forzamos un ancho mínimo. En PC ocupará el 100%, en móvil medirá 1300px y generará scroll --}}
                <div class="min-w-[1300px] w-full">
                    
                    <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50">
                        @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                            <div class="py-3 text-center text-[11px] md:text-xs font-bold text-zinc-500 uppercase tracking-wider border-r border-zinc-200 last:border-0">{{ $d }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-7 gap-px bg-zinc-200">
                        @php
                            $start = $monthDate->copy()->startOfMonth();
                            $empty = $start->dayOfWeekIso - 1;
                            $days = $monthDate->daysInMonth;
                        @endphp

                        @for ($i = 0; $i < $empty; $i++) <div class="bg-zinc-50/50 min-h-[140px]"></div> @endfor

                        @for ($day = 1; $day <= $days; $day++)
                            @php
                                $cur = $monthDate->copy()->day($day)->toDateString();
                                $sessionsInDay = $sessionsByDate->get($cur, collect());
                                $isToday = \Carbon\Carbon::parse($cur)->isToday();
                            @endphp
                            
                            <div class="bg-white min-h-[140px] p-2 transition {{ $isToday ? 'ring-2 ring-inset ring-zinc-900 bg-zinc-50/30' : '' }}">
                                <span class="text-sm font-bold flex items-center justify-center h-7 w-7 rounded-full mb-2 {{ $isToday ? 'bg-zinc-900 text-white' : 'text-zinc-500' }}">{{ $day }}</span>
                                
                                <div class="space-y-2">
                                    @foreach($sessionsInDay as $session)
                                        @php 
                                            $c = $session->workshop->color ?? 'indigo'; 
                                            $bgClass = match($c) {
                                                'emerald' => 'bg-emerald-500', 'rose' => 'bg-rose-500', 'purple' => 'bg-purple-500',
                                                'amber' => 'bg-amber-500', 'indigo' => 'bg-indigo-500', 'teal' => 'bg-teal-500',
                                                'cyan' => 'bg-cyan-500', 'fuchsia' => 'bg-fuchsia-500', 'slate' => 'bg-slate-500',
                                                default => 'bg-indigo-500',
                                            };
                                            
                                            $imageUrl = $session->workshop->image_path 
                                                ? asset('storage/' . $session->workshop->image_path) 
                                                : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=4f46e5&background=e0e7ff&size=128';

                                            $studioLogo = $session->workshop->studio->icon_path ?? $session->workshop->studio->logo_path ?? null;
                                            $studioImageUrl = $studioLogo 
                                                ? asset('storage/' . $studioLogo) 
                                                : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->studio->name).'&color=ffffff&background=18181b&size=128';

                                            $isPaid = $session->is_paid ?? false;
                                            $isCancelled = $session->is_cancelled ?? ($session->status === 'cancelled') ?? false;
                                            $hasFamily = !empty($session->family_student_ids);
                                            
                                            if ($isCancelled) {
                                                $cardClasses = 'border-zinc-200 bg-zinc-50 opacity-75 grayscale';
                                            } else {
                                                $cardClasses = $isPaid ? 'border-zinc-200 hover:border-zinc-400' : 'border-rose-200 bg-rose-50/30 hover:border-rose-400';
                                            }
                                        @endphp
                                        
                                        <button onclick="openClassDetails(this)"
                                                data-title="{{ $session->workshop->name }}"
                                                data-studio="{{ $session->workshop->studio->name }}"
                                                data-studio-image="{{ $studioImageUrl }}"
                                                data-teacher="{{ $session->workshop->teacher ? $session->workshop->teacher->first_name . ' ' . $session->workshop->teacher->last_name : 'Por asignar' }}"
                                                data-time="{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}"
                                                data-date="{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F') }}"
                                                data-subdomain="{{ $session->workshop->studio->subdomain }}"
                                                data-image="{{ $imageUrl }}" 
                                                data-address="{{ $session->workshop->studio->address ?? 'Dirección no especificada' }}"
                                                data-status="{{ $isPaid ? 'paid' : 'unpaid' }}"
                                                data-cancelled="{{ $isCancelled ? 'true' : 'false' }}"
                                                data-available="{{ $session->available_spots ?? $session->workshop->max_students ?? '?' }}"
                                                data-max="{{ $session->max_spots ?? $session->workshop->max_students ?? '?' }}"
                                                data-pending="{{ $session->pending_count ?? 0 }}"
                                                class="relative w-full text-left p-2 pl-3 bg-white border {{ $cardClasses }} rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-zinc-900 active:scale-95 flex items-center gap-2.5">
                                            
                                            <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isCancelled ? 'bg-zinc-400' : $bgClass }}"></div>

                                            {{-- Le quité el "hidden sm:block" a la imagen para que siempre se vea bien, total ahora tenemos espacio --}}
                                            <img src="{{ $imageUrl }}" class="w-8 h-8 rounded-lg object-cover shadow-sm border border-zinc-100 shrink-0">

                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    <div class="text-[10px] font-extrabold leading-none {{ $isCancelled ? 'text-zinc-500 line-through' : 'text-zinc-900' }}">
                                                        {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}
                                                    </div>
                                                    
                                                    @if($isCancelled)
                                                        <span class="bg-rose-100 text-rose-700 text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest shrink-0">Anulada</span>
                                                    @elseif($isPaid)
                                                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Pagada"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                    @else
                                                        <span class="flex h-2 w-2 relative shrink-0" title="Pendiente de pago">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] font-bold mt-0.5 truncate {{ $isCancelled ? 'text-zinc-500' : 'text-zinc-600' }}">
                                                    {{ $session->workshop->name }}
                                                </div>
                                                <div class="text-[8px] font-black uppercase tracking-wider text-zinc-400 mt-0.5 truncate group-hover:text-zinc-600 transition-colors flex items-center gap-1">
                                                    {{ $session->workshop->studio->name }}
                                                    @if($hasFamily)
                                                        <span class="text-[7px] bg-violet-100 text-violet-600 px-1 py-0 rounded font-black tracking-wider shrink-0">FAMILIAR</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
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
        </div>
    </div>

    {{-- MODAL DETALLES DE CLASE --}}
    <div id="classDetailsModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" onclick="closeClassDetails()"></div>
        
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="modalCard">
            
            <div class="h-32 sm:h-48 w-full bg-zinc-200 relative">
                <img id="modalImage" src="" alt="Cover" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/60 to-transparent"></div>
                <button onclick="closeClassDetails()" class="absolute top-4 right-4 p-2 text-zinc-700 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full transition-colors focus:outline-none shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                
                <div class="absolute bottom-4 left-6 flex items-center gap-3">
                    <img id="modalStudioImage" src="" alt="Studio Icon" class="w-8 h-8 rounded-lg object-cover ring-2 ring-white/30 shadow-sm bg-zinc-900">
                    <span id="modalStudio" class="px-2.5 py-1 bg-white/20 backdrop-blur-md text-white border border-white/30 text-[10px] font-black rounded-lg tracking-widest uppercase shadow-sm">Estudio</span>
                </div>
            </div>

            {{-- BANNER DE ESTADO (Pago o Cancelación) --}}
            <div id="modalStatusBanner" class="px-6 py-3 border-b flex items-center gap-2 font-bold text-sm">
                </div>

            <div class="p-6 md:p-8 overflow-y-auto flex-1">
                <div class="mb-6">
                    <h3 id="modalTitle" class="text-2xl font-black text-zinc-900 leading-tight">Clase</h3>
                </div>

                <div class="space-y-3 mb-8">
                    {{-- CUPOS DISPONIBLES --}}
                    <div class="flex items-center gap-3 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-violet-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Cupos</p>
                            <p id="modalCupos" class="text-sm font-bold text-zinc-900">—</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p id="modalDate" class="text-sm font-bold text-zinc-900 capitalize">Fecha</p>
                            <p id="modalTime" class="text-xs font-medium text-zinc-500">Hora</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-emerald-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Profesor/a</p>
                            <p id="modalTeacher" class="text-sm font-bold text-zinc-900">Nombre</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-rose-500 mt-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Ubicación</p>
                            <p id="modalAddress" class="text-sm font-bold text-zinc-900 mb-2 leading-tight">Dirección del Estudio</p>
                            
                            <a href="#" id="modalMapLink" target="_blank" class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                Cómo llegar en Google Maps
                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="#" id="modalLink" class="block w-full bg-zinc-900 text-white font-bold py-3.5 rounded-xl text-center shadow-md shadow-zinc-900/20 hover:bg-zinc-800 transition-all duration-200 active:scale-95 text-sm">
                    Ir al Dashboard del Estudio
                </a>
            </div>
        </div>
    </div>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #d4d4d8; border-radius: 20px; }
    </style>

    <script>
        const modal = document.getElementById('classDetailsModal');
        const modalCard = document.getElementById('modalCard');

        function openClassDetails(button) {
            document.getElementById('modalTitle').innerText = button.getAttribute('data-title');
            document.getElementById('modalStudio').innerText = button.getAttribute('data-studio');
            document.getElementById('modalTeacher').innerText = button.getAttribute('data-teacher');
            document.getElementById('modalDate').innerText = button.getAttribute('data-date');
            document.getElementById('modalTime').innerText = button.getAttribute('data-time') + ' hrs';
            
            document.getElementById('modalImage').src = button.getAttribute('data-image');
            document.getElementById('modalStudioImage').src = button.getAttribute('data-studio-image');
            
            const address = button.getAttribute('data-address');
            document.getElementById('modalAddress').innerText = address;
            document.getElementById('modalMapLink').href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
            
            const subdomain = button.getAttribute('data-subdomain');
            const baseDomain = window.location.hostname.replace('www.', '');
            document.getElementById('modalLink').href = `${window.location.protocol}//${subdomain}.${baseDomain}/dashboard`;

            // LÓGICA DEL ESTADO DE PAGO / CANCELACIÓN EN EL MODAL
            const statusBanner = document.getElementById('modalStatusBanner');
            const isPaid = button.getAttribute('data-status') === 'paid';
            const isCancelled = button.getAttribute('data-cancelled') === 'true';

            // --- CUPOS DISPONIBLES ---
            const available = parseInt(button.getAttribute('data-available')) || 0;
            const maxSpots = parseInt(button.getAttribute('data-max')) || 0;
            const pending = parseInt(button.getAttribute('data-pending')) || 0;
            const cuposEl = document.getElementById('modalCupos');
            if (maxSpots > 0) {
                const percentFilled = maxSpots > 0 ? Math.round(( (maxSpots - available) / maxSpots) * 100) : 0;
                let cuposHTML = `<span class="font-black ${available <= 0 ? 'text-rose-600' : available <= 3 ? 'text-amber-600' : 'text-emerald-600'}">${available} de ${maxSpots} cupos disponibles</span>`;
                if (pending > 0) {
                    cuposHTML += ` <span class="text-zinc-400 font-medium">· ${pending} ${pending === 1 ? 'interesado' : 'interesados'} sin pagar</span>`;
                }
                cuposEl.innerHTML = cuposHTML;
            } else {
                cuposEl.innerText = 'Sin límite de cupos';
            }

            if (isCancelled) {
                statusBanner.className = 'px-6 py-3 border-b flex items-center gap-2 font-bold text-sm bg-rose-100 border-rose-200 text-rose-800';
                statusBanner.innerHTML = `
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Esta clase ha sido cancelada por el estudio.
                `;
            } else if (isPaid) {
                statusBanner.className = 'px-6 py-3 border-b flex items-center gap-2 font-bold text-sm bg-emerald-50 border-emerald-100 text-emerald-700';
                statusBanner.innerHTML = `
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Reserva confirmada y pagada
                `;
            } else {
                statusBanner.className = 'px-6 py-3 border-b flex items-center gap-2 font-bold text-sm bg-rose-50 border-rose-100 text-rose-700';
                statusBanner.innerHTML = `
                    <span class="flex h-3 w-3 relative shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                    Pago pendiente
                `;
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalCard.classList.remove('scale-95', 'opacity-0');
                modalCard.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeClassDetails() {
            modalCard.classList.remove('scale-100', 'opacity-100');
            modalCard.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape" && !modal.classList.contains('hidden')) {
                closeClassDetails();
            }
        });
    </script>
</x-app-layout>
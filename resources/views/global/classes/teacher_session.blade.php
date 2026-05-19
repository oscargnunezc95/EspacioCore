<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-24">
        
        {{-- Título y subtítulo (Ahora visible en todos los tamaños) --}}
        <div class="text-center mb-8 md:mb-12">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Pasar Lista</h1>
            <p class="mt-2 md:mt-3 text-zinc-500 text-base md:text-lg">Gestiona la asistencia de tus estudiantes.</p>
        </div>

        {{-- Cabecera Unificada (Card Header) --}}
        <div class="mt-2 mb-8 p-1">
            {{-- Breadcrumbs con navegación funcional --}}
            <nav class="flex text-xs font-bold text-zinc-500 mb-3 gap-2 items-center">
                <a href="{{ route('global.classes.teacher') }}" class="hover:text-zinc-900 transition-colors">Mi Agenda</a>
                <span>/</span>
                <span class="text-zinc-900">Lista de Asistencia</span>
            </nav>

            {{-- Contenedor del Título y el Botón "Volver" --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">
                <h1 class="text-2xl md:text-3xl font-black text-zinc-900 truncate flex-1 min-w-0">
                    Asistencia de Clase
                </h1>
                
                {{-- Botón Responsivo (Volver) --}}
                <a href="{{ route('global.classes.teacher') }}" 
                   class="shrink-0 ml-auto bg-zinc-100 text-zinc-700 border border-zinc-200 px-3 sm:px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-zinc-200 hover:text-zinc-900 focus:ring-2 focus:ring-zinc-200 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2">
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span class="hidden sm:inline">Volver a Mi Agenda</span>
                </a>
            </div>
        </div>

        {{-- Alerta Global si está cancelada --}}
        @if($session->is_cancelled)
            <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-200 flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Esta clase ha sido suspendida por la administración del estudio. No se puede pasar lista.
            </div>
        @endif

        {{-- CONTENEDOR MAESTRO UNIFICADO --}}
        <div class="bg-white rounded-2xl md:rounded-3xl shadow-lg border border-zinc-200 overflow-hidden mb-12 {{ $session->is_cancelled ? 'opacity-75 grayscale-[20%]' : '' }}">
            
            {{-- A. BANNER SUPERIOR DE LA CLASE --}}
            @php
                $imageUrl = $session->workshop->image_path 
                    ? asset('storage/' . $session->workshop->image_path) 
                    : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=4f46e5&background=e0e7ff&size=512';
                
                $studioLogo = $session->workshop->studio->icon_path ?? $session->workshop->studio->logo_path ?? null;
                $studioAvatar = $studioLogo 
                    ? asset('storage/' . $studioLogo) 
                    : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->studio->name).'&color=ffffff&background=18181b&size=128';
            @endphp
            
            <div class="relative min-h-[200px] md:min-h-[240px] w-full bg-zinc-900 flex flex-col justify-end">
                <img src="{{ $imageUrl }}" alt="Cover" class="absolute inset-0 w-full h-full object-cover z-0 opacity-80">
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/90 via-zinc-900/50 to-transparent z-0"></div>

                <div class="relative z-10 p-5 sm:p-6 md:p-8 w-full mt-auto">
                    
                    {{-- Insignia del Estudio (En flujo relativo) --}}
                    <div class="flex items-center gap-2 mb-3">
                        <img src="{{ $studioAvatar }}" alt="Studio" class="w-6 h-6 md:w-8 md:h-8 rounded-md md:rounded-lg object-cover ring-2 ring-white/30 shadow-sm bg-zinc-900">
                        <span class="px-2.5 py-1 bg-white/20 backdrop-blur-md text-white border border-white/20 text-[9px] md:text-[10px] font-black rounded-lg tracking-widest uppercase shadow-sm">
                            {{ $session->workshop->studio->name }}
                        </span>
                    </div>

                    <h2 class="text-2xl md:text-4xl font-black text-white tracking-tight mb-4 leading-tight">{{ $session->workshop->name }}</h2>
                    
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 md:gap-4">
                        <div class="flex items-center gap-2 text-zinc-800 bg-white/95 px-3 md:px-4 py-2 md:py-2.5 rounded-xl border border-zinc-100/70 inline-flex shadow-xl backdrop-blur-md">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="capitalize text-xs md:text-sm font-bold">{{ $parsedMonth->translatedFormat('l d \d\e F') }}</span>
                            <span class="text-zinc-300 mx-1">|</span>
                            <span class="font-black text-zinc-900 text-xs md:text-sm">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- B. BARRA DE HERRAMIENTAS DE LA LISTA --}}
            <div class="p-4 md:p-6 bg-zinc-50 border-b border-zinc-200">
                <div class="flex flex-row items-center justify-between gap-3 md:gap-4 w-full">
                    
                    {{-- Buscador --}}
                    <div class="flex-1 relative min-w-0">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" id="searchStudent" onkeyup="filterStudents()" placeholder="Buscar alumna/o..." 
                               class="w-full pl-9 pr-3 py-2 md:py-2.5 rounded-xl border border-zinc-300 bg-white text-zinc-900 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none shadow-sm {{ $session->is_cancelled ? 'pointer-events-none bg-zinc-100' : '' }}">
                    </div>

                    {{-- Indicadores --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @if($session->is_cancelled)
                            <span class="bg-rose-100 text-rose-700 text-[10px] md:text-xs font-black px-2 md:px-3 py-2.5 md:py-2 rounded-xl uppercase tracking-wider border border-rose-200 flex items-center gap-1.5 shadow-sm" title="Clase Cancelada">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <span class="hidden sm:inline">Cancelada</span>
                            </span>
                        @endif
                        
                        <div class="flex items-center gap-1.5 md:gap-2 bg-emerald-50 px-3 md:px-4 py-2 md:py-2.5 rounded-xl border border-emerald-200 text-emerald-800 shadow-sm" title="Alumnas inscritas">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="font-bold text-sm hidden sm:inline">Inscritas:</span>
                            <span class="font-black text-emerald-600 text-sm leading-none mt-0.5">{{ $students->count() }} @if($session->workshop->max_students) <span class="text-emerald-400 font-medium text-[10px] md:text-xs">/ {{ $session->workshop->max_students }}</span> @endif</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- C. LISTA DE ALUMNAS --}}
            <ul class="divide-y divide-zinc-100" id="studentsList">
                @forelse($students as $student)
                    @php
                        $isPresent = $session->attendances->contains('student_id', $student->id);
                        $toggleUrl = route('attendance.toggle', [
                            'subdomain' => $session->workshop->studio->subdomain, 
                            'session' => $session->id, 
                            'student' => $student->id
                        ]);
                        
                        $studentAvatar = 'https://ui-avatars.com/api/?name='.urlencode($student->name).'&color=4f46e5&background=e0e7ff&bold=true';
                    @endphp
                    <li class="student-item p-4 md:p-6 flex items-center gap-4 transition-colors hover:bg-zinc-50/80 {{ $session->is_cancelled ? 'pointer-events-none' : '' }}">
                        
                        {{-- Switch de Asistencia --}}
                        <button onclick="toggleAttendance('{{ $toggleUrl }}', this)" 
                                class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 {{ $isPresent ? 'bg-emerald-500' : 'bg-zinc-200' }}" 
                                role="switch" aria-checked="{{ $isPresent ? 'true' : 'false' }}" {{ $session->is_cancelled ? 'disabled' : '' }}>
                            <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isPresent ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                        
                        {{-- Nombre, Avatar y Documento --}}
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <img src="{{ $studentAvatar }}" class="w-9 h-9 rounded-full border border-indigo-100 shadow-sm shrink-0" alt="Avatar">
                            <div class="min-w-0 flex-1">
                                <p class="student-name font-bold text-zinc-900 text-sm md:text-base leading-tight truncate">{{ $student->name }}</p>
                                <p class="text-[10px] md:text-[11px] font-bold text-zinc-400 mt-0.5 uppercase tracking-wider truncate">
                                    {{ $student->formatted_national_id ? 'DoC: ' . $student->formatted_national_id : 'Sin Documento' }}
                                </p>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-10 text-center text-sm font-medium text-zinc-500 flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-zinc-100 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        Nadie se ha inscrito a esta clase todavía.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>

{{-- Lógica JS para el Buscador y Asistencia --}}
<script>
    function filterStudents() {
        let input = document.getElementById('searchStudent').value.toLowerCase();
        let items = document.querySelectorAll('.student-item');
        
        items.forEach(item => {
            let textContent = item.querySelector('.student-name').innerText.toLowerCase();
            item.style.display = textContent.includes(input) ? 'flex' : 'none';
        });
    }

    function toggleAttendance(url, btn) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const present = btn.getAttribute('aria-checked') === 'true';
        
        btn.classList.replace(present ? 'bg-emerald-500' : 'bg-zinc-200', present ? 'bg-zinc-200' : 'bg-emerald-500');
        btn.querySelector('span').classList.replace(present ? 'translate-x-5' : 'translate-x-0', present ? 'translate-x-0' : 'translate-x-5');
        btn.setAttribute('aria-checked', !present);

        fetch(url, {
            method: 'POST', 
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': token, 
                'Accept': 'application/json' 
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en el servidor');
        })
        .catch(error => {
            console.error('Error al guardar asistencia:', error);
            const wasPresent = !present;
            btn.classList.replace(wasPresent ? 'bg-emerald-500' : 'bg-zinc-200', wasPresent ? 'bg-zinc-200' : 'bg-emerald-500');
            btn.querySelector('span').classList.replace(wasPresent ? 'translate-x-5' : 'translate-x-0', wasPresent ? 'translate-x-0' : 'translate-x-5');
            btn.setAttribute('aria-checked', wasPresent);
            alert("Hubo un problema de conexión. No se pudo guardar la asistencia.");
        });
    }
</script>
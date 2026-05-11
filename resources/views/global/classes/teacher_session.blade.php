<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-24">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-zinc-900 tracking-tight">Mis clases a dictar</h1>
            <p class="mt-4 text-zinc-500 text-lg">Tu agenda unificada como Profesor/a.</p>
        </div>
        
        {{-- Hero Banner con Imagen del Taller --}}
        @php
            // 1. Imagen del Taller (Fondo Principal)
            $imageUrl = $session->workshop->image_path 
                ? asset('storage/' . $session->workshop->image_path) 
                : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=4f46e5&background=e0e7ff&size=512';
            
            // 2. Avatar del Estudio (Miniatura)
            $studioLogo = $session->workshop->studio->icon_path ?? $session->workshop->studio->logo_path ?? null;
            $studioAvatar = $studioLogo 
                ? asset('storage/' . $studioLogo) 
                : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->studio->name).'&color=ffffff&background=18181b&size=128';
        @endphp
        
        <div class="relative w-full h-48 md:h-64 rounded-3xl overflow-hidden mb-8 shadow-md border border-zinc-200">
            <img src="{{ $imageUrl }}" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/90 via-zinc-900/40 to-transparent"></div>
            
            <div class="absolute bottom-0 left-0 p-6 md:p-8 w-full flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    {{-- Inyección del Avatar y Título del Estudio para coherencia visual --}}
                    <div class="flex items-center gap-3 mb-3">
                        <img src="{{ $studioAvatar }}" alt="Studio Icon" class="w-8 h-8 rounded-lg object-cover ring-2 ring-white/30 shadow-sm bg-zinc-900">
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white border border-white/20 text-xs font-black rounded-lg tracking-widest uppercase shadow-sm">
                            {{ $session->workshop->studio->name }}
                        </span>
                    </div>
                    
                    <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight">{{ $session->workshop->name }}</h1>
                </div>
            </div>
        </div>
        
        {{-- Migajas de pan --}}
        <div class="mb-8">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('global.classes.teacher') }}" class="text-sm font-bold text-zinc-400 hover:text-zinc-900 transition-colors">Volver a Mi Agenda</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-sm font-bold text-zinc-900">Lista de estudiantes</span>
            </div>
        </div>

        <div class="space-y-10 py-4">
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm">
                <div class="flex items-center gap-3 text-zinc-700 font-medium">
                    <div class="bg-indigo-50 border border-indigo-100 p-2.5 rounded-xl text-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-zinc-900 capitalize">{{ $parsedMonth->translatedFormat('l d \d\e F') }}</p>
                        <p class="text-xs text-zinc-500 font-medium mt-0.5">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs • Presencial</p>
                    </div>
                </div>
                
                <div class="text-right">
                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-1">Inscritas</p>
                    <p class="text-2xl font-black text-zinc-900">{{ $students->count() }} @if($session->workshop->max_students) <span class="text-zinc-400 text-sm">/ {{ $session->workshop->max_students }}</span> @endif</p>
                </div>
            </div>

            @if($session->is_cancelled)
                <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-200 flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Esta clase ha sido suspendida por la administración del estudio.
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden {{ $session->is_cancelled ? 'opacity-60 pointer-events-none grayscale-[20%]' : '' }}">
                <ul class="divide-y divide-zinc-100">
                    @forelse($students as $student)
                        @php
                            $isPresent = $session->attendances->contains('student_id', $student->id);
                            $toggleUrl = route('attendance.toggle', [
                                'subdomain' => $session->workshop->studio->subdomain, 
                                'session' => $session->id, 
                                'student' => $student->id
                            ]);
                            
                            // Avatar del Alumno
                            $studentAvatar = 'https://ui-avatars.com/api/?name='.urlencode($student->name).'&color=4f46e5&background=e0e7ff&bold=true';
                        @endphp
                        <li class="p-4 md:p-6 flex items-center gap-5 transition-colors hover:bg-zinc-50/80">
                            
                            {{-- Switch de Asistencia --}}
                            <button onclick="toggleAttendance('{{ $toggleUrl }}', this)" 
                                    class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 {{ $isPresent ? 'bg-emerald-500' : 'bg-zinc-200' }}" 
                                    role="switch" aria-checked="{{ $isPresent ? 'true' : 'false' }}">
                                <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isPresent ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                            
                            <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $studentAvatar }}" class="w-8 h-8 rounded-full border border-indigo-100 shadow-sm" alt="Avatar">
                                    <p class="font-bold text-zinc-900 text-sm md:text-base">{{ $student->name }}</p>
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
    </div>

    {{-- API Fetch Optimizado --}}
    <script>
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
</x-app-layout>
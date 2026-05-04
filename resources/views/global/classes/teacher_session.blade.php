<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Encabezado Gigante (Igual a la vista anterior) --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-zinc-900 tracking-tight">{{ $session->workshop->name }}</h1>
            <p class="mt-3 text-zinc-500 font-light text-lg">Pasa la asistencia de tus alumnas en esta sesión.</p>
        </div>
        
        {{-- Migajas de pan con los estilos correctos alineados a la izquierda --}}
        <div class="mb-8">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('global.classes.teacher') }}" class="text-sm font-bold text-zinc-400 hover:text-zinc-900 transition-colors">Mis clases</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <a href="{{ route('global.classes.teacher.calendar', $parsedMonth->format('Y-m')) }}" class="text-sm font-bold text-zinc-400 hover:text-zinc-900 transition-colors capitalize">{{ $parsedMonth->translatedFormat('F Y') }}</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-sm font-bold text-zinc-900">Lista de estudiantes</span>
            </div>
        </div>

        <div class="space-y-10 py-4">
            <!-- Info de la Clase -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm">
                <div class="flex items-center gap-3 text-zinc-700 font-medium">
                    <div class="bg-zinc-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-zinc-900 capitalize">{{ $parsedMonth->translatedFormat('l d \d\e F') }}</p>
                        <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs &bull; {{ $session->workshop->studio->name }}</p>
                    </div>
                </div>
                
                <div class="text-right">
                    <p class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Inscritas</p>
                    <p class="text-xl font-black text-zinc-900">{{ $students->count() }} @if($session->workshop->max_students) <span class="text-zinc-400 text-sm">/ {{ $session->workshop->max_students }}</span> @endif</p>
                </div>
            </div>

            @if($session->is_cancelled)
                <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Esta clase ha sido suspendida por la administración.
                </div>
            @endif

            <!-- LISTA DE ASISTENCIA -->
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden {{ $session->is_cancelled ? 'opacity-60 pointer-events-none' : '' }}">
                <ul class="divide-y divide-zinc-100">
                    @forelse($students as $student)
                        @php
                            $isPresent = $session->attendances->contains('student_id', $student->id);
                        @endphp
                        <li class="p-4 md:p-6 flex items-center gap-5 transition-colors hover:bg-zinc-50/80">
                            <!-- Toggle Switch -->
                            <button onclick="toggleAttendance({{ $session->id }}, {{ $student->id }}, this)" 
                                    class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 {{ $isPresent ? 'bg-emerald-500' : 'bg-zinc-200' }}" 
                                    role="switch" aria-checked="{{ $isPresent ? 'true' : 'false' }}">
                                <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isPresent ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                            
                            <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between gap-2">
                                <p class="font-bold text-zinc-900 text-sm md:text-base">{{ $student->name }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="p-8 text-center text-sm font-medium text-zinc-500">Nadie se ha inscrito a esta clase todavía.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- API Fetch --}}
    <script>
        function toggleAttendance(sid, stid, btn) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const present = btn.getAttribute('aria-checked') === 'true';
            
            btn.classList.replace(present ? 'bg-emerald-500' : 'bg-zinc-200', present ? 'bg-zinc-200' : 'bg-emerald-500');
            btn.querySelector('span').classList.replace(present ? 'translate-x-5' : 'translate-x-0', present ? 'translate-x-0' : 'translate-x-5');
            btn.setAttribute('aria-checked', !present);

            // Reutilizamos la ruta del subdominio mediante la URL dinámica del estudio
            const subdomain = "{{ $session->workshop->studio->subdomain }}";
            fetch(`/${subdomain}/sessions/${sid}/attendance/${stid}`, {
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            });
        }
    </script>
</x-app-layout>
<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-zinc-900 tracking-tight">Mis Clases como Profesor</h1>
            <p class="mt-3 text-zinc-500 font-light text-lg">Revisa tu horario unificado en todos los estudios de la red en los que eres profesor.</p>
        </div>
        <div class="max-w-7xl mx-auto px-4  sm:px-6 lg:px-8 mt-8">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('global.classes.teacher') }}" class="text-sm font-bold text-zinc-400 hover:text-zinc-900 transition-colors">Meses</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-sm font-bold text-zinc-900 capitalize">{{ $parsedMonth->translatedFormat('F Y') }}</span>
            </div>
        </div>

        <div class="space-y-10 py-4">
            @forelse($sessions as $date => $daySessions)
                <div>
                    {{-- Encabezado del Día --}}
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-zinc-900 text-white rounded-xl px-4 py-2 text-center shadow-sm">
                            <span class="block text-xs font-bold uppercase tracking-widest">{{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}</span>
                            <span class="block text-xl font-black leading-none mt-0.5">{{ \Carbon\Carbon::parse($date)->format('d') }}</span>
                        </div>
                        <div class="h-px bg-zinc-200 flex-1"></div>
                    </div>

                    {{-- Lista de Clases de ese Día --}}
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($daySessions as $session)
                            @php 
                                $color = $session->workshop->color ?? 'blue'; 
                                $borderColor = match($color) {
                                    'emerald' => 'border-emerald-500', 'rose' => 'border-rose-500', 'purple' => 'border-purple-500',
                                    'amber' => 'border-amber-500', 'indigo' => 'border-indigo-500', 'teal' => 'border-teal-500',
                                    'cyan' => 'border-cyan-500', 'fuchsia' => 'border-fuchsia-500', 'slate' => 'border-slate-500',
                                    default => 'border-blue-500',
                                };
                            @endphp

                            {{-- EL MAGICO SALTO AL SUBDOMINIO: Al hacer clic, redirige al panel del estudio específico --}}
                            <a href="{{ route('global.classes.teacher.session', $session->id) }}"
                               class="group bg-white border-l-4 {{ $borderColor }} border-y border-r border-y-zinc-200 border-r-zinc-200 rounded-r-2xl p-5 hover:bg-zinc-50 transition-all shadow-sm hover:shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="text-lg font-black text-zinc-900">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</span>
                                        <h4 class="text-base font-bold text-zinc-700">{{ $session->workshop->name }}</h4>
                                        @if($session->is_cancelled)
                                            <span class="bg-rose-100 text-rose-700 text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider">Suspendida</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-xs font-bold mt-2">
                                        <span class="text-zinc-500 bg-zinc-100 px-2 py-1 rounded-md">{{ $session->workshop->studio->name }}</span>
                                        @if($session->workshop->discipline)
                                            <span class="text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">{{ $session->workshop->discipline->name }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center text-indigo-600 font-bold text-sm opacity-0 group-hover:opacity-100 transition-opacity translate-x-2 group-hover:translate-x-0">
                                    Tomar Asistencia <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-zinc-200">
                    <p class="text-zinc-400 font-bold">No hay clases programadas para este mes.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
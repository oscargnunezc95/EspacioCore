<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-zinc-900 tracking-tight">Mis Clases como Alumno</h1>
            <p class="mt-3 text-zinc-500 font-light text-lg">Revisa tu horario unificado en todos los estudios de la red.</p>
        </div>

        @if($workshops->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($workshops as $workshop)
                    @php 
                        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']; 
                        $parsedDays = is_array($workshop->repeat_days) ? $workshop->repeat_days : json_decode($workshop->repeat_days, true) ?? [];
                    @endphp
                    <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-1 bg-zinc-100 text-zinc-600 text-xs font-bold rounded-md tracking-wide uppercase">
                                    {{ $workshop->studio->name ?? 'Estudio' }}
                                </span>
                                @if($workshop->is_single_class)
                                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">Clase Única</span>
                                @endif
                            </div>
                            
                            <h3 class="text-xl font-black text-zinc-900 mb-1">{{ $workshop->name }}</h3>
                            <p class="text-sm font-medium text-zinc-500 mb-4">
                                Prof: {{ $workshop->teacher->name ?? 'Por asignar' }}
                            </p>

                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-zinc-600">
                                    <svg class="w-4 h-4 mr-2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ \Carbon\Carbon::parse($workshop->start_time)->format('H:i') }} hrs
                                </div>
                                <div class="flex items-center text-sm text-zinc-600">
                                    <svg class="w-4 h-4 mr-2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @if($workshop->is_single_class && $workshop->specific_date)
                                        {{ \Carbon\Carbon::parse($workshop->specific_date)->translatedFormat('d M Y') }}
                                    @elseif(!empty($parsedDays))
                                        {{ implode(', ', array_map(fn($d) => $dias[$d] ?? '', $parsedDays)) }}
                                    @else
                                        Días por definir
                                    @endif
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('dashboard', ['subdomain' => $workshop->studio->subdomain ?? '']) }}" class="block w-full text-center px-4 py-2.5 bg-zinc-900 text-white text-sm font-bold rounded-xl hover:bg-zinc-800 transition-colors">
                            Ir al Estudio
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Estado Vacío -->
            <div class="bg-white rounded-3xl border border-zinc-200 py-24 px-6 text-center shadow-sm">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-zinc-50 border border-zinc-100 mb-6">
                    <svg class="w-10 h-10 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900 mb-2">Tu Horario está en camino</h3>
                <p class="text-zinc-500 max-w-md mx-auto text-sm leading-relaxed mb-8">
                    Aún no estás inscrita/o en ninguna clase activa. Pronto podrás ver aquí el calendario unificado de todas tus clases.
                </p>
                <a href="{{ route('explore') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-zinc-700 bg-zinc-100 hover:bg-zinc-200 transition-colors duration-200">
                    Explorar nuevos talleres
                </a>
            </div>
        @endif

    </div>
</x-app-layout>
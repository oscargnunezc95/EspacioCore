<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-zinc-900 tracking-tight">Mis Clases como Profesor</h1>
            <p class="mt-3 text-zinc-500 font-light text-lg">Revisa tu horario unificado en todos los estudios de la red en los que eres profesor.</p>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-sm font-bold text-zinc-900 capitalize">Meses</span>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 py-4">
            @forelse($months as $month)
                <a href="{{ route('global.classes.teacher.calendar', $month['id']) }}" class="group block bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm hover:shadow-xl hover:border-zinc-300 hover:-translate-y-1 transition-all duration-300 cursor-pointer relative overflow-hidden">
                    
                    {{-- Decoración visual --}}
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-zinc-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
                    
                    <div class="relative z-10 flex flex-col h-full">
                        <h3 class="text-xl font-black text-zinc-900 mb-1 capitalize">{{ $month['name'] }}</h3>
                        <p class="text-sm font-bold text-indigo-600 mb-4">{{ $month['session_count'] }} clases programadas</p>
                        
                        <div class="mt-auto pt-4 border-t border-zinc-100">
                            <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-1">Estudios:</p>
                            <p class="text-xs font-bold text-zinc-700 truncate" title="{{ $month['studios'] }}">
                                {{ $month['studios'] }}
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-20 bg-white rounded-3xl border-2 border-dashed border-zinc-200 text-center">
                    <p class="text-zinc-400 font-bold">No tienes clases asignadas a futuro.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
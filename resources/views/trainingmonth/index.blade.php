<x-app-layout>
    
    {{-- 1. NAVEGACIÓN DEL ESTUDIO (Libre de paddings, pegado arriba) --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO (Contenedor maestro acoplado a la nueva arquitectura) --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Cabecera Unificada de Planificación --}}
        <div class="mt-2 mb-8 p-1">

            {{-- Breadcrumbs --}}
            <div class="flex text-xs font-bold text-stone-500 mb-3 gap-2 items-center">
                <span class="text-amber-600">Planificación</span>
            </div>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">
                
                {{-- Título --}}
                <h1 class="text-2xl md:text-3xl font-black  truncate flex-1 min-w-0">
                    Ciclos Mensuales
                </h1>

                {{-- Botón Responsivo --}}
                <button onclick="openMonthModal()" class="shrink-0 ml-auto bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2 text-sm">
                    
                    {{-- Icono de Calendario con (+) --}}
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12v4m-2-2h4"></path>
                    </svg>
                    
                    {{-- Texto oculto en móviles --}}
                    <span class="hidden sm:inline">Planificar Mes</span>
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($months as $month)
                @php $d = \Carbon\Carbon::parse($month->first_date); @endphp
                
                <a href="{{ route('trainingmonth.show', $month->month_id) }}" class="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-200 group flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-bold text-stone-400 uppercase mb-1 tracking-widest">{{ $d->format('Y') }}</div>
                        <h2 class="text-2xl font-black text-stone-900 capitalize group-hover:text-stone-600 transition-colors duration-200">{{ $d->translatedFormat('F') }}</h2>
                    </div>
                    <div class="mt-6 flex items-center text-stone-900 font-bold text-sm group-hover:translate-x-1 transition-transform duration-200">
                        Ver calendario 
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center border-2 border-dashed border-stone-200 rounded-2xl bg-stone-50">
                    <svg class="mx-auto h-12 w-12 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-bold text-stone-900">No hay ciclos generados</h3>
                    <p class="mt-1 text-sm text-stone-500">Comienza planificando el primer mes de entrenamientos.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div id="monthModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-md w-full shadow-xl border border-stone-100 transform transition-all relative">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-stone-900">Generar Nuevo Mes</h3>
                <button type="button" onclick="closeMonthModal()" class="text-stone-400 hover:text-stone-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="{{ route('trainingmonth.store', ['subdomain' => request()->route('subdomain')]) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-bold text-stone-700 mb-2">Elegir Mes y Año</label>
                    <input type="month" 
                           name="month_year" 
                           value="{{ old('month_year') }}" 
                           onclick="try { this.showPicker(); } catch(e) {}"
                           class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 transition-all duration-200 outline-none cursor-pointer {{ $errors->has('month_year') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}" 
                           required>
                    @error('month_year') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="mb-8">
                    <label class="block text-sm font-bold text-stone-700 mb-2">Talleres a incluir</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto border border-stone-200 rounded-xl p-3 bg-stone-50/50 hide-scrollbar {{ $errors->has('workshops') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
                        @forelse($workshops as $w)
                            @php 
                                $dias = ['Domingos', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábados']; 
                            @endphp
                            
                            <label class="flex items-start gap-3 p-3 bg-white border border-stone-200 rounded-lg cursor-pointer hover:border-stone-400 hover:shadow-sm transition-all duration-200">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input type="checkbox" name="workshops[]" value="{{ $w->id }}" checked class="w-4 h-4 text-stone-900 rounded border-stone-300 focus:ring-red-600 cursor-pointer">
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-stone-900 text-sm leading-tight">{{ $w->name }}</span>
                                    
                                    {{-- LÓGICA DE DÍAS CORREGIDA --}}
                                    <span class="text-stone-500 text-xs font-medium mt-1">
                                        @if($w->is_single_class && $w->specific_date)
                                            Clase Única: {{ \Carbon\Carbon::parse($w->specific_date)->translatedFormat('d \d\e F') }}
                                        @elseif($w->schedules && $w->schedules->count() > 0)
                                            @php
                                                // Extraer días únicos y ordenarlos
                                                $diasUnicos = $w->schedules->pluck('day_of_week')->unique()->sort()->values()->toArray();
                                            @endphp
                                            Se repite los {{ implode(', ', array_map(fn($d) => $dias[$d] ?? '', $diasUnicos)) }}
                                        @else
                                            <span class="text-rose-500">Sin horarios configurados</span>
                                        @endif
                                    </span>
                                </div>
                            </label>
                        @empty
                            <div class="text-sm text-stone-500 italic text-center py-4">No hay talleres configurados.</div>
                        @endforelse
                    </div>
                    @error('workshops') <p class="text-xs text-rose-600 font-bold mt-1">Debes seleccionar al menos un taller.</p> @enderror
                </div>

                <div class="flex gap-3 pt-2 border-t border-stone-100">
                    <button type="button" onclick="closeMonthModal()" class="w-full bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 text-sm">Crear Calendario</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script de control de Modal --}}
    <script>
        const modalBackdrop = document.getElementById('monthModal');
        let isMouseDownOnBackdrop = false;

        modalBackdrop.addEventListener('mousedown', function(e) {
            isMouseDownOnBackdrop = (e.target === modalBackdrop);
        });

        modalBackdrop.addEventListener('mouseup', function(e) {
            if (isMouseDownOnBackdrop && e.target === modalBackdrop) {
                closeMonthModal();
            }
            isMouseDownOnBackdrop = false;
        });

        function openMonthModal() {
            document.body.style.overflow = 'hidden';
            modalBackdrop.classList.remove('hidden');
        }

        function closeMonthModal() {
            document.body.style.overflow = '';
            modalBackdrop.classList.add('hidden');
        }

        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                openMonthModal();
            });
        @endif
    </script>

    {{-- Estilo para el scroll del modal --}}
    <style>
        .hide-scrollbar::-webkit-scrollbar { width: 6px; }
        .hide-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .hide-scrollbar::-webkit-scrollbar-thumb { background-color: #e4e4e7; border-radius: 20px; }
    </style>
</x-app-layout>
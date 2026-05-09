<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />

        <div class="mt-8">
            <x-studio-header 
                title="Ciclos Mensuales" 
                :breadcrumbs="[
                    ['name' => 'Planificación']
                ]"
            >
                <x-slot name="actions">
                    <button onclick="openMonthModal()" class="bg-zinc-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-zinc-800 focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95">
                        + Planificar Mes
                    </button>
                </x-slot>
            </x-studio-header>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($months as $month)
                @php $d = \Carbon\Carbon::parse($month->first_date); @endphp
                
                <a href="{{ route('trainingmonth.show', $month->month_id) }}" class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-200 group flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-bold text-zinc-400 uppercase mb-1 tracking-widest">{{ $d->format('Y') }}</div>
                        <h2 class="text-2xl font-black text-zinc-900 capitalize group-hover:text-zinc-600 transition-colors duration-200">{{ $d->translatedFormat('F') }}</h2>
                    </div>
                    <div class="mt-6 flex items-center text-zinc-900 font-bold text-sm group-hover:translate-x-1 transition-transform duration-200">
                        Ver calendario 
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center border-2 border-dashed border-zinc-200 rounded-2xl bg-zinc-50">
                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-bold text-zinc-900">No hay ciclos generados</h3>
                    <p class="mt-1 text-sm text-zinc-500">Comienza planificando el primer mes de entrenamientos.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div id="monthModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-md w-full shadow-xl border border-zinc-100 transform transition-all relative">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-zinc-900">Generar Nuevo Mes</h3>
                <button type="button" onclick="closeMonthModal()" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="{{ route('trainingmonth.store', ['subdomain' => request()->route('subdomain')]) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-bold text-zinc-700 mb-2">Elegir Mes y Año</label>
                    <input type="month" 
                           name="month_year" 
                           value="{{ old('month_year') }}" 
                           onclick="try { this.showPicker(); } catch(e) {}"
                           class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all duration-200 outline-none cursor-pointer {{ $errors->has('month_year') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}" 
                           required>
                    @error('month_year') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="mb-8">
                    <label class="block text-sm font-bold text-zinc-700 mb-2">Talleres a incluir</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto border border-zinc-200 rounded-xl p-3 bg-zinc-50/50 hide-scrollbar {{ $errors->has('workshops') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
                        @forelse($workshops as $w)
                            @php 
                                $dias = ['Domingos', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábados']; 
                            @endphp
                            
                            <label class="flex items-start gap-3 p-3 bg-white border border-zinc-200 rounded-lg cursor-pointer hover:border-zinc-400 hover:shadow-sm transition-all duration-200">
                                <div class="flex items-center h-5 mt-0.5">
                                    <input type="checkbox" name="workshops[]" value="{{ $w->id }}" checked class="w-4 h-4 text-zinc-900 rounded border-zinc-300 focus:ring-zinc-900 cursor-pointer">
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-zinc-900 text-sm leading-tight">{{ $w->name }}</span>
                                    
                                    {{-- LÓGICA DE DÍAS CORREGIDA --}}
                                    <span class="text-zinc-500 text-xs font-medium mt-1">
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
                            <div class="text-sm text-zinc-500 italic text-center py-4">No hay talleres configurados.</div>
                        @endforelse
                    </div>
                    @error('workshops') <p class="text-xs text-rose-600 font-bold mt-1">Debes seleccionar al menos un taller.</p> @enderror
                </div>

                <div class="flex gap-3 pt-2 border-t border-zinc-100">
                    <button type="button" onclick="closeMonthModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all duration-200 active:scale-95 text-sm">Crear Calendario</button>
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
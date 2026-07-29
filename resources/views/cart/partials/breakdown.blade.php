{{-- COMPONENTE DE DESGLOSE ENRIQUECIDO PARA EL CARRITO --}}
<div class="space-y-3 pt-1 text-left">
    @forelse($breakdown as $item)
        <div class="bg-white rounded-2xl border border-zinc-200/80 p-3.5 shadow-2xs transition-all duration-200 hover:border-zinc-300">
            
            {{-- Fila Principal: Título del Pack/Combo, Alumno y Precio --}}
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <span class="text-sm font-black text-zinc-900 block leading-snug truncate">
                        {{ $item['name'] }}
                    </span>
                    
                    {{-- Badges de Promoción y Nombre del Alumno --}}
                    @if(!empty($item['badges']))
                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                            @foreach($item['badges'] as $index => $badge)
                                @php
                                    // El último badge en tu arreglo siempre es el nombre del alumno
                                    $isNameBadge = ($index === count($item['badges']) - 1);
                                    $colorClass = $isNameBadge 
                                        ? 'bg-indigo-100 text-indigo-700 border-indigo-200/60' 
                                        : 'bg-emerald-100 text-emerald-700 border-emerald-200/60';
                                @endphp
                                <span class="inline-flex items-center text-[10px] font-black {{ $colorClass }} border px-1.5 py-0.5 rounded uppercase tracking-wider shadow-2xs">
                                    @if($isNameBadge)
                                        <svg class="w-2.5 h-2.5 mr-1 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                                    @endif
                                    {{ $badge }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                {{-- Monto Subtotal --}}
                <span class="text-sm sm:text-base font-black {{ $item['is_discount'] ? 'text-emerald-600' : 'text-zinc-900' }} shrink-0">
                    {{ $item['is_discount'] ? '' : '$' }}{{ number_format($item['subtotal'], 0, ',', '.') }}
                </span>
            </div>

            {{-- Detalle de Clases que Componen este Pack o Selección --}}
            @if(!empty($item['items']))
                <div class="mt-3 pt-2.5 border-t border-zinc-100 space-y-1.5">
                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">
                        Sesiones incluidas en este cálculo:
                    </p>
                    
                    <div class="space-y-1">
                        @foreach($item['items'] as $session)
                            <div class="flex items-center justify-between text-xs py-1.5 px-2.5 rounded-xl transition-colors {{ $session['is_paid_history'] ? 'bg-indigo-50/70 border border-indigo-100/80 text-indigo-950 font-semibold' : 'bg-zinc-50/70 text-zinc-700 font-medium' }}">
                                
                                {{-- Fecha y Hora --}}
                                <div class="flex items-center gap-1.5 min-w-0 truncate">
                                    <span class="truncate">📅 {{ $session['date_formatted'] }}</span>
                                    <span class="text-zinc-300 shrink-0">|</span>
                                    <span class="text-zinc-500 shrink-0">{{ $session['time_formatted'] }}</span>
                                </div>
                                
                                {{-- Distintivo de Clase Ya Pagada (Abono Retroactivo) --}}
                                @if($session['is_paid_history'])
                                    <span class="inline-flex items-center gap-1 text-[9px] font-black bg-indigo-600 text-white px-2 py-0.5 rounded-md shadow-2xs shrink-0 uppercase tracking-wider ml-2">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        Clase Ya Pagada
                                    </span>
                                @else
                                    <span class="text-[9px] font-bold text-zinc-400 uppercase tracking-wider shrink-0 ml-2">
                                        A pagar
                                    </span>
                                @endif

                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @empty
        <div class="text-center py-4 text-zinc-400 text-xs font-medium">
            0 clases seleccionadas
        </div>
    @endforelse
</div>
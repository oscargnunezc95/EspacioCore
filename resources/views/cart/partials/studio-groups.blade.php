@foreach($groupedSessions as $studioId => $sessions)
    @php
        $studio = $sessions->first()->workshop->studio;
    @endphp
    
    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden mb-8 studio-cart-group" data-studio-id="{{ $studio->id }}">
        
        {{-- Cabecera del Estudio --}}
        <div class="bg-zinc-50 border-b border-zinc-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-black uppercase text-lg">
                    {{ substr($studio->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-lg font-black text-zinc-900 leading-tight">{{ $studio->name }}</h2>
                    <p class="text-xs font-medium text-zinc-500">Selecciona las clases a pagar</p>
                </div>
            </div>
            
            {{-- Checkbox Global (Seleccionar todo el estudio) --}}
            <label class="flex items-center gap-2 cursor-pointer hover:bg-zinc-200/50 p-2 rounded-lg transition-colors">
                <span class="text-sm font-bold text-zinc-700">Seleccionar Todo</span>
                <input type="checkbox" onchange="toggleStudioSelection(this, {{ $studio->id }})" class="w-5 h-5 text-zinc-900 border-zinc-300 rounded focus:ring-zinc-900 cursor-pointer">
            </label>
        </div>

        {{-- Lista de Clases del Estudio --}}
        <ul class="divide-y divide-zinc-100 px-6 py-2">
            @foreach($sessions as $session)
                @php
                    // Obtenemos el precio base (drop-in) de esta clase en particular para el dataset
                    $basePrice = $session->workshop->prices->where('class_count', 1)->first()->price ?? 0;
                @endphp
                <li class="py-4 flex items-center justify-between group">
                    <label class="flex items-center gap-4 cursor-pointer flex-1">
                        <input type="checkbox" 
                            name="selected_sessions[]" 
                            value="{{ $session->id }}" 
                            data-studio-id="{{ $studio->id }}"
                            data-workshop-id="{{ $session->workshop_id }}"
                            data-base-price="{{ $basePrice }}"
                            data-promotions="{{ json_encode([ ['classes' => 4, 'price' => 20000], ['classes' => 8, 'price' => 35000] ]) }}"
                            onchange="calculateCart()"
                            class="session-checkbox w-5 h-5 text-zinc-900 border-zinc-300 rounded focus:ring-zinc-900 cursor-pointer transition-all duration-200">
                        
                        <div class="flex-1">
                            <p class="font-bold text-zinc-900 group-hover:text-indigo-600 transition-colors">{{ $session->workshop->name }}</p>
                            <p class="text-sm text-zinc-500 flex items-center gap-1.5 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d M') }} a las {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}
                            </p>
                        </div>
                    </label>
                    
                    {{-- Bloque derecho: Precio y Botón de Eliminar --}}
                    <div class="text-right flex items-center gap-4 ml-4">
                        <span class="text-sm font-black text-zinc-900">${{ number_format($basePrice, 0, ',', '.') }}</span>
                        
                        <button type="button" onclick="removeCartItem({{ $session->id }}, this)" class="p-2 text-zinc-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors focus:outline-none" title="Remover clase">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>

        {{-- Footer de Total por Estudio (Cálculo Dinámico) --}}
        <div class="bg-zinc-50 px-6 py-5 border-t border-zinc-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="w-full md:w-auto">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-1">Desglose</p>
                <div id="breakdown-{{ $studio->id }}" class="text-sm text-zinc-700 min-h-[20px]">
                    0 clases seleccionadas
                </div>
            </div>
            
            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                <div class="text-right">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-1">Total Estudio</p>
                    <p class="text-2xl font-black text-zinc-900 leading-none" id="total-{{ $studio->id }}">$0</p>
                </div>
                <button onclick="payStudio({{ $studio->id }})" disabled id="btn-pay-{{ $studio->id }}" class="bg-zinc-900 text-white font-bold px-6 py-3 rounded-xl hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 active:scale-95 shadow-sm">
                    Pagar Selección
                </button>
            </div>
        </div>
    </div>
@endforeach
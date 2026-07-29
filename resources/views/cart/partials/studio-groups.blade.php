@foreach($groupedSessions as $studioId => $sessions)
    @php
        $studio = $sessions->first()->workshop->studio;
        $studioPromos = $promotions->get($studio->id, collect());
        $studioPacks = $packs->get($studio->id, collect());

        $studioLogo = $studio->icon_path ?? $studio->logo_path ?? null;
        $studioAvatar = $studioLogo 
            ? asset('storage/' . $studioLogo) 
            : 'https://ui-avatars.com/api/?name='.urlencode($studio->name).'&color=0f766e&background=ccfbf1';

        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
        $protocol = request()->secure() ? 'https://' : 'http://';
        $fullStudioUrl = $protocol . $studio->subdomain . '.' . $domain;
        
        $hasMercadoPago = !empty($studio->mp_access_token);
    @endphp
    
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden mb-8 studio-cart-group" data-studio-id="{{ $studio->id }}">
        
        {{-- Cabecera del Estudio --}}
        <div class="bg-stone-50 border-b border-stone-200 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $studioAvatar }}" alt="{{ $studio->name }}" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover shrink-0 border border-stone-200 shadow-sm bg-white">
                <div class="flex flex-col">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 leading-none">{{ $studio->name }}</h2>
                        <button type="button" onclick="openPromoModal('promo-modal-{{ $studio->id }}')" class="hidden sm:flex text-[10px] font-bold text-teal-700 bg-white border border-teal-200 shadow-sm px-2.5 py-1 rounded-md hover:bg-teal-50 hover:border-teal-300 transition-all duration-200 active:scale-95 items-center gap-1 uppercase tracking-widest leading-none mt-0.5">
                            <svg class="w-3 h-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v1m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Ver Ofertas
                        </button>
                    </div>
                    <a href="{{ $fullStudioUrl }}" target="_blank" class="group/link flex items-center gap-1.5 text-xs font-medium text-stone-500 hover:text-red-600 transition-colors w-fit mt-1">
                        <svg class="w-3.5 h-3.5 text-stone-400 group-hover/link:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        <span class="underline decoration-transparent group-hover/link:decoration-red-200 transition-all">{{ $studio->subdomain }}.{{ $domain }}</span>
                    </a>
                </div>
            </div>
            
            <label class="flex items-center gap-2 cursor-pointer hover:bg-stone-200/50 p-2 rounded-lg transition-colors w-fit sm:w-auto">
                <span class="text-sm font-bold text-stone-700">Seleccionar Todo</span>
                <input type="checkbox" onchange="toggleStudioSelection(this, {{ $studio->id }})" class="w-5 h-5 text-stone-900 border-stone-300 rounded focus:ring-red-600 cursor-pointer" {{ !$hasMercadoPago ? 'disabled' : 'checked' }}>
            </label>
        </div>

        <div class="sm:hidden bg-stone-50/80 px-4 py-3 border-b border-stone-200">
            <button type="button" onclick="openPromoModal('promo-modal-{{ $studio->id }}')" class="w-full bg-white border border-teal-200 shadow-sm text-xs font-bold text-teal-700 py-2.5 rounded-xl hover:bg-teal-50 transition-all active:scale-95 flex justify-center items-center gap-1.5 uppercase tracking-widest">
                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Toca aquí para ver Ofertas y Packs
            </button>
        </div>

        @if(!$hasMercadoPago)
            <div class="bg-amber-50 border-b border-amber-100 px-6 py-3 flex items-start sm:items-center gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <p class="text-sm font-medium text-amber-800 leading-snug">
                    Este espacio aún no habilita los pagos online. Comunícate directamente con <span class="font-bold">{{ $studio->name }}</span> para coordinar.
                </p>
            </div>
        @endif

        {{-- CAPA DE LOGICA UI: Identificar si es Alumno Único o Grupo Familiar --}}
        @php
            $isSingleStudent = $activeDependents->isEmpty();
        @endphp

        {{-- Lista Agrupada por Taller y luego por Fechas --}}
        <ul class="divide-y divide-stone-100 px-6 py-2 {{ !$hasMercadoPago ? 'opacity-75 grayscale-[20%]' : '' }}">
            
            {{-- 1. AGRUPACIÓN POR TALLER (WORKSHOP) --}}
            @foreach($sessions->groupBy('workshop_id') as $workshopId => $workshopSessions)
                @php
                    $firstSession = $workshopSessions->first();
                    $workshop = $firstSession->workshop;
                    $basePrice = $workshop->prices->where('class_count', 1)->first()->price ?? 0;
                    $workshopImg = $workshop->image_path ?? null;
                    $workshopAvatar = $workshopImg 
                        ? asset('storage/' . $workshopImg) 
                        : 'https://ui-avatars.com/api/?name='.urlencode($workshop->name).'&color=4f46e5&background=e0e7ff';
                    
                    // Extraer nombre del titular para el badge compacto
                    $titularName = $firstSession->students->first()->first_name ?? auth()->user()->name ?? 'Estudiante';
                @endphp

                <li class="py-5 flex flex-col group">
                    {{-- Cabecera Única del Taller --}}
                    <div class="flex items-start sm:items-center justify-between gap-4 mb-3 bg-stone-50/60 p-3 rounded-2xl border border-stone-100">
                        <div class="flex items-center gap-3.5">
                            <img src="{{ $workshopAvatar }}" alt="Taller" class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl object-cover border border-stone-200 shrink-0 shadow-sm bg-white">
                            <div>
                                <p class="font-black text-stone-900 text-base sm:text-lg leading-tight">{{ $workshop->name }}</p>
                                
                                {{-- BADGE INTELIGENTE DE IDENTIDAD (Ahorro de espacio) --}}
                                @if($isSingleStudent)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-widest bg-indigo-50 text-indigo-700 border border-indigo-100/80 px-2 py-0.5 rounded-md mt-1 shadow-2xs">
                                        <svg class="w-2.5 h-2.5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                                        Alumno: {{ $titularName }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest bg-stone-200/70 text-stone-600 px-2 py-0.5 rounded-md mt-1">
                                        👥 Selección Familiar
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="text-xs font-bold text-stone-400 uppercase tracking-wider hidden sm:block">
                            {{ $workshopSessions->count() }} {{ $workshopSessions->count() === 1 ? 'fecha reservada' : 'fechas reservadas' }}
                        </span>
                    </div>

                    {{-- 2. LISTA DE FECHAS DE ESTE TALLER --}}
                    <div class="mt-1 sm:ml-14 space-y-2 border-l-2 border-stone-100 pl-3 sm:pl-4">
                        @foreach($workshopSessions as $session)
                            @php
                                $sessionAvailable = $session->available_spots ?? 99;
                                $pendingForUser = $session->pending_user_count ?? 0;
                                $isOverbooked = $pendingForUser > $sessionAvailable;
                                $isLowStock = $sessionAvailable <= 2 && $sessionAvailable > 0 && !$isOverbooked;
                            @endphp

                            {{-- MODO A: ALUMNO ÚNICO (Diseño ultra-compacto por fecha) --}}
                            @if($isSingleStudent)
                                @php
                                    $st = $session->students->first();
                                    if(!$st) continue;
                                    $depId = 'null';
                                @endphp

                                <label class="flex items-center justify-between p-2.5 hover:bg-stone-50 rounded-xl transition-all duration-200 group/chk border border-transparent hover:border-stone-200 cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" 
                                            value="{{ $session->id }}-{{ $st->id }}" 
                                            data-studio-id="{{ $studio->id }}"
                                            onchange="calculateCart()"
                                            class="session-checkbox w-5 h-5 text-red-600 border-stone-300 rounded focus:ring-red-600 cursor-pointer transition-all duration-200 disabled:bg-stone-100 disabled:cursor-not-allowed"
                                            {{ !$hasMercadoPago ? 'disabled' : '' }}
                                            checked>
                                        
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            <span class="text-sm font-bold text-stone-800 group-hover/chk:text-red-600 transition-colors capitalize">
                                                📅 {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d M') }}
                                            </span>
                                            <span class="text-stone-300 font-light hidden sm:inline">|</span>
                                            <span class="text-xs font-semibold text-stone-500 bg-stone-100 px-2 py-0.5 rounded-md">
                                                🕒 {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs
                                            </span>

                                            {{-- Badges de Stock inline --}}
                                            @if($isOverbooked)
                                                <span class="text-[10px] font-black bg-rose-50 text-rose-600 border border-rose-200 px-1.5 py-0.5 rounded">
                                                    ⚠️ Solo {{ $sessionAvailable }} cupo(s)
                                                </span>
                                            @elseif($isLowStock)
                                                <span class="text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200 px-1.5 py-0.5 rounded">
                                                    🔥 Quedan {{ $sessionAvailable }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 shrink-0">
                                        <span class="text-sm font-black text-stone-900">${{ number_format($basePrice, 0, ',', '.') }}</span>
                                        
                                        @if($st->is_locked_debt)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200 cursor-help"
                                                title="Asististe a esta clase. El pago es obligatorio para saldar tu deuda con el estudio.">
                                                <svg class="w-3 h-3 mr-1 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                Deuda
                                            </span>
                                        @else
                                            <button type="button" onclick="removeCartItem({{ $session->id }}, {{ $depId }}, this)" class="p-1.5 text-stone-400 hover:text-rose-500 hover:bg-rose-100 rounded-lg transition-colors focus:outline-none" title="Remover reserva">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                </label>

                            {{-- MODO B: GRUPO FAMILIAR (Desglose por fecha y luego por hermano/hijo) --}}
                            @else
                                <div class="p-3 bg-stone-50/50 rounded-2xl border border-stone-100 space-y-2">
                                    {{-- Mini cabecera de la Fecha --}}
                                    <div class="flex flex-wrap items-center justify-between gap-2 pb-1 border-b border-stone-200/60">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-stone-800 capitalize">
                                                📅 {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d M') }}
                                            </span>
                                            <span class="text-xs font-semibold text-stone-500 bg-white border border-stone-200 px-1.5 py-0.5 rounded">
                                                🕒 {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs
                                            </span>
                                        </div>

                                        @if($isOverbooked)
                                            <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                                ⚠️ Solo {{ $sessionAvailable }} cupo(s) disponible(s)
                                            </span>
                                        @elseif($isLowStock)
                                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                🔥 Quedan {{ $sessionAvailable }} cupos
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Checkboxes por cada familiar en esta fecha --}}
                                    <div class="space-y-1 pl-1">
                                        @foreach($session->students as $st)
                                            @php
                                                $isTitular = ($st->user_id === auth()->id());
                                                $depId = 'null';
                                                if (!$isTitular) {
                                                    $dep = auth()->user()->dependents->where('national_id', $st->national_id)->first();
                                                    $depId = $dep ? $dep->id : 'null';
                                                }
                                            @endphp

                                            <label class="flex items-center justify-between p-1.5 hover:bg-white rounded-xl transition-colors group/chk cursor-pointer">
                                                <div class="flex items-center gap-3">
                                                    <input type="checkbox" 
                                                        value="{{ $session->id }}-{{ $st->id }}" 
                                                        data-studio-id="{{ $studio->id }}"
                                                        onchange="calculateCart()"
                                                        class="session-checkbox w-4 h-4 text-red-600 border-stone-300 rounded focus:ring-red-600 cursor-pointer transition-all duration-200 disabled:bg-stone-100"
                                                        {{ !$hasMercadoPago ? 'disabled' : '' }}
                                                        checked>
                                                    <span class="text-sm font-bold text-stone-700 group-hover/chk:text-red-600 transition-colors">
                                                        {{ $st->first_name }} {{ $st->last_name }}
                                                        @if($isTitular) <span class="text-[9px] bg-indigo-100 text-indigo-700 font-black px-1.5 py-0.5 rounded uppercase tracking-widest ml-1">Titular</span> @endif
                                                    </span>
                                                </div>
                                                
                                                <div class="flex items-center gap-3">
                                                    <span class="text-xs font-black text-stone-900">${{ number_format($basePrice, 0, ',', '.') }}</span>
                                                    @if($st->is_locked_debt)
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-50 text-rose-700 border border-rose-200">Deuda</span>
                                                    @else
                                                        <button type="button" onclick="removeCartItem({{ $session->id }}, {{ $depId }}, this)" class="p-1 text-stone-400 hover:text-rose-500 rounded transition-colors focus:outline-none" title="Remover">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>

        {{-- Footer de Total --}}
        <div class="bg-stone-50 px-6 py-5 border-t border-stone-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="w-full md:w-auto">
                <p class="text-xs font-bold text-stone-500 uppercase tracking-widest mb-1">Desglose</p>
                <div id="breakdown-{{ $studio->id }}" class="text-sm text-stone-700 min-h-[20px]">
                    <span class='text-stone-400'>0 clases seleccionadas</span>
                </div>
            </div>
            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                <div class="text-right">
                    <p class="text-xs font-bold text-stone-500 uppercase tracking-widest mb-1">Total Estudio</p>
                    <p class="text-2xl font-black text-stone-900 leading-none" id="total-{{ $studio->id }}">$0</p>
                </div>
                @if($hasMercadoPago)
                    <button onclick="payStudio({{ $studio->id }})" disabled id="btn-pay-{{ $studio->id }}" class="bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold w-36 h-12 rounded-xl flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 active:scale-95 shadow-sm">
                        Pagar Selección
                    </button>
                @else
                    <button disabled class="bg-stone-200 text-stone-400 font-bold w-36 h-12 rounded-xl flex items-center justify-center cursor-not-allowed shadow-inner text-sm uppercase tracking-wider">
                        No Disponible
                    </button>
                @endif
            </div>
        </div>

        {{-- MODAL DE PROMOCIONES --}}
        <div id="promo-modal-{{ $studio->id }}" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" onclick="closePromoModal('promo-modal-{{ $studio->id }}')"></div>
            
            <div class="modal-card relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">
                
                {{-- Header del Modal --}}
                <div class="bg-teal-700 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-white">
                        <svg class="w-6 h-6 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v1m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-xl font-black">Ahorra en {{ $studio->name }}</h3>
                    </div>
                    <button onclick="closePromoModal('promo-modal-{{ $studio->id }}')" class="text-teal-200 hover:text-white transition-colors bg-teal-800/50 p-1.5 rounded-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Cuerpo del Modal (Enriquecido con Vigencia y Tiempos) --}}
                <div class="p-6 overflow-y-auto bg-stone-50/50">
                    
                    {{-- 1. Promociones / Combos --}}
                    @if($studioPromos->isNotEmpty())
                        <div class="mb-8">
                            <h4 class="text-xs font-black text-stone-400 uppercase tracking-widest mb-3">Promociones y Combos</h4>
                            <div class="space-y-3">
                                @foreach($studioPromos as $promo)
                                    <div class="bg-white border border-teal-100 p-4 rounded-2xl shadow-sm relative overflow-hidden">
                                        <div class="absolute top-0 left-0 w-1.5 h-full bg-teal-500"></div>
                                        <div class="pl-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="font-bold text-stone-900 text-lg leading-tight">{{ $promo->name }}</p>
                                                
                                                {{-- BADGE DE VIGENCIA DE LA PROMO --}}
                                                @if(isset($promo->validity_months))
                                                    <span class="text-[10px] font-extrabold bg-teal-50 text-teal-700 border border-teal-200/60 px-2 py-0.5 rounded-md uppercase tracking-wider shrink-0">
                                                        🕒 {{ $promo->validity_months == 0 ? 'Sin límite' : $promo->validity_months . ($promo->validity_months == 1 ? ' mes' : ' meses') }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($promo->type === 'specific_combo')
                                                <p class="text-sm text-stone-500 mt-1 mb-2">Para activar este combo, debes seleccionar:</p>
                                                
                                                <ul class="space-y-1.5 bg-stone-50 p-3 rounded-xl border border-stone-100 mb-3">
                                                    @foreach($promo->workshopPrices as $reqPrice)
                                                        <li class="flex items-center gap-2 text-sm text-stone-700 font-medium">
                                                            <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            <span>Pack de <strong class="text-stone-900">{{ $reqPrice->class_count }} clases</strong> de {{ $reqPrice->workshop->name }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                
                                                <div class="flex items-center justify-between pt-2 border-t border-stone-100">
                                                    <span class="text-sm font-bold text-stone-500">Precio Especial:</span>
                                                    <span class="text-lg font-black text-teal-700">${{ number_format($promo->total_price, 0, ',', '.') }}</span>
                                                </div>
                                            @else
                                                <p class="text-sm text-stone-500 mt-1">Lleva <strong class="text-stone-800">{{ $promo->class_count }} clases</strong> o más y obtén un descuento global de:</p>
                                                <p class="text-lg font-black text-teal-700 mt-1">${{ number_format($promo->additional_price, 0, ',', '.') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 2. Packs por Taller --}}
                    @if($studioPacks->isNotEmpty())
                        <div>
                            <h4 class="text-xs font-black text-stone-400 uppercase tracking-widest mb-3">Packs por Disciplina</h4>
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($studioPacks as $workshopName => $packs)
                                    <div class="bg-white border border-stone-200 p-4 rounded-2xl shadow-sm">
                                        <p class="font-bold text-stone-900 mb-2 border-b border-stone-100 pb-2 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                            {{ $workshopName }}
                                        </p>
                                        
                                        <ul class="divide-y divide-stone-100">
                                            @foreach($packs as $pack)
                                                <li class="flex justify-between items-center py-2.5 first:pt-1 last:pb-0">
                                                    <div class="flex flex-col pr-2">
                                                        <span class="text-stone-800 font-bold text-sm">Pack de {{ $pack->class_count }} clases</span>
                                                        
                                                        {{-- DETALLE DE VIGENCIA DEL PACK --}}
                                                        <span class="text-[11px] text-stone-400 font-medium flex items-center gap-1 mt-0.5">
                                                            🕒 {{ $pack->validity_months == 0 ? 'Sin límite de tiempo' : 'Vigencia: ' . $pack->validity_months . ($pack->validity_months == 1 ? ' mes' : ' meses') }}
                                                            @if($pack->validity_months > 0)
                                                                <span class="text-stone-300">|</span>
                                                                <span>{{ $pack->validity_type === 'calendar' ? 'Mes calendario' : 'Días continuos' }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    
                                                    <span class="font-black text-stone-900 bg-stone-100 px-2.5 py-1 rounded-xl text-sm shrink-0">
                                                        ${{ number_format($pack->price, 0, ',', '.') }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
                
                {{-- Footer del Modal --}}
                <div class="p-6 border-t border-stone-100 bg-white">
                    <button onclick="closePromoModal('promo-modal-{{ $studio->id }}')" class="w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95">
                        Entendido, volver al carrito
                    </button>
                </div>
            </div>
        </div>
        
    </div>
@endforeach
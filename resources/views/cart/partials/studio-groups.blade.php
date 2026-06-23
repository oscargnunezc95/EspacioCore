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
    
    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden mb-8 studio-cart-group" data-studio-id="{{ $studio->id }}">
        
        {{-- Cabecera del Estudio --}}
        <div class="bg-zinc-50 border-b border-zinc-200 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $studioAvatar }}" alt="{{ $studio->name }}" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover shrink-0 border border-zinc-200 shadow-sm bg-white">
                <div class="flex flex-col">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <h2 class="text-lg sm:text-xl font-black text-zinc-900 leading-none">{{ $studio->name }}</h2>
                        <button type="button" onclick="openPromoModal('promo-modal-{{ $studio->id }}')" class="hidden sm:flex text-[10px] font-bold text-teal-700 bg-white border border-teal-200 shadow-sm px-2.5 py-1 rounded-md hover:bg-teal-50 hover:border-teal-300 transition-all duration-200 active:scale-95 items-center gap-1 uppercase tracking-widest leading-none mt-0.5">
                            <svg class="w-3 h-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v1m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Ver Ofertas
                        </button>
                    </div>
                    <a href="{{ $fullStudioUrl }}" target="_blank" class="group/link flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-indigo-600 transition-colors w-fit mt-1">
                        <svg class="w-3.5 h-3.5 text-zinc-400 group-hover/link:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        <span class="underline decoration-transparent group-hover/link:decoration-indigo-200 transition-all">{{ $studio->subdomain }}.{{ $domain }}</span>
                    </a>
                </div>
            </div>
            
            <label class="flex items-center gap-2 cursor-pointer hover:bg-zinc-200/50 p-2 rounded-lg transition-colors w-fit sm:w-auto">
                <span class="text-sm font-bold text-zinc-700">Seleccionar Todo</span>
                <input type="checkbox" onchange="toggleStudioSelection(this, {{ $studio->id }})" class="w-5 h-5 text-zinc-900 border-zinc-300 rounded focus:ring-zinc-900 cursor-pointer" {{ !$hasMercadoPago ? 'disabled' : 'checked' }}>
            </label>
        </div>

        <div class="sm:hidden bg-zinc-50/80 px-4 py-3 border-b border-zinc-200">
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

        {{-- Lista de Clases del Estudio --}}
        <ul class="divide-y divide-zinc-100 px-6 py-2 {{ !$hasMercadoPago ? 'opacity-75 grayscale-[20%]' : '' }}">
            @foreach($sessions as $session)
                @php
                    $basePrice = $session->workshop->prices->where('class_count', 1)->first()->price ?? 0;
                    $workshopImg = $session->workshop->image_path ?? null;
                    $workshopAvatar = $workshopImg 
                        ? asset('storage/' . $workshopImg) 
                        : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&color=4f46e5&background=e0e7ff';
                @endphp
                <li class="py-5 flex flex-col group">
                    {{-- Info General de la Sesión --}}
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ $workshopAvatar }}" alt="Taller" class="w-12 h-12 rounded-xl object-cover border border-zinc-200 shrink-0 shadow-sm">
                        <div class="flex-1">
                            <p class="font-bold text-zinc-900 leading-tight">{{ $session->workshop->name }}</p>
                            <p class="text-sm text-zinc-500 flex items-center gap-1.5 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d M') }} a las {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}
                            </p>
                        </div>
                    </div>

                    {{-- CAPA 2: Indicador de stock por sesión --}}
                    @php
                        $sessionAvailable = $session->available_spots ?? 99;
                        $sessionMax = $session->max_students ?? 99;
                        $pendingForUser = $session->pending_user_count ?? 0;
                        $isOverbooked = $pendingForUser > $sessionAvailable;
                        $isLowStock = $sessionAvailable <= 2 && $sessionAvailable > 0 && !$isOverbooked;
                    @endphp

                    @if($isOverbooked)
                    <div class="flex items-center gap-1.5 ml-0">
                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span class="text-[11px] font-black text-rose-600">Solo {{ $sessionAvailable }} cupo(s) disponible(s) — tienes {{ $pendingForUser }} reserva(s)</span>
                    </div>
                    @elseif($isLowStock)
                    <div class="flex items-center gap-1.5 ml-0">
                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-[11px] font-black text-amber-600">Quedan {{ $sessionAvailable }} cupos</span>
                    </div>
                    @endif

                    {{-- Lista Individual de Alumnos en esta sesión --}}
                    <div class="mt-2 sm:ml-16 flex flex-col gap-2 border-l-2 border-zinc-100 pl-4">
                        @foreach($session->students as $st)
                            @php
                                // PRINCIPIO: user_id es quien ASISTE. Si es auth()->id(), es el titular.
                                $isTitular = ($st->user_id === auth()->id());
                                $depId = 'null';
                                if (!$isTitular) {
                                    $dep = auth()->user()->dependents->where('national_id', $st->national_id)->first();
                                    $depId = $dep ? $dep->id : 'null';
                                }
                            @endphp
                            <label class="flex items-center justify-between cursor-pointer p-2.5 hover:bg-zinc-50 rounded-xl transition-colors group/chk border border-transparent hover:border-zinc-200">
                                <div class="flex items-center gap-3">
                                    {{-- EL VALUE AHORA ES COMPUESTO: session_id-student_id --}}
                                    <input type="checkbox" 
                                        value="{{ $session->id }}-{{ $st->id }}" 
                                        data-studio-id="{{ $studio->id }}"
                                        onchange="calculateCart()"
                                        class="session-checkbox w-5 h-5 text-indigo-600 border-zinc-300 rounded focus:ring-indigo-600 cursor-pointer transition-all duration-200 disabled:bg-zinc-100 disabled:cursor-not-allowed"
                                        {{ !$hasMercadoPago ? 'disabled' : '' }}
                                        checked>
                                    <span class="text-sm font-bold text-zinc-700 group-hover/chk:text-indigo-600 transition-colors">
                                        {{ $st->first_name }} {{ $st->last_name }}
                                        @if($isTitular) <span class="text-[9px] bg-zinc-200 text-zinc-600 px-1.5 py-0.5 rounded uppercase tracking-widest ml-1">Titular</span> @endif
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <span class="text-sm font-black text-zinc-900">${{ number_format($basePrice, 0, ',', '.') }}</span>
                                    <button type="button" onclick="removeCartItem({{ $session->id }}, {{ $depId }}, this)" class="p-1.5 text-zinc-400 hover:text-rose-500 hover:bg-rose-100 rounded-lg transition-colors focus:outline-none" title="Remover reserva">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>

        {{-- Footer de Total (Igual) --}}
        <div class="bg-zinc-50 px-6 py-5 border-t border-zinc-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="w-full md:w-auto">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-1">Desglose</p>
                <div id="breakdown-{{ $studio->id }}" class="text-sm text-zinc-700 min-h-[20px]">
                    <span class='text-zinc-400'>0 clases seleccionadas</span>
                </div>
            </div>
            <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                <div class="text-right">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-1">Total Estudio</p>
                    <p class="text-2xl font-black text-zinc-900 leading-none" id="total-{{ $studio->id }}">$0</p>
                </div>
                @if($hasMercadoPago)
                    <button onclick="payStudio({{ $studio->id }})" disabled id="btn-pay-{{ $studio->id }}" class="bg-zinc-900 text-white font-bold w-36 h-12 rounded-xl flex items-center justify-center hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 active:scale-95 shadow-sm">
                        Pagar Selección
                    </button>
                @else
                    <button disabled class="bg-zinc-200 text-zinc-400 font-bold w-36 h-12 rounded-xl flex items-center justify-center cursor-not-allowed shadow-inner text-sm uppercase tracking-wider">
                        No Disponible
                    </button>
                @endif
            </div>
        </div>
        {{-- EL MODAL DE PROMOCIONES SE MANTIENE EXACTAMENTE IGUAL --}}
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

                {{-- Cuerpo del Modal --}}
                <div class="p-6 overflow-y-auto bg-zinc-50/50">
                    
                    {{-- 1. Promociones / Combos --}}
                    @if($studioPromos->isNotEmpty())
                        <div class="mb-8">
                            <h4 class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-3">Promociones y Combos</h4>
                            <div class="space-y-3">
                                @foreach($studioPromos as $promo)
                                    <div class="bg-white border border-teal-100 p-4 rounded-2xl shadow-sm relative overflow-hidden">
                                        <div class="absolute top-0 left-0 w-1.5 h-full bg-teal-500"></div>
                                        <div class="pl-2">
                                            <p class="font-bold text-zinc-900 text-lg">{{ $promo->name }}</p>
                                            
                                            @if($promo->type === 'specific_combo')
                                                <p class="text-sm text-zinc-500 mt-1 mb-2">Para activar este combo, debes seleccionar:</p>
                                                
                                                <ul class="space-y-1.5 bg-zinc-50 p-3 rounded-xl border border-zinc-100 mb-3">
                                                    @foreach($promo->workshopPrices as $reqPrice)
                                                        <li class="flex items-center gap-2 text-sm text-zinc-700 font-medium">
                                                            <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            <span>Pack de <strong class="text-zinc-900">{{ $reqPrice->class_count }} clases</strong> de {{ $reqPrice->workshop->name }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                
                                                <div class="flex items-center justify-between pt-2 border-t border-zinc-100">
                                                    <span class="text-sm font-bold text-zinc-500">Precio Especial:</span>
                                                    <span class="text-lg font-black text-teal-700">${{ number_format($promo->total_price, 0, ',', '.') }}</span>
                                                </div>
                                            @else
                                                <p class="text-sm text-zinc-500 mt-1">Lleva <strong class="text-zinc-800">{{ $promo->class_count }} clases</strong> o más y obtén un descuento global de:</p>
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
                            <h4 class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-3">Packs por Disciplina</h4>
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($studioPacks as $workshopName => $packs)
                                    <div class="bg-white border border-zinc-200 p-4 rounded-2xl shadow-sm">
                                        <p class="font-bold text-zinc-900 mb-2 border-b border-zinc-100 pb-2 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                            {{ $workshopName }}
                                        </p>
                                        <ul class="space-y-2">
                                            @foreach($packs as $pack)
                                                <li class="flex justify-between items-center text-sm">
                                                    <span class="text-zinc-600 font-medium">Pack de {{ $pack->class_count }} clases</span>
                                                    <span class="font-black text-zinc-900 bg-zinc-100 px-2 py-0.5 rounded-md">${{ number_format($pack->price, 0, ',', '.') }}</span>
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
                <div class="p-6 border-t border-zinc-100 bg-white">
                    <button onclick="closePromoModal('promo-modal-{{ $studio->id }}')" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl hover:bg-zinc-800 transition-colors active:scale-95 shadow-sm">
                        Entendido, volver al carrito
                    </button>
                </div>
            </div>
        </div>
        
    </div>
@endforeach
<x-app-layout>
    {{-- 1. NAVEGACIÓN DEL ESTUDIO (Libre de paddings, pegado arriba) --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Cabecera Unificada del Directorio --}}
        <div class="mt-2 mb-8 p-1">

            {{-- Breadcrumbs --}}
            <div class="flex text-xs font-bold text-amber-600 mb-3 gap-2 items-center">
                <a href="{{ route('students.index', ['subdomain' => request()->route('subdomain')]) }}" class="hover:text-stone-900 transition-colors">Alumnas/os</a>
                <span>/</span>
                <span class="text-amber-600">Perfil</span>
            </div>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">
                {{-- Título --}}
                <h1 class="text-2xl md:text-3xl font-black tracking-tight truncate flex-1 min-w-0">
                    {{ $student->name }}
                </h1>
            </div>
        </div>

        {{-- SUBMENÚ: Calendario / Pagos --}}
        <div class="flex space-x-1 bg-stone-100/80 p-1 rounded-xl w-fit border border-stone-200 mb-6">
            <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}"
               class="px-5 py-2 rounded-lg font-bold transition-all duration-200 text-sm {{ request()->routeIs('students.calendar') ? 'bg-white shadow-sm text-red-600' : 'text-stone-500 hover:text-stone-700' }}">
                Calendario
            </a>
            <a href="{{ route('students.payments', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}"
               class="px-5 py-2 rounded-lg font-bold transition-all duration-200 text-sm {{ request()->routeIs('students.payments') ? 'bg-white shadow-sm text-red-600' : 'text-stone-500 hover:text-stone-700' }}">
                Pagos
            </a>
        </div>

        @php
            $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
            $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
        @endphp

        @if (session('success'))
            {{-- Limpieza automática del carrito al concretar un pago exitoso --}}
            <script>localStorage.removeItem('studio_cart_student_{{ $student->id }}');</script>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- CONTROLES DEL CALENDARIO --}}
        <div class="flex justify-between items-center mb-6 bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-stone-200">
            <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id, 'month' => $prevMonth]) }}" 
               class="px-3 sm:px-4 py-2.5 bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 rounded-xl font-bold transition-all duration-200 text-sm flex items-center gap-1.5 sm:gap-2 active:scale-95">
                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                <span class="hidden sm:inline">Anterior</span>
            </a>
            
            <h2 class="text-lg sm:text-xl md:text-2xl font-black text-stone-800 capitalize truncate px-2 text-center">
                {{ $monthDate->translatedFormat('F Y') }}
            </h2>
            
            <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id, 'month' => $nextMonth]) }}" 
               class="px-3 sm:px-4 py-2.5 bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 rounded-xl font-bold transition-all duration-200 text-sm flex items-center gap-1.5 sm:gap-2 active:scale-95">
                <span class="hidden sm:inline">Siguiente</span>
                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        {{-- Calendario Maestro --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="grid grid-cols-7 border-b border-stone-200 bg-stone-50">
                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                    <div class="py-3 text-center text-[10px] md:text-xs font-bold text-stone-500 uppercase tracking-wider border-r border-stone-200 last:border-0">{{ $d }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-px bg-stone-200">
                @php
                    $start = $monthDate->copy()->startOfMonth();
                    $empty = $start->dayOfWeekIso - 1;
                    $days = $monthDate->daysInMonth;
                @endphp

                @for ($i = 0; $i < $empty; $i++) <div class="bg-stone-50/50 min-h-[140px]"></div> @endfor

                @for ($day = 1; $day <= $days; $day++)
                    @php
                        $cur = $monthDate->copy()->day($day)->toDateString();
                        $sessionsInDay = $sessionsByDate->get($cur, collect());
                        $isToday = \Carbon\Carbon::parse($cur)->isToday();
                        $classCount = $sessionsInDay->count();
                    @endphp
                    
                    <div class="bg-white min-h-[140px] p-1.5 md:p-2 flex flex-col relative transition-all {{ $isToday ? 'ring-2 ring-inset ring-red-600 bg-stone-50/30' : '' }}">
                        <span class="text-sm font-bold flex items-center justify-center h-6 w-6 md:h-7 md:w-7 rounded-full mb-2 {{ $isToday ? 'bg-red-600 text-white' : 'text-stone-500' }}">{{ $day }}</span>
                        
                        @if($classCount > 0)
                            @php
                                // Data para el Modal de Celular
                                $dayData = $sessionsInDay->map(function($s) use ($student, $paidSessionIds, $cur) {
                                    $c = $s->workshop->color ?? 'blue'; 
                                    $bgClass = match($c) {
                                        'emerald' => 'bg-emerald-500', 'rose' => 'bg-rose-500', 'purple' => 'bg-purple-500',
                                        'amber' => 'bg-amber-500', 'indigo' => 'bg-red-500', 'teal' => 'bg-teal-500',
                                        'cyan' => 'bg-cyan-500', 'fuchsia' => 'bg-fuchsia-500', 'slate' => 'bg-slate-500',
                                        default => 'bg-blue-500',
                                    };
                                    
                                    $isEnrolled = $s->students->contains('id', $student->id);
                                    $hasAttendance = $s->attendances->contains('student_id', $student->id);
                                    $isPaid = in_array($s->id, $paidSessionIds);
                                    $isCancelled = $s->is_cancelled ?? ($s->status === 'cancelled') ?? false;
                                    $isObligation = $isEnrolled || $hasAttendance;

                                    return [
                                        'id' => $s->id,
                                        'time' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                                        'name' => $s->workshop->name,
                                        'dateRaw' => $cur,
                                        'dateFormatted' => \Carbon\Carbon::parse($cur)->format('d/m') . ' a las ' . \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                                        'bgClass' => $bgClass,
                                        'isPaid' => (bool) $isPaid,
                                        'isCancelled' => (bool) $isCancelled,
                                        'isObligation' => (bool) $isObligation,
                                        'isEnrolled' => (bool) $isEnrolled,
                                        'hasAttendance' => (bool) $hasAttendance
                                    ];
                                })->toJson();
                                $formattedDate = \Carbon\Carbon::parse($cur)->translatedFormat('l d \d\e F');
                            @endphp

                            {{-- LÓGICA DE ESCRITORIO: Lista visible directamente en la celda --}}
                            <div class="hidden md:block space-y-1.5 flex-1">
                                @foreach($sessionsInDay as $s)
                                    @php 
                                        $c = $s->workshop->color ?? 'blue'; 
                                        $bgClass = match($c) {
                                            'emerald' => 'bg-emerald-500', 'rose' => 'bg-rose-500', 'purple' => 'bg-purple-500',
                                            'amber' => 'bg-amber-500', 'indigo' => 'bg-red-500', 'teal' => 'bg-teal-500',
                                            'cyan' => 'bg-cyan-500', 'fuchsia' => 'bg-fuchsia-500', 'slate' => 'bg-slate-500',
                                            default => 'bg-blue-500',
                                        };
                                        
                                        $isEnrolled = $s->students->contains('id', $student->id);
                                        $hasAttendance = $s->attendances->contains('student_id', $student->id);
                                        $isPaid = in_array($s->id, $paidSessionIds);
                                        $isCancelled = $s->is_cancelled ?? ($s->status === 'cancelled') ?? false;
                                        $isObligation = $isEnrolled || $hasAttendance;

                                        if ($isCancelled) {
                                            $cardStyle = 'bg-stone-50 border-stone-200 opacity-75 grayscale';
                                        } elseif ($isPaid) {
                                            $cardStyle = 'bg-white border-stone-200';
                                        } elseif ($isObligation) {
                                            $cardStyle = 'bg-rose-50/40 border-rose-300 hover:border-rose-400';
                                        } else {
                                            $cardStyle = 'bg-stone-50 border-stone-200 hover:border-stone-300';
                                        }
                                        
                                        $wrapperTag = (!$isPaid && !$isCancelled) ? 'label' : 'div';
                                        $cursorClass = (!$isPaid && !$isCancelled) ? 'cursor-pointer' : '';
                                    @endphp
                                    
                                    <{{ $wrapperTag }} class="relative w-full block text-left p-2 pl-3 border rounded-lg shadow-sm overflow-hidden transition-all duration-200 {{ $cardStyle }} {{ $cursorClass }} group">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isCancelled ? 'bg-stone-400' : $bgClass }}"></div>

                                        <div class="flex justify-between items-start">
                                            <div class="text-[10px] md:text-xs font-extrabold {{ $isCancelled ? 'text-stone-500 line-through' : 'text-stone-900' }}">
                                                {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                            </div>
                                            
                                            {{-- CHECKBOX DE PAGO --}}
                                            @if(!$isPaid && !$isCancelled)
                                                <input type="checkbox" 
                                                       value="{{ $s->id }}" 
                                                       data-date="{{ $cur }}"
                                                       data-class-name="{{ $s->workshop->name }}"
                                                       data-class-date="{{ \Carbon\Carbon::parse($cur)->format('d/m') }} a las {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}"
                                                       onchange="handlePaymentSelection(this)" 
                                                       class="payment-cb w-4 h-4 text-emerald-600 border-stone-300 rounded focus:ring-emerald-500 cursor-pointer relative z-10" title="Seleccionar para pagar">
                                            @endif
                                        </div>
                                        
                                        <div class="text-[9px] font-bold leading-tight mt-1 line-clamp-1 {{ $isCancelled ? 'text-stone-500' : 'text-stone-800' }}">
                                            {{ $s->workshop->name }}
                                        </div>

                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            @if($isCancelled)
                                                <span class="text-[8px] font-black uppercase tracking-wider text-rose-700 bg-rose-100 px-1 rounded border border-rose-200">Anulada</span>
                                            @elseif($isPaid)
                                                <span class="text-[8px] font-black uppercase tracking-wider text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">Pagada</span>
                                            @elseif($isObligation)
                                                <span class="text-[8px] font-black uppercase tracking-wider text-rose-600 bg-rose-100 px-1 rounded border border-rose-200">Debe</span>
                                            @else
                                                <span class="text-[8px] font-black uppercase tracking-wider text-stone-500 bg-stone-200 px-1 rounded">Disponible</span>
                                            @endif

                                            @if($hasAttendance && !$isCancelled)
                                                <span class="text-[8px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 px-1 rounded border border-emerald-100">Presente</span>
                                            @elseif($isEnrolled && !$isCancelled)
                                                <span class="text-[8px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 px-1 rounded border border-amber-100">Inscrita</span>
                                            @endif
                                        </div>
                                    </{{ $wrapperTag }}>
                                @endforeach
                            </div>

                            {{-- LÓGICA MÓVIL: Botón agrupador dinámico --}}
                            <button id="day-btn-{{ $cur }}" data-date="{{ $cur }}" onclick="openDayClasses('{{ $formattedDate }}', {{ $dayData }})" class="day-btn mt-auto mb-auto mx-1 py-2 px-2 bg-red-50 hover:bg-red-100 border border-red-100 rounded-xl flex flex-col items-center justify-center gap-1 group transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-red-500 md:hidden">
                                <span class="btn-num text-lg font-black text-red-600 leading-none group-hover:scale-110 transition-transform">{{ $classCount }}</span>
                                <span class="btn-text text-[9px] font-bold text-red-700 uppercase tracking-widest">{{ $classCount === 1 ? 'Clase' : 'Clases' }}</span>
                            </button>
                        @endif
                    </div>
                @endfor

                @php
                    $remainingCells = 7 - (($empty + $days) % 7);
                    if ($remainingCells == 7) $remainingCells = 0;
                @endphp
                @for ($i = 0; $i < $remainingCells; $i++) <div class="bg-stone-50/50 min-h-[140px]"></div> @endfor
            </div>
        </div>

        {{-- MODAL CON LA LISTA DE CLASES DEL DÍA (Para Móviles) --}}
        <div id="dayClassesModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" onclick="closeDayClasses()"></div>
            
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="dayModalCard">
                <div class="px-5 py-4 border-b border-stone-100 flex justify-between items-center bg-stone-50 shrink-0">
                    <div>
                        <h3 class="text-lg font-black text-stone-900 tracking-tight">Clases del Día</h3>
                        <p id="modalDayDate" class="text-[11px] font-bold text-stone-500 capitalize mt-0.5">Fecha</p>
                    </div>
                    <button onclick="closeDayClasses()" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-200 rounded-full transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-4 overflow-y-auto flex-1 space-y-3" id="modalClassesList">
                    {{-- Contenido inyectado por JS --}}
                </div>
                
                <div class="p-4 border-t border-stone-100 bg-white shrink-0">
                    <button onclick="closeDayClasses()" class="w-full bg-stone-100 text-stone-700 border border-stone-200 font-bold py-2.5 rounded-xl hover:bg-stone-200 transition-all duration-200 active:scale-95 text-sm">
                        Cerrar Lista
                    </button>
                </div>
            </div>
        </div>

        {{-- BARRA FLOTANTE DE PAGO --}}
        <div id="bulkPaymentBar" class="fixed bottom-0 left-0 right-0 bg-white border-t border-stone-200 p-3 sm:p-4 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.1)] transform translate-y-full transition-transform duration-300 z-[70]">
            <div class="max-w-7xl mx-auto w-full px-2 sm:px-6 lg:px-8 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="bg-emerald-100 text-emerald-600 font-black h-8 w-8 sm:h-9 sm:w-9 rounded-full flex items-center justify-center text-xs sm:text-sm shadow-inner" id="selectedCount">0</div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-0 sm:gap-2">
                        <span class="text-xs sm:text-sm font-bold text-stone-700 hidden sm:inline">Seleccionadas</span>
                        <button onclick="clearCart()" class="text-[10px] sm:text-xs font-bold text-stone-400 hover:text-stone-700 underline underline-offset-2 transition-colors">Vaciar</button>
                    </div>
                    <div id="gatewayPriceContainer" class="hidden sm:flex items-center gap-1.5 bg-emerald-50 border border-emerald-100 px-2 sm:px-3 py-1 rounded-lg">
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Total</span>
                        <span id="gatewayPrice" class="text-sm sm:text-base font-black text-emerald-700">--</span>
                    </div>
                </div>
                <div class="flex gap-2 items-center">
                    <span id="gatewayPriceMobile" class="text-sm font-black text-emerald-700 sm:hidden bg-emerald-50 px-2 py-1 rounded-lg">--</span>
                    <button onclick="openPaymentModal()" class="bg-gradient-to-r from-stone-600 to-stone-700 hover:from-stone-500 hover:to-stone-600 text-white font-bold py-2.5 px-3 sm:px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 text-xs sm:text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Pago Manual
                    </button>
                    <button onclick="openGatewayModal()" id="btnPasarela" disabled class="bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-3 sm:px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 text-xs sm:text-sm flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:from-red-600 disabled:hover:to-rose-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Pasarela
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL DE PAGO MANUAL --}}
        <div id="paymentModal" class="fixed inset-0 z-[80] hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-xl border border-stone-100 transform transition-all relative max-h-[90vh] flex flex-col">
                
                <div class="flex justify-between items-center p-6 md:p-8 pb-4 shrink-0 border-b border-stone-100">
                    <h3 class="text-xl font-black text-stone-900">Registrar Pago</h3>
                    <button type="button" onclick="closePaymentModal()" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form action="{{ route('payments.store', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}" method="POST" enctype="multipart/form-data" class="overflow-y-auto flex-1 p-6 md:p-8 pt-4">
                    @csrf
                    <div id="hiddenSessionInputs"></div>
                    
                    <div class="mb-4">
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center">
                            <span class="block text-xs font-bold text-emerald-600 uppercase tracking-widest mb-1">Pagando</span>
                            <span class="text-2xl font-black text-emerald-700" id="modalClassCountText">0 Clases</span>
                            
                            {{-- LISTA DE CLASES QUE SE ESTÁN PAGANDO --}}
                            <div id="modalClassesSelectedList" class="mt-3">
                                {{-- Se inyecta por JS --}}
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Método de Pago *</label>
                        <select name="payment_method" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white">
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="efectivo">Efectivo</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Monto a Cobrar</label>
                        <div class="w-full bg-stone-100 border border-stone-200 rounded-xl px-4 py-3 flex items-center justify-between shadow-inner">
                            <span class="text-sm font-bold text-stone-600">Total calculado:</span>
                            <span id="modalDisplayAmount" class="text-lg font-black text-emerald-600">--</span>
                        </div>
                        {{-- Input oculto para enviar el valor exacto al servidor --}}
                        <input type="hidden" name="amount" id="modalHiddenAmount" value="0">
                    </div>
                    
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Comprobante <span class="text-stone-400 font-normal">(Opcional)</span></label>
                        <input type="file" name="receipt" accept="image/jpeg,image/png,image/jpg,application/pdf" class="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer">
                    </div>

                    <div class="flex gap-3 pt-6 border-t border-stone-100 shrink-0 bg-white sticky bottom-0">
                        <button type="button" onclick="closePaymentModal()" class="w-full font-bold text-stone-600 bg-stone-100 hover:bg-stone-200 py-3.5 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                        <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3.5 rounded-xl shadow-sm hover:bg-emerald-700 active:scale-95 transition-all duration-200 text-sm flex items-center justify-center gap-2">
                            Confirmar Pago
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL PASARELA DE PAGO --}}
        <div id="gatewayModal" class="fixed inset-0 z-[85] hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-xl border border-stone-100 max-h-[90vh] flex flex-col overflow-hidden transform transition-all">

                <div class="flex justify-between items-center p-5 sm:p-6 pb-4 shrink-0 border-b border-stone-100">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-stone-900">Pasarela de Pago</h3>
                        <p class="text-[11px] font-bold text-stone-500 mt-0.5">Selecciona el método de cobro</p>
                    </div>
                    <button onclick="closeGatewayModal()" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-5 sm:p-6 space-y-4">
                    <div id="gatewayPriceSummary" class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                        <span class="block text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-1">Total a Cobrar</span>
                        <span class="text-2xl font-black text-emerald-700" id="gatewayTotalPrice">--</span>
                        <div id="gatewayBreakdown" class="mt-2 text-left text-[11px] text-stone-500 space-y-0.5"></div>
                    </div>

                    <div id="gatewayError" class="hidden p-3 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold border border-rose-100"></div>

                    <div id="gatewayLoading" class="hidden text-center py-4">
                        <div class="animate-spin h-8 w-8 border-4 border-emerald-500 border-t-transparent rounded-full mx-auto"></div>
                        <p class="text-sm text-stone-500 mt-2">Procesando...</p>
                    </div>

                    <div id="gatewayQrContainer" class="hidden p-4 bg-white border border-stone-200 rounded-xl text-center"></div>

                    {{-- OPCIÓN 1: QR MERCADOPAGO --}}
                    <div id="gatewayOptionStatic" class="border border-stone-200 rounded-2xl overflow-hidden">
                        <button onclick="toggleGatewayOption('static')" class="w-full flex items-center justify-between p-4 text-left hover:bg-stone-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-100 text-blue-600 h-9 w-9 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-stone-800">QR MercadoPago</p>
                                    <p class="text-[11px] text-stone-500">Solo con la app de MP. La alumna escanea y paga al instante.</p>
                                </div>
                            </div>
                            <svg id="gatewayChevronStatic" class="w-4 h-4 text-stone-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="gatewayBodyStatic" class="hidden px-4 pb-4 space-y-3">
                            @if(!empty($studio->mp_external_pos_id))
                                <div class="text-center">
                                    <img src="{{ $studio->mp_pos_qr_url }}" alt="QR MercadoPago" class="mx-auto max-w-[180px] rounded-xl border border-stone-200">
                                    <p class="text-[10px] text-stone-400 mt-2">Este QR es fijo. La alumna debe escanearlo con la app de MercadoPago.</p>
                                </div>
                            @endif
                            <button onclick="generateStaticQR()" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-xl shadow-sm hover:bg-blue-700 active:scale-95 transition-all duration-200 text-sm">
                                Generar Orden de Pago
                            </button>
                        </div>
                    </div>

                    {{-- OPCIÓN 2: QR NORMAL --}}
                    <div id="gatewayOptionDynamic" class="border border-stone-200 rounded-2xl overflow-hidden">
                        <button onclick="toggleGatewayOption('dynamic')" class="w-full flex items-center justify-between p-4 text-left hover:bg-stone-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-100 text-purple-600 h-9 w-9 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-stone-800">QR Normal</p>
                                    <p class="text-[11px] text-stone-500">Se genera un QR nuevo por cada cobro. Abre en navegador o app MP.</p>
                                </div>
                            </div>
                            <svg id="gatewayChevronDynamic" class="w-4 h-4 text-stone-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="gatewayBodyDynamic" class="hidden px-4 pb-4 space-y-3">
                            <p class="text-xs text-stone-500 text-center">Se generará un QR nuevo con el link de pago.</p>
                            <button onclick="generateDynamicQR()" class="w-full bg-purple-600 text-white font-bold py-2.5 rounded-xl shadow-sm hover:bg-purple-700 active:scale-95 transition-all duration-200 text-sm">
                                Generar QR
                            </button>
                        </div>
                    </div>

                    {{-- OPCIÓN 3: LINK POR CORREO --}}
                    <div id="gatewayOptionEmail" class="border border-stone-200 rounded-2xl overflow-hidden">
                        <button onclick="toggleGatewayOption('email')" class="w-full flex items-center justify-between p-4 text-left hover:bg-stone-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="bg-amber-100 text-amber-600 h-9 w-9 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-stone-800">Link por Correo</p>
                                    <p class="text-[11px] text-stone-500">
                                        @if($student->email)
                                            Se enviará a <strong>{{ $student->email }}</strong>
                                        @else
                                            <span class="text-amber-600">Sin correo registrado</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <svg id="gatewayChevronEmail" class="w-4 h-4 text-stone-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="gatewayBodyEmail" class="hidden px-4 pb-4 space-y-3">
                            @if($student->email)
                                <p class="text-xs text-stone-500 text-center">La alumna recibirá un correo con el link para pagar.</p>
                                <button onclick="sendPaymentEmail()" class="w-full bg-amber-500 text-white font-bold py-2.5 rounded-xl shadow-sm hover:bg-amber-600 active:scale-95 transition-all duration-200 text-sm">
                                    Enviar Link de Pago
                                </button>
                            @else
                                <div class="p-3 bg-amber-50 text-amber-700 rounded-xl text-xs font-bold text-center border border-amber-100">
                                    Esta alumna no tiene correo electrónico registrado. Edita su perfil para agregar uno.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div id="gatewaySuccess" class="hidden p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold text-center border border-emerald-100">
                        <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span id="gatewaySuccessMsg"></span>
                    </div>

                </div>

                <div class="p-4 sm:p-5 border-t border-stone-100 bg-stone-50 shrink-0">
                    <button onclick="closeGatewayModal()" class="w-full bg-stone-200 text-stone-700 border border-stone-300 font-bold py-2.5 rounded-xl hover:bg-stone-300 transition-all duration-200 active:scale-95 text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <script>
            // ==========================================
            // CONFIGURACIÓN: URLs del Gateway y CSRF
            // ==========================================
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const gatewayCalculateUrl = "{{ route('gateway.calculate', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}";
            const gatewayStaticQrUrl = "{{ route('gateway.static-qr', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}";
            const gatewayDynamicQrUrl = "{{ route('gateway.dynamic-qr', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}";
            const gatewaySendEmailUrl = "{{ route('gateway.send-email', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}";
            
            @if(!empty($studio->mp_access_token))
                document.getElementById('btnPasarela').disabled = false;
            @endif

            // ==========================================
            // LÓGICA DE CARRITO PERSISTENTE (Cross-Month)
            // ==========================================
            const cartStorageKey = 'studio_cart_student_{{ $student->id }}';
            let calculatedTotal = 0;
            let calculatedSymbol = '$';

            // Obtener carrito del localStorage
            function getCart() {
                return JSON.parse(localStorage.getItem(cartStorageKey)) || {};
            }

            // Guardar carrito en localStorage
            function saveCart(cart) {
                localStorage.setItem(cartStorageKey, JSON.stringify(cart));
            }

            // Limpiar carrito por completo (Botón Vaciar)
            function clearCart() {
                localStorage.removeItem(cartStorageKey);
                syncUIWithCart();
            }

            // Al cargar la página, restaurar el estado desde el Storage
            document.addEventListener('DOMContentLoaded', () => {
                syncUIWithCart();
            });

            function handlePaymentSelection(element = null) {
                let cart = getCart();

                if (element) {
                    const id = element.value;
                    if (element.checked) {
                        cart[id] = {
                            id: id,
                            name: element.getAttribute('data-class-name'),
                            date: element.getAttribute('data-class-date')
                        };
                    } else {
                        delete cart[id];
                    }
                } else {
                    document.querySelectorAll('.payment-cb').forEach(cb => {
                        const id = cb.value;
                        if (cb.checked) {
                            cart[id] = {
                                id: id,
                                name: cb.getAttribute('data-class-name'),
                                date: cb.getAttribute('data-class-date')
                            };
                        } else {
                            delete cart[id];
                        }
                    });
                }

                saveCart(cart);
                syncUIWithCart();
            }

            function syncUIWithCart() {
                let cart = getCart();
                let selectedCount = Object.keys(cart).length;

                // 1. Restaurar visualmente los checkboxes del mes actual
                document.querySelectorAll('.payment-cb, .payment-cb-modal').forEach(cb => {
                    cb.checked = cart.hasOwnProperty(cb.value);
                });

                updateMobileDayButtons();

                // 2. Controlar la barra flotante
                let bar = document.getElementById('bulkPaymentBar');
                let countSpan = document.getElementById('selectedCount');

                if (selectedCount > 0) {
                    bar.classList.remove('translate-y-full');
                    countSpan.innerText = selectedCount;
                } else {
                    bar.classList.add('translate-y-full');
                }

                // 3. Recalcular precio
                updatePriceDisplay(cart);
            }

            async function updatePriceDisplay(cart = getCart()) {
                const ids = Object.keys(cart);
                
                const priceEl = document.getElementById('gatewayPrice');
                const priceMobileEl = document.getElementById('gatewayPriceMobile');
                const priceContainer = document.getElementById('gatewayPriceContainer');
                
                const modalDisplayEl = document.getElementById('modalDisplayAmount');
                const modalHiddenEl = document.getElementById('modalHiddenAmount');

                if (ids.length === 0) {
                    priceEl.innerText = '--';
                    priceMobileEl.innerText = '--';
                    if (priceContainer) priceContainer.classList.add('hidden');
                    if (modalDisplayEl) modalDisplayEl.innerText = '--';
                    if (modalHiddenEl) modalHiddenEl.value = 0;
                    calculatedTotal = 0;
                    return;
                }

                try {
                    if (modalDisplayEl) modalDisplayEl.innerText = 'Calculando...';
                    
                    const params = new URLSearchParams();
                    ids.forEach(id => params.append('session_ids[]', id));
                    const url = gatewayCalculateUrl + '?' + params.toString();
                    
                    const resp = await fetch(url);
                    const data = await resp.json();
                    
                    calculatedTotal = data.total;
                    calculatedSymbol = data.currency_symbol || '$';
                    const priceText = calculatedSymbol + ' ' + calculatedTotal.toLocaleString('es-CL');
                    
                    priceEl.innerText = priceText;
                    priceMobileEl.innerText = priceText;
                    if (priceContainer) priceContainer.classList.remove('hidden');
                    
                    if (modalDisplayEl) modalDisplayEl.innerText = priceText;
                    if (modalHiddenEl) modalHiddenEl.value = calculatedTotal;
                } catch (e) {
                    priceEl.innerText = '-- (error)';
                    priceMobileEl.innerText = '--';
                    if (modalDisplayEl) modalDisplayEl.innerText = 'Error al calcular';
                    if (modalHiddenEl) modalHiddenEl.value = 0;
                }
            }

            function updateMobileDayButtons() {
                let cart = getCart();
                const activeDates = new Set();
                
                // Mapear solo los checkboxes visibles de la vista actual
                document.querySelectorAll('.payment-cb').forEach(cb => {
                    if (cart.hasOwnProperty(cb.value)) {
                        activeDates.add(cb.getAttribute('data-date'));
                    }
                });

                document.querySelectorAll('.day-btn').forEach(btn => {
                    const date = btn.getAttribute('data-date');
                    const numEl = btn.querySelector('.btn-num');
                    const textEl = btn.querySelector('.btn-text');

                    if (activeDates.has(date)) {
                        btn.classList.replace('bg-red-50', 'bg-emerald-50');
                        btn.classList.replace('border-red-100', 'border-emerald-200');
                        btn.classList.replace('hover:bg-red-100', 'hover:bg-emerald-100');
                        numEl.classList.replace('text-red-600', 'text-emerald-600');
                        textEl.classList.replace('text-red-700', 'text-emerald-700');
                    } else {
                        btn.classList.replace('bg-emerald-50', 'bg-red-50');
                        btn.classList.replace('border-emerald-200', 'border-red-100');
                        btn.classList.replace('hover:bg-emerald-100', 'hover:bg-red-100');
                        numEl.classList.replace('text-emerald-600', 'text-red-600');
                        textEl.classList.replace('text-emerald-700', 'text-red-700');
                    }
                });
            }

            // ==========================================
            // LÓGICA DEL MODAL DE PAGO MANUAL FINAL
            // ==========================================
            function openPaymentModal() {
                let cart = getCart();
                let ids = Object.keys(cart);
                
                if(ids.length === 0) return;

                document.getElementById('modalClassCountText').innerText = ids.length === 1 ? '1 Clase' : `${ids.length} Clases`;

                const displayAmountEl = document.getElementById('modalDisplayAmount');
                const hiddenAmountEl = document.getElementById('modalHiddenAmount');
                
                if (displayAmountEl && hiddenAmountEl && calculatedTotal > 0) {
                    displayAmountEl.innerText = calculatedSymbol + ' ' + calculatedTotal.toLocaleString('es-CL');
                    hiddenAmountEl.value = calculatedTotal;
                }

                let hiddenContainer = document.getElementById('hiddenSessionInputs');
                let listContainer = document.getElementById('modalClassesSelectedList');
                
                hiddenContainer.innerHTML = ''; 
                let listHtml = '<ul class="text-[11px] text-stone-600 text-left bg-white p-3 rounded-lg border border-emerald-100 space-y-1.5 list-none">';
                
                // Construir lista leyendo exclusivamente el localStorage
                Object.values(cart).forEach(item => {
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'session_ids[]';
                    input.value = item.id;
                    hiddenContainer.appendChild(input);

                    listHtml += `
                        <li class="flex items-start gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            <span class="font-bold text-stone-900">${item.name} <span class="font-normal text-stone-500 block sm:inline">(${item.date})</span></span>
                        </li>
                    `;
                });
                
                listHtml += '</ul>';
                listContainer.innerHTML = listHtml;

                document.body.style.overflow = 'hidden';
                document.getElementById('paymentModal').classList.remove('hidden');
            }

            function closePaymentModal() {
                document.body.style.overflow = '';
                document.getElementById('paymentModal').classList.add('hidden');
            }

            // ==========================================
            // LÓGICA DEL MODAL MÓVIL (Lista de Clases del Día)
            // ==========================================
            const dayModal = document.getElementById('dayClassesModal');
            const dayModalCard = document.getElementById('dayModalCard');
            const listContainer = document.getElementById('modalClassesList');

            function openDayClasses(dateStr, classesArray) {
                let cart = getCart();
                document.getElementById('modalDayDate').innerText = dateStr;
                listContainer.innerHTML = '';

                classesArray.forEach(cls => {
                    let cardStyle = '';
                    if (cls.isCancelled) cardStyle = 'bg-stone-50 border-stone-200 opacity-75 grayscale';
                    else if (cls.isPaid) cardStyle = 'bg-white border-stone-200';
                    else if (cls.isObligation) cardStyle = 'bg-rose-50/40 border-rose-300';
                    else cardStyle = 'bg-stone-50 border-stone-200';

                    const titleClass = cls.isCancelled ? 'text-stone-500 line-through' : 'text-stone-900';
                    const bgClass = cls.isCancelled ? 'bg-stone-400' : cls.bgClass;

                    let badges = '';
                    if (cls.isCancelled) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded border border-rose-200">Anulada</span>`;
                    else if (cls.isPaid) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">Pagada</span>`;
                    else if (cls.isObligation) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-rose-600 bg-rose-100 px-1.5 py-0.5 rounded border border-rose-200">Debe</span>`;
                    else badges += `<span class="text-[8px] font-black uppercase tracking-wider text-stone-500 bg-stone-200 px-1.5 py-0.5 rounded">Disponible</span>`;

                    if (cls.hasAttendance && !cls.isCancelled) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100 ml-1">Presente</span>`;
                    else if (cls.isEnrolled && !cls.isCancelled) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100 ml-1">Inscrita</span>`;

                    let checkboxHtml = '';
                    let wrapperTag = 'div';
                    let cursorClass = '';

                    if (!cls.isPaid && !cls.isCancelled) {
                        wrapperTag = 'label';
                        cursorClass = 'cursor-pointer hover:border-stone-400 hover:shadow-md transition-all duration-200';
                        const isChecked = cart.hasOwnProperty(cls.id) ? 'checked' : '';
                        checkboxHtml = `<input type="checkbox" value="${cls.id}" data-date="${cls.dateRaw}" data-class-name="${cls.name}" data-class-date="${cls.dateFormatted}" onchange="handlePaymentSelection(this)" class="payment-cb-modal w-5 h-5 text-emerald-600 border-stone-300 rounded focus:ring-emerald-500 cursor-pointer relative z-10" ${isChecked}>`;
                    }

                    listContainer.innerHTML += `
                        <${wrapperTag} class="relative w-full block text-left p-3 pl-4 border rounded-xl shadow-sm overflow-hidden ${cardStyle} ${cursorClass}">
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 ${bgClass}"></div>
                            <div class="flex justify-between items-start mb-1">
                                <div class="text-sm font-extrabold ${titleClass}">${cls.time} hrs</div>
                                ${checkboxHtml}
                            </div>
                            <div class="text-sm font-bold leading-tight mt-1 mb-2 ${cls.isCancelled ? 'text-stone-500' : 'text-stone-800'}">
                                ${cls.name}
                            </div>
                            <div class="flex flex-wrap gap-1 items-center">
                                ${badges}
                            </div>
                        </${wrapperTag}>
                    `;
                });

                dayModal.classList.remove('hidden');
                setTimeout(() => {
                    dayModalCard.classList.remove('scale-95', 'opacity-0');
                    dayModalCard.classList.add('scale-100', 'opacity-100');
                }, 10);
                document.body.style.overflow = 'hidden';
            }

            function closeDayClasses() {
                dayModalCard.classList.remove('scale-100', 'opacity-100');
                dayModalCard.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    dayModal.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === "Escape" && !dayModal.classList.contains('hidden')) closeDayClasses();
                if (event.key === "Escape") closeGatewayModal();
            });

            // ==========================================
            // LÓGICA DEL MODAL PASARELA (Gateway)
            // ==========================================
            let currentGatewayOption = null;

            function openGatewayModal() {
                let cart = getCart();
                let ids = Object.keys(cart);
                if (ids.length === 0) return;

                document.getElementById('gatewayError').classList.add('hidden');
                document.getElementById('gatewayLoading').classList.add('hidden');
                document.getElementById('gatewayQrContainer').classList.add('hidden');
                document.getElementById('gatewayQrContainer').innerHTML = '';
                document.getElementById('gatewaySuccess').classList.add('hidden');

                ['static', 'dynamic', 'email'].forEach(opt => {
                    document.getElementById('gatewayBody' + opt.charAt(0).toUpperCase() + opt.slice(1)).classList.add('hidden');
                    const chevron = document.getElementById('gatewayChevron' + opt.charAt(0).toUpperCase() + opt.slice(1));
                    if (chevron) chevron.classList.remove('rotate-180');
                });
                currentGatewayOption = null;

                fetchPriceAndUpdateModal();

                document.body.style.overflow = 'hidden';
                document.getElementById('gatewayModal').classList.remove('hidden');
            }

            function closeGatewayModal() {
                document.body.style.overflow = '';
                document.getElementById('gatewayModal').classList.add('hidden');
                currentGatewayOption = null;
            }

            async function fetchPriceAndUpdateModal() {
                document.getElementById('gatewayLoading').classList.remove('hidden');
                document.getElementById('gatewayError').classList.add('hidden');

                let cart = getCart();
                let ids = Object.keys(cart);

                try {
                    const url = gatewayCalculateUrl + '?' + ids.map(id => 'session_ids[]=' + id).join('&');
                    const resp = await fetch(url);
                    if (!resp.ok) throw new Error('Error al calcular el precio');
                    const data = await resp.json();

                    document.getElementById('gatewayTotalPrice').innerText = data.currency_symbol + ' ' + data.total.toLocaleString('es-CL');

                    let breakdownHtml = '';
                    if (data.breakdown && data.breakdown.length > 0) {
                        breakdownHtml = data.breakdown.map(b =>
                            `<div class="flex justify-between ${b.is_discount ? 'text-amber-600' : ''}">
                                <span>${b.name} ${b.badges ? b.badges.map(bg => '<span class="text-[9px] bg-stone-200 px-1 rounded ml-1">'+bg+'</span>').join('') : ''}</span>
                                <span class="font-bold">${b.is_discount ? '-' : ''}${data.currency_symbol} ${Math.abs(b.subtotal).toLocaleString('es-CL')}</span>
                            </div>`
                        ).join('');
                    }
                    document.getElementById('gatewayBreakdown').innerHTML = breakdownHtml;
                } catch (e) {
                    document.getElementById('gatewayError').innerText = e.message;
                    document.getElementById('gatewayError').classList.remove('hidden');
                } finally {
                    document.getElementById('gatewayLoading').classList.add('hidden');
                }
            }

            function toggleGatewayOption(option) {
                const bodyId = 'gatewayBody' + option.charAt(0).toUpperCase() + option.slice(1);
                const chevronId = 'gatewayChevron' + option.charAt(0).toUpperCase() + option.slice(1);
                const body = document.getElementById(bodyId);
                const chevron = document.getElementById(chevronId);

                const isOpen = !body.classList.contains('hidden');

                ['static', 'dynamic', 'email'].forEach(opt => {
                    const b = document.getElementById('gatewayBody' + opt.charAt(0).toUpperCase() + opt.slice(1));
                    const c = document.getElementById('gatewayChevron' + opt.charAt(0).toUpperCase() + opt.slice(1));
                    if (b) b.classList.add('hidden');
                    if (c) c.classList.remove('rotate-180');
                });

                if (!isOpen) {
                    body.classList.remove('hidden');
                    if (chevron) chevron.classList.add('rotate-180');
                    currentGatewayOption = option;
                } else {
                    currentGatewayOption = null;
                }
            }

            async function generateStaticQR() {
                document.getElementById('gatewayError').classList.add('hidden');
                document.getElementById('gatewayLoading').classList.remove('hidden');
                document.getElementById('gatewaySuccess').classList.add('hidden');

                let cart = getCart();
                let ids = Object.keys(cart);
                try {
                    const resp = await fetch(gatewayStaticQrUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ session_ids: ids })
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw new Error(data.error || 'Error al generar la orden');

                    const bodyEl = document.getElementById('gatewayBodyStatic');
                    const existingImg = bodyEl.querySelector('img');
                    if (existingImg) {
                        existingImg.classList.remove('hidden');
                    }
                    document.getElementById('gatewaySuccessMsg').innerText = 'Orden generada. El pago se procesará cuando la alumna escanee el QR con la app de MercadoPago.';
                    document.getElementById('gatewaySuccess').classList.remove('hidden');
                } catch (e) {
                    document.getElementById('gatewayError').innerText = e.message;
                    document.getElementById('gatewayError').classList.remove('hidden');
                } finally {
                    document.getElementById('gatewayLoading').classList.add('hidden');
                }
            }

            async function generateDynamicQR() {
                document.getElementById('gatewayError').classList.add('hidden');
                document.getElementById('gatewayLoading').classList.remove('hidden');
                document.getElementById('gatewaySuccess').classList.add('hidden');
                const qrContainer = document.getElementById('gatewayQrContainer');
                qrContainer.classList.add('hidden');
                qrContainer.innerHTML = '';

                let cart = getCart();
                let ids = Object.keys(cart);
                try {
                    const resp = await fetch(gatewayDynamicQrUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ session_ids: ids })
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw new Error(data.error || 'Error al generar el QR');

                    qrContainer.innerHTML = `
                        <div id="dynamicQrCode" class="mx-auto inline-block"></div>
                        <p class="text-xs text-stone-500 mt-3">Escanea este QR para pagar</p>
                        <a href="${data.init_point}" target="_blank" class="text-[11px] text-purple-600 underline mt-1.5 inline-block font-bold hover:text-purple-800 transition-colors">
                            Abrir link de pago →
                        </a>
                    `;
                    qrContainer.classList.remove('hidden');

                    if (typeof QRCode !== 'undefined') {
                        new QRCode(document.getElementById('dynamicQrCode'), {
                            text: data.init_point,
                            width: 180,
                            height: 180,
                            colorDark: '#292524',
                            colorLight: '#ffffff',
                        });
                    }

                    document.getElementById('gatewaySuccessMsg').innerText = 'QR generado. La alumna puede escanearlo o abrir el link.';
                    document.getElementById('gatewaySuccess').classList.remove('hidden');
                } catch (e) {
                    document.getElementById('gatewayError').innerText = e.message;
                    document.getElementById('gatewayError').classList.remove('hidden');
                } finally {
                    document.getElementById('gatewayLoading').classList.add('hidden');
                }
            }

            async function sendPaymentEmail() {
                document.getElementById('gatewayError').classList.add('hidden');
                document.getElementById('gatewayLoading').classList.remove('hidden');
                document.getElementById('gatewaySuccess').classList.add('hidden');

                let cart = getCart();
                let ids = Object.keys(cart);
                try {
                    const resp = await fetch(gatewaySendEmailUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ session_ids: ids })
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw new Error(data.error || 'Error al enviar el correo');

                    document.getElementById('gatewaySuccessMsg').innerText = data.message || 'Link de pago enviado exitosamente.';
                    document.getElementById('gatewaySuccess').classList.remove('hidden');
                } catch (e) {
                    document.getElementById('gatewayError').innerText = e.message;
                    document.getElementById('gatewayError').classList.remove('hidden');
                } finally {
                    document.getElementById('gatewayLoading').classList.add('hidden');
                }
            }
        </script>

        {{-- QR Code generation library --}}
        <script src="{{ asset('js/qrcode.min.js') }}"></script>
    </div>
</x-app-layout>
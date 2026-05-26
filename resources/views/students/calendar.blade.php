<x-app-layout>
    {{-- 1. NAVEGACIÓN DEL ESTUDIO (Libre de paddings, pegado arriba) --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO (Aquí mantenemos tu x-data para las pestañas internas) --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'activos' }">

        {{-- Cabecera Unificada del Directorio --}}
        <div class="mt-2 mb-8 p-1">

            {{-- Breadcrumbs --}}
            <div class="flex text-xs font-bold text-zinc-500 mb-3 gap-2 items-center">
                <a href="{{ route('students.index', ['subdomain' => request()->route('subdomain')]) }}" class="hover:text-zinc-900 transition-colors">Alumnas/os</a>
                <span>/</span>
                <span class="text-zinc-900">Perfil</span>
            </div>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">
                
                {{-- Título --}}
                <h1 class="text-2xl md:text-3xl font-black text-zinc-900 truncate flex-1 min-w-0">
                    {{ $student->name }}
                </h1>

            </div>
        </div>
        @php
            $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
            $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
        @endphp
        
        
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- CONTROLES DEL CALENDARIO (Refactorizados) --}}
        <div class="flex justify-between items-center mb-6 bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-zinc-200">
            <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id, 'month' => $prevMonth]) }}" 
               class="px-3 sm:px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm flex items-center gap-1.5 sm:gap-2 active:scale-95">
                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                <span class="hidden sm:inline">Anterior</span>
            </a>
            
            <h2 class="text-lg sm:text-xl md:text-2xl font-black text-zinc-800 capitalize truncate px-2 text-center">
                {{ $monthDate->translatedFormat('F Y') }}
            </h2>
            
            <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id, 'month' => $nextMonth]) }}" 
               class="px-3 sm:px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm flex items-center gap-1.5 sm:gap-2 active:scale-95">
                <span class="hidden sm:inline">Siguiente</span>
                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        {{-- Calendario Maestro --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50">
                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                    <div class="py-3 text-center text-[10px] md:text-xs font-bold text-zinc-500 uppercase tracking-wider border-r border-zinc-200 last:border-0">{{ $d }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-px bg-zinc-200">
                @php
                    $start = $monthDate->copy()->startOfMonth();
                    $empty = $start->dayOfWeekIso - 1;
                    $days = $monthDate->daysInMonth;
                @endphp

                @for ($i = 0; $i < $empty; $i++) <div class="bg-zinc-50/50 min-h-[140px]"></div> @endfor

                @for ($day = 1; $day <= $days; $day++)
                    @php
                        $cur = $monthDate->copy()->day($day)->toDateString();
                        $sessionsInDay = $sessionsByDate->get($cur, collect());
                        $isToday = \Carbon\Carbon::parse($cur)->isToday();
                        $classCount = $sessionsInDay->count();
                    @endphp
                    
                    <div class="bg-white min-h-[140px] p-1.5 md:p-2 flex flex-col relative transition-all {{ $isToday ? 'ring-2 ring-inset ring-zinc-900 bg-zinc-50/30' : '' }}">
                        <span class="text-sm font-bold flex items-center justify-center h-6 w-6 md:h-7 md:w-7 rounded-full mb-2 {{ $isToday ? 'bg-zinc-900 text-white' : 'text-zinc-500' }}">{{ $day }}</span>
                        
                        @if($classCount > 0)
                            @php
                                // Data para el Modal de Celular
                                $dayData = $sessionsInDay->map(function($s) use ($student, $paidSessionIds, $cur) {
                                    $c = $s->workshop->color ?? 'blue'; 
                                    $bgClass = match($c) {
                                        'emerald' => 'bg-emerald-500', 'rose' => 'bg-rose-500', 'purple' => 'bg-purple-500',
                                        'amber' => 'bg-amber-500', 'indigo' => 'bg-indigo-500', 'teal' => 'bg-teal-500',
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
                                            'amber' => 'bg-amber-500', 'indigo' => 'bg-indigo-500', 'teal' => 'bg-teal-500',
                                            'cyan' => 'bg-cyan-500', 'fuchsia' => 'bg-fuchsia-500', 'slate' => 'bg-slate-500',
                                            default => 'bg-blue-500',
                                        };
                                        
                                        $isEnrolled = $s->students->contains('id', $student->id);
                                        $hasAttendance = $s->attendances->contains('student_id', $student->id);
                                        $isPaid = in_array($s->id, $paidSessionIds);
                                        $isCancelled = $s->is_cancelled ?? ($s->status === 'cancelled') ?? false;
                                        $isObligation = $isEnrolled || $hasAttendance;

                                        if ($isCancelled) {
                                            $cardStyle = 'bg-zinc-50 border-zinc-200 opacity-75 grayscale';
                                        } elseif ($isPaid) {
                                            $cardStyle = 'bg-white border-zinc-200';
                                        } elseif ($isObligation) {
                                            $cardStyle = 'bg-rose-50/40 border-rose-300 hover:border-rose-400';
                                        } else {
                                            $cardStyle = 'bg-zinc-50 border-zinc-200 hover:border-zinc-300';
                                        }
                                        
                                        // Decidimos si es <label> (clicable) o <div> (no clicable)
                                        $wrapperTag = (!$isPaid && !$isCancelled) ? 'label' : 'div';
                                        $cursorClass = (!$isPaid && !$isCancelled) ? 'cursor-pointer' : '';
                                    @endphp
                                    
                                    <{{ $wrapperTag }} class="relative w-full block text-left p-2 pl-3 border rounded-lg shadow-sm overflow-hidden transition-all duration-200 {{ $cardStyle }} {{ $cursorClass }} group">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isCancelled ? 'bg-zinc-400' : $bgClass }}"></div>

                                        <div class="flex justify-between items-start">
                                            <div class="text-[10px] md:text-xs font-extrabold {{ $isCancelled ? 'text-zinc-500 line-through' : 'text-zinc-900' }}">
                                                {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                            </div>
                                            
                                            {{-- CHECKBOX DE PAGO (Todo el contenedor es clickable por ser label) --}}
                                            @if(!$isPaid && !$isCancelled)
                                                <input type="checkbox" 
                                                       value="{{ $s->id }}" 
                                                       data-date="{{ $cur }}"
                                                       data-class-name="{{ $s->workshop->name }}"
                                                       data-class-date="{{ \Carbon\Carbon::parse($cur)->format('d/m') }} a las {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}"
                                                       onchange="handlePaymentSelection(this)" 
                                                       class="payment-cb w-4 h-4 text-emerald-600 border-zinc-300 rounded focus:ring-emerald-500 cursor-pointer relative z-10" title="Seleccionar para pagar">
                                            @endif
                                        </div>
                                        
                                        <div class="text-[9px] font-bold leading-tight mt-1 line-clamp-1 {{ $isCancelled ? 'text-zinc-500' : 'text-zinc-800' }}">
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
                                                <span class="text-[8px] font-black uppercase tracking-wider text-zinc-500 bg-zinc-200 px-1 rounded">Disponible</span>
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
                            <button id="day-btn-{{ $cur }}" data-date="{{ $cur }}" onclick="openDayClasses('{{ $formattedDate }}', {{ $dayData }})" class="day-btn mt-auto mb-auto mx-1 py-2 px-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-xl flex flex-col items-center justify-center gap-1 group transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 md:hidden">
                                <span class="btn-num text-lg font-black text-indigo-600 leading-none group-hover:scale-110 transition-transform">{{ $classCount }}</span>
                                <span class="btn-text text-[9px] font-bold text-indigo-700 uppercase tracking-widest">{{ $classCount === 1 ? 'Clase' : 'Clases' }}</span>
                            </button>
                        @endif
                    </div>
                @endfor

                @php
                    $remainingCells = 7 - (($empty + $days) % 7);
                    if ($remainingCells == 7) $remainingCells = 0;
                @endphp
                @for ($i = 0; $i < $remainingCells; $i++) <div class="bg-zinc-50/50 min-h-[140px]"></div> @endfor
            </div>
        </div>

        {{-- HISTORIAL DE PAGOS --}}
        <div class="mt-12 mb-8">
            <h2 class="text-2xl font-black text-zinc-800 mb-6">Historial de Pagos</h2>
            
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50 uppercase text-[10px] font-black text-zinc-400 tracking-tighter">
                        <tr>
                            <th class="px-4 md:px-6 py-4 text-left">Pago y Fecha</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-left">Método</th>
                            <th class="hidden md:table-cell px-4 md:px-6 py-4 text-left">Clases Cubiertas</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-center">Comprobante</th>
                            <th class="px-4 md:px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($student->payments()->latest()->get() as $payment)
                            @php
                                $method = $payment->payment_method ?? 'manual';
                            @endphp
                            <tr class="hover:bg-zinc-50 transition-colors">
                                
                                {{-- 1. COLUMNA PRINCIPAL (Visible siempre, agrupa datos en celular) --}}
                                <td class="px-4 md:px-6 py-4">
                                    <div class="text-sm font-black text-emerald-600">${{ number_format($payment->amount, 0, ',', '.') }}</div>
                                    <div class="text-xs font-bold text-zinc-900 mt-0.5">{{ $payment->created_at->translatedFormat('d M Y') }}</div>
                                    
                                    {{-- Datos inyectados solo para celulares (sm:hidden) --}}
                                    <div class="sm:hidden mt-2 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider {{ $method === 'transferencia' ? 'text-teal-600' : ($method === 'efectivo' ? 'text-amber-600' : 'text-indigo-600') }}">
                                        {{ $method }}
                                    </div>
                                    <div class="md:hidden mt-1.5 text-[10px] text-zinc-500 leading-tight">
                                        {{ $payment->classSessions->count() }} {{ $payment->classSessions->count() === 1 ? 'Clase' : 'Clases' }}
                                    </div>
                                </td>
                                
                                {{-- 2. MÉTODO (Oculto en celulares) --}}
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-tighter {{ $method === 'transferencia' ? 'text-teal-600' : ($method === 'efectivo' ? 'text-amber-600' : 'text-indigo-600') }}">
                                        {{ $method }}
                                    </div>
                                </td>

                                {{-- 3. CLASES (Oculto en celulares y tablets pequeñas) --}}
                                <td class="hidden md:table-cell px-4 md:px-6 py-4">
                                    <ul class="text-[10px] font-bold text-zinc-500 uppercase list-disc list-inside space-y-0.5">
                                        @foreach($payment->classSessions as $paidSession)
                                            <li>{{ $paidSession->workshop->name }} ({{ \Carbon\Carbon::parse($paidSession->date)->format('d/m') }})</li>
                                        @endforeach
                                    </ul>
                                </td>

                                {{-- 4. COMPROBANTE (Oculto en celular, se manda a acciones) --}}
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 text-center">
                                    @if($payment->receipt_path)
                                        <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="text-[10px] font-black bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition uppercase border border-blue-200">Ver Foto</a>
                                    @else
                                        <span class="text-[10px] text-zinc-400 font-bold bg-zinc-50 px-3 py-1.5 rounded-lg border border-zinc-100">Sin foto</span>
                                    @endif
                                </td>

                                {{-- 5. ACCIONES --}}
                                <td class="px-4 md:px-6 py-4 text-right">
                                    <div class="flex flex-col items-end gap-2">
                                        {{-- Link de comprobante reubicado para celulares --}}
                                        @if($payment->receipt_path)
                                            <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="sm:hidden text-[10px] font-black text-blue-600 uppercase">Ver Comprobante</a>
                                        @endif
                                        
                                        <form action="{{ route('payments.destroy', ['subdomain' => request()->route('subdomain'), 'payment' => $payment->id]) }}" method="POST" class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('¿Estás segura de ANULAR este pago?')" class="text-rose-500 font-bold text-xs hover:text-rose-700 transition">
                                                Anular
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-sm font-bold text-zinc-400">Esta alumna/o aún no tiene pagos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL CON LA LISTA DE CLASES DEL DÍA (Para Móviles) --}}
    <div id="dayClassesModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" onclick="closeDayClasses()"></div>
        
        {{-- PASO 1: max-h-[90vh] flex flex-col --}}
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="dayModalCard">
            
            {{-- PASO 2: shrink-0 en el Header --}}
            <div class="px-5 py-4 border-b border-zinc-100 flex justify-between items-center bg-zinc-50 shrink-0">
                <div>
                    <h3 class="text-lg font-black text-zinc-900 tracking-tight">Clases del Día</h3>
                    <p id="modalDayDate" class="text-[11px] font-bold text-zinc-500 capitalize mt-0.5">Fecha</p>
                </div>
                <button onclick="closeDayClasses()" class="p-2 text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- PASO 3: overflow-y-auto flex-1 en la Lista (Cuerpo) --}}
            <div class="p-4 overflow-y-auto flex-1 space-y-3" id="modalClassesList">
                {{-- Contenido inyectado por JS --}}
            </div>
            
            {{-- PASO 2: shrink-0 en el Footer --}}
            <div class="p-4 border-t border-zinc-100 bg-white shrink-0">
                <button onclick="closeDayClasses()" class="w-full bg-zinc-100 text-zinc-700 font-bold py-2.5 rounded-xl hover:bg-zinc-200 transition-colors active:scale-95 text-sm">
                    Cerrar Lista
                </button>
            </div>
        </div>
    </div>

    {{-- BARRA FLOTANTE DE PAGO --}}
    <div id="bulkPaymentBar" class="fixed bottom-0 left-0 right-0 bg-white border-t border-zinc-200 p-4 flex justify-between items-center shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.1)] transform translate-y-full transition-transform duration-300 z-[70]">
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-emerald-100 text-emerald-600 font-black h-8 w-8 rounded-full flex items-center justify-center text-sm shadow-inner" id="selectedCount">0</div>
                <span class="text-sm font-bold text-zinc-700 hidden sm:inline">Clases seleccionadas para pago</span>
                <span class="text-sm font-bold text-zinc-700 sm:hidden">Seleccionadas</span>
            </div>
            <button onclick="openPaymentModal()" class="bg-zinc-900 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-zinc-800 transition-all active:scale-95 shadow-sm">
                Registrar Pago
            </button>
        </div>
    </div>

    {{-- MODAL DE PAGO MANUAL --}}
    <div id="paymentModal" class="fixed inset-0 z-[80] hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        {{-- PASO 1: max-h-[90vh] flex flex-col --}}
        <div class="bg-white rounded-3xl max-w-md w-full shadow-xl border border-zinc-100 transform transition-all relative max-h-[90vh] flex flex-col">
            
            {{-- PASO 2: shrink-0 en el Header --}}
            <div class="flex justify-between items-center p-6 md:p-8 pb-4 shrink-0 border-b border-zinc-100">
                <h3 class="text-xl font-black text-zinc-900">Registrar Pago</h3>
                <button type="button" onclick="closePaymentModal()" class="p-2 text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            {{-- PASO 3: overflow-y-auto flex-1 en el Formulario (Cuerpo) --}}
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
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Método de Pago *</label>
                    <select name="payment_method" required class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white">
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="efectivo">Efectivo</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Monto Pagado ($) *</label>
                    <input type="number" name="amount" placeholder="Ej: 25000" min="0" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                </div>
                
                <div class="mb-8">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Comprobante <span class="text-zinc-400 font-normal">(Opcional)</span></label>
                    <input type="file" name="receipt" accept="image/jpeg,image/png,image/jpg,application/pdf" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer">
                </div>

                {{-- PASO 2: shrink-0 en el Footer (Botones de acción, pegados abajo) --}}
                <div class="flex gap-3 pt-6 border-t border-zinc-100 shrink-0 bg-white sticky bottom-0">
                    <button type="button" onclick="closePaymentModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3.5 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3.5 rounded-xl shadow-sm hover:bg-emerald-700 active:scale-95 transition-all duration-200 text-sm flex items-center justify-center gap-2">
                        Confirmar Pago
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ==========================================
        // LÓGICA DE CHECKBOXES Y BARRA DE PAGO GLOBAL
        // ==========================================
        let selectedPaymentIds = new Set();

        function handlePaymentSelection(element = null) {
            // Sincroniza el Set Global de selecciones
            if (element) {
                const id = parseInt(element.value);
                if (element.checked) {
                    selectedPaymentIds.add(id);
                } else {
                    selectedPaymentIds.delete(id);
                }
            } else {
                document.querySelectorAll('.payment-cb').forEach(cb => {
                    const id = parseInt(cb.value);
                    if (cb.checked) selectedPaymentIds.add(id);
                    else selectedPaymentIds.delete(id);
                });
            }

            // Sincroniza todos los checkboxes en el DOM (Escritorio y Modal)
            document.querySelectorAll('.payment-cb, .payment-cb-modal').forEach(cb => {
                cb.checked = selectedPaymentIds.has(parseInt(cb.value));
            });

            // Actualiza el color verde de los botones agrupadores en el calendario
            updateMobileDayButtons();

            // Actualiza la Barra Flotante
            let bar = document.getElementById('bulkPaymentBar');
            let countSpan = document.getElementById('selectedCount');
            
            if(selectedPaymentIds.size > 0) {
                bar.classList.remove('translate-y-full');
                countSpan.innerText = selectedPaymentIds.size;
            } else {
                bar.classList.add('translate-y-full');
            }
        }

        function updateMobileDayButtons() {
            // Comprobamos qué fechas tienen al menos una clase seleccionada
            const activeDates = new Set();
            document.querySelectorAll('.payment-cb:checked').forEach(cb => {
                activeDates.add(cb.getAttribute('data-date'));
            });

            // Aplicamos los estilos reactivos a los botones correspondientes
            document.querySelectorAll('.day-btn').forEach(btn => {
                const date = btn.getAttribute('data-date');
                const numEl = btn.querySelector('.btn-num');
                const textEl = btn.querySelector('.btn-text');

                if (activeDates.has(date)) {
                    // Estado Activo (Verde)
                    btn.classList.replace('bg-indigo-50', 'bg-emerald-50');
                    btn.classList.replace('border-indigo-100', 'border-emerald-200');
                    btn.classList.replace('hover:bg-indigo-100', 'hover:bg-emerald-100');
                    numEl.classList.replace('text-indigo-600', 'text-emerald-600');
                    textEl.classList.replace('text-indigo-700', 'text-emerald-700');
                } else {
                    // Estado Normal (Índigo)
                    btn.classList.replace('bg-emerald-50', 'bg-indigo-50');
                    btn.classList.replace('border-emerald-200', 'border-indigo-100');
                    btn.classList.replace('hover:bg-emerald-100', 'hover:bg-indigo-100');
                    numEl.classList.replace('text-emerald-600', 'text-indigo-600');
                    textEl.classList.replace('text-emerald-700', 'text-indigo-700');
                }
            });
        }

        // ==========================================
        // LÓGICA DEL MODAL DE PAGO FINAL
        // ==========================================
        function openPaymentModal() {
            if(selectedPaymentIds.size === 0) return;

            // 1. Mostrar conteo
            document.getElementById('modalClassCountText').innerText = selectedPaymentIds.size === 1 ? '1 Clase' : `${selectedPaymentIds.size} Clases`;

            // 2. Llenar los inputs ocultos para el backend y la lista visible para el usuario
            let hiddenContainer = document.getElementById('hiddenSessionInputs');
            let listContainer = document.getElementById('modalClassesSelectedList');
            
            hiddenContainer.innerHTML = ''; 
            let listHtml = '<ul class="text-[11px] text-zinc-600 text-left bg-white p-3 rounded-lg border border-emerald-100 space-y-1.5 list-none">';
            
            // Extraer la información desde los checkboxes de escritorio (que siempre están en el DOM)
            document.querySelectorAll('.payment-cb:checked').forEach(cb => {
                // Inputs Ocultos
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'session_ids[]';
                input.value = cb.value;
                hiddenContainer.appendChild(input);

                // Lista Visible
                const className = cb.getAttribute('data-class-name');
                const classDate = cb.getAttribute('data-class-date');
                listHtml += `
                    <li class="flex items-start gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span class="font-bold text-zinc-900">${className} <span class="font-normal text-zinc-500 block sm:inline">(${classDate})</span></span>
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
            document.getElementById('modalDayDate').innerText = dateStr;
            listContainer.innerHTML = '';

            classesArray.forEach(cls => {
                let cardStyle = '';
                if (cls.isCancelled) cardStyle = 'bg-zinc-50 border-zinc-200 opacity-75 grayscale';
                else if (cls.isPaid) cardStyle = 'bg-white border-zinc-200';
                else if (cls.isObligation) cardStyle = 'bg-rose-50/40 border-rose-300';
                else cardStyle = 'bg-zinc-50 border-zinc-200';

                const titleClass = cls.isCancelled ? 'text-zinc-500 line-through' : 'text-zinc-900';
                const bgClass = cls.isCancelled ? 'bg-zinc-400' : cls.bgClass;

                let badges = '';
                if (cls.isCancelled) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded border border-rose-200">Anulada</span>`;
                else if (cls.isPaid) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">Pagada</span>`;
                else if (cls.isObligation) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-rose-600 bg-rose-100 px-1.5 py-0.5 rounded border border-rose-200">Debe</span>`;
                else badges += `<span class="text-[8px] font-black uppercase tracking-wider text-zinc-500 bg-zinc-200 px-1.5 py-0.5 rounded">Disponible</span>`;

                if (cls.hasAttendance && !cls.isCancelled) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100 ml-1">Presente</span>`;
                else if (cls.isEnrolled && !cls.isCancelled) badges += `<span class="text-[8px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100 ml-1">Inscrita</span>`;

                let checkboxHtml = '';
                let wrapperTag = 'div';
                let cursorClass = '';

                // Hacemos que todo el contenedor móvil también sea un label clickeable
                if (!cls.isPaid && !cls.isCancelled) {
                    wrapperTag = 'label';
                    cursorClass = 'cursor-pointer hover:border-zinc-400 hover:shadow-md transition-all duration-200';
                    const isChecked = selectedPaymentIds.has(cls.id) ? 'checked' : '';
                    checkboxHtml = `<input type="checkbox" value="${cls.id}" data-date="${cls.dateRaw}" data-class-name="${cls.name}" data-class-date="${cls.dateFormatted}" onchange="handlePaymentSelection(this)" class="payment-cb-modal w-5 h-5 text-emerald-600 border-zinc-300 rounded focus:ring-emerald-500 cursor-pointer relative z-10" ${isChecked}>`;
                }

                listContainer.innerHTML += `
                    <${wrapperTag} class="relative w-full block text-left p-3 pl-4 border rounded-xl shadow-sm overflow-hidden ${cardStyle} ${cursorClass}">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 ${bgClass}"></div>
                        <div class="flex justify-between items-start mb-1">
                            <div class="text-sm font-extrabold ${titleClass}">${cls.time} hrs</div>
                            ${checkboxHtml}
                        </div>
                        <div class="text-sm font-bold leading-tight mt-1 mb-2 ${cls.isCancelled ? 'text-zinc-500' : 'text-zinc-800'}">
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
        });
    </script>
</x-app-layout>
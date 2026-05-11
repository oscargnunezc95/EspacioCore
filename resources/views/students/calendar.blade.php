<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />
        <div class="mt-8">
            <x-studio-header 
                title="Perfil de Alumna" 
                :breadcrumbs="[
                    ['name' => 'alumnas/os', 'url' => route('students.index', ['subdomain' => request()->route('subdomain')])],
                    ['name' => $student->name]
                ]"
            >
            </x-studio-header>
        </div>
    </x-slot>

    @php
        $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
    @endphp
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-24">
        
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-2xl shadow-sm border border-zinc-200">
            <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id, 'month' => $prevMonth]) }}" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm">&larr; Anterior</a>
            <h2 class="text-xl md:text-2xl font-black text-zinc-800 capitalize">{{ $monthDate->translatedFormat('F Y') }}</h2>
            <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id, 'month' => $nextMonth]) }}" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 rounded-xl font-bold text-zinc-600 transition text-sm">Siguiente &rarr;</a>
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
                    @endphp
                    
                    <div class="bg-white min-h-[140px] p-1.5 md:p-2 transition {{ $isToday ? 'ring-2 ring-inset ring-zinc-900 bg-zinc-50/30' : '' }}">
                        <span class="text-sm font-bold flex items-center justify-center h-6 w-6 md:h-7 md:w-7 rounded-full mb-2 {{ $isToday ? 'bg-zinc-900 text-white' : 'text-zinc-500' }}">{{ $day }}</span>
                        
                        <div class="space-y-1.5">
                            @foreach($sessionsInDay as $s)
                                @php 
                                    $c = $s->workshop->color ?? 'blue'; 
                                    $bgClass = match($c) {
                                        'emerald' => 'bg-emerald-500', 'rose' => 'bg-rose-500', 'purple' => 'bg-purple-500',
                                        'amber' => 'bg-amber-500', 'indigo' => 'bg-indigo-500', 'teal' => 'bg-teal-500',
                                        'cyan' => 'bg-cyan-500', 'fuchsia' => 'bg-fuchsia-500', 'slate' => 'bg-slate-500',
                                        default => 'bg-blue-500',
                                    };
                                    
                                    // CORRECCIÓN CRÍTICA: Cambiado $session por $s
                                    $imageUrl = $s->workshop->image_path 
                                        ? asset('storage/' . $s->workshop->image_path) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->name).'&color=4f46e5&background=e0e7ff&size=128';

                                    $studioLogo = $s->workshop->studio->icon_path ?? $s->workshop->studio->logo_path ?? null;
                                    $studioImageUrl = $studioLogo 
                                        ? asset('storage/' . $studioLogo) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($s->workshop->studio->name).'&color=ffffff&background=18181b&size=128';

                                    // LÓGICA DE ESTADOS
                                    $isEnrolled = $s->students->contains('id', $student->id);
                                    $hasAttendance = $s->attendances->contains('student_id', $student->id);
                                    $isPaid = in_array($s->id, $paidSessionIds);
                                    $isCancelled = $s->is_cancelled ?? ($s->status === 'cancelled') ?? false;
                                    $isObligation = $isEnrolled || $hasAttendance;

                                    // Estilos Dinámicos (Incluyendo Canceladas)
                                    if ($isCancelled) {
                                        $cardStyle = 'bg-zinc-50 border-zinc-200 opacity-75 grayscale';
                                    } elseif ($isPaid) {
                                        $cardStyle = 'bg-white border-zinc-200';
                                    } elseif ($isObligation) {
                                        $cardStyle = 'bg-rose-50/40 border-rose-300 hover:border-rose-500';
                                    } else {
                                        $cardStyle = 'bg-zinc-50 border-zinc-200 hover:border-zinc-400 hover:shadow-md';
                                    }
                                @endphp
                                
                                <button onclick="openClassDetails(this)"
                                        data-title="{{ $s->workshop->name }}"
                                        data-studio="{{ $s->workshop->studio->name }}"
                                        data-studio-image="{{ $studioImageUrl }}"
                                        data-teacher="{{ $s->workshop->teacher->name ?? 'Por asignar' }}"
                                        data-time="{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}"
                                        data-date="{{ \Carbon\Carbon::parse($s->date)->translatedFormat('l d \d\e F') }}"
                                        data-subdomain="{{ $s->workshop->studio->subdomain }}"
                                        data-image="{{ $imageUrl }}" 
                                        data-address="{{ $s->workshop->studio->address ?? 'Dirección no especificada' }}"
                                        data-status="{{ $isPaid ? 'paid' : 'unpaid' }}"
                                        data-cancelled="{{ $isCancelled ? 'true' : 'false' }}"
                                        class="relative w-full text-left p-2 md:p-2.5 pl-3 border rounded-lg shadow-sm overflow-hidden transition-all duration-200 {{ $cardStyle }} group focus:outline-none focus:ring-2 focus:ring-zinc-900 active:scale-95">
                                    
                                    {{-- Barra de Color --}}
                                    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isCancelled ? 'bg-zinc-400' : $bgClass }}"></div>

                                    <div class="flex justify-between items-start">
                                        <div class="text-[10px] md:text-xs font-extrabold {{ $isCancelled ? 'text-zinc-500 line-through' : 'text-zinc-900' }}">
                                            {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                        </div>
                                        
                                        {{-- Checkbox de cobro: NO MOSTRAR si está pagado o cancelado --}}
                                        @if(!$isPaid && !$isCancelled)
                                            <input type="checkbox" value="{{ $s->id }}" onchange="handlePaymentSelection()" class="payment-cb w-4 h-4 text-emerald-600 border-zinc-300 rounded focus:ring-emerald-500 cursor-pointer relative z-10" title="Seleccionar para pagar">
                                        @endif
                                    </div>
                                    
                                    <div class="text-[9px] md:text-[10px] font-bold leading-tight mt-1 line-clamp-1 {{ $isCancelled ? 'text-zinc-500' : 'text-zinc-800' }}">
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
                                </button>
                            @endforeach
                        </div>
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
                            <th class="px-6 py-4 text-left">Fecha de Pago</th>
                            <th class="px-6 py-4 text-left">Monto</th>
                            <th class="px-6 py-4 text-left">Clases Cubiertas</th>
                            <th class="px-6 py-4 text-center">Comprobante</th>
                            <th class="px-6 py-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($student->payments()->latest()->get() as $payment)
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-zinc-900">
                                    {{ $payment->created_at->translatedFormat('d M Y - H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-emerald-600">
                                    ${{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    <ul class="text-[10px] font-bold text-zinc-500 uppercase list-disc list-inside">
                                        @foreach($payment->classSessions as $paidSession)
                                            <li>{{ $paidSession->workshop->name }} ({{ \Carbon\Carbon::parse($paidSession->date)->format('d/m') }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($payment->receipt_path)
                                        <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="text-[10px] font-black bg-blue-50 text-blue-600 px-3 py-1 rounded hover:bg-blue-100 transition uppercase border border-blue-200">Ver Foto</a>
                                    @else
                                        <span class="text-[10px] text-zinc-400 font-bold">Sin foto</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('payments.destroy', ['subdomain' => request()->route('subdomain'), 'payment' => $payment->id]) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Estás segura de ANULAR este pago? Las clases volverán a marcarse como deuda o disponibles.')" class="text-rose-500 font-bold text-sm hover:text-rose-700 hover:underline transition">
                                            Anular Pago
                                        </button>
                                    </form>
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

    {{-- BARRA FLOTANTE --}}
    <div id="bulkPaymentBar" class="fixed bottom-0 left-0 right-0 bg-white border-t border-zinc-200 p-4 flex justify-between items-center shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.1)] transform translate-y-full transition-transform duration-300 z-40">
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-emerald-100 text-emerald-600 font-black h-8 w-8 rounded-full flex items-center justify-center text-sm" id="selectedCount">0</div>
                <span class="text-sm font-bold text-zinc-700">Clases seleccionadas para pago</span>
            </div>
            <button onclick="openPaymentModal()" class="bg-zinc-900 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-zinc-800 transition-all active:scale-95">
                Registrar Pago
            </button>
        </div>
    </div>

    {{-- MODAL DE PAGO --}}
    <div id="paymentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-md w-full shadow-xl border border-zinc-100 transform transition-all relative">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-zinc-900">Registrar Pago</h3>
                <button type="button" onclick="closePaymentModal()" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="{{ route('payments.store', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="hiddenSessionInputs"></div>
                
                <div class="mb-4">
                    <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-4 text-center">
                        <span class="block text-xs font-bold text-zinc-500 uppercase tracking-widest mb-1">Pagando</span>
                        <span class="text-xl font-black text-zinc-900" id="modalClassCountText">0 Clases</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-zinc-700 mb-2">Monto Pagado ($) *</label>
                    <input type="number" name="amount" placeholder="Ej: 25000" min="0" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-bold text-zinc-700 mb-2">Comprobante <span class="text-zinc-400 font-normal">(Opcional)</span></label>
                    <input type="file" name="receipt" accept="image/jpeg,image/png,image/jpg,application/pdf" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer">
                </div>

                <div class="flex gap-3 pt-2 border-t border-zinc-100">
                    <button type="button" onclick="closePaymentModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-emerald-700 active:scale-95 transition-all duration-200 text-sm">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DETALLES DE CLASE --}}
    <div id="classDetailsModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" onclick="closeClassDetails()"></div>
        
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="modalCard">
            <div class="h-32 sm:h-48 w-full bg-zinc-200 relative">
                <img id="modalImage" src="" alt="Cover" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/60 to-transparent"></div>
                <button onclick="closeClassDetails()" class="absolute top-4 right-4 p-2 text-zinc-700 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full transition-colors focus:outline-none shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <div class="absolute bottom-4 left-6 flex items-center gap-3">
                    <img id="modalStudioImage" src="" alt="Studio Icon" class="w-8 h-8 rounded-lg object-cover ring-2 ring-white/30 shadow-sm bg-zinc-900">
                    <span id="modalStudio" class="px-2.5 py-1 bg-white/20 backdrop-blur-md text-white border border-white/30 text-[10px] font-black rounded-lg tracking-widest uppercase shadow-sm">Estudio</span>
                </div>
            </div>

            <div id="modalStatusBanner" class="px-6 py-3 border-b flex items-center gap-2 font-bold text-sm"></div>

            <div class="p-6 md:p-8 overflow-y-auto flex-1">
                <div class="mb-6">
                    <h3 id="modalTitle" class="text-2xl font-black text-zinc-900 leading-tight">Clase</h3>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p id="modalDate" class="text-sm font-bold text-zinc-900 capitalize">Fecha</p>
                            <p id="modalTime" class="text-xs font-medium text-zinc-500">Hora</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-emerald-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Profesor/a</p>
                            <p id="modalTeacher" class="text-sm font-bold text-zinc-900">Nombre</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 text-zinc-600 bg-zinc-50 p-3 rounded-2xl border border-zinc-100">
                        <div class="bg-white p-2.5 rounded-xl shadow-sm border border-zinc-100 text-rose-500 mt-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">Ubicación</p>
                            <p id="modalAddress" class="text-sm font-bold text-zinc-900 mb-2 leading-tight">Dirección del Estudio</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const paymentBackdrop = document.getElementById('paymentModal');
        let isPaymentMouseDown = false;

        paymentBackdrop.addEventListener('mousedown', function(e) {
            isPaymentMouseDown = (e.target === paymentBackdrop);
        });

        paymentBackdrop.addEventListener('mouseup', function(e) {
            if (isPaymentMouseDown && e.target === paymentBackdrop) {
                closePaymentModal();
            }
            isPaymentMouseDown = false;
        });

        function handlePaymentSelection() {
            let selected = document.querySelectorAll('.payment-cb:checked');
            let bar = document.getElementById('bulkPaymentBar');
            let countSpan = document.getElementById('selectedCount');
            
            if(selected.length > 0) {
                bar.classList.remove('translate-y-full');
                countSpan.innerText = selected.length;
            } else {
                bar.classList.add('translate-y-full');
            }
        }

        function openPaymentModal() {
            let selected = document.querySelectorAll('.payment-cb:checked');
            if(selected.length === 0) return;

            document.getElementById('modalClassCountText').innerText = selected.length === 1 ? '1 Clase' : `${selected.length} Clases`;

            let hiddenContainer = document.getElementById('hiddenSessionInputs');
            hiddenContainer.innerHTML = ''; 
            
            selected.forEach(cb => {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'session_ids[]';
                input.value = cb.value;
                hiddenContainer.appendChild(input);
            });

            document.body.style.overflow = 'hidden';
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.body.style.overflow = '';
            document.getElementById('paymentModal').classList.add('hidden');
        }

        const modal = document.getElementById('classDetailsModal');
        const modalCard = document.getElementById('modalCard');

        function openClassDetails(button) {
            document.getElementById('modalTitle').innerText = button.getAttribute('data-title');
            document.getElementById('modalStudio').innerText = button.getAttribute('data-studio');
            document.getElementById('modalTeacher').innerText = button.getAttribute('data-teacher');
            document.getElementById('modalDate').innerText = button.getAttribute('data-date');
            document.getElementById('modalTime').innerText = button.getAttribute('data-time') + ' hrs';
            
            document.getElementById('modalImage').src = button.getAttribute('data-image');
            document.getElementById('modalStudioImage').src = button.getAttribute('data-studio-image');
            
            const address = button.getAttribute('data-address');
            document.getElementById('modalAddress').innerText = address;

            const statusBanner = document.getElementById('modalStatusBanner');
            const isPaid = button.getAttribute('data-status') === 'paid';
            const isCancelled = button.getAttribute('data-cancelled') === 'true';
            
            if (isCancelled) {
                statusBanner.className = 'px-6 py-3 border-b flex items-center gap-2 font-bold text-sm bg-rose-100 border-rose-200 text-rose-800';
                statusBanner.innerHTML = `
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Esta clase ha sido cancelada por el estudio.
                `;
            } else if (isPaid) {
                statusBanner.className = 'px-6 py-3 border-b flex items-center gap-2 font-bold text-sm bg-emerald-50 border-emerald-100 text-emerald-700';
                statusBanner.innerHTML = `
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Clase Pagada
                `;
            } else {
                statusBanner.className = 'px-6 py-3 border-b flex items-center gap-2 font-bold text-sm bg-rose-50 border-rose-100 text-rose-700';
                statusBanner.innerHTML = `
                    <span class="flex h-3 w-3 relative shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                    Pago pendiente
                `;
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalCard.classList.remove('scale-95', 'opacity-0');
                modalCard.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeClassDetails() {
            modalCard.classList.remove('scale-100', 'opacity-100');
            modalCard.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }
    </script>
</x-app-layout>
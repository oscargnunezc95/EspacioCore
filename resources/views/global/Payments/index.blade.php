<x-app-layout>
    <div class="py-12 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-24">
        
        {{-- Encabezado --}}
        <div class="mb-10">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Historial de Pagos</h1>
            <p class="mt-2 text-zinc-500 text-lg">Consulta tus transacciones y los detalles de tus clases pagadas.</p>
        </div>

        {{-- Contenedor Principal --}}
        <div class="bg-white rounded-3xl shadow-sm border border-zinc-200 overflow-hidden">
            
            @if($payments->isEmpty())
                <div class="py-24 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-zinc-50 border border-zinc-100 mb-6">
                        <svg class="w-10 h-10 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900">No hay pagos registrados</h3>
                    <p class="text-zinc-500 mt-2 max-w-xs mx-auto">Cuando realices tu primera reserva pagada, aparecerá aquí.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-50/80 border-b border-zinc-200 text-[11px] uppercase tracking-widest text-zinc-500 font-black">
                                <th class="px-6 py-5">Fecha y Hora</th>
                                <th class="px-6 py-5">Estudio</th>
                                <th class="px-6 py-5">Método</th>
                                <th class="px-6 py-5 text-right">Monto</th>
                                <th class="px-6 py-5 text-center">Estado</th>
                                <th class="px-6 py-5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($payments as $payment)
                                @php
                                    $studio = $payment->student->studio;
                                    $studioLogo = $studio->logo_path ?? $studio->image_path;
                                    $studioAvatar = $studioLogo 
                                        ? asset('storage/' . $studioLogo) 
                                        : 'https://ui-avatars.com/api/?name='.urlencode($studio->name).'&color=4f46e5&background=e0e7ff';
                                @endphp
                                <tr class="hover:bg-zinc-50/50 transition-colors group">
                                    {{-- Fecha --}}
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="text-sm font-bold text-zinc-900">{{ $payment->created_at->translatedFormat('d M, Y') }}</div>
                                        <div class="text-[11px] text-zinc-400 font-medium uppercase">{{ $payment->created_at->format('H:i') }} hrs</div>
                                    </td>
                                    
                                    {{-- Estudio (Con Imagen) --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $studioAvatar }}" class="w-9 h-9 rounded-xl object-cover border border-zinc-100 shadow-sm shrink-0">
                                            <span class="text-sm font-bold text-zinc-700 truncate max-w-[150px]">{{ $studio->name }}</span>
                                        </div>
                                    </td>
                                    
                                    {{-- Método --}}
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="flex items-center gap-2 text-xs font-bold text-zinc-500 uppercase tracking-tighter">
                                            <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                            {{ $payment->payment_method ?? 'Transacción Online' }}
                                        </div>
                                    </td>
                                    
                                    {{-- Monto --}}
                                    <td class="px-6 py-5 whitespace-nowrap text-right">
                                        <span class="text-base font-black text-zinc-900">${{ number_format($payment->amount, 0, ',', '.') }}</span>
                                    </td>
                                    
                                    {{-- Estado --}}
                                    <td class="px-6 py-5 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-teal-50 text-teal-700 border border-teal-100">
                                            <div class="w-1.5 h-1.5 rounded-full bg-teal-500"></div>
                                            Aprobado
                                        </span>
                                    </td>

                                    {{-- Botón Detalle --}}
                                    <td class="px-6 py-5 text-right">
                                        <button onclick="openPaymentDetail({{ $payment->id }})" class="inline-flex items-center gap-2 text-xs font-black text-indigo-600 bg-indigo-50 px-4 py-2 rounded-xl hover:bg-indigo-600 hover:text-white transition-all active:scale-95 uppercase tracking-widest">
                                            Ver Detalle
                                        </button>
                                    </td>
                                </tr>

                                {{-- MODAL DE DETALLE (Inyectado por cada fila para facilidad) --}}
                                <div id="payment-modal-{{ $payment->id }}" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
                                    <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-card flex flex-col max-h-[85vh]">
                                        
                                        {{-- Header Modal --}}
                                        <div class="bg-zinc-900 p-8 text-white relative">
                                            
                                            <p class="text-teal-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Comprobante de Pago</p>
                                            <h3 class="text-2xl font-black italic tracking-tighter">ID #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</h3>
                                            <div class="mt-4 flex items-center gap-3">
                                                <img src="{{ $studioAvatar }}" class="w-8 h-8 rounded-lg object-cover ring-2 ring-white/20">
                                                <span class="font-bold text-sm text-zinc-300">{{ $studio->name }}</span>
                                            </div>
                                        </div>
                                        
                                        {{-- Lista de Clases --}}
                                        <div class="p-8 overflow-y-auto flex-1 bg-zinc-50/50">
                                            <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-6">Sesiones incluidas en este pago</p>
                                            <ul class="space-y-4">
                                                @foreach($payment->classSessions as $session)
                                                    @php
                                                        $wsImg = $session->workshop->image_path;
                                                        $wsAvatar = $wsImg 
                                                            ? asset('storage/'.$wsImg) 
                                                            : 'https://ui-avatars.com/api/?name='.urlencode($session->workshop->name).'&background=e0e7ff&color=4338ca';
                                                    @endphp
                                                    <li class="flex items-center gap-4 bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm group hover:border-indigo-300 transition-colors">
                                                        <img src="{{ $wsAvatar }}" class="w-14 h-14 rounded-xl object-cover border-2 border-white shadow-sm shrink-0">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="font-black text-zinc-900 truncate">{{ $session->workshop->name }}</p>
                                                            <div class="flex items-center gap-2 mt-1 text-xs font-bold text-zinc-500">
                                                                <svg class="w-3.5 h-3.5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                <span class="capitalize">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d M') }}</span>
                                                                <span class="text-zinc-300">•</span>
                                                                <span>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        {{-- Footer Modal --}}
                                        <div class="p-8 bg-white border-t border-zinc-100 flex justify-between items-center">
                                            <div>
                                                <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-1">Monto Total</p>
                                                <p class="text-3xl font-black text-zinc-900 italic tracking-tighter">${{ number_format($payment->amount, 0, ',', '.') }}</p>
                                            </div>
                                            <button onclick="closePaymentDetail({{ $payment->id }})" class="bg-zinc-100 text-zinc-600 px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-zinc-200 transition-all active:scale-95">
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($payments->hasPages())
                    <div class="px-6 py-6 border-t border-zinc-100 bg-zinc-50/50">
                        {{ $payments->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- LÓGICA DE APERTURA/CIERRE DE MODAL --}}
    <script>
        function openPaymentDetail(id) {
            const modal = document.getElementById(`payment-modal-${id}`);
            const card = modal.querySelector('.modal-card');
            
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closePaymentDetail(id) {
            const modal = document.getElementById(`payment-modal-${id}`);
            const card = modal.querySelector('.modal-card');
            
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }

        // Cerrar con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                const openModal = document.querySelector('[id^="payment-modal-"]:not(.hidden)');
                if (openModal) {
                    const id = openModal.id.split('-').pop();
                    closePaymentDetail(id);
                }
            }
        });
    </script>
</x-app-layout>
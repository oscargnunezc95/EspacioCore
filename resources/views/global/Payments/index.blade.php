<x-app-layout>

    <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24" x-data="{ activeTab: 'pagos' }">
        
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Historial de Pagos</h1>
            <p class="mt-3 text-zinc-500 font-medium text-base md:text-lg">Consulta tus transacciones y los detalles de las clases pagadas por ti y tu familia.</p>
        </div>

        

        {{-- TABS --}}
        <div class="flex space-x-1 bg-zinc-100/80 p-1 rounded-xl w-fit mb-8 border border-zinc-200 mx-auto">
            <button @click="activeTab = 'pagos'" 
                    :class="activeTab === 'pagos' ? 'bg-white shadow-sm text-zinc-900' : 'text-zinc-500 hover:text-zinc-700'"
                    class="px-6 py-2 rounded-lg font-bold transition-all duration-200 text-sm">
                Pagos Realizados
            </button>
            <button @click="activeTab = 'ingresos'" 
                    :class="activeTab === 'ingresos' ? 'bg-white shadow-sm text-emerald-700' : 'text-zinc-500 hover:text-zinc-700'"
                    class="px-6 py-2 rounded-lg font-bold transition-all duration-200 text-sm">
                Ingresos Recibidos
                @if($teacherPayments->total() > 0)
                    <span class="ml-1.5 text-xs bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">{{ $teacherPayments->total() }}</span>
                @endif
            </button>
        </div>

        {{-- BANNER DE MERCADO PAGO (visible siempre) --}}
        @if(!$mpLinked)
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 max-w-2xl mx-auto">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-800">Vincula Mercado Pago para recibir tus pagos</p>
                        <p class="text-xs text-amber-600 mt-0.5">Los estudios necesitan tu cuenta vinculada para pagarte directamente.</p>
                        <p class="text-xs text-amber-500 mt-1 italic">
                            ⚡ Si ya iniciaste sesión en Mercado Pago en este navegador, la vinculación será instantánea (solo verás una recarga de la página).
                        </p>
                    </div>
                </div>
                <a href="{{ route('mp.oauth.teacher.redirect') }}"
                   class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    Vincular Mercado Pago
                </a>
            </div>
        @else
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 max-w-2xl mx-auto">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-800">Cuenta de Mercado Pago vinculada</p>
                        <p class="text-xs text-emerald-600 mt-0.5">Los estudios pueden pagarte directamente a tu cuenta.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('global.payments.mp.disconnect') }}" class="shrink-0">
                    @csrf @method('DELETE')
                    <button type="submit" 
                            onclick="return confirm('¿Desvincular tu cuenta de Mercado Pago? Los estudios ya no podrán pagarte directamente hasta que la vuelvas a vincular.')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-100 hover:bg-rose-200 text-rose-700 rounded-xl text-sm font-bold transition-all duration-200 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Desvincular
                    </button>
                </form>
            </div>
        @endif
        
        {{-- TAB 1: PAGOS REALIZADOS --}}
        <div x-show="activeTab === 'pagos'" x-cloak>
            <div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-zinc-200 overflow-hidden">
                
                @if($payments->isEmpty())
                    <div class="py-24 px-6 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-zinc-50 border border-zinc-100 mb-6">
                            <svg class="w-10 h-10 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900">No hay pagos registrados</h3>
                        <p class="text-zinc-500 mt-2 max-w-xs mx-auto">Cuando realices tu primera reserva pagada, aparecerá aquí.</p>
                    </div>
                @else
                    <div class="w-full">
                        <table class="w-full text-left border-collapse">
                            
                            <thead class="hidden md:table-header-group">
                                <tr class="bg-zinc-50/80 border-b border-zinc-200 text-[11px] uppercase tracking-widest text-zinc-500 font-black">
                                    <th class="px-6 py-5">Fecha y Hora</th>
                                    <th class="px-6 py-5">Estudio y Alumno</th>
                                    <th class="px-6 py-5">Método de Pago</th>
                                    <th class="px-6 py-5 text-right">Monto</th>
                                    <th class="px-6 py-5 text-right">Acción</th>
                                </tr>
                            </thead>
                            
                            <tbody class="block md:table-row-group">
                                @foreach($payments as $payment)
                                    @php
                                        $studio = $payment->student->studio;
                                        $studioLogo = $studio->icon_path ?? $studio->logo_path;
                                        $studioAvatar = $studioLogo 
                                            ? asset('storage/' . $studioLogo) 
                                            : 'https://ui-avatars.com/api/?name='.urlencode($studio->name).'&color=4f46e5&background=e0e7ff';
                                        
                                        $method = $payment->payment_method ?? 'transferencia';
                                        $studentName = $payment->student->first_name;
                                    @endphp
                                    
                                    <tr class="flex flex-col md:table-row p-5 md:p-0 border-b border-zinc-200 last:border-0 hover:bg-zinc-50/50 transition-colors group gap-3 md:gap-0">
                                        
                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5 md:whitespace-nowrap">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Fecha y Hora</span>
                                            <div class="text-right md:text-left">
                                                <div class="text-sm font-bold text-zinc-900">{{ $payment->created_at->translatedFormat('d M, Y') }}</div>
                                                <div class="text-[11px] text-zinc-400 font-medium uppercase">{{ $payment->created_at->format('H:i') }} hrs</div>
                                            </div>
                                        </td>
                                        
                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Estudio / Alumno</span>
                                            <div class="flex items-center gap-2 md:gap-3 text-right md:text-left">
                                                <img src="{{ $studioAvatar }}" class="w-7 h-7 md:w-9 md:h-9 rounded-lg md:rounded-xl object-cover border border-zinc-100 shadow-sm shrink-0">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-zinc-700 truncate max-w-[150px] leading-tight">{{ $studio->name }}</span>
                                                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mt-0.5">
                                                        👤 {{ $studentName }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5 md:whitespace-nowrap">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Método</span>
                                            @if(in_array($method, ['online', 'pasarela de pago', 'mercadopago']))
                                                <div class="flex items-center gap-1.5 md:gap-2 text-xs font-bold text-indigo-600 uppercase tracking-tighter">
                                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                    Web / App
                                                </div>
                                            @elseif($method === 'transferencia')
                                                <div class="flex items-center gap-1.5 md:gap-2 text-xs font-bold text-teal-600 uppercase tracking-tighter">
                                                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                                    Transferencia
                                                </div>
                                            @else
                                                <div class="flex items-center gap-1.5 md:gap-2 text-xs font-bold text-amber-600 uppercase tracking-tighter">
                                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08-.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Efectivo
                                                </div>
                                            @endif
                                        </td>
                                        
                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5 md:whitespace-nowrap md:text-right mt-2 md:mt-0 pt-3 md:pt-0 border-t border-zinc-100 md:border-none">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Total Pagado</span>
                                            <span class="text-lg md:text-base font-black text-zinc-900">${{ number_format($payment->amount, 0, ',', '.') }}</span>
                                        </td>

                                        <td class="md:table-cell md:px-6 md:py-5 md:text-right mt-1 md:mt-0">
                                            <button onclick="openPaymentDetail({{ $payment->id }})" class="w-full md:w-auto inline-flex justify-center items-center gap-2 text-xs font-black text-indigo-600 bg-indigo-50 px-4 py-3 md:py-2 rounded-xl hover:bg-indigo-600 hover:text-white transition-all active:scale-95 uppercase tracking-widest">
                                                Ver Detalle
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- MODAL DE DETALLE --}}
                                    <div id="payment-modal-{{ $payment->id }}" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
                                        <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-card flex flex-col max-h-[85vh]">
                                            <div class="bg-zinc-900 p-6 md:p-8 text-white relative shrink-0">
                                                <p class="text-teal-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Comprobante de Pago</p>
                                                <h3 class="text-2xl font-black italic tracking-tighter">ID #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</h3>
                                                <div class="mt-4 flex items-center gap-3">
                                                    <img src="{{ $studioAvatar }}" class="w-8 h-8 rounded-lg object-cover ring-2 ring-white/20">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-sm text-zinc-300 leading-tight">{{ $studio->name }}</span>
                                                        <span class="text-[10px] text-zinc-400 uppercase tracking-widest">A nombre de: {{ $studentName }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-6 md:p-8 overflow-y-auto flex-1 bg-zinc-50/50">
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
                                                                    <span class="text-zinc-300">&bull;</span>
                                                                    <span>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="p-6 md:p-8 bg-white border-t border-zinc-100 flex justify-between items-center shrink-0">
                                                <div>
                                                    <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-1">Monto Total</p>
                                                    <p class="text-3xl font-black text-zinc-900 italic tracking-tighter">${{ number_format($payment->amount, 0, ',', '.') }}</p>
                                                </div>
                                                <button onclick="closePaymentDetail({{ $payment->id }})" class="bg-zinc-100 text-zinc-600 px-6 md:px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-zinc-200 transition-all active:scale-95">
                                                    Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-6 py-6 border-t border-zinc-100 bg-zinc-50/50">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB 2: INGRESOS RECIBIDOS --}}
        <div x-show="activeTab === 'ingresos'" x-cloak>
            <div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-zinc-200 overflow-hidden">
                
                @if($teacherPayments->isEmpty())
                    <div class="py-24 px-6 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-50 border border-emerald-100 mb-6">
                            <svg class="w-10 h-10 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900">Sin ingresos aún</h3>
                        <p class="text-zinc-500 mt-2 max-w-xs mx-auto">Cuando un estudio te pague por tus clases, aparecerá aquí.</p>
                    </div>
                @else
                    <div class="w-full">
                        <table class="w-full text-left border-collapse">
                            
                            <thead class="hidden md:table-header-group">
                                <tr class="bg-emerald-50/50 border-b border-emerald-100 text-[11px] uppercase tracking-widest text-emerald-700 font-black">
                                    <th class="px-6 py-5">Fecha</th>
                                    <th class="px-6 py-5">Estudio</th>
                                    <th class="px-6 py-5">Mes Liquidado</th>
                                    <th class="px-6 py-5">Método</th>
                                    <th class="px-6 py-5 text-right">Monto</th>
                                    <th class="px-6 py-5 text-center">Estado</th>
                                </tr>
                            </thead>
                            
                            <tbody class="block md:table-row-group">
                                @foreach($teacherPayments as $tp)
                                    @php
                                        $studio = $tp->studio;
                                        $studioLogo = $studio->icon_path ?? $studio->logo_path;
                                        $studioAvatar = $studioLogo 
                                            ? asset('storage/' . $studioLogo) 
                                            : 'https://ui-avatars.com/api/?name='.urlencode($studio->name).'&color=ffffff&background=18181b';
                                    @endphp
                                    
                                    <tr class="flex flex-col md:table-row p-5 md:p-0 border-b border-zinc-200 last:border-0 hover:bg-emerald-50/20 transition-colors group gap-3 md:gap-0">
                                        
                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5 md:whitespace-nowrap">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Fecha</span>
                                            <div class="text-right md:text-left">
                                                <div class="text-sm font-bold text-zinc-900">{{ $tp->created_at->translatedFormat('d M, Y') }}</div>
                                                <div class="text-[11px] text-zinc-400 font-medium uppercase">{{ $tp->created_at->format('H:i') }} hrs</div>
                                            </div>
                                        </td>
                                        
                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Estudio</span>
                                            <div class="flex items-center gap-2 md:gap-3 text-right md:text-left">
                                                <img src="{{ $studioAvatar }}" class="w-7 h-7 md:w-9 md:h-9 rounded-lg md:rounded-xl object-cover border border-zinc-100 shadow-sm shrink-0 bg-zinc-900">
                                                <span class="text-sm font-bold text-zinc-700 truncate max-w-[150px] leading-tight">{{ $studio->name }}</span>
                                            </div>
                                        </td>

                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5 md:whitespace-nowrap">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Mes</span>
                                            <span class="text-sm font-bold text-zinc-600">
                                                {{ \Carbon\Carbon::createFromFormat('Y-m', $tp->month_year)->translatedFormat('F Y') }}
                                            </span>
                                        </td>
                                        
                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5 md:whitespace-nowrap">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Método</span>
                                            @if($tp->payment_method === 'mercadopago')
                                                <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-600 uppercase tracking-tighter">
                                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                    Mercado Pago
                                                </div>
                                            @else
                                                <div class="flex items-center gap-1.5 text-xs font-bold text-teal-600 uppercase tracking-tighter">
                                                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                                    Manual
                                                </div>
                                            @endif
                                        </td>
                                        
                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5 md:whitespace-nowrap md:text-right mt-2 md:mt-0 pt-3 md:pt-0 border-t border-zinc-100 md:border-none">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Monto</span>
                                            <span class="text-lg md:text-base font-black text-emerald-600">${{ number_format($tp->amount, 0, ',', '.') }}</span>
                                        </td>

                                        <td class="flex justify-between items-center md:table-cell md:px-6 md:py-5 md:text-center mt-1 md:mt-0">
                                            <span class="md:hidden text-[10px] font-black text-zinc-400 uppercase tracking-widest">Estado</span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg {{ $tp->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $tp->status === 'paid' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                                {{ $tp->status === 'paid' ? 'Pagado' : 'Pendiente' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-6 py-6 border-t border-zinc-100 bg-zinc-50/50">
                        {{ $teacherPayments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    
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

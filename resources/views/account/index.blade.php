<x-app-layout>

    {{-- Inyectamos los tabs superiores --}}
    <x-studio-tabs />


    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Cabecera Unificada de Promociones --}}
        <div class="mt-2 mb-8 p-1"> {{-- p-1 evita recortes si aplicas rounded global --}}

            {{-- Breadcrumbs (Coherentes con el estilo de la plataforma) --}}
            <div class="flex text-xs font-bold text-zinc-500 mb-3 gap-2 items-center">
                <span class="text-zinc-900">Cuenta</span>
            </div>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-3 sm:gap-4 w-full">
                
                {{-- Título (Ocupa el espacio disponible y trunca si es muy largo) --}}
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-zinc-900 truncate flex-1 min-w-0">
                    Cuenta y pagos
                </h1>

                @if($studio->mp_access_token)
                    {{-- Botón de Desvincular (Destructivo) --}}
                    <form action="{{ route('account.mp.disconnect') }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas desvincular tu cuenta? No podrás recibir pagos hasta que vuelvas a conectarla.');" class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold text-rose-600 bg-white border border-rose-200 rounded-xl hover:bg-rose-50 hover:border-rose-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all duration-200 active:scale-[0.98]">
                            {{-- Texto corto para móviles --}}
                            <span class="sm:hidden">Desvincular</span>
                            {{-- Texto largo para escritorio --}}
                            <span class="hidden sm:inline">Desvincular Cuenta</span>
                        </button>
                    </form>
                @else
                    {{-- BOTÓN MODIFICADO: Ahora abre el Modal en lugar de redirigir --}}
                    <button type="button" onclick="openOAuthWarningModal()" class="shrink-0 inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold text-white bg-[#009EE3] border border-transparent rounded-xl hover:bg-[#008ACA] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009EE3] transition-all duration-200 shadow-sm active:scale-[0.98]">
                        {{-- Texto corto para móviles --}}
                        <span class="sm:hidden">Vincular MP</span>
                        {{-- Texto largo para escritorio --}}
                        <span class="hidden sm:inline">Vincular Cuenta de Mercado Pago</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Tarjeta de Integración de Pagos --}}
        <div class="bg-white border border-zinc-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 sm:p-8">
                
                {{-- Cabecera: Título a la izquierda, Badge a la derecha --}}
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                        Mercado Pago Connect
                    </h3>
                    
                    {{-- Indicador de Estado --}}
                    @if($studio->mp_access_token)
                        <span class="shrink-0 inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Conectado
                        </span>
                    @else
                        <span class="shrink-0 inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-600 border border-zinc-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>
                            Desconectado
                        </span>
                    @endif
                </div>

                {{-- Descripción debajo de la cabecera --}}
                <p class="mt-2 text-sm text-zinc-500">
                    Vincula tu cuenta de Mercado Pago para que tus alumnos paguen online de manera anticipada.
                </p>
                
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- SECCIÓN 2: HISTORIAL DE PAGOS DE ALUMNAS --}}
        {{-- ======================================================== --}}

        <div class="bg-white my-4 border border-zinc-200 rounded-2xl overflow-hidden shadow-sm">
            @if(isset($payments) && $payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-zinc-50 border-b border-zinc-100 text-zinc-500 uppercase tracking-widest text-[10px] font-black">
                            <tr>
                                <th scope="col" class="px-6 py-4">Fecha</th>
                                <th scope="col" class="px-6 py-4">Alumna/o</th>
                                <th scope="col" class="px-6 py-4 hidden sm:table-cell">Concepto</th>
                                <th scope="col" class="px-6 py-4 hidden md:table-cell">Método</th>
                                <th scope="col" class="px-6 py-4 text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 text-zinc-700">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-zinc-50/50 transition-colors duration-200">
                                    <td class="px-6 py-4 font-medium text-zinc-900">
                                        {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y') }}
                                        <span class="block sm:hidden text-xs text-zinc-400 font-normal mt-0.5">{{ $payment->payment_method ?? 'General' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="font-bold truncate max-w-[120px] sm:max-w-[200px]">{{ $payment->student->name ?? 'Usuario Eliminado' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 hidden sm:table-cell">
                                        <span class="bg-zinc-100 text-zinc-600 px-2.5 py-1 rounded-md text-xs font-bold border border-zinc-200">
                                            {{ $payment->payment_type === 'pack' ? 'Pack/Mensualidad' : 'Clase Suelta' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 hidden md:table-cell text-xs font-bold text-zinc-500 capitalize">
                                        @if($payment->payment_method === 'mercadopago')
                                            <span class="text-blue-600 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Mercado Pago</span>
                                        @elseif($payment->payment_method === 'transferencia')
                                            <span class="text-teal-600 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Transferencia</span>
                                        @else
                                            <span class="text-zinc-600 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span> Efectivo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-zinc-900">
                                        ${{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($payments->hasPages())
                    <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50">
                        {{ $payments->links() }}
                    </div>
                @endif
            @else
                <div class="px-6 py-4 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-zinc-50 border border-zinc-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-zinc-900 font-black text-lg">Aún no hay movimientos</h3>
                    <p class="text-zinc-500 text-sm mt-1 max-w-sm">Cuando tus alumnas/os realicen pagos manuales o a través de Mercado Pago, aparecerán registrados aquí.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL DE ADVERTENCIA OAUTH (INTERSTICIO) --}}
    {{-- ======================================================== --}}
    <div id="oauthWarningModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        {{-- Fondo oscuro --}}
        <div class="absolute inset-0 bg-zinc-900/70 backdrop-blur-sm" onclick="closeOAuthWarningModal()"></div>
        
        {{-- Tarjeta --}}
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 transform scale-95 opacity-0 transition-all duration-300" id="oauthWarningCard">
            
            <div class="w-16 h-16 bg-[#009EE3]/10 text-[#009EE3] rounded-2xl flex items-center justify-center mb-6 border border-[#009EE3]/20 shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>

            <h3 class="text-2xl font-black text-zinc-900 leading-tight mb-3">Atención antes de continuar</h3>
            
            <p class="text-zinc-600 text-sm leading-relaxed mb-4">
                Serás redirigido a los servidores de Mercado Pago para autorizar la conexión con tu estudio. 
            </p>
            
            <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl mb-8">
                <p class="text-amber-800 text-xs font-bold uppercase tracking-wider mb-1">Evita errores contables</p>
                <p class="text-amber-700 text-sm leading-relaxed">
                    Si ya tienes una sesión de Mercado Pago iniciada en tu navegador, <strong class="font-black">la conexión será inmediata</strong>. Asegúrate de estar usando la cuenta de <strong class="font-black">tu Estudio o Empresa</strong> y no tu cuenta personal de compras.
                </p>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeOAuthWarningModal()" class="flex-1 bg-zinc-100 text-zinc-700 font-bold py-3.5 rounded-xl hover:bg-zinc-200 transition-colors text-sm">
                    Cancelar
                </button>
                
                {{-- Enlace real hacia tu controlador --}}
                <a href="{{ route('mp.oauth.redirect') }}" class="flex-1 bg-[#009EE3] text-white font-bold py-3.5 rounded-xl shadow-sm hover:bg-[#008ACA] transition-all active:scale-95 text-sm flex items-center justify-center">
                    Entendido, conectar
                </a>
            </div>
        </div>
    </div>

    {{-- SCRIPT DEL MODAL --}}
    <script>
        function openOAuthWarningModal() {
            const modal = document.getElementById('oauthWarningModal');
            const card = document.getElementById('oauthWarningCard');
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeOAuthWarningModal() {
            const modal = document.getElementById('oauthWarningModal');
            const card = document.getElementById('oauthWarningCard');
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>

</x-app-layout>
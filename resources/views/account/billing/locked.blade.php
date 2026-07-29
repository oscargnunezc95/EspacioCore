<x-app-layout>
    <x-studio-tabs />

    <div class="pt-6 pb-12 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Icono y título --}}
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-rose-50 border border-rose-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black  mb-2">Estudio Bloqueado</h1>
            <p class="text-stone-500 text-sm max-w-md mx-auto">
                Tu estudio tiene facturas de comisiones vencidas. Para seguir usando EstadoPrisma, necesitas regularizar tu situación.
            </p>
        </div>

        {{-- Tarjeta de deuda --}}
        <div class="bg-white border border-rose-200 rounded-2xl overflow-hidden shadow-sm mb-6">
            <div class="bg-rose-50 border-b border-rose-200 px-6 py-4">
                <h3 class="text-sm font-black text-rose-800 uppercase tracking-wider">
                    ⚠️ Facturas Vencidas
                </h3>
            </div>

            <div class="divide-y divide-stone-100">
                @foreach($pastDueInvoices as $invoice)
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="font-bold text-stone-900">
                                Período {{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->format('F Y') }}
                            </p>
                            <p class="text-xs text-stone-400 mt-0.5">
                                Vencimiento: {{ $invoice->due_date->format('d/m/Y') }}
                                · Comisión: 5% sobre ${{ number_format($invoice->gross_sales, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xl font-black text-rose-700">${{ number_format($invoice->total_due, 0, ',', '.') }}</p>
                            @if($invoice->founder_savings > 0)
                                <p class="text-[10px] text-emerald-600 font-bold">💚 Ahorro Founder: ${{ number_format($invoice->founder_savings, 0, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-rose-50 border-t border-rose-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-rose-800">Total de la Deuda</p>
                    <p class="text-2xl font-black text-rose-900">${{ number_format($totalDebt, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Explicación empática --}}
        <div class="bg-stone-50 border border-stone-200 rounded-2xl p-6 mb-8">
            <h3 class="font-bold text-stone-800 mb-2">¿Qué pasó?</h3>
            <p class="text-sm text-stone-600 leading-relaxed mb-3">
                El período de pago de las facturas de comisión finalizó el día <strong>5 del mes</strong>.
                Mientras tengas facturas pendientes, el acceso a la gestión de tu estudio está restringido.
            </p>
            <p class="text-sm text-stone-600 leading-relaxed">
                Una vez realizado el pago, el desbloqueo es <strong class="text-emerald-700">inmediato</strong> y podrás seguir usando
                EstadoPrisma con normalidad. Si necesitas ayuda, escríbenos a <a href="mailto:soporte@estadoprisma.test" class="text-amber-700 underline hover:text-amber-800">soporte@estadoprisma.test</a>.
            </p>
        </div>

        {{-- Botón de pago (paga la factura más antigua primero) --}}
        @php
            $oldestInvoice = $pastDueInvoices->sortBy('billing_period')->first();
        @endphp
        <div class="text-center">
            <form action="{{ route('account.billing.pay', [$subdomain, $oldestInvoice->id]) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="inline-flex items-center px-8 py-4 text-base font-black text-white bg-rose-600 border border-transparent rounded-2xl hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-rose-400 transition-all duration-200 shadow-lg shadow-rose-200 active:scale-95">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Pagar Factura (Mercado Pago)
                </button>
            </form>
            <p class="text-xs text-stone-400 mt-3">Serás redirigido a Mercado Pago para completar el pago de forma segura.</p>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-studio-tabs />

    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs --}}
        <div class="flex text-xs font-bold text-stone-500 mb-3 gap-2 items-center">
            <a href="{{ route('account.index', $subdomain) }}" class="hover:text-amber-600 transition-colors">Cuenta</a>
            <span class="text-stone-300">/</span>
            <span class="text-amber-600">Facturación</span>
        </div>

        <div class="flex flex-row items-center justify-between gap-3 sm:gap-4 w-full mb-8">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-black truncate flex-1 min-w-0">
                Facturación y Comisiones
            </h1>
        </div>

        {{-- ================================================================ --}}
        {{-- TARJETA 1: PROYECCIÓN DEL MES EN CURSO (TIEMPO REAL) --}}
        {{-- ================================================================ --}}
        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm mb-6">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Mes en Curso</h3>
                        <p class="text-xs text-stone-400">Proyección en tiempo real · {{ now()->format('F Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Ventas Brutas --}}
                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-4">
                        <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-1">Ventas Brutas</p>
                        <p class="text-2xl font-black text-stone-900">${{ number_format($projection['gross_sales'], 0, ',', '.') }}</p>
                        <p class="text-[10px] text-stone-400 mt-1">Pagos válidos del mes</p>
                    </div>

                    {{-- Comisión Proyectada --}}
                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-4">
                        <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-1">Comisión (5%)</p>
                        <p class="text-2xl font-black text-stone-900">${{ number_format($projection['projected_commission'], 0, ',', '.') }}</p>
                        <p class="text-[10px] text-stone-400 mt-1">5% sobre ventas brutas</p>
                    </div>

                    {{-- Piso Mínimo --}}
                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-4">
                        <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-1">Piso Mensual</p>
                        <p class="text-2xl font-black text-stone-900">${{ number_format($projection['projected_minimum_floor'], 0, ',', '.') }}</p>
                        <p class="text-[10px] text-stone-400 mt-1">
                            @if($projection['projected_minimum_floor'] < 15000)
                                Prorrateado (estudio nuevo)
                            @else
                                Piso estándar
                            @endif
                        </p>
                    </div>

                    {{-- Total Proyectado --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <p class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-1">Total Estimado</p>
                        <p class="text-2xl font-black text-amber-700">${{ number_format($projection['projected_total'], 0, ',', '.') }}</p>
                        <p class="text-[10px] text-amber-500 mt-1">A facturar el día 1 del próximo mes</p>
                    </div>
                </div>

                @if($projection['projected_savings'] > 0)
                    <div class="mt-4 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3">
                        <span class="text-2xl">💚</span>
                        <div>
                            <p class="text-sm font-bold text-emerald-800">Ahorro proyectado este mes: <span class="text-lg">${{ number_format($projection['projected_savings'], 0, ',', '.') }}</span></p>
                            <p class="text-xs text-emerald-600">Tu beneficio Founder está capeando la comisión al piso de ${{ number_format($projection['projected_minimum_floor'], 0, ',', '.') }}.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- TARJETA 2: BANNER DE BENEFICIO FOUNDER (SI APLICA) --}}
        {{-- ================================================================ --}}
        @if($studio->isFounderActive())
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-300 rounded-2xl overflow-hidden shadow-sm mb-6">
                <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="text-4xl shrink-0">👑</div>
                    <div class="flex-1">
                        <h3 class="text-lg font-black text-emerald-800">Beneficio Fundador Activo</h3>
                        <p class="text-sm text-emerald-700 mt-1">
                            Tu comisión mensual nunca superará los <strong>$15.000</strong>.
                            Te quedan <strong class="text-emerald-900">{{ $studio->founder_cycles_remaining }} meses</strong> restantes de este beneficio exclusivo.
                        </p>
                    </div>
                    <div class="shrink-0 bg-white/70 border border-emerald-200 rounded-xl px-4 py-3 text-center">
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Meses Restantes</p>
                        <p class="text-3xl font-black text-emerald-700">{{ $studio->founder_cycles_remaining }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ================================================================ --}}
        {{-- TARJETA 3: HISTORIAL DE FACTURAS --}}
        {{-- ================================================================ --}}
        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-stone-100 text-stone-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Historial de Facturas</h3>
                        <p class="text-xs text-stone-400">Últimas facturas de comisiones por uso de la plataforma</p>
                    </div>
                </div>

                @if($invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-stone-50 border-b border-stone-100 text-stone-500 uppercase tracking-widest text-[10px] font-black">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Período</th>
                                    <th scope="col" class="px-6 py-4 hidden sm:table-cell">Ventas Brutas</th>
                                    <th scope="col" class="px-6 py-4 hidden md:table-cell">Comisión</th>
                                    <th scope="col" class="px-6 py-4 hidden md:table-cell">Piso Mínimo</th>
                                    <th scope="col" class="px-6 py-4 hidden md:table-cell">Ahorro Founder</th>
                                    <th scope="col" class="px-6 py-4 text-right">Total</th>
                                    <th scope="col" class="px-6 py-4 text-center">Estado</th>
                                    <th scope="col" class="px-6 py-4 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 text-stone-700">
                                @foreach($invoices as $invoice)
                                    <tr class="hover:bg-stone-50/50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-medium text-stone-900">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->format('M Y') }}
                                        </td>
                                        <td class="px-6 py-4 hidden sm:table-cell text-stone-500">
                                            ${{ number_format($invoice->gross_sales, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell text-stone-500">
                                            ${{ number_format($invoice->calculated_commission, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell text-stone-500">
                                            ${{ number_format($invoice->minimum_floor, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell">
                                            @if($invoice->founder_savings > 0)
                                                <span class="text-emerald-600 font-bold">-${{ number_format($invoice->founder_savings, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-stone-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right font-black text-stone-900">
                                            ${{ number_format($invoice->total_due, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $invoice->getStatusBadgeClassAttribute() }}">
                                                {{ $invoice->getStatusLabelAttribute() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if(!$invoice->isPaid())
                                                <form action="{{ route('account.billing.pay', [$subdomain, $invoice->id]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-amber-600 border border-transparent rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200 active:scale-95">
                                                        Pagar ahora
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-emerald-600 font-bold flex items-center justify-end gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    {{ $invoice->paid_at?->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($invoices->hasPages())
                        <div class="px-6 py-4 border-t border-stone-100 bg-stone-50">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                @else
                    <div class="flex flex-col items-center justify-center text-center py-12">
                        <div class="w-16 h-16 bg-stone-50 border border-stone-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-stone-900 font-black text-lg">Sin facturas aún</h3>
                        <p class="text-stone-500 text-sm mt-1 max-w-sm">
                            Las facturas se generan automáticamente el día 1 de cada mes. Aquí podrás ver tu historial y realizar los pagos.
                        </p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>

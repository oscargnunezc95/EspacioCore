<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            {{-- Botón Volver --}}
            <a href="{{ route('admin.studios.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 shadow-sm transition-all duration-200 hover:bg-zinc-50 hover:border-zinc-300 focus:ring-2 focus:ring-zinc-300 focus:ring-offset-2 focus:outline-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Volver
            </a>

            <div>
                <h2 class="text-xl font-semibold text-zinc-900">
                    Auditoría de Ingresos: {{ $studio->name }}
                </h2>
                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $studio->subdomain }} — Ledger de transacciones Mercado Pago
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl space-y-6">

            {{-- Barra de Filtros --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                <form method="GET" action="{{ route('admin.studios.audit', $studio->id) }}" class="flex items-end gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label for="month" class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            Mes a Auditar
                        </label>
                        <input type="month"
                               id="month"
                               name="month"
                               value="{{ $selectedMonth }}"
                               class="w-56 rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm transition-all duration-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    </div>

                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-300 focus:ring-offset-2 focus:outline-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                        Filtrar
                    </button>
                </form>
            </div>

            {{-- Top Cards: Resumen del Mes --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                {{-- Gross Sales --}}
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">
                        Volumen Bruto
                    </p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">
                        ${{ number_format($totals->gross_sales, 0, ',', '.') }}
                    </p>
                    <p class="mt-0.5 text-xs text-emerald-500">
                        Ventas totales en Mercado Pago
                    </p>
                </div>

                {{-- Platform Fee --}}
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50/50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">
                        Nuestra Comisión
                    </p>
                    <p class="mt-2 text-2xl font-bold text-indigo-700">
                        ${{ number_format($totals->platform_fee, 0, ',', '.') }}
                    </p>
                    <p class="mt-0.5 text-xs text-indigo-500">
                        Retención de plataforma
                    </p>
                </div>

                {{-- Net Transfer --}}
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">
                        Pago Neto al Estudio
                    </p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">
                        ${{ number_format($totals->net_transfer, 0, ',', '.') }}
                    </p>
                    <p class="mt-0.5 text-xs text-emerald-500">
                        A transferir al estudio
                    </p>
                </div>
            </div>

            {{-- Tabla Ledger --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Fecha
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Transacción MP
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Alumno
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Total Bruto
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Nuestra Comisión
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($payments as $payment)
                                <tr class="transition-colors duration-200 hover:bg-zinc-50/50">
                                    {{-- Fecha --}}
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-zinc-900">
                                        {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y') }}
                                        <div class="text-xs text-zinc-400 font-normal mt-0.5">
                                            {{ \Carbon\Carbon::parse($payment->created_at)->format('H:i') }}
                                        </div>
                                    </td>

                                    {{-- ID Transacción MP --}}
                                    <td class="whitespace-nowrap px-6 py-4 font-mono text-xs text-zinc-500">
                                        {{ $payment->mp_payment_id }}
                                    </td>

                                    {{-- Alumno --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-zinc-600">
                                        {{ $payment->student?->first_name ?? '—' }}
                                    </td>

                                    {{-- Total Bruto --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-medium text-emerald-600">
                                        ${{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>

                                    {{-- Nuestra Comisión --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-medium text-indigo-600">
                                        ${{ number_format($payment->platform_fee, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-zinc-400">
                                        No se encontraron transacciones para este mes.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if ($payments->hasPages())
                    <div class="border-t border-zinc-200 px-6 py-4">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-admin-layout>

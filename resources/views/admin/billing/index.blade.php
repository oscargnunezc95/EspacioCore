<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-stone-900">
            Gestión de Facturación
        </h2>
        <p class="mt-1 text-sm text-stone-500">
            Configura el piso mínimo estándar y ajusta el piso de facturas individuales antes de su pago.
        </p>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl space-y-8">

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{-- SECCIÓN 1: PISO MÍNIMO ESTÁNDAR --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 bg-stone-50 px-6 py-4">
                    <h3 class="text-sm font-black text-stone-700 uppercase tracking-wider">
                        ⚙️ Piso Mínimo Estándar
                    </h3>
                    <p class="mt-0.5 text-xs text-stone-400">
                        Este valor se usa como base al generar nuevas facturas mensuales. Afecta a todos los estudios.
                    </p>
                </div>
                <div class="px-6 py-5">
                    <form id="standard-floor-form" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        @csrf
                        <div class="flex-1 w-full sm:w-auto">
                            <label for="standard-floor-value" class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-1">
                                Valor base ($)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400 font-bold">$</span>
                                <input type="number"
                                       id="standard-floor-value"
                                       name="value"
                                       value="{{ $standardFloor }}"
                                       min="0"
                                       max="99999999"
                                       step="1"
                                       class="block w-full rounded-lg border border-stone-300 pl-8 pr-4 py-2.5 text-sm font-bold text-stone-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none transition-all duration-200">
                            </div>
                        </div>
                        <button type="submit"
                                id="save-standard-floor-btn"
                                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 mt-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Guardar
                        </button>
                    </form>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{-- SECCIÓN 2: FACTURAS PENDIENTES (EDITABLES) --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 bg-amber-50 px-6 py-4">
                    <h3 class="text-sm font-black text-amber-800 uppercase tracking-wider">
                        📋 Facturas Pendientes
                    </h3>
                    <p class="mt-0.5 text-xs text-amber-600">
                        Puedes modificar el piso mínimo de cada factura antes de que el estudio la pague.
                    </p>
                </div>

                @if($pendingInvoices->count() > 0)
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-stone-200 text-sm">
                            <thead class="bg-stone-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Estudio</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500 hidden sm:table-cell">Período</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500 hidden lg:table-cell">Ventas</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500 hidden lg:table-cell">Comisión</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Piso Mínimo</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Total</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-stone-500 hidden sm:table-cell">Estado</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500 hidden md:table-cell">Vence</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach($pendingInvoices as $invoice)
                                    <tr class="transition-colors duration-200 hover:bg-stone-50/50">
                                        <td class="px-4 py-3 font-medium text-stone-900 max-w-[140px] truncate" title="{{ $invoice->studio?->name }}">
                                            {{ $invoice->studio?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-stone-600 hidden sm:table-cell">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->format('M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-stone-500 hidden lg:table-cell">
                                            ${{ number_format($invoice->gross_sales, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-stone-500 hidden lg:table-cell">
                                            ${{ number_format($invoice->calculated_commission, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <span class="text-stone-400 font-bold text-xs">$</span>
                                                <input type="number"
                                                       data-invoice-floor-input="{{ $invoice->id }}"
                                                       value="{{ $invoice->minimum_floor }}"
                                                       min="0"
                                                       max="99999999"
                                                       step="1"
                                                       class="w-24 rounded-md border border-stone-300 px-2 py-1.5 text-sm text-right font-bold text-stone-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none transition-all duration-200">
                                                <button type="button"
                                                        data-invoice-floor-save="{{ $invoice->id }}"
                                                        class="inline-flex items-center rounded-md bg-stone-100 px-2 py-1.5 text-xs font-medium text-stone-600 transition-all duration-200 hover:bg-amber-100 hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-300 disabled:cursor-not-allowed disabled:opacity-50"
                                                        title="Guardar piso mínimo">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right font-black text-stone-900">
                                            ${{ number_format($invoice->total_due, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border {{ $invoice->getStatusBadgeClassAttribute() }}">
                                                {{ $invoice->getStatusLabelAttribute() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs text-stone-400 hidden md:table-cell">
                                            {{ $invoice->due_date?->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-12 h-12 bg-stone-50 border border-stone-100 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-stone-400">No hay facturas pendientes.</p>
                    </div>
                @endif
            </div>

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{-- SECCIÓN 3: FACTURAS PAGADAS (SOLO LECTURA) --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 bg-emerald-50 px-6 py-4">
                    <h3 class="text-sm font-black text-emerald-800 uppercase tracking-wider">
                        ✅ Historial de Facturas Pagadas
                    </h3>
                    <p class="mt-0.5 text-xs text-emerald-600">
                        Solo lectura. Últimas 50 facturas pagadas.
                    </p>
                </div>

                @if($paidInvoices->count() > 0)
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-stone-200 text-sm">
                            <thead class="bg-stone-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Estudio</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500 hidden sm:table-cell">Período</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500 hidden lg:table-cell">Ventas</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500 hidden lg:table-cell">Comisión</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Piso Mínimo</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Total</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500 hidden md:table-cell">Pagado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach($paidInvoices as $invoice)
                                    <tr class="transition-colors duration-200 hover:bg-stone-50/50">
                                        <td class="px-4 py-3 font-medium text-stone-900 max-w-[140px] truncate" title="{{ $invoice->studio?->name }}">
                                            {{ $invoice->studio?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-stone-600 hidden sm:table-cell">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->format('M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-stone-500 hidden lg:table-cell">
                                            ${{ number_format($invoice->gross_sales, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-stone-500 hidden lg:table-cell">
                                            ${{ number_format($invoice->calculated_commission, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-stone-500">
                                            ${{ number_format($invoice->minimum_floor, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-black text-stone-900">
                                            ${{ number_format($invoice->total_due, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs text-emerald-600 font-bold hidden md:table-cell">
                                            {{ $invoice->paid_at?->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-12 h-12 bg-stone-50 border border-stone-100 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-stone-400">No hay facturas pagadas aún.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Toast --}}
    <div id="toast" class="pointer-events-none fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg opacity-0 transition-all duration-300 translate-y-2" role="alert" aria-live="polite">
        <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="toast-message">Guardado correctamente.</span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            let toastTimer = null;

            function showToast(message, isError = false) {
                clearTimeout(toastTimer);
                toastMessage.textContent = message;
                if (isError) {
                    toast.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
                    toast.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800');
                } else {
                    toast.classList.remove('border-rose-200', 'bg-rose-50', 'text-rose-800');
                    toast.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
                }
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
                toastTimer = setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    toast.classList.remove('opacity-100', 'translate-y-0');
                }, 3500);
            }

            // ─── 1. GUARDAR PISO ESTÁNDAR ───────────────────────────────
            const standardFloorForm = document.getElementById('standard-floor-form');
            const saveStandardFloorBtn = document.getElementById('save-standard-floor-btn');

            standardFloorForm?.addEventListener('submit', async function (e) {
                e.preventDefault();
                const value = document.getElementById('standard-floor-value').value;
                const originalHTML = saveStandardFloorBtn.innerHTML;

                saveStandardFloorBtn.disabled = true;
                saveStandardFloorBtn.innerHTML = `<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>`;

                try {
                    const response = await fetch('{{ route('admin.billing.update-standard-floor') }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ value: parseInt(value) }),
                    });

                    if (!response.ok) {
                        const data = await response.json();
                        throw new Error(data.message || 'Error al guardar.');
                    }

                    const data = await response.json();
                    showToast(data.message);
                } catch (error) {
                    showToast(error.message || 'Error de conexión.', true);
                } finally {
                    saveStandardFloorBtn.disabled = false;
                    saveStandardFloorBtn.innerHTML = originalHTML;
                }
            });

            // ─── 2. GUARDAR PISO MÍNIMO DE FACTURA INDIVIDUAL ───────────
            document.querySelectorAll('[data-invoice-floor-save]').forEach(button => {
                button.addEventListener('click', async function () {
                    const invoiceId = this.getAttribute('data-invoice-floor-save');
                    const input = document.querySelector(`[data-invoice-floor-input="${invoiceId}"]`);
                    const value = input?.value;

                    if (!input || value === undefined) return;

                    const originalHTML = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = `<svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>`;

                    try {
                        const response = await fetch(`/admin/billing/invoices/${invoiceId}/floor`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ minimum_floor: parseInt(value) }),
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.message || 'Error al guardar.');
                        }

                        const data = await response.json();
                        showToast(data.message);
                    } catch (error) {
                        showToast(error.message || 'Error de conexión.', true);
                    } finally {
                        this.disabled = false;
                        this.innerHTML = originalHTML;
                    }
                });
            });
        });
    </script>
</x-admin-layout>

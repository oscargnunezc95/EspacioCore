<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-stone-900">
            Gestión de Estudios
        </h2>
        <p class="mt-1 text-sm text-stone-500">
            Administra el beneficio Founder de cada estudio. El sistema de planes fue reemplazado por Facturación por Uso (5% de comisión con piso de $15.000).
        </p>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl space-y-6">

            @if ($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tabla de Estudios --}}
            <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Estudio</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Dueño</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">📧 Correo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">📱 WhatsApp</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-stone-500">👑 Founder</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-stone-500">Ciclos Restantes</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-stone-500">Última Factura</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-stone-500">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($studios as $studio)
                                <tr class="transition-colors duration-200 hover:bg-stone-50/50">
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-stone-900">
                                        {{ $studio->name }}
                                        <div class="text-xs text-stone-400 font-normal mt-0.5">{{ $studio->subdomain }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-stone-600">
                                        {{ $studio->user?->email ?? 'Sin dueño' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-stone-600">
                                        {{ $studio->email ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-stone-600">
                                        {{ $studio->whatsapp ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <input type="checkbox"
                                               data-founder-toggle="{{ $studio->id }}"
                                               {{ $studio->is_founder ? 'checked' : '' }}
                                               class="h-5 w-5 rounded border-stone-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <input type="number"
                                               data-cycles-input="{{ $studio->id }}"
                                               value="{{ $studio->founder_cycles_remaining }}"
                                               min="0" max="12"
                                               class="w-20 rounded-md border border-stone-300 px-2 py-1 text-sm text-center text-stone-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none">
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-xs text-stone-500">
                                        @php $lastInvoice = $studio->invoices->first(); @endphp
                                        @if($lastInvoice)
                                            <span class="font-bold">{{ $lastInvoice->billing_period }}</span>
                                            <span class="block {{ $lastInvoice->status === 'paid' ? 'text-emerald-600' : ($lastInvoice->status === 'past_due' ? 'text-rose-600' : 'text-amber-600') }}">
                                                ${{ number_format($lastInvoice->total_due, 0, ',', '.') }} · {{ $lastInvoice->getStatusLabelAttribute() }}
                                            </span>
                                        @else
                                            <span class="text-stone-300">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.studios.audit', $studio->id) }}"
                                               class="inline-flex items-center gap-1.5 rounded-md border border-stone-200 bg-white px-3 py-1.5 text-xs font-medium text-stone-700 shadow-sm transition-all duration-200 hover:bg-stone-50 hover:border-stone-300 focus:ring-2 focus:ring-stone-300 focus:ring-offset-1 focus:outline-none">
                                                Auditar
                                            </a>
                                            <button type="button"
                                                    data-save-btn="{{ $studio->id }}"
                                                    class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition-all duration-200 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-300 focus:ring-offset-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50">
                                                Guardar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-stone-400">
                                        No se encontraron estudios registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast" class="pointer-events-none fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg opacity-0 transition-all duration-300 translate-y-2" role="alert" aria-live="polite">
        <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="toast-message">Estado actualizado correctamente.</span>
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
                }, 3000);
            }

            document.querySelectorAll('[data-save-btn]').forEach(button => {
                button.addEventListener('click', async function () {
                    const studioId = this.getAttribute('data-save-btn');
                    const isFounder = document.querySelector(`[data-founder-toggle="${studioId}"]`).checked;
                    const cycles = parseInt(document.querySelector(`[data-cycles-input="${studioId}"]`).value) || 0;

                    const originalHTML = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = `<svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>`;

                    try {
                        const response = await fetch(`/admin/estudios/${studioId}/plan`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                is_founder: isFounder,
                                founder_cycles_remaining: cycles,
                            }),
                        });

                        if (!response.ok) throw new Error('Error al actualizar.');
                        const data = await response.json();
                        showToast(data.message || 'Estado actualizado.');
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

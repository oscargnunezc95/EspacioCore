<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-zinc-900">
            Gestión de Estudios y Planes
        </h2>
        <p class="mt-1 text-sm text-zinc-500">
            Administra los planes de suscripción por estudio. Los cambios de comisiones y límites se aplican al instante.
        </p>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl space-y-6">

            {{-- Mensaje global de errores de validación --}}
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
            <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Estudio
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Dueño
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Plan Asignado
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($studios as $studio)
                                <tr class="transition-colors duration-200 hover:bg-zinc-50/50" data-studio-id="{{ $studio->id }}">
                                    
                                    {{-- Nombre del Estudio --}}
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-zinc-900">
                                        {{ $studio->name }}
                                        <div class="text-xs text-zinc-400 font-normal mt-0.5">{{ $studio->subdomain }}</div>
                                    </td>

                                    {{-- Email del Dueño --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-zinc-600">
                                        {{ $studio->user?->email ?? 'Sin dueño' }}
                                    </td>

                                    {{-- Selector de Plan --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <select data-plan-select="{{ $studio->id }}"
                                            class="w-56 rounded-md border border-zinc-300 px-3 py-1.5 text-sm text-zinc-900 shadow-sm transition-all duration-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none cursor-pointer">
                                            
                                            @if(isset($plans))
                                                @foreach($plans as $plan)
                                                    <option value="{{ $plan->id }}" {{ $studio->subscription_plan_id == $plan->id ? 'selected' : '' }}>
                                                        {{ $plan->name }} ({{ $plan->platform_fee_percent }}%)
                                                    </option>
                                                @endforeach
                                            @endif
                                            
                                        </select>
                                    </td>

                                    {{-- Acciones del estudio --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Botón Auditar --}}
                                            <a href="{{ route('admin.studios.audit', $studio->id) }}"
                                               class="inline-flex items-center gap-1.5 rounded-md border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 shadow-sm transition-all duration-200 hover:bg-zinc-50 hover:border-zinc-300 focus:ring-2 focus:ring-zinc-300 focus:ring-offset-1 focus:outline-none">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 01-3-3V5.25A2.25 2.25 0 0012 3c-1.002 0-1.5.602-2.503 1.272C8.618 4.848 7.474 5.586 6 5.586H5.25A2.25 2.25 0 003 7.836v10.328A2.25 2.25 0 005.25 20.25h13.5A2.25 2.25 0 0021 18v-6z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Auditar
                                            </a>

                                            {{-- Botón Guardar --}}
                                            <button type="button"
                                            data-save-btn="{{ $studio->id }}"
                                            class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition-all duration-200 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-300 focus:ring-offset-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar
                                        </button>
                                    </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-zinc-400">
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

    {{-- Toast de confirmación --}}
    <div id="toast"
         class="pointer-events-none fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg opacity-0 transition-all duration-300 translate-y-2"
         role="alert"
         aria-live="polite">
        <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span id="toast-message">Plan actualizado correctamente.</span>
    </div>

    {{-- JavaScript Vanilla --}}
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
                    toast.className = toast.className
                        .replace(/border-emerald-200|bg-emerald-50|text-emerald-800/g, '')
                        + ' border-rose-200 bg-rose-50 text-rose-800';
                } else {
                    toast.className = toast.className
                        .replace(/border-rose-200|bg-rose-50|text-rose-800/g, '')
                        + ' border-emerald-200 bg-emerald-50 text-emerald-800';
                }

                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');

                toastTimer = setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    toast.classList.remove('opacity-100', 'translate-y-0');
                }, 3000);
            }

            // Delegación de eventos en todos los botones "Guardar"
            document.querySelectorAll('[data-save-btn]').forEach(button => {
                button.addEventListener('click', async function () {
                    const studioId = this.getAttribute('data-save-btn');
                    const select = document.querySelector(`[data-plan-select="${studioId}"]`);
                    const planValue = select.value === "" ? null : select.value;

                    // Feedback visual: deshabilitar botón mientras se procesa
                    const originalHTML = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = `
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Guardando...
                    `;

                    try {
                        const response = await fetch(
                            `/admin/estudios/${studioId}/plan`,
                            {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    subscription_plan_id: planValue,
                                }),
                            }
                        );

                        if (!response.ok) {
                            const data = await response.json();
                            const msg = data.message || data.error || 'Error al actualizar el plan.';
                            throw new Error(msg);
                        }

                        const data = await response.json();
                        showToast(data.message || 'Plan actualizado correctamente.');
                    } catch (error) {
                        showToast(error.message || 'Error de conexión. Intenta nuevamente.', true);
                    } finally {
                        // Restaurar botón
                        this.disabled = false;
                        this.innerHTML = originalHTML;
                    }
                });
            });
        });
    </script>
</x-admin-layout>
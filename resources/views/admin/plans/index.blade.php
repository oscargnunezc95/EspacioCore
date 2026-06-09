<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-zinc-900">Planes de Suscripción</h2>
                <p class="mt-1 text-sm text-zinc-500">Administra los planes SaaS, cupos limitados y ciclos de vida.</p>
            </div>
            <button onclick="openModal()" class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-zinc-800 active:scale-95">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Plan
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Alertas Globales --}}
            @if(session('success'))
                <div class="rounded-lg bg-emerald-50 p-4 text-sm font-medium text-emerald-800 border border-emerald-200">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="rounded-lg bg-rose-50 p-4 text-sm font-medium text-rose-800 border border-rose-200">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tabla de Datos --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500">Precio (CLP)</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500">Comisión</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500">Límites</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500">Estudios</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($plans as $plan)
                                <tr class="transition-colors duration-200 hover:bg-zinc-50/50">
                                    <td class="whitespace-nowrap px-6 py-4 font-bold text-zinc-900">
                                        {{ $plan->name }}
                                        <div class="text-xs font-normal text-zinc-400 font-mono">{{ $plan->slug }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-zinc-600">
                                        ${{ number_format($plan->price, 0, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center font-medium text-zinc-700">
                                        {{ $plan->platform_fee_percent }}%
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <div class="flex flex-col gap-1 items-center">
                                            @if($plan->capacity_limit)
                                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/10" title="Cupos totales">
                                                    {{ $plan->capacity_limit }} Cupos
                                                </span>
                                            @endif
                                            @if($plan->max_billing_cycles)
                                                <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10" title="Duración máxima">
                                                    {{ $plan->max_billing_cycles }} Meses
                                                </span>
                                            @endif
                                            @if(!$plan->capacity_limit && !$plan->max_billing_cycles)
                                                <span class="text-zinc-400 text-xs">Ilimitado</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-zinc-600 font-bold">
                                        {{ $plan->studios_count }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <button type="button" 
                                            data-toggle-plan="{{ $plan->id }}"
                                            class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 {{ $plan->is_active ? 'bg-indigo-600' : 'bg-zinc-200' }}">
                                            <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $plan->is_active ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                        </button>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <button type="button" 
                                            onclick="openModal({{ $plan->toJson() }})"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium transition-colors">
                                            Editar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-zinc-400">No hay planes registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Formulario (Crear/Editar) --}}
    <div id="planModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-zinc-900/60 transition-opacity backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form id="planForm" method="POST" action="{{ route('admin.plans.store') }}">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        
                        <div class="bg-white px-6 pb-6 pt-5">
                            <h3 class="text-lg font-bold leading-6 text-zinc-900 mb-4" id="modalTitle">Nuevo Plan</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700">Nombre del Plan</label>
                                    <input type="text" name="name" id="planName" required class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700">Precio Fijo (CLP)</label>
                                        <input type="number" name="price" id="planPrice" required min="0" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700">Comisión % (Adicional)</label>
                                        <input type="number" step="0.01" name="platform_fee_percent" id="planFee" required min="0" max="100" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <div class="border-t border-zinc-100 pt-4 mt-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-3">Límites (Opcional)</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700">Cupo Máximo (Estudios)</label>
                                            <input type="number" name="capacity_limit" id="planCapacity" min="1" placeholder="Ej: 7" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700">Meses de Vida (Ciclos)</label>
                                            <input type="number" name="max_billing_cycles" id="planCycles" min="1" placeholder="Ej: 6" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-zinc-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl border-t border-zinc-100">
                            <button type="button" onclick="closeModal()" class="rounded-xl px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-200 transition-colors">Cancelar</button>
                            <button type="submit" class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-zinc-800 transition-all active:scale-95">Guardar Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Vanilla JS --}}
    <script>
        const modal = document.getElementById('planModal');
        const form = document.getElementById('planForm');
        const title = document.getElementById('modalTitle');
        const methodInput = document.getElementById('formMethod');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function openModal(plan = null) {
            modal.classList.remove('hidden');
            
            if (plan) {
                // Editar
                title.textContent = 'Editar Plan';
                methodInput.value = 'PUT';
                form.action = `/admin/planes/${plan.id}`;
                
                document.getElementById('planName').value = plan.name;
                document.getElementById('planPrice').value = plan.price;
                document.getElementById('planFee').value = plan.platform_fee_percent;
                document.getElementById('planCapacity').value = plan.capacity_limit || '';
                document.getElementById('planCycles').value = plan.max_billing_cycles || '';
            } else {
                // Crear
                title.textContent = 'Nuevo Plan';
                methodInput.value = 'POST';
                form.action = '{{ route("admin.plans.store") }}';
                form.reset();
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
            form.reset();
        }

        // Lógica de Toggle con AJAX
        document.querySelectorAll('[data-toggle-plan]').forEach(button => {
            button.addEventListener('click', async function() {
                const planId = this.getAttribute('data-toggle-plan');
                const toggleKnob = this.querySelector('span');
                
                try {
                    const response = await fetch(`/admin/planes/${planId}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // Actualizar UI del toggle
                        if (data.is_active) {
                            this.classList.replace('bg-zinc-200', 'bg-indigo-600');
                            toggleKnob.classList.replace('translate-x-0', 'translate-x-4');
                        } else {
                            this.classList.replace('bg-indigo-600', 'bg-zinc-200');
                            toggleKnob.classList.replace('translate-x-4', 'translate-x-0');
                        }
                    }
                } catch (error) {
                    console.error('Error al cambiar el estado del plan', error);
                }
            });
        });
    </script>
</x-admin-layout>
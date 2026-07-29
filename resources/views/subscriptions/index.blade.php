<x-app-layout>

    {{-- Tabs de navegación del estudio --}}
    <x-studio-tabs />

    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs --}}
        <div class="flex text-xs font-bold text-stone-500 mb-3 gap-2 items-center">
            <span class="text-stone-900">Mi Suscripción</span>
        </div>

        {{-- Cabecera: Título + Plan Actual --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-black truncate">
                Mi Suscripción
            </h1>

            {{-- Indicador del Plan Actual --}}
            @php
                $currentPlan = $studio->subscriptionPlan;
                $isFree = $studio->subscription_status === 'free';
                $pendingPlan = $studio->nextPlan;
            @endphp
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                {{-- Plan Actual --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border
                            @if($isFree)
                                bg-stone-100 text-stone-600 border-stone-300
                            @else
                                bg-emerald-50 text-emerald-700 border-emerald-300
                            @endif">
                    <span class="text-xs uppercase tracking-wider text-stone-400">Plan actual</span>
                    <span>{{ $currentPlan ? $currentPlan->name : 'Plan Gratuito' }}</span>
                </div>

                {{-- Próximo Plan (si hay next_plan_id) --}}
                @if($pendingPlan)
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border bg-amber-50 text-amber-700 border-amber-300">
                        <span class="text-xs uppercase tracking-wider text-amber-400">Próximo plan</span>
                        <span>{{ $pendingPlan->name }}</span>
                    </div>
                @endif
            </div>
        </div>
        {{-- BANNER DE ALERTA: PAGO RECHAZADO / MOROSO (past_due) --}}
        @if($studio->subscription_status === 'past_due')
            <div class="mt-4 mb-6 p-4 sm:p-5 bg-amber-50 border border-amber-200 rounded-xl flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
                {{-- Ícono de advertencia --}}
                <div class="shrink-0 text-amber-600 mt-0.5">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>

                {{-- Contenido textual --}}
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm sm:text-base font-bold text-amber-900">
                        Acceso Restringido: Actualiza tu medio de pago
                    </h4>
                    <p class="text-xs sm:text-sm text-amber-700 mt-1 leading-relaxed">
                        No pudimos procesar el cobro automático de tu suscripción. Si no regularizas tu cuenta en los próximos días, pasarás al Plan Gratuito. Puedes forzar el pago ahora mismo con otra tarjeta.
                    </p>
                </div>

                {{-- Botón de acción --}}
                <div class="shrink-0 w-full sm:w-auto">
                    <form action="{{ route('studios.retry-payment', ['studio' => $studio->id]) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-bold bg-amber-600 text-white hover:bg-amber-700 transition-all duration-200 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Pagar con otra tarjeta
                        </button>
                    </form>
                </div>
            </div>
        @endif
        {{-- Contenedor Alpine.js: País + Grid + Modal comparten el mismo scope reactivo --}}
        <div x-data="subscriptionPlans()"
             x-init="init({
                isGrace: {{ $isGracePeriod ? 'true' : 'false' }},
                isFree: {{ $isFree ? 'true' : 'false' }},
                currentPlanSlug: '{{ $currentPlan ? $currentPlan->slug : 'free' }}'
             })">

            {{-- Selector de País/Moneda --}}
            <div class="mb-8">
                <label for="country-select" class="block text-sm font-bold text-stone-900 mb-2">
                    País de facturación
                </label>
                <div class="relative max-w-xs">
                    <select id="country-select"
                            x-model="selectedCountry"
                            class="appearance-none w-full bg-white border border-stone-300 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-stone-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-stone-900 focus:border-stone-900 cursor-pointer">
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-stone-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Grid de Planes --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach($activePlans as $plan)
                @php
                    $isCurrentPlan = $studio->subscription_plan_id === $plan->id;
                    $isFreePlan = $plan->slug === 'free';
                    $priceFormatted = '$' . number_format($plan->price, 0, ',', '.');
                @endphp

                <div class="relative flex flex-col bg-white rounded-2xl border transition-all duration-200
                            @if($isCurrentPlan)
                                border-emerald-400 ring-2 ring-emerald-100 shadow-lg
                            @else
                                border-stone-200 hover:border-stone-400 hover:shadow-md
                            @endif">

                    {{-- Insignia "Plan Actual" --}}
                    @if($isCurrentPlan)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 bg-emerald-500 text-white text-xs font-bold rounded-full shadow-sm">
                            Plan Actual
                        </div>
                    @endif

                    {{-- Cabecera de la Card --}}
                    <div class="p-6 pb-4 text-center border-b border-stone-100">
                        <h3 class="text-lg font-black text-stone-900 mb-1">{{ $plan->name }}</h3>
                        @if($plan->features)
                            <p class="text-xs text-stone-500 leading-relaxed">{{ $plan->features }}</p>
                        @endif
                    </div>

                    {{-- Precio --}}
                    <div class="p-6 pb-4 text-center">
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-3xl md:text-4xl font-black text-stone-900">
                                {{ $priceFormatted }}
                            </span>
                            <span class="text-sm font-medium text-stone-400">CLP</span>
                        </div>
                        <p class="text-xs text-stone-400 mt-1">por mes</p>
                    </div>

                    {{-- Detalles del Plan --}}
                    <div class="px-6 pb-2 flex-1">
                        <ul class="space-y-2 text-sm text-stone-600">
                            @if($plan->platform_fee_percent !== null)
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Comisión por pago de clases: <strong>{{ $plan->platform_fee_percent }}%</strong></span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Botón de Acción --}}
                    <div class="p-6 pt-4">
                        @php
                            $isPendingPlan = $studio->next_plan_id === $plan->id;
                        @endphp

                        @if($isCurrentPlan)
                            {{-- BOTÓN: PLAN ACTUAL --}}
                            <button type="button" disabled
                                    class="w-full py-3 px-4 rounded-xl text-sm font-bold bg-stone-100 text-stone-400 cursor-not-allowed transition-all duration-200">
                                Plan Actual
                            </button>

                        @elseif($isPendingPlan)
                            {{-- BOTÓN: PLAN PROGRAMADO (NUEVO) --}}
                            <button type="button" disabled
                                    class="w-full py-3 px-4 rounded-xl text-sm font-bold bg-amber-50 text-amber-600 border border-amber-200 cursor-not-allowed transition-all duration-200">
                                Plan Programado
                            </button>

                        @else
                            {{-- BOTÓN: CAMBIAR A ESTE PLAN --}}
                            <form action="{{ route('studios.subscribe', ['studio' => $studio->id]) }}" method="POST"
                                  x-on:submit.prevent="openModal($event.target, '{{ $plan->slug }}', '{{ $plan->name }}')">
                                @csrf
                                <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                                <input type="hidden" name="country_id" x-bind:value="selectedCountry">
                                <button type="submit"
                                        class="w-full py-3 px-4 rounded-xl text-sm font-bold transition-all duration-200 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2
                                               @if($isFreePlan)
                                                   bg-zinc-800 text-white hover:bg-zinc-900 focus:ring-zinc-500
                                               @else
                                                   bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white focus:ring-red-500 shadow-sm
                                               @endif">
                                    @if($isFreePlan)
                                        Cambiar a Plan Gratuito
                                    @else
                                        Seleccionar Plan
                                    @endif
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>

        {{-- ================================================================================= --}}
        {{-- MODAL DE CONFIRMACIÓN DINÁMICO (Alpine.js) --}}
        {{-- ================================================================================= --}}
        <div x-show="showModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="modal-title">

            {{-- Overlay --}}
            <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm"
                 x-on:click="closeModal()"
                 aria-hidden="true"></div>

            {{-- Panel del Modal --}}
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10 border border-stone-200"
                 x-on:click.outside="closeModal()">

                {{-- Ícono de advertencia --}}
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-50 text-amber-600 mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>

                {{-- Título del plan destino --}}
                <h3 id="modal-title" class="text-lg font-black text-stone-900 text-center mb-2">
                    Cambiar a <span x-text="selectedPlanName"></span>
                </h3>

                {{-- Mensaje dinámico según el tipo de cambio --}}
                <p class="text-sm text-stone-600 text-center leading-relaxed mb-6"
                   x-text="modalMessage"></p>

                {{-- Acciones --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            x-on:click="closeModal()"
                            class="w-full bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95">
                        Cancelar
                    </button>
                    <button type="button"
                            x-on:click="confirmChange()"
                            class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white transition-all duration-200 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2"
                            :class="selectedPlanIsFree ? 'bg-zinc-800 hover:bg-zinc-900 focus:ring-zinc-500' : 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 focus:ring-red-500'">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        {{-- Mensajes Flash de Laravel (éxito / error desde el backend) --}}
        @if(session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-init="setTimeout(() => show = false, 8000)"
                 class="fixed bottom-6 right-6 z-50 max-w-sm bg-emerald-50 border border-emerald-200 rounded-xl shadow-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                    </div>
                    <button type="button" x-on:click="show = false" class="text-emerald-400 hover:text-emerald-600 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-init="setTimeout(() => show = false, 8000)"
                 class="fixed bottom-6 right-6 z-50 max-w-sm bg-red-50 border border-red-200 rounded-xl shadow-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    <button type="button" x-on:click="show = false" class="text-red-400 hover:text-red-600 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @error('plan_slug')
            <div x-data="{ show: true }"
                 x-show="show"
                 x-transition
                 x-init="setTimeout(() => show = false, 6000)"
                 class="fixed bottom-6 right-6 z-50 max-w-sm bg-red-50 border border-red-200 rounded-xl shadow-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-red-800">{{ $message }}</p>
                    </div>
                    <button type="button" x-on:click="show = false" class="text-red-400 hover:text-red-600 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @enderror

        </div>{{-- Fin contenedor Alpine.js --}}

        {{-- Mensaje cuando no hay planes --}}
        @if($activePlans->isEmpty())
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-stone-100 text-stone-400 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-stone-900 mb-1">No hay planes disponibles</h3>
                <p class="text-sm text-stone-500">No hay planes de suscripción activos en este momento. Vuelve más tarde.</p>
            </div>
        @endif
    </div>

</x-app-layout>

{{-- ================================================================================= --}}
{{-- SCRIPT Alpine.js: Lógica del modal de confirmación --}}
{{-- ================================================================================= --}}
<script>
function subscriptionPlans() {
    return {
        selectedCountry: '{{ $studio->user->country_id ?? 1 }}',
        showModal: false,
        selectedPlanSlug: '',
        selectedPlanName: '',
        selectedPlanIsFree: false,
        modalMessage: '',
        isGrace: false,
        isFree: false,
        currentPlanSlug: 'free',
        activeForm: null,

        init(config) {
            this.isGrace = config.isGrace;
            this.isFree = config.isFree;
            this.currentPlanSlug = config.currentPlanSlug;
        },

        openModal(form, planSlug, planName) {
            this.activeForm = form;
            this.selectedPlanSlug = planSlug;
            this.selectedPlanName = planName;
            this.selectedPlanIsFree = planSlug === 'free';

            // Determinar el mensaje según las reglas de negocio
            if (planSlug === 'free') {
                // ── PASAR A GRATIS ──
                if (this.isGrace) {
                    this.modalMessage = 'Se cancelará tu suscripción, perderás los beneficios premium inmediatamente y se reembolsará tu último pago a tu tarjeta.';
                } else {
                    this.modalMessage = 'Tu plan actual estará activo hasta acabar tu período. Luego volverás al plan gratuito y no se harán más cobros automáticos.';
                }
            } else if (this.currentPlanSlug === 'free') {
                // ── UPGRADE DESDE GRATIS (primer pago, sin gracia) ──
                this.modalMessage = 'Estás a punto de iniciar tu suscripción a ' + planName + '. Serás redirigido a Mercado Pago para completar el pago.';
            } else if (this.isGrace) {
                // ── CAMBIO CON GRACIA (≤ 7 días) ──
                this.modalMessage = 'Se te reembolsará el cobro de tu mes actual inmediatamente y deberás ingresar tus datos para iniciar el nuevo plan hoy.';
            } else {
                // ── CAMBIO SIN GRACIA (> 7 días) — Intención Futura ──
                this.modalMessage = 'Programaremos tu cambio. Disfruta tu plan actual hasta fin de mes; luego te enviaremos el link de pago para iniciar este nuevo plan.';
            }

            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.activeForm = null;
        },

        confirmChange() {
            if (this.activeForm) {
                this.activeForm.submit();
            }
            this.showModal = false;
        }
    };
}
</script>

<style>
    [x-cloak] { display: none !important; }
</style>

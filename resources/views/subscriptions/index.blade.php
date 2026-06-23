<x-app-layout>

    {{-- Tabs de navegación del estudio --}}
    <x-studio-tabs />

    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs --}}
        <div class="flex text-xs font-bold text-zinc-500 mb-3 gap-2 items-center">
            <span class="text-zinc-900">Mi Suscripción</span>
        </div>

        {{-- Cabecera: Título + Plan Actual --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-zinc-900 truncate">
                Mi Suscripción
            </h1>

            {{-- Indicador del Plan Actual --}}
            @php
                $currentPlan = $studio->subscriptionPlan;
                $isFree = $studio->subscription_status === 'free';
            @endphp
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border
                        @if($isFree)
                            bg-zinc-100 text-zinc-600 border-zinc-300
                        @else
                            bg-emerald-50 text-emerald-700 border-emerald-300
                        @endif">
                <span class="text-xs uppercase tracking-wider text-zinc-400">Plan actual</span>
                <span>{{ $currentPlan ? $currentPlan->name : 'Plan Gratuito' }}</span>
            </div>
        </div>

        {{-- Contenedor Alpine.js: Selector de País + Grid de Planes comparten el mismo scope reactivo --}}
        <div x-data="{ selectedCountry: '{{ $studio->user->country_id ?? 1 }}' }">

            {{-- Selector de País/Moneda --}}
            <div class="mb-8">
                <label for="country-select" class="block text-sm font-bold text-zinc-900 mb-2">
                    País de facturación
                </label>
                <div class="relative max-w-xs">
                    <select id="country-select"
                            x-model="selectedCountry"
                            class="appearance-none w-full bg-white border border-zinc-300 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-zinc-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 cursor-pointer">
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                    {{-- Icono chevron personalizado --}}
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400">
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
                                border-zinc-200 hover:border-zinc-400 hover:shadow-md
                            @endif">

                    {{-- Insignia "Plan Actual" --}}
                    @if($isCurrentPlan)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 bg-emerald-500 text-white text-xs font-bold rounded-full shadow-sm">
                            Plan Actual
                        </div>
                    @endif

                    {{-- Cabecera de la Card --}}
                    <div class="p-6 pb-4 text-center border-b border-zinc-100">
                        <h3 class="text-lg font-black text-zinc-900 mb-1">{{ $plan->name }}</h3>
                        @if($plan->features)
                            <p class="text-xs text-zinc-500 leading-relaxed">{{ $plan->features }}</p>
                        @endif
                    </div>

                    {{-- Precio --}}
                    <div class="p-6 pb-4 text-center">
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-3xl md:text-4xl font-black text-zinc-900">
                                {{ $priceFormatted }}
                            </span>
                            <span class="text-sm font-medium text-zinc-400">CLP</span>
                        </div>
                        <p class="text-xs text-zinc-400 mt-1">por mes</p>
                    </div>

                    {{-- Detalles del Plan --}}
                    <div class="px-6 pb-2 flex-1">
                        <ul class="space-y-2 text-sm text-zinc-600">
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
                        @if($isCurrentPlan)
                            <button type="button" disabled
                                    class="w-full py-3 px-4 rounded-xl text-sm font-bold bg-zinc-100 text-zinc-400 cursor-not-allowed transition-all duration-200">
                                Plan Actual
                            </button>
                        @else
                            <form action="{{ route('studios.subscribe', ['studio' => $studio->id]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                                <input type="hidden" name="country_id" x-bind:value="selectedCountry">
                                <button type="submit"
                                        class="w-full py-3 px-4 rounded-xl text-sm font-bold transition-all duration-200 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2
                                               @if($isFreePlan)
                                                   bg-zinc-800 text-white hover:bg-zinc-900 focus:ring-zinc-500
                                               @else
                                                   bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500 shadow-sm
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

        </div>{{-- Fin contenedor Alpine.js --}}

        {{-- Mensaje cuando no hay planes --}}
        @if($activePlans->isEmpty())
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-zinc-100 text-zinc-400 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-zinc-900 mb-1">No hay planes disponibles</h3>
                <p class="text-sm text-zinc-500">No hay planes de suscripción activos en este momento. Vuelve más tarde.</p>
            </div>
        @endif
    </div>

</x-app-layout>

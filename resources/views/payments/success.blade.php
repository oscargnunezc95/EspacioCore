<x-app-layout>
    <div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-md w-full bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 text-center">

            {{-- Icono de Éxito --}}
            <div class="w-16 h-16 bg-emerald-50 border border-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            {{-- ================================================================ --}}
            {{-- PAGO DE FACTURA DE COMISIONES (PLATAFORMA) --}}
            {{-- ================================================================ --}}
            @if($paymentType === 'platform_invoice_payment')
                <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight">¡Factura Pagada!</h1>
                <p class="mt-3 text-sm text-stone-500 leading-relaxed">
                    Tu factura de comisiones ha sido pagada exitosamente. Tu estudio ya no tiene restricciones y todas las funcionalidades están habilitadas.
                </p>

                <div class="mt-8 bg-stone-50 border border-stone-100 rounded-2xl p-4 text-left space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-stone-400 font-medium">Estado de Transacción:</span>
                        <span class="bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider text-[10px]">Aprobado</span>
                    </div>
                    @if($paymentId)
                        <div class="flex justify-between items-center text-xs pt-2 border-t border-stone-200/60">
                            <span class="text-stone-400 font-medium">ID de Pago (MP):</span>
                            <span class="font-mono text-stone-700 font-bold tracking-tight">{{ $paymentId }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-8 space-y-3">
                    @if(!empty($meta['studio_id']))
                        @php
                            $studio = \App\Models\Studio::find($meta['studio_id']);
                        @endphp
                        @if($studio)
                            <a href="{{ route('account.index', ['subdomain' => $studio->subdomain]) }}"
                               class="block w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95">
                                Ir al Panel de Mi Estudio
                            </a>
                        @endif
                    @endif
                    <a href="{{ route('explore') }}"
                       class="block w-full bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95">
                        Volver a EstadoPrisma
                    </a>
                </div>

            {{-- ================================================================ --}}
            {{-- PAGO DE CLASE / RESERVA (ALUMNA) — COMPORTAMIENTO HISTÓRICO --}}
            {{-- ================================================================ --}}
            @else
                <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight">¡Pago Confirmado!</h1>
                <p class="mt-3 text-sm text-stone-500 leading-relaxed">
                    Tu cupo ha sido asegurado con éxito. Hemos enviado el comprobante de reserva y los detalles de acceso a tu correo electrónico.
                </p>

                <div class="mt-8 bg-stone-50 border border-stone-100 rounded-2xl p-4 text-left space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-stone-400 font-medium">Estado de Transacción:</span>
                        <span class="bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider text-[10px]">Aprobado</span>
                    </div>
                    @if($paymentId)
                        <div class="flex justify-between items-center text-xs pt-2 border-t border-stone-200/60">
                            <span class="text-stone-400 font-medium">ID de Pago (MP):</span>
                            <span class="font-mono text-stone-700 font-bold tracking-tight">{{ $paymentId }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-8 space-y-3">
                    <a href="{{ route('global.classes.student') }}"
                       class="block w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95">
                        Ver Mis Clases Asignadas
                    </a>
                    <a href="{{ route('explore') }}"
                       class="block w-full bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95">
                        Explorar Más Talleres
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

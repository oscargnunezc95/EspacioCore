<x-app-layout>
    <div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-md w-full bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 text-center">

            {{-- Icono de Error Semántico --}}
            <div class="w-16 h-16 bg-rose-50 border border-rose-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            {{-- Titulares --}}
            <h1 class="text-3xl font-black text-stone-900 tracking-tight">Pago Rechazado</h1>
            <p class="mt-3 text-sm text-stone-500 leading-relaxed">
                Hubo un inconveniente al procesar tu tarjeta de crédito o débito. Tu banco rechazó la transacción y **no se ha realizado ningún cobro en tu cuenta**.
            </p>

            <div class="mt-6 bg-stone-50 border border-stone-100 rounded-2xl p-4 text-left text-xs text-stone-500 space-y-1.5">
                <p class="font-bold text-stone-800 mb-1">Motivos comunes:</p>
                <p>• Fondos insuficientes en la tarjeta seleccionada.</p>
                <p>• Bloqueo de seguridad preventivo para transacciones online.</p>
                <p>• Datos de vencimiento o CVV mal digitados.</p>
            </div>

            {{-- Botones de Acción --}}
            <div class="mt-8 space-y-3">
                <a href="{{ route('cart.index') }}"
                   class="block w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95">
                    Reintentar Pago en el Carrito
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-md w-full bg-white border border-stone-200 rounded-3xl p-6 sm:p-8 text-center">

            {{-- Icono de Espera Semántico --}}
            <div class="w-16 h-16 bg-amber-50 border border-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            {{-- Titulares --}}
            <h1 class="text-3xl font-black text-stone-900 tracking-tight">Pago en Procesamiento</h1>
            <p class="mt-3 text-sm text-stone-500 leading-relaxed">
                Mercado Pago está verificando la transacción con tu institución financiera. No te preocupes, tu reserva se mantendrá retenida temporalmente mientras se confirma el flujo bancario.
            </p>

            <div class="mt-6 bg-amber-50/50 border border-amber-100 rounded-2xl p-4 text-left">
                <p class="text-xs text-amber-800 font-medium leading-normal">
                    💡 **Nota:** Una vez aprobado, el sistema liberará tus cupos e internamente se te notificará por correo. No es necesario que intentes pagar de nuevo.
                </p>
            </div>

            {{-- Botones de Acción --}}
            <div class="mt-8 space-y-3">
                <a href="{{ route('cart.index') }}"
                   class="block w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95">
                    Volver a Mis Reservas
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
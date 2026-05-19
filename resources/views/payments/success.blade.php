<x-app-layout>
    <div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-md w-full bg-white border border-zinc-200 rounded-3xl p-6 sm:p-8 text-center">
            
            {{-- Icono de Éxito Semántico --}}
            <div class="w-16 h-16 bg-emerald-50 border border-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            {{-- Titulares --}}
            <h1 class="text-3xl font-black text-zinc-900 tracking-tight">¡Pago Confirmado!</h1>
            <p class="mt-3 text-sm text-zinc-500 leading-relaxed">
                Tu cupo ha sido asegurado con éxito. Hemos enviado el comprobante de reserva y los detalles de acceso a tu correo electrónico.
            </p>

            {{-- Detalles de la Transacción --}}
            <div class="mt-8 bg-zinc-50 border border-zinc-100 rounded-2xl p-4 text-left space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-zinc-400 font-medium">Estado de Transacción:</span>
                    <span class="bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider text-[10px]">Aprobado</span>
                </div>
                @if($paymentId)
                    <div class="flex justify-between items-center text-xs pt-2 border-t border-zinc-200/60">
                        <span class="text-zinc-400 font-medium">ID de Pago (MP):</span>
                        <span class="font-mono text-zinc-700 font-bold tracking-tight">{{ $paymentId }}</span>
                    </div>
                @endif
            </div>

            {{-- Botones de Acción Accionables --}}
            <div class="mt-8 space-y-3">
                <a href="{{ route('global.classes.student') }}" 
                   class="block w-full bg-zinc-900 text-white font-bold py-3.5 px-4 rounded-xl shadow-sm hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all duration-200 text-sm active:scale-[0.98]">
                    Ver Mis Clases Asignadas
                </a>
                <a href="{{ route('explore') }}" 
                   class="block w-full bg-zinc-100 text-zinc-600 font-bold py-3.5 px-4 rounded-xl hover:bg-zinc-200/70 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-200 transition-all duration-200 text-sm active:scale-[0.98]">
                    Explorar Más Talleres
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
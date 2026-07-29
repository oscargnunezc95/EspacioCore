<x-app-layout>
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-stone-50 w-full">
    
    <div class="w-full sm:max-w-md mt-6 px-8 bg-white shadow-xl border border-stone-100 overflow-hidden sm:rounded-[2rem]">
        
        <div class="flex justify-center mb-6">
            <!-- Ícono de Correo -->
            <div class="w-16 h-16 bg-stone-100 rounded-full flex items-center justify-center text-stone-900">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
        </div>

        <h2 class="text-2xl font-black text-center text-stone-900 mb-4 tracking-tight">Revisa tu correo</h2>

        <div class="mb-6 text-sm text-stone-600 text-center leading-relaxed font-medium">
            ¡Gracias por unirte! Antes de continuar, necesitamos verificar tu identidad. 
            Haz clic en el enlace que acabamos de enviar a tu correo electrónico.
        </div>

        <!-- Mensaje de éxito al reenviar -->
        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 p-4 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl flex gap-3 text-center">
                Se ha enviado un nuevo enlace de verificación a tu bandeja de entrada.
            </div>
        @endif

        <div class="mt-8 flex flex-col space-y-4">
            <!-- Botón para Reenviar -->
            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 transition-all duration-300 active:scale-95">
                    Reenviar correo de verificación
                </button>
            </form>

            <!-- Botón para Cerrar Sesión -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full text-sm text-stone-500 hover:text-stone-900 font-bold underline decoration-stone-300 underline-offset-4 transition-colors text-center py-2">
                    Cerrar sesión
                </button>
            </form>
        </div>
        
    </div>
</div>
</x-app-layout>
<x-guest-layout>
    <!-- Contenedor Principal (Centrado y Responsivo) -->
    <div class="max-w-md mx-auto mt-16 mb-24 px-4 sm:px-6">
        
        <!-- Tarjeta Limpia (Sin sombras exageradas, bordes consistentes) -->
        <div class="bg-white border border-zinc-200 rounded-xl p-6 sm:p-8 shadow-sm">
            
            <!-- Jerarquía Visual: Título claro -->
            <h1 class="text-2xl font-bold text-zinc-900 mb-4">
                Recuperar contraseña
            </h1>

            <div class="mb-6 text-sm text-zinc-600 leading-relaxed">
                ¿Olvidaste tu contraseña? No hay problema. Indícanos tu correo electrónico y te enviaremos un enlace para que puedas crear una nueva.
            </div>

            <!-- Alertas de Sesión -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Campo de Correo -->
                <div>
                    <x-input-label for="email" value="Correo Electrónico" class="text-zinc-700 font-medium" />
                    
                    <x-text-input id="email" 
                                  class="block mt-1 w-full border-zinc-300 focus:border-fuchsia-500 focus:ring-fuchsia-500 transition-colors duration-200" 
                                  type="email" 
                                  name="email" 
                                  :value="old('email')" 
                                  required 
                                  autofocus />
                                  
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm" />
                </div>

                <!-- Acción Principal -->
                <div class="flex items-center justify-end mt-8">
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 bg-zinc-900 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 focus:ring-offset-2 transition-all duration-200 active:scale-95">
                        Enviar enlace de recuperación
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Enlace de regreso (Opcional, mejora la UX) -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors duration-200">
                Volver a Iniciar Sesión
            </a>
        </div>
        
    </div>
</x-guest-layout>
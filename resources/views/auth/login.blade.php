<x-guest-layout>
<div class="flex flex-col lg:flex-row w-full min-h-screen">
    
    <div class="flex flex-col justify-center flex-1 px-8 py-8 sm:px-16 lg:px-24 xl:px-32 bg-white">
        <div class="w-full max-w-sm mx-auto lg:mx-0">
            
            <div class="mb-10">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-800">EstadoPrisma</h1>
                <p class="mt-2 text-sm text-zinc-500 font-light">El centro de tu movimiento. Inicia sesión para gestionar tu espacio.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-100">
                    <p class="text-sm text-rose-600 font-medium">Credenciales incorrectas. Por favor, intenta de nuevo.</p>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-600">Correo electrónico</label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" autocomplete="email" required autofocus
                            class="block w-full px-4 py-3 bg-zinc-100 border-transparent rounded-xl text-zinc-800 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-300 focus:ring-2 focus:ring-zinc-200 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-zinc-600">Contraseña</label>
                        <a href="#" class="text-sm font-medium text-zinc-500 hover:text-zinc-800 transition-colors duration-200">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    <div class="mt-2">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="block w-full px-4 py-3 bg-zinc-100 border-transparent rounded-xl text-zinc-800 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-300 focus:ring-2 focus:ring-zinc-200 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 rounded border-zinc-300 text-zinc-800 focus:ring-zinc-500 transition-all duration-200">
                    <label for="remember" class="ml-2 block text-sm text-zinc-500">
                        Mantener sesión iniciada
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="flex w-full justify-center px-4 py-3 text-sm font-medium text-white bg-zinc-800 rounded-xl hover:bg-zinc-700 focus:outline-none active:scale-[0.98] transition-all duration-200 shadow-sm">
                        Entrar al Espacio
                    </button>
                </div>
            </form>

            <div class="mt-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-zinc-100"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-zinc-400 font-light">O continúa con</span>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('google.redirect') }}" 
                    class="flex w-full items-center justify-center gap-3 px-4 py-3 text-sm font-medium text-zinc-600 bg-white border border-zinc-200 rounded-xl hover:bg-zinc-50 focus:outline-none active:scale-[0.98] transition-all duration-200">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="hidden lg:flex lg:flex-1 relative bg-zinc-900 overflow-hidden">
        <img class="absolute inset-0 h-full w-full object-cover opacity-[0.65] mix-blend-overlay grayscale-[30%]" 
             src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" 
             alt="Energía y movimiento EstadoPrisma">
        
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/90 via-zinc-900/20 to-transparent"></div>
        
        <div class="absolute bottom-12 left-12 right-12 z-10 text-white">
            <blockquote class="space-y-4">
                <p class="text-2xl font-light leading-snug">
                    "La gestión debe ser invisible para que el arte sea el único protagonista. Sincroniza tu estudio en segundos."
                </p>
            </blockquote>
        </div>
    </div>

</div>
</x-guest-layout>
<x-guest-layout>
<div class="flex flex-col lg:flex-row w-full min-h-screen">
    
    <div class="flex flex-col justify-center flex-1 px-8 py-12 sm:px-16 lg:px-24 xl:px-32 bg-white">
        <div class="w-full max-w-sm mx-auto lg:mx-0">
            
            <div class="mb-10">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Únete al Espacio</h1>
                <p class="mt-2 text-sm text-zinc-500 font-medium">Crea tu cuenta gratis y comienza a gestionar tu estudio.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-100 flex gap-3">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <ul class="text-sm text-rose-700 font-medium list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Botón de Registro con Google -->
            <a href="{{ route('google.redirect') }}" class="flex items-center justify-center w-full px-4 py-3 text-sm font-bold text-zinc-700 bg-white border border-zinc-300 rounded-xl hover:bg-zinc-50 hover:text-zinc-900 transition-all duration-200 shadow-sm gap-3 active:scale-[0.98]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Registrarse con Google
            </a>

            <!-- Separador Visual -->
            <div class="relative mt-8 mb-6">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-zinc-200"></div>
                </div>
                <div class="relative flex justify-center text-sm font-medium">
                    <span class="px-3 bg-white text-zinc-400">O regístrate con tu correo</span>
                </div>
            </div>

            <!-- Formulario Tradicional -->
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-bold text-zinc-700">Nombre completo</label>
                    <div class="mt-1.5">
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                            class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-zinc-700">Correo electrónico</label>
                    <div class="mt-1.5">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                            class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-zinc-700">Contraseña</label>
                    <div class="mt-1.5">
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                            class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-zinc-700">Confirmar contraseña</label>
                    <div class="mt-1.5">
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="flex w-full justify-center px-4 py-3 text-sm font-bold text-white bg-zinc-900 rounded-xl hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 active:scale-[0.98] transition-all duration-200 shadow-sm">
                        Crear mi cuenta
                    </button>
                    <!-- Aviso de verificación UX -->
                    <p class="mt-3 text-center text-xs text-zinc-500 font-medium">
                        Te enviaremos un correo para verificar tu cuenta de forma segura.
                    </p>
                </div>
            </form>

            <div class="mt-8 text-center text-sm">
                <span class="text-zinc-500 font-medium">¿Ya tienes una cuenta?</span>
                <a href="{{ route('login') }}" class="font-bold text-zinc-900 hover:text-zinc-600 transition-colors duration-200 underline decoration-zinc-300 underline-offset-4">
                    Inicia sesión aquí
                </a>
            </div>
            
        </div>
    </div>

    <!-- Columna Imagen -->
    <div class="hidden lg:flex lg:flex-1 relative bg-zinc-900 overflow-hidden">
        <img class="absolute inset-0 h-full w-full object-cover opacity-[0.65] mix-blend-overlay scale-105 grayscale-[30%]" 
             src="https://images.unsplash.com/photo-1518834107812-67b0b7c58434?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" 
             alt="Comunidad y movimiento EspacioCore">
        
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/95 via-zinc-900/30 to-transparent"></div>
        
        <div class="absolute bottom-12 left-12 right-12 z-10 text-white">
            <blockquote class="space-y-4">
                <h3 class="text-3xl font-bold tracking-tight">Tu estudio, tu red.</h3>
                <p class="text-lg text-zinc-300 font-medium max-w-md leading-relaxed">
                    Miles de alumnas/os están buscando dónde entrenar hoy. Conecta tu espacio, organiza tus clases y deja que la plataforma haga el resto.
                </p>
            </blockquote>
        </div>
    </div>

</div>
</x-guest-layout>
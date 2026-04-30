@extends('layouts.guest')

@section('content')
<div class="flex flex-col lg:flex-row w-full min-h-screen">
    
    <div class="flex flex-col justify-center flex-1 px-8 py-12 sm:px-16 lg:px-24 xl:px-32 bg-white">
        <div class="w-full max-w-sm mx-auto lg:mx-0">
            
            <div class="mb-10">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-800">Únete al Espacio</h1>
                <p class="mt-2 text-sm text-zinc-500 font-light">Crea tu cuenta gratis y comienza a gestionar tu estudio.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-100">
                    <ul class="text-sm text-rose-600 font-medium list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-600">Nombre completo</label>
                    <div class="mt-1">
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                            class="block w-full px-4 py-3 bg-zinc-100 border-transparent rounded-xl text-zinc-800 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-300 focus:ring-2 focus:ring-zinc-200 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-600">Correo electrónico</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                            class="block w-full px-4 py-3 bg-zinc-100 border-transparent rounded-xl text-zinc-800 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-300 focus:ring-2 focus:ring-zinc-200 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-600">Contraseña</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                            class="block w-full px-4 py-3 bg-zinc-100 border-transparent rounded-xl text-zinc-800 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-300 focus:ring-2 focus:ring-zinc-200 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-zinc-600">Confirmar contraseña</label>
                    <div class="mt-1">
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            class="block w-full px-4 py-3 bg-zinc-100 border-transparent rounded-xl text-zinc-800 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-300 focus:ring-2 focus:ring-zinc-200 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="flex w-full justify-center px-4 py-3 text-sm font-medium text-white bg-zinc-800 rounded-xl hover:bg-zinc-700 focus:outline-none active:scale-[0.98] transition-all duration-200 shadow-sm">
                        Crear mi cuenta
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center text-sm">
                <span class="text-zinc-500">¿Ya tienes una cuenta?</span>
                <a href="{{ route('login') }}" class="font-medium text-zinc-800 hover:text-zinc-600 transition-colors duration-200">
                    Inicia sesión aquí
                </a>
            </div>
            
        </div>
    </div>

    <div class="hidden lg:flex lg:flex-1 relative bg-zinc-900 overflow-hidden">
        <img class="absolute inset-0 h-full w-full object-cover opacity-[0.65] mix-blend-overlay scale-105 grayscale-[30%]" 
             src="https://images.unsplash.com/photo-1518834107812-67b0b7c58434?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" 
             alt="Comunidad y movimiento EspacioCore">
        
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/95 via-zinc-900/30 to-transparent"></div>
        
        <div class="absolute bottom-12 left-12 right-12 z-10 text-white">
            <blockquote class="space-y-4">
                <h3 class="text-3xl font-semibold tracking-tight">Tu estudio, tu red.</h3>
                <p class="text-lg text-zinc-300 font-light max-w-md leading-relaxed">
                    Miles de alumnas están buscando dónde entrenar hoy. Conecta tu espacio, organiza tus clases y deja que la plataforma haga el resto.
                </p>
            </blockquote>
        </div>
    </div>

</div>
@endsection
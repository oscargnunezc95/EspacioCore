@extends('layouts.guest')

@section('content')
<main class="flex items-center justify-center w-full bg-slate-50 px-6">
    <div class="text-center space-y-12">
        
        <div class="space-y-4">
            <h1 class="text-7xl md:text-9xl font-black tracking-tighter text-slate-900">
                EspacioCore
            </h1>
            <p class="text-xl md:text-2xl text-slate-500 font-light tracking-wide uppercase">
                Arte · Gestión · Movimiento
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" 
                       class="w-full sm:w-auto px-10 py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 hover:scale-105 active:scale-95 transition-all duration-200 shadow-xl shadow-slate-200">
                        Ir al Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="w-full sm:w-auto px-10 py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 hover:scale-105 active:scale-95 transition-all duration-200 shadow-xl shadow-slate-200">
                        Iniciar Sesión
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" 
                           class="w-full sm:w-auto px-10 py-4 bg-white text-slate-900 border border-slate-200 rounded-xl font-bold hover:bg-slate-50 hover:scale-105 active:scale-95 transition-all duration-200">
                            Unirse al Espacio
                        </a>
                    @endif
                @endauth
            @endif
        </div>

    </div>
</main>
@endsection
@extends('layouts.guest')

@section('content')
<div class="bg-zinc-50 w-full min-h-screen pb-20">
    
    <div class="bg-white border-b border-zinc-200/60 pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-8">
                <h1 class="text-4xl font-bold tracking-tight text-zinc-900">Encuentra tu próximo desafío</h1>
                <p class="mt-4 text-lg text-zinc-500 font-light">Telas, lira, pole dance, contemporáneo. Descubre las mejores clases en tu ciudad.</p>
            </div>

            <div class="max-w-3xl mx-auto">
                <form class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" placeholder="Ej. Telas Aéreas Básicas..." class="block w-full pl-11 pr-4 py-3 bg-zinc-100 border-transparent rounded-xl text-zinc-900 placeholder-zinc-500 focus:bg-white focus:border-zinc-300 focus:ring-2 focus:ring-zinc-200 transition-all duration-200">
                    </div>
                    <button type="submit" class="inline-flex justify-center items-center px-8 py-3 text-sm font-medium rounded-xl text-white bg-zinc-900 hover:bg-zinc-800 focus:outline-none active:scale-[0.98] transition-all duration-200 shadow-sm">
                        Buscar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
        
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-zinc-800">Clases disponibles hoy</h2>
            <span class="text-sm font-medium text-zinc-500">Mostrando 3 resultados</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="group bg-white rounded-2xl border border-zinc-200/60 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="relative h-48 bg-zinc-200 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Telas Aéreas" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 grayscale-[20%]">
                    <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-zinc-900">
                        Quedan 2 cupos
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Telas Aéreas</span>
                            <h3 class="text-lg font-bold text-zinc-900 mt-1">Acondicionamiento y Figuras</h3>
                        </div>
                        <span class="text-lg font-black text-zinc-900">$8.000</span>
                    </div>
                    <div class="space-y-2 mb-6 text-sm text-zinc-600">
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Hoy, 19:30 - 21:00 hrs
                        </p>
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Estudio Gravedad Zero
                        </p>
                    </div>
                    <a href="{{ route('register') }}" class="block w-full text-center px-4 py-3 bg-zinc-100 hover:bg-zinc-900 hover:text-white text-zinc-900 text-sm font-semibold rounded-xl transition-colors duration-200">
                        Reservar Clase
                    </a>
                </div>
            </div>

            <div class="group bg-white rounded-2xl border border-zinc-200/60 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="relative h-48 bg-zinc-200 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1518834107812-67b0b7c58434?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Danza Contemporánea" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 grayscale-[20%]">
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Danza Contemp.</span>
                            <h3 class="text-lg font-bold text-zinc-900 mt-1">Flow y Piso</h3>
                        </div>
                        <span class="text-lg font-black text-zinc-900">$7.000</span>
                    </div>
                    <div class="space-y-2 mb-6 text-sm text-zinc-600">
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Mañana, 10:00 - 11:30 hrs
                        </p>
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Movimiento Studio
                        </p>
                    </div>
                    <a href="{{ route('register') }}" class="block w-full text-center px-4 py-3 bg-zinc-100 hover:bg-zinc-900 hover:text-white text-zinc-900 text-sm font-semibold rounded-xl transition-colors duration-200">
                        Reservar Clase
                    </a>
                </div>
            </div>

            <div class="group bg-white rounded-2xl border border-zinc-200/60 overflow-hidden opacity-75">
                <div class="relative h-48 bg-zinc-200 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Lira" class="w-full h-full object-cover grayscale-[50%]">
                    <div class="absolute inset-0 bg-zinc-900/40 flex items-center justify-center">
                        <span class="bg-zinc-900 text-white px-4 py-2 rounded-lg font-bold tracking-wide uppercase text-sm">Agotada</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Lira</span>
                            <h3 class="text-lg font-bold text-zinc-900 mt-1">Secuencias Nivel 1</h3>
                        </div>
                    </div>
                    <div class="space-y-2 mb-6 text-sm text-zinc-600">
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Sábado, 12:00 - 13:30 hrs
                        </p>
                    </div>
                    <button disabled class="block w-full text-center px-4 py-3 bg-zinc-50 text-zinc-400 text-sm font-semibold rounded-xl cursor-not-allowed border border-zinc-200">
                        Sin cupos
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
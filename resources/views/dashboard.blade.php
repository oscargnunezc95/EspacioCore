<x-app-layout>
    <div class="py-12 bg-zinc-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-800">
                    Hola, {{ explode(' ', Auth::user()->name)[0] }} 👋
                </h2>
                <p class="mt-2 text-lg text-zinc-500 font-light">
                    Tu espacio, tu movimiento. Aquí tienes el resumen de tu actividad.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="col-span-1 md:col-span-2 bg-white rounded-2xl border border-zinc-200/60 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="p-6 md:p-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                        <div class="space-y-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600">
                                Próxima Reserva
                            </span>
                            <h3 class="text-2xl font-semibold text-zinc-800">Telas Aéreas - Nivel Intermedio</h3>
                            <p class="text-zinc-500 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Estudio Gravedad Zero
                            </p>
                            <p class="text-zinc-600 font-medium">Hoy, 19:00 hrs</p>
                        </div>
                        
                        <div class="w-full sm:w-auto">
                            <button class="w-full inline-flex justify-center items-center px-6 py-3 border border-zinc-200 text-sm font-medium rounded-xl text-zinc-600 bg-zinc-50 hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none transition-all duration-200">
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-rose-50/50 rounded-2xl border border-rose-100 p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-medium text-rose-700 uppercase tracking-wide">Pendiente de Pago</h3>
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="mt-4 text-4xl font-bold text-rose-900">$15.000</p>
                        <p class="mt-1 text-sm text-rose-600/80">1 clase(s) por regularizar</p>
                    </div>
                    <button class="mt-6 w-full inline-flex justify-center items-center px-4 py-3 text-sm font-medium rounded-xl text-white bg-rose-600 hover:bg-rose-700 focus:outline-none transition-all duration-200">
                        Pagar ahora
                    </button>
                </div>
            </div>

            <div class="mt-12 bg-zinc-900 rounded-3xl overflow-hidden relative shadow-lg">
                <div class="absolute inset-0 opacity-[0.15] bg-[url('https://images.unsplash.com/photo-1547153760-18fc86324498?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')] bg-cover bg-center"></div>
                
                <div class="relative p-8 md:p-12 lg:p-16 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-8">
                    <div class="max-w-2xl text-white space-y-4">
                        <h3 class="text-3xl font-semibold tracking-tight">¿Lista para probar algo nuevo?</h3>
                        <p class="text-zinc-400 text-lg font-light">Descubre estudios de danza, telas, lira y más disciplinas cerca de ti.</p>
                    </div>
                    <a href="#" class="shrink-0 inline-flex items-center px-8 py-4 text-lg font-medium rounded-xl text-zinc-900 bg-white hover:bg-zinc-100 active:scale-95 transition-all duration-200 shadow-md">
                        Explorar Clases
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
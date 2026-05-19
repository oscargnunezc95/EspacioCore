<x-guest-layout>
    <div class="bg-white min-h-screen">
        
        {{-- ========================================== --}}
        {{-- HERO SECTION (Lo que hace la plataforma) --}}
        {{-- ========================================== --}}
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-16 text-center">
            <h1 class="text-5xl md:text-6xl font-black text-zinc-900 tracking-tighter mb-6">
                Gestiona tu estudio, <br class="hidden md:block">
                <span class="text-indigo-600">nosotros automatizamos el resto.</span>
            </h1>
            <p class="mt-4 text-lg md:text-xl text-zinc-500 font-medium max-w-2xl mx-auto leading-relaxed">
                EstadoPrisma es la plataforma todo-en-uno que centraliza tus reservas, automatiza los pagos y le da a tus alumnos una experiencia de clase mundial.
            </p>
            
            {{-- Badges de Características Rápidas --}}
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-zinc-50 border border-zinc-200 rounded-full text-sm font-bold text-zinc-700 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    Reservas 24/7
                </span>
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-zinc-50 border border-zinc-200 rounded-full text-sm font-bold text-zinc-700 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    Pagos Integrados
                </span>
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-zinc-50 border border-zinc-200 rounded-full text-sm font-bold text-zinc-700 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    Portal de Alumnos
                </span>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- SECCIÓN DE PRECIOS (Con Toggle Alpine.js) --}}
        {{-- ========================================== --}}
        <div x-data="{ annual: true }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 mb-24">
            
            <div class="text-center mb-12">
                <h2 class="text-3xl font-black text-zinc-900 mb-4">Planes simples, sin letras chicas</h2>
                
                {{-- Toggle Mensual / Anual --}}
                <div class="flex items-center justify-center gap-4 mt-8">
                    <span class="text-sm font-bold cursor-pointer transition-colors" :class="annual ? 'text-zinc-400' : 'text-zinc-900'" @click="annual = false">Mensual</span>
                    
                    <button type="button" class="relative inline-flex h-7 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2" 
                            :class="annual ? 'bg-indigo-600' : 'bg-zinc-200'" 
                            @click="annual = !annual">
                        <span class="sr-only">Cambiar plan</span>
                        <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" 
                              :class="annual ? 'translate-x-7' : 'translate-x-0'"></span>
                    </button>
                    
                    <span class="text-sm font-bold cursor-pointer transition-colors flex items-center gap-2" :class="annual ? 'text-zinc-900' : 'text-zinc-400'" @click="annual = true">
                        Anual
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] uppercase tracking-widest px-2 py-0.5 rounded-md font-black">Ahorras 20%</span>
                    </span>
                </div>
            </div>

            {{-- GRILLA DE PLANES --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto items-center">
                
                {{-- PLAN BÁSICO --}}
                <div class="bg-white border border-zinc-200 rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full transform-gpu isolate">
                    <div class="mb-6">
                        <h3 class="text-lg font-black text-zinc-900">Básico</h3>
                        <p class="text-zinc-500 text-sm mt-2 font-medium">Ideal para instructores independientes o estudios nuevos.</p>
                    </div>
                    
                    <div class="mb-8">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-zinc-900" x-text="annual ? '$24.000' : '$29.990'"></span>
                            <span class="text-sm font-bold text-zinc-400">/mes</span>
                        </div>
                        <p class="text-xs text-zinc-400 font-medium mt-1" x-show="annual" x-cloak>Facturado anualmente ($288.000)</p>
                    </div>

                    <ul class="space-y-4 mb-8 flex-1">
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-700">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Hasta 100 alumnos activos
                        </li>
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-700">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Gestión de reservas básica
                        </li>
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-700">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Perfil público del estudio
                        </li>
                    </ul>

                    <a href="{{ route('register') }}" class="w-full block text-center bg-zinc-100 text-zinc-900 hover:bg-zinc-200 font-bold py-3.5 rounded-xl transition-colors active:scale-95">
                        Comenzar Gratis
                    </a>
                </div>

                {{-- PLAN PRO (Destacado) --}}
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 shadow-xl md:scale-105 hover:-translate-y-1 transition-transform duration-300 flex flex-col h-full relative transform-gpu isolate">
                    
                    {{-- Etiqueta Popular --}}
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-indigo-500 text-white text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-full shadow-md">
                            Más Popular
                        </span>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-black text-white">Pro</h3>
                        <p class="text-zinc-400 text-sm mt-2 font-medium">Para estudios establecidos que buscan escalar y automatizar.</p>
                    </div>
                    
                    <div class="mb-8">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-white" x-text="annual ? '$45.000' : '$54.990'"></span>
                            <span class="text-sm font-bold text-zinc-500">/mes</span>
                        </div>
                        <p class="text-xs text-zinc-500 font-medium mt-1" x-show="annual" x-cloak>Facturado anualmente ($540.000)</p>
                    </div>

                    <ul class="space-y-4 mb-8 flex-1">
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-300">
                            <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Alumnos ilimitados
                        </li>
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-300">
                            <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Integración con Mercado Pago
                        </li>
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-300">
                            <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Control de aforo y listas de espera
                        </li>
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-300">
                            <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Múltiples profesores
                        </li>
                    </ul>

                    <a href="{{ route('register') }}" class="w-full block text-center bg-indigo-600 text-white hover:bg-indigo-500 font-bold py-3.5 rounded-xl shadow-sm transition-colors active:scale-95">
                        Seleccionar Pro
                    </a>
                </div>

                {{-- PLAN ÉLITE --}}
                <div class="bg-white border border-zinc-200 rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full transform-gpu isolate">
                    <div class="mb-6">
                        <h3 class="text-lg font-black text-zinc-900">Élite</h3>
                        <p class="text-zinc-500 text-sm mt-2 font-medium">La solución completa para franquicias o múltiples sucursales.</p>
                    </div>
                    
                    <div class="mb-8">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-zinc-900" x-text="annual ? '$89.000' : '$109.990'"></span>
                            <span class="text-sm font-bold text-zinc-400">/mes</span>
                        </div>
                        <p class="text-xs text-zinc-400 font-medium mt-1" x-show="annual" x-cloak>Facturado anualmente ($1.068.000)</p>
                    </div>

                    <ul class="space-y-4 mb-8 flex-1">
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-700">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Todo lo del plan Pro
                        </li>
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-700">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Múltiples sucursales
                        </li>
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-700">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Reportes financieros avanzados
                        </li>
                        <li class="flex items-start gap-3 text-sm font-medium text-zinc-700">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Soporte prioritario por WhatsApp
                        </li>
                    </ul>

                    <a href="{{ route('register') }}" class="w-full block text-center bg-zinc-100 text-zinc-900 hover:bg-zinc-200 font-bold py-3.5 rounded-xl transition-colors active:scale-95">
                        Contactar Ventas
                    </a>
                </div>

            </div>
        </div>
        
    </div>
</x-guest-layout>
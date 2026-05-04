<x-app-layout>
    
    <x-slot name="header">
        
        <!-- 1. Pestañas en la parte superior -->
        <x-studio-tabs />

        <!-- 2. Header (Migajas, Título y Botones) separado hacia abajo -->
        <div class="mt-8">
            <x-studio-header 
                title="Dashboard principal" 
                :breadcrumbs="[
                    ['name' => 'Panel principal']
                ]"
            >
            </x-studio-header>
        </div>
        
    </x-slot>
    
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Tarjeta de Bienvenida -->
        <div class="bg-white shadow-sm border border-zinc-200 rounded-3xl mb-8 overflow-hidden">
            <div class="p-8 md:p-10 flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-1 text-zinc-900">¡Hola, {{ explode(' ', Auth::user()->name)[0] }}!</h3>
                    <p class="text-zinc-500 font-medium text-lg">
                        Bienvenida al panel de gestión de <span class="font-bold text-zinc-800">{{ $currentStudio->name }}</span>.
                    </p>
                </div>
                <div class="hidden md:flex h-16 w-16 bg-zinc-50 border border-zinc-100 text-zinc-900 rounded-2xl items-center justify-center font-bold text-2xl shadow-inner">
                    👋
                </div>
            </div>
        </div>

        <!-- Tarjetas de Módulos (Grid) -->
        <!-- Observa la limpieza en los route(), ya no necesitan el array del subdominio -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Módulo alumnas/os -->
            <a href="{{ route('students.index') }}" class="group bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm hover:shadow-xl hover:border-zinc-300 hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="text-4xl font-black text-zinc-900 tracking-tighter">{{ $studentsCount ?? 0 }}</span>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-zinc-900 group-hover:text-zinc-600 transition-colors duration-200">Directorio de alumnas/os</h4>
                    <p class="text-sm text-zinc-500 mt-2 font-medium">Gestiona inscripciones, créditos y perfiles.</p>
                </div>
            </a>

            <!-- Módulo Clases -->
            <a href="{{ route('workshops.index') }}" class="group bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm hover:shadow-xl hover:border-zinc-300 hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-4xl font-black text-zinc-900 tracking-tighter">{{ $workshopsCount ?? 0 }}</span>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-zinc-900 group-hover:text-zinc-600 transition-colors duration-200">Configurar Clases</h4>
                    <p class="text-sm text-zinc-500 mt-2 font-medium">Crea talleres, define horarios y cupos.</p>
                </div>
            </a>

            <!-- Módulo Planificación -->
            <a href="{{ route('entrenamientos.index') }}" class="group bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm hover:shadow-xl hover:border-zinc-300 hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div class="text-amber-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-zinc-900 group-hover:text-zinc-600 transition-colors duration-200">Planificación Mensual</h4>
                    <p class="text-sm text-zinc-500 mt-2 font-medium">Genera el calendario y toma asistencia.</p>
                </div>
            </a>

        </div>
    </div>
</x-app-layout>
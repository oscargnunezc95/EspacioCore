<div class="border-b border-zinc-200">
    <nav class="-mb-px flex space-x-8 overflow-x-auto hide-scrollbar" aria-label="Tabs">
        
        <!-- Tab: Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 flex items-center gap-2
                  {{ request()->routeIs('dashboard') 
                      ? 'border-zinc-900 text-zinc-900' 
                      : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Panel General
        </a>

        <!-- Tab: alumnas/os -->
        <a href="{{ route('students.index') }}" 
           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 flex items-center gap-2
                  {{ request()->routeIs('students.*') 
                      ? 'border-zinc-900 text-zinc-900' 
                      : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('students.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Alumnas/os
        </a>

        <!-- Tab: Equipo -->
        <a href="{{ route('teachers.index') }}" 
           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 flex items-center gap-2
                  {{ request()->routeIs('teachers.*') 
                      ? 'border-zinc-900 text-zinc-900' 
                      : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('teachers.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
            Profesores
        </a>

        <!-- Tab: Clases -->
        <a href="{{ route('workshops.index') }}" 
           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 flex items-center gap-2
                  {{ request()->routeIs('workshops.*') 
                      ? 'border-zinc-900 text-zinc-900' 
                      : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('workshops.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Configurar talleres
        </a>

        <!-- Tab: Promociones (NUEVO) -->
        <a href="{{ route('promotions.index') }}" 
           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 flex items-center gap-2
                  {{ request()->routeIs('promotions.*') 
                      ? 'border-zinc-900 text-zinc-900' 
                      : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('promotions.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            Promociones
        </a>

        <!-- Tab: Planificación -->
        <a href="{{ route('entrenamientos.index') }}" 
           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 flex items-center gap-2
                  {{ request()->routeIs('entrenamientos.*') 
                      ? 'border-zinc-900 text-zinc-900' 
                      : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('entrenamientos.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            Clases mensuales
        </a>

    </nav>
</div>

<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
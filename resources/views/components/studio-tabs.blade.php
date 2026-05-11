<div class="bg-white rounded-3xl border border-zinc-200 shadow-sm overflow-hidden mb-8">
    
    {{-- ========================================== --}}
    {{-- HEADER DEL ESTUDIO (BANNER + LOGO) --}}
    {{-- ========================================== --}}
    <div class="relative">
        {{-- Banner Background --}}
        <div class="h-32 w-full bg-gradient-to-br from-zinc-800 to-zinc-900 relative overflow-hidden">
            @if($currentStudio->logo_path)
                <img src="{{ asset('storage/' . $currentStudio->logo_path) }}" class="w-full h-full object-cover opacity-40 blur-sm scale-105" alt="Cover">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
        </div>

        {{-- Contenido Superpuesto (Logo + Info) --}}
        <div class="absolute bottom-0 left-0 w-full px-6 pb-4 md:px-8 flex items-end gap-4">
            {{-- Logo --}}
            <div class="shrink-0 mb-[-24px] relative z-10">
                @if($currentStudio->logo_path)
                    <img src="{{ asset('storage/' . $currentStudio->logo_path) }}" alt="Logo" class="h-20 w-20 md:h-24 md:w-24 rounded-2xl object-cover border-4 border-white shadow-md bg-white">
                @else
                    <div class="h-20 w-20 md:h-24 md:w-24 rounded-2xl bg-zinc-900 border-4 border-white text-white flex items-center justify-center font-black text-3xl shadow-md">
                        {{ substr($currentStudio->name, 0, 1) }}
                    </div>
                @endif
            </div>

            {{-- Título y Subdominio --}}
            <div class="flex-1 pb-1">
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight drop-shadow-md leading-tight">
                    {{ $currentStudio->name }}
                </h1>
                <p class="text-sm font-medium text-zinc-300 drop-shadow flex items-center gap-1.5 mt-0.5">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    {{ $currentStudio->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'espaciocore.test' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Espaciador para el logo que sobresale --}}
    <div class="h-full w-full bg-white border-b border-zinc-100"></div>

    {{-- ========================================== --}}
    {{-- NAVEGACIÓN (TABS) --}}
    {{-- ========================================== --}}
    <div class="px-6 md:px-8 bg-white">
        <nav class="-mb-px flex space-x-8 overflow-x-auto hide-scrollbar" aria-label="Tabs">
            
            <a href="{{ route('dashboard') }}" 
               class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('dashboard') 
                          ? 'border-zinc-900 text-zinc-900' 
                          : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Panel General
            </a>

            <a href="{{ route('students.index') }}" 
               class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('students.*') 
                          ? 'border-zinc-900 text-zinc-900' 
                          : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('students.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Alumnas/os
            </a>

            <a href="{{ route('teachers.index') }}" 
               class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('teachers.*') 
                          ? 'border-zinc-900 text-zinc-900' 
                          : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('teachers.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                Profesores
            </a>

            <a href="{{ route('workshops.index') }}" 
               class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('workshops.*') 
                          ? 'border-zinc-900 text-zinc-900' 
                          : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('workshops.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Configurar talleres
            </a>

            <a href="{{ route('promotions.index') }}" 
               class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('promotions.*') 
                          ? 'border-zinc-900 text-zinc-900' 
                          : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('promotions.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                Promociones
            </a>

            <a href="{{ route('trainingmonth.index') }}" 
               class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('trainingmonth.*') 
                          ? 'border-zinc-900 text-zinc-900' 
                          : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('trainingmonth.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Clases mensuales
            </a>

        </nav>
    </div>
</div>

<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
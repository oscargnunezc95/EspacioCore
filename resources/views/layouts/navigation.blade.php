@php
    // CLASES DE ESTILO PARA ESCRITORIO
    $desktopActive = ' text-zinc-900 font-bold bg-zinc-50 rounded-xl';
    $desktopInactive = 'border-transparent text-zinc-500 font-medium hover:text-zinc-900 hover:border-zinc-300 hover:bg-zinc-50/50 rounded-xl';
    
    // CLASES DE ESTILO PARA MÓVILES
    $mobileActive = 'border-zinc-900 text-zinc-900 bg-zinc-50 font-bold';
    $mobileInactive = 'border-transparent text-zinc-600 font-medium hover:bg-zinc-50 hover:border-zinc-300 hover:text-zinc-900';

    // LÓGICA DE RUTA ACTIVA
    $isStudioRouteActive = request()->routeIs('studios.*') || request()->route('subdomain') !== null;
    $isTeacherActive = request()->routeIs('global.classes.teacher*'); 
@endphp

{{-- ========================================== --}}
{{-- CONTENEDOR MAESTRO (AHORA ÉL ES EL STICKY) --}}
{{-- ========================================== --}}
<div x-data="{ 
        mobileMenuOpen: false,
        isHidden: false,
        lastScrollY: 0
     }" 
     @scroll.window="
        let currentScrollY = window.scrollY;
        // Solo escondemos si pasamos los 80px (tamaño del nav) y vamos hacia abajo
        if (currentScrollY > 80 && currentScrollY > lastScrollY) {
            isHidden = true;
        } else {
            isHidden = false;
        }
        lastScrollY = currentScrollY;
     "
     {{-- Al ocultarse, evitamos que este contenedor invisible bloquee los clics en los tabs --}}
     :class="isHidden ? 'pointer-events-none' : ''"
     class="sticky top-0 z-50 w-full">
    
    {{-- ========================================== --}}
    {{-- NAVEGACIÓN SUPERIOR (ANIMADA) --}}
    {{-- ========================================== --}}
    <nav :class="isHidden ? '-translate-y-full' : 'translate-y-0'"
         class="bg-white border-b border-zinc-200/80 transition-transform duration-300 ease-in-out transform w-full pointer-events-auto">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                
                <div class="flex items-center">
                    {{-- LOGO INTELIGENTE: Guest -> Home | Auth -> Explore --}}
                    <a href="{{ auth()->check() ? route('explore') : route('home') }}" class="flex-shrink-0 flex items-center gap-2 group">
                        <div class="w-8 h-8 bg-zinc-900 rounded-lg flex items-center justify-center group-hover:bg-zinc-700 transition-colors">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                        </div>
                        <span class="text-2xl font-black tracking-tighter text-zinc-900">EstadoPrisma</span>
                    </a>

                    {{-- NAVEGACIÓN ESCRITORIO + TABLET --}}
                    {{-- Redujimos el margen izquierdo en md para ganar espacio (md:ml-4) --}}
                    <div class="hidden md:ml-4 md:flex md:items-center md:space-x-0.5 lg:space-x-1 xl:ml-8 xl:space-x-2">
                        
                        {{-- Se agregó 'whitespace-nowrap' y se optimizó el padding (px-2 lg:px-3) --}}
                        <a href="{{ route('explore') }}" class="inline-flex items-center gap-1.5 px-2 lg:px-3 py-3.5 border-b-2 text-sm whitespace-nowrap transition-all duration-200 {{ request()->routeIs('explore') ? $desktopActive : $desktopInactive }}">
                            <svg class="w-5 h-5 transition-colors {{ request()->routeIs('explore') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            {{-- Cambiamos de xl:inline a lg:inline para que el texto aparezca antes sin romperse --}}
                            <span class="hidden lg:inline">Explorar Clases</span>
                        </a>
                        
                        <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-1.5 px-2 lg:px-3 py-3.5 border-b-2 text-sm whitespace-nowrap transition-all duration-200 {{ request()->routeIs('cart.*') ? $desktopActive : $desktopInactive }}">
                            <div class="relative flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <span id="portal-badge" style="{{ $portalBadgeCount > 0 ? 'display:flex;' : 'display:none;' }}" 
                                      class="absolute -top-1.5 -right-2 bg-rose-500 text-white text-[10px] font-black w-4 h-4 rounded-full items-center justify-center shadow-sm">
                                    {{ $portalBadgeCount }}
                                </span>
                            </div>
                            <span class="hidden lg:inline">Mis Reservas</span>
                        </a>

                        @auth
                            <a href="{{ route('global.classes.student') }}" class="inline-flex items-center gap-1.5 px-2 lg:px-3 py-3.5 border-b-2 text-sm whitespace-nowrap transition-all duration-200 {{ request()->routeIs('global.classes.student*') ? $desktopActive : $desktopInactive }}">
                                <svg class="w-5 h-5 transition-colors {{ request()->routeIs('global.classes.student*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="hidden lg:inline">Portal Alumna</span>
                            </a>
                            
                            <a href="{{ route('global.classes.teacher') }}" class="inline-flex items-center gap-1.5 px-2 lg:px-3 py-3.5 border-b-2 text-sm whitespace-nowrap transition-all duration-200 {{ $isTeacherActive ? $desktopActive : $desktopInactive }}">
                                <svg class="w-5 h-5 transition-colors {{ $isTeacherActive ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="hidden lg:inline">Portal Profesor</span>
                            </a>
                            
                            <a href="{{ route('studios.index') }}" class="inline-flex items-center gap-1.5 px-2 lg:px-3 py-3.5 border-b-2 text-sm whitespace-nowrap transition-all duration-200 {{ $isStudioRouteActive ? $desktopActive : $desktopInactive }}">
                                <svg class="w-5 h-5 transition-colors {{ $isStudioRouteActive ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5-.615a3.001 3.001 0 013.75-.615m0 0a3.001 3.001 0 003.75-.615m0 0a3.001 3.001 0 013.75-.615m0 0a3.001 3.001 0 003.75-.615m0 0V5.25A2.25 2.25 0 0019.5 3h-15a2.25 2.25 0 00-2.25 2.25v.894M7.5 15h9m-9 0V15h9"></path>
                                </svg>
                                <span class="hidden lg:inline">Mis Espacios</span>
                            </a>
                            
                        @endauth
                    </div>
                </div>

                <div class="flex items-center gap-2 md:gap-3 xl:gap-6">
                    {{-- Soporte (visible para todos) --}}
                    <a href="{{ route('support.create') }}" class="hidden md:inline-flex items-center gap-1.5 px-4 py-3.5 border-b-2 text-sm transition-all duration-200 {{ request()->routeIs('support.*') ? $desktopActive : $desktopInactive }}">
                        <svg class="w-5 h-5 transition-colors {{ request()->routeIs('support.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="hidden xl:inline">Soporte</span>
                    </a>
                    @guest
                        <div class="hidden md:flex items-center space-x-4">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
                                Iniciar Sesión
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-5 py-2.5 text-sm font-bold text-white bg-zinc-900 rounded-xl hover:bg-zinc-800 focus:outline-none active:scale-[0.98] transition-all duration-200 shadow-sm">
                                Unirse al Espacio
                            </a>
                        </div>
                    @endguest

                    @auth
                        <div class="flex items-center gap-2 md:gap-3 xl:gap-4">
                            {{-- ============================================== --}}
                            {{-- CAMPANITA DE NOTIFICACIONES (INYECCIÓN AQUÍ) --}}
                            {{-- ============================================== --}}
                            <div class="pt-1">
                                <x-notifications-dropdown />
                            </div>

                            {{-- MENÚ DE PERFIL (ESCRITORIO & TABLET) --}}
                            <div class="relative hidden sm:block" x-data="{ openProfile: false }" @click.outside="openProfile = false" @close.stop="openProfile = false">
                                <button @click="openProfile = ! openProfile" class="flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 focus:outline-none transition-colors">
                                    <div class="h-9 w-9 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-900 font-black border border-zinc-200 shadow-sm hover:border-zinc-300 transition-all">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <span class="hidden lg:block">{{ explode(' ', Auth::user()->name)[0] }}</span>
                                    <svg class="h-4 w-4 text-zinc-400 hidden xl:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>

                                <div x-show="openProfile" 
                                     x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                                     x-transition:leave="transition ease-in duration-150" 
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                                     x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
                                     class="absolute right-0 top-full mt-2 w-48 rounded-xl shadow-lg bg-white border border-zinc-100 py-2 z-50 hidden"
                                     :class="{'hidden': !openProfile}" x-cloak>
                                    
                                    <div class="px-4 py-2 border-b border-zinc-100 mb-1 text-[10px] text-zinc-400 font-bold uppercase tracking-widest">Tu Cuenta</div>
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition">Configuración de Perfil</a>
                                    <a href="{{ route('profile.family.index') }}" class="block px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition">Familia</a>
                                    <a href="{{ route('global.payments.index') }}" class="block px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition">Pagos</a>
                                    
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-rose-600 font-bold hover:bg-rose-50 transition">Cerrar Sesión</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth

                    {{-- BOTÓN HAMBURGUESA MÓVIL --}}
                    <div class="flex items-center md:hidden">
                        <button @click="mobileMenuOpen = true" class="inline-flex items-center justify-center p-2 rounded-lg text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 focus:outline-none transition pointer-events-auto">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- ===================================== --}}
    {{-- MENÚ LATERAL MÓVIL --}}
    {{-- ===================================== --}}
    
    {{-- Backdrop oscuro --}}
    <div x-show="mobileMenuOpen" 
         x-transition.opacity.duration.300ms 
         @click="mobileMenuOpen = false" 
         class="fixed inset-0 bg-zinc-900/60 z-50 md:hidden pointer-events-auto" 
         style="display: none;"></div>

    {{-- Cajón Deslizante --}}
    <div :class="{'translate-x-0': mobileMenuOpen, '-translate-x-full': !mobileMenuOpen}" 
         class="-translate-x-full fixed inset-y-0 left-0 z-[60] w-4/5 max-w-sm bg-white shadow-2xl transition-transform duration-300 ease-in-out flex flex-col md:hidden transform pointer-events-auto" x-cloak>
        
        <div class="flex justify-between items-center px-4 h-20 border-b border-zinc-100 shrink-0">
            {{-- LOGO INTELIGENTE (Versión Móvil) --}}
            <a href="{{ auth()->check() ? route('explore') : route('home') }}" class="text-xl font-black tracking-tighter text-zinc-900">EstadoPrisma</a>
            
            <button @click="mobileMenuOpen = false" class="p-2 text-zinc-400 hover:text-zinc-700 bg-zinc-50 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto pt-4 pb-6 space-y-1">
            
            @auth
                {{-- Cabecera de Perfil --}}
                <div class="px-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-zinc-900 flex items-center justify-center text-white text-lg font-black shadow-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-base font-bold text-zinc-900 leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-zinc-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                </div>
                <div class="border-t border-zinc-100 mb-2 mx-4"></div>
            @endauth

            <a href="{{ route('explore') }}" class="block pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ request()->routeIs('explore') ? $mobileActive : $mobileInactive }}">
                Explorar Clases
            </a>
            
            <a href="{{ route('cart.index') }}" class="flex items-center justify-between pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ request()->routeIs('cart.*') ? $mobileActive : $mobileInactive }}">
                <span>Mis Reservas</span>
                <span style="{{ $portalBadgeCount > 0 ? 'display:flex;' : 'display:none;' }}" class="bg-rose-500 text-white text-[10px] font-black w-5 h-5 rounded-full items-center justify-center shadow-sm">
                    {{ $portalBadgeCount }}
                </span>
            </a>

            @guest
                <div class="border-t border-zinc-100 my-4 mx-4"></div>
                <a href="{{ route('login') }}" class="block pl-4 pr-4 py-3 text-base font-bold text-zinc-600 hover:bg-zinc-50 transition">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="block pl-4 pr-4 py-3 text-base font-black text-indigo-600 hover:bg-zinc-50 transition">Unirse al Espacio</a>
            @endguest

            @auth
                <a href="{{ route('global.classes.student') }}" class="block pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ request()->routeIs('global.classes.student*') ? $mobileActive : $mobileInactive }}">
                    Portal Alumna
                </a>
                
                <a href="{{ route('global.classes.teacher') }}" class="block pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ $isTeacherActive ? $mobileActive : $mobileInactive }}">
                    Portal Profesor
                </a>

                <a href="{{ route('studios.index') }}" class="block pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ $isStudioRouteActive ? $mobileActive : $mobileInactive }}">
                    Mis Espacios
                </a>
            @endauth

            <a href="{{ route('support.create') }}" class="block pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ request()->routeIs('support.*') ? $mobileActive : $mobileInactive }}">
                Soporte
            </a>
        </div>

        @auth
            {{-- Pie del Menú Móvil --}}
            <div class="border-t border-zinc-100 p-4 bg-zinc-50 shrink-0 space-y-2">
                <a href="{{ route('global.payments.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-bold text-zinc-700 hover:bg-zinc-200/50 rounded-xl transition">
                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Pagos
                </a>
                <a href="{{ route('profile.family.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-bold text-zinc-700 hover:bg-zinc-200/50 rounded-xl transition">
                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Familia
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-bold text-zinc-700 hover:bg-zinc-200/50 rounded-xl transition">
                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Configuración
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-rose-600 bg-rose-100/50 hover:bg-rose-100 rounded-xl transition">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        @endauth
    </div>
</div>
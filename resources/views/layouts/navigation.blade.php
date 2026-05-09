@php
    // CLASES DE ESTILO PARA ESCRITORIO
    $desktopActive = ' text-zinc-900 font-bold bg-zinc-50 rounded-xl';
    $desktopInactive = 'border-transparent text-zinc-500 font-medium hover:text-zinc-900 hover:border-zinc-300 hover:bg-zinc-50/50 rounded-xl';
    
    // CLASES DE ESTILO PARA MÓVILES
    $mobileActive = 'border-zinc-900 text-zinc-900 bg-zinc-50 font-bold';
    $mobileInactive = 'border-transparent text-zinc-600 font-medium hover:bg-zinc-50 hover:border-zinc-300 hover:text-zinc-900';
@endphp

<nav x-data="{ mobileMenuOpen: false }" class="bg-white border-b border-zinc-200/80 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 ">
            
            <div class="flex items-center ">
                <a href="{{ route('explore') }}" class="flex-shrink-0 flex items-center gap-2 group">
                    <div class="w-8 h-8 bg-zinc-900 rounded-lg flex items-center justify-center group-hover:bg-zinc-700 transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                    </div>
                    <span class="text-2xl font-black tracking-tighter text-zinc-900">EspacioCore</span>
                </a>

                {{-- NAVEGACIÓN ESCRITORIO --}}
                <div class="hidden sm:ml-10 sm:flex sm:items-center sm:space-x-2">
                    
                    <a href="{{ route('explore') }}" class="inline-flex items-center px-4 py-3.5 border-b-2 text-sm transition-all duration-200 {{ request()->routeIs('explore') ? $desktopActive : $desktopInactive }}">
                        Explorar Clases
                    </a>
                    
                    <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-1.5 px-4 py-3.5 border-b-2 text-sm transition-all duration-200 {{ request()->routeIs('cart.*') ? $desktopActive : $desktopInactive }}">
                        <div class="relative flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span id="portal-badge" style="{{ $portalBadgeCount > 0 ? 'display:flex;' : 'display:none;' }}" 
                                class="absolute -top-1.5 -right-2 bg-rose-500 text-white text-[10px] font-black w-4 h-4 rounded-full items-center justify-center shadow-sm">
                                {{ $portalBadgeCount }}
                            </span>
                        </div>
                        <span>Mis Reservas</span>
                    </a>

                    @auth
                        {{-- Portal Alumna --}}
                        <a href="{{ route('global.classes.student') }}" class="inline-flex items-center gap-1.5 px-4 py-3.5 border-b-2 text-sm transition-all duration-200 {{ request()->routeIs('global.classes.student') ? $desktopActive : $desktopInactive }}">
                            <svg class="w-5 h-5 transition-colors {{ request()->routeIs('global.classes.student') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Portal Alumna</span>
                        </a>
                        
                        {{-- Portal Profesor --}}
                        <a href="{{ route('global.classes.teacher') }}" class="inline-flex items-center gap-1.5 px-4 py-3.5 border-b-2 text-sm transition-all duration-200 {{ request()->routeIs('global.classes.teacher') ? $desktopActive : $desktopInactive }}">
                            <svg class="w-5 h-5 transition-colors {{ request()->routeIs('global.classes.teacher') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Portal Profesor</span>
                        </a>
                        
                        {{-- Mis Espacios --}}
                        <a href="{{ route('studios.index') }}" class="inline-flex items-center gap-1.5 px-4 py-3.5 border-b-2 text-sm transition-all duration-200 {{ request()->routeIs('studios.*') ? $desktopActive : $desktopInactive }}">
                            <svg class="w-5 h-5 transition-colors {{ request()->routeIs('studios.*') ? 'text-zinc-900' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5-.615a3.001 3.001 0 013.75-.615m0 0a3.001 3.001 0 003.75-.615m0 0a3.001 3.001 0 013.75-.615m0 0a3.001 3.001 0 003.75-.615m0 0V5.25A2.25 2.25 0 0019.5 3h-15a2.25 2.25 0 00-2.25 2.25v.894M7.5 15h9m-9 0V15h9"></path>
                            </svg>
                            <span>Mis Espacios</span>
                        </a>
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @guest
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
                            Iniciar Sesión
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-5 py-2.5 text-sm font-bold text-white bg-zinc-900 rounded-xl hover:bg-zinc-800 focus:outline-none active:scale-[0.98] transition-all duration-200 shadow-sm">
                            Unirse al Espacio
                        </a>
                    </div>
                @endguest

                @auth
                    <div class="relative ml-3" x-data="{ openProfile: false }" @click.outside="openProfile = false" @close.stop="openProfile = false">
                        <button @click="openProfile = ! openProfile" class="flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 focus:outline-none transition-colors">
                            <div class="h-9 w-9 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-900 font-black border border-zinc-200 shadow-sm hover:border-zinc-300 transition-all">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="hidden lg:block">{{ explode(' ', Auth::user()->name)[0] }}</span>
                            <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>

                        <div x-show="openProfile" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
                             class="absolute right-0 top-full mt-2 w-48 rounded-xl shadow-lg bg-white border border-zinc-100 py-2 z-50 hidden"
                             :class="{'hidden': !openProfile}">
                            
                            <div class="px-4 py-2 border-b border-zinc-100 mb-1 text-[10px] text-zinc-400 font-bold uppercase tracking-widest">
                                Tu Cuenta
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition">Configuración de Perfil</a>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-rose-600 font-bold hover:bg-rose-50 transition">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="mobileMenuOpen = ! mobileMenuOpen" class="inline-flex items-center justify-center p-2 rounded-lg text-zinc-400 hover:text-zinc-900 hover:bg-zinc-50 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenuOpen, 'inline-flex': ! mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- NAVEGACIÓN MÓVIL --}}
    <div :class="{'block': mobileMenuOpen, 'hidden': ! mobileMenuOpen}" class="hidden sm:hidden bg-white border-t border-zinc-100 shadow-xl absolute w-full">
        <div class="pt-2 pb-4 space-y-1">
            
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
                <div class="border-t border-zinc-100 my-2"></div>
                <a href="{{ route('login') }}" class="block pl-4 pr-4 py-3 text-base font-medium text-zinc-600 hover:bg-zinc-50 transition">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="block pl-4 pr-4 py-3 text-base font-bold text-zinc-900 hover:bg-zinc-50 transition">Unirse al Espacio</a>
            @endguest

            @auth
                <a href="{{ route('studios.index') }}" class="block pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ request()->routeIs('studios.*') ? $mobileActive : $mobileInactive }}">
                    Mis Espacios (Lobby)
                </a>
                
                <a href="{{ route('global.classes.student') }}" class="block pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ request()->routeIs('global.classes.student') ? $mobileActive : $mobileInactive }}">
                    Mis Clases (Alumna)
                </a>
                
                <a href="{{ route('global.classes.teacher') }}" class="block pl-4 pr-4 py-3 border-l-4 text-base transition-colors {{ request()->routeIs('global.classes.teacher') ? $mobileActive : $mobileInactive }}">
                    Mis Clases (Profesor)
                </a>
                                
                @if(request()->route('subdomain'))
                    @php $currentSubdomain = request()->route('subdomain'); @endphp
                    <div class="px-4 py-4 bg-zinc-50/80 border-t border-b border-zinc-100 mt-2">
                        <div class="text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-3">Panel del Estudio</div>
                        <div class="space-y-1 pl-2 border-l-2 border-zinc-200">
                            <a href="{{ route('dashboard', ['subdomain' => $currentSubdomain]) }}" class="block px-3 py-2 text-sm font-bold text-zinc-600 hover:text-zinc-900 transition">Dashboard</a>
                            <a href="{{ route('students.index', ['subdomain' => $currentSubdomain]) }}" class="block px-3 py-2 text-sm font-bold text-zinc-600 hover:text-zinc-900 transition">Alumnas/os</a>
                            <a href="{{ route('workshops.index', ['subdomain' => $currentSubdomain]) }}" class="block px-3 py-2 text-sm font-bold text-zinc-600 hover:text-zinc-900 transition">Clases</a>
                        </div>
                    </div>
                @endif

                <div class="border-t border-zinc-100 pt-4 pb-1">
                    <div class="flex items-center px-4 mb-4">
                        <div class="h-10 w-10 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-900 font-black border border-zinc-200 shadow-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-bold text-zinc-900">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-zinc-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left pl-4 pr-4 py-3 text-base font-bold text-rose-600 hover:bg-rose-50 transition">Cerrar Sesión</button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>
<nav x-data="{ mobileMenuOpen: false }" class="bg-white border-b border-zinc-200/80 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center gap-2 group">
                    <div class="w-8 h-8 bg-zinc-900 rounded-lg flex items-center justify-center group-hover:bg-zinc-700 transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                    </div>
                    <span class="text-2xl font-black tracking-tighter text-zinc-900">EspacioCore</span>
                </a>

                <div class="hidden sm:ml-10 sm:flex sm:space-x-8">
                    @guest
                        <a href="{{ route('explore') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-zinc-500 hover:text-zinc-900 hover:border-zinc-300 transition duration-200">
                            Explorar Clases
                        </a>
                    @endguest

                    @auth
                        <a href="{{ route('studios.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-zinc-500 hover:text-zinc-900 hover:border-zinc-300 transition duration-200">
                            Mis Espacios
                        </a>

                        @if(request()->route('subdomain'))
                            @php $currentSubdomain = request()->route('subdomain'); @endphp
                            
                            <div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                                <button @click="open = ! open" type="button" class="inline-flex items-center px-1 pt-1 border-b-2 border-zinc-900 text-sm font-medium text-zinc-900 transition duration-200 group">
                                    Panel de Estudio
                                    <svg class="ml-1 h-4 w-4 text-zinc-400 group-hover:text-zinc-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                                     x-transition:leave="transition ease-in duration-150" 
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                                     x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
                                     class="absolute top-full left-0 mt-1 w-56 rounded-xl shadow-lg bg-white border border-zinc-100 py-2 z-50 hidden"
                                     :class="{'hidden': !open}">
                                    
                                    <a href="{{ route('dashboard', ['subdomain' => $currentSubdomain]) }}" class="block px-4 py-2 text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 font-medium transition">
                                        Panel Principal (Dashboard)
                                    </a>
                                    <a href="{{ route('students.index', ['subdomain' => $currentSubdomain]) }}" class="block px-4 py-2 text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 font-medium transition">
                                        Directorio de Alumnas
                                    </a>
                                    <a href="{{ route('workshops.index', ['subdomain' => $currentSubdomain]) }}" class="block px-4 py-2 text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 font-medium transition">
                                        Configurar Clases
                                    </a>
                                    <a href="{{ route('entrenamientos.index', ['subdomain' => $currentSubdomain]) }}" class="block px-4 py-2 text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 font-medium transition">
                                        Planificación Mensual
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @guest
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
                            Iniciar Sesión
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-5 py-2.5 text-sm font-medium text-white bg-zinc-900 rounded-xl hover:bg-zinc-800 focus:outline-none active:scale-[0.98] transition-all duration-200 shadow-sm">
                            Unirse al Espacio
                        </a>
                    </div>
                @endguest

                @auth
                    <div class="relative ml-3" x-data="{ openProfile: false }" @click.outside="openProfile = false" @close.stop="openProfile = false">
                        <button @click="openProfile = ! openProfile" class="flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 focus:outline-none transition-colors">
                            <div class="h-9 w-9 rounded-full bg-zinc-200 flex items-center justify-center text-zinc-600 font-bold border border-zinc-300">
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
                            
                            <div class="px-4 py-2 border-b border-zinc-100 mb-1 text-xs text-zinc-400 font-medium uppercase tracking-wider">
                                Tu Cuenta
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition">Configuración de Perfil</a>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-rose-600 font-medium hover:bg-rose-50 transition">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="mobileMenuOpen = ! mobileMenuOpen" class="inline-flex items-center justify-center p-2 rounded-lg text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenuOpen, 'inline-flex': ! mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': mobileMenuOpen, 'hidden': ! mobileMenuOpen}" class="hidden sm:hidden bg-white border-t border-zinc-100 shadow-xl absolute w-full">
        <div class="pt-2 pb-4 space-y-1">
            @guest
                <a href="{{ route('explore') }}" class="block pl-4 pr-4 py-3 border-l-4 border-transparent text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:border-zinc-300 transition">Explorar Clases</a>
                <div class="border-t border-zinc-100 my-2"></div>
                <a href="{{ route('login') }}" class="block pl-4 pr-4 py-3 text-base font-medium text-zinc-600 hover:bg-zinc-50 transition">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="block pl-4 pr-4 py-3 text-base font-bold text-zinc-900 hover:bg-zinc-50 transition">Unirse al Espacio</a>
            @endguest

            @auth
                <a href="{{ route('studios.index') }}" class="block pl-4 pr-4 py-3 border-l-4 border-transparent text-base font-bold text-zinc-900 hover:bg-zinc-50 transition">Mis Espacios (Lobby)</a>
                
                @if(request()->route('subdomain'))
                    @php $currentSubdomain = request()->route('subdomain'); @endphp
                    <div class="px-4 py-3 bg-zinc-50/50">
                        <div class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-2">Panel del Estudio</div>
                        <div class="space-y-1 pl-2 border-l-2 border-zinc-200">
                            <a href="{{ route('dashboard', ['subdomain' => $currentSubdomain]) }}" class="block px-3 py-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 transition">Dashboard</a>
                            <a href="{{ route('students.index', ['subdomain' => $currentSubdomain]) }}" class="block px-3 py-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 transition">Alumnas</a>
                            <a href="{{ route('workshops.index', ['subdomain' => $currentSubdomain]) }}" class="block px-3 py-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 transition">Clases</a>
                        </div>
                    </div>
                @endif

                <div class="border-t border-zinc-100 pt-4 pb-1">
                    <div class="flex items-center px-4 mb-3">
                        <div class="h-10 w-10 rounded-full bg-zinc-200 flex items-center justify-center text-zinc-600 font-bold border border-zinc-300">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-zinc-800">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-zinc-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left pl-4 pr-4 py-3 text-base font-medium text-rose-600 hover:bg-rose-50 transition">Cerrar Sesión</button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>
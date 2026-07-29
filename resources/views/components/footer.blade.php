<footer class="bg-white border-t border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8">
            
            {{-- Columna 1: Marca y Copyright --}}
            <div class="md:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2 mb-4">
                    <img src="{{ asset('logo-estadoprisma.png') }}" alt="EstadoPrisma" class="w-auto h-12">
                </a>
                <p class="text-sm text-stone-500 font-medium mb-6">
                    El sistema operativo diseñado para academias de arte, danza y circo.
                </p>
            </div>

            {{-- Columna 2: Producto --}}
            <div>
                <h4 class="text-xs font-black text-stone-900 uppercase tracking-widest mb-4">Producto</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('explore') }}" class="text-sm font-medium text-stone-500 hover:text-red-600 transition-colors">Explorar Clases</a></li>
                    <li><a href="{{ route('home') }}#precios" class="text-sm font-medium text-stone-500 hover:text-red-600 transition-colors">Tarifas y Comisiones</a></li>
                    <li><a href="{{ route('login') }}" class="text-sm font-medium text-stone-500 hover:text-red-600 transition-colors">Iniciar Sesión</a></li>
                </ul>
            </div>

            {{-- Columna 3: Soporte --}}
            <div>
                <h4 class="text-xs font-black text-stone-900 uppercase tracking-widest mb-4">Soporte</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('support.create') }}" class="text-sm font-medium text-stone-500 hover:text-red-600 transition-colors">Centro de Ayuda</a></li>
                    <li><a href="{{ route('support.create') }}" class="text-sm font-medium text-stone-500 hover:text-red-600 transition-colors">Contacto</a></li>
                </ul>
            </div>

            {{-- Columna 4: Legal --}}
            <div>
                <h4 class="text-xs font-black text-stone-900 uppercase tracking-widest mb-4">Legal</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('legal.terms') }}" class="text-sm font-medium text-stone-500 hover:text-red-600 transition-colors">Términos de Servicio</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="text-sm font-medium text-stone-500 hover:text-red-600 transition-colors">Políticas de Privacidad</a></li>
                </ul>
            </div>

        </div>

        {{-- Separador y Copyright final --}}
        <div class="mt-12 pt-8 border-t border-stone-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-stone-400 font-medium">
                &copy; {{ date('Y') }} EstadoPrisma. Todos los derechos reservados.
            </p>

        </div>
    </div>
</footer>
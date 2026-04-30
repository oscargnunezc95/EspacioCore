<x-app-layout>
    <div class="py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-zinc-900 tracking-tight">Mis Espacios</h1>
            <p class="mt-3 text-zinc-500 font-light text-lg">Selecciona el estudio que deseas administrar o registra una nueva sucursal.</p>
        </div>

        @if (session('success'))
            <div class="mb-8 p-4 bg-emerald-50 text-emerald-700 rounded-xl font-medium border border-emerald-200 text-center">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Lista de Estudios Creados --}}
            @forelse($studios as $studio)
                <a href="{{ route('dashboard', ['subdomain' => $studio->subdomain]) }}" class="group bg-white rounded-3xl p-8 border border-zinc-200 shadow-sm hover:shadow-xl hover:border-zinc-300 transition-all duration-300 flex flex-col justify-between min-h-[200px]">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div class="h-12 w-12 rounded-xl bg-zinc-900 text-white flex items-center justify-center font-bold text-xl shadow-sm">
                                {{ substr($studio->name, 0, 1) }}
                            </div>
                            <span class="bg-zinc-100 text-zinc-600 text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full border border-zinc-200">
                                Activo
                            </span>
                        </div>
                        <h2 class="text-2xl font-bold text-zinc-900 group-hover:text-zinc-600 transition">{{ $studio->name }}</h2>
                        <p class="text-sm font-medium text-zinc-400 mt-1 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            {{ $studio->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'espaciocore.test' }}
                        </p>
                    </div>
                    <div class="mt-6 flex items-center text-sm font-bold text-zinc-900 uppercase tracking-wide group-hover:translate-x-2 transition-transform">
                        Entrar al Panel <span class="ml-2">&rarr;</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 bg-white border border-dashed border-zinc-300 rounded-3xl text-center">
                    <svg class="mx-auto h-12 w-12 text-zinc-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <h3 class="text-lg font-medium text-zinc-900">Aún no tienes estudios</h3>
                    <p class="mt-1 text-sm text-zinc-500">Comienza registrando tu primer local comercial.</p>
                </div>
            @endforelse

            {{-- Botón para Crear Nuevo Estudio (Modal) --}}
            <button onclick="document.getElementById('studioModal').classList.remove('hidden')" class="bg-zinc-50 rounded-3xl p-8 border-2 border-dashed border-zinc-300 hover:border-zinc-400 hover:bg-zinc-100 transition-all duration-300 flex flex-col items-center justify-center text-zinc-500 hover:text-zinc-800 min-h-[200px]">
                <div class="h-12 w-12 rounded-full bg-white border border-zinc-200 shadow-sm flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-lg font-bold tracking-tight">Registrar Nueva Sucursal</span>
            </button>
        </div>
    </div>

    {{-- MODAL CREAR ESTUDIO SIMPLIFICADO --}}
    <div id="studioModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/40 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl border border-zinc-100">
            <h3 class="text-2xl font-bold mb-2 text-zinc-900 tracking-tight">Nuevo Espacio</h3>
            <p class="text-sm text-zinc-500 mb-6">Ingresa el nombre de tu estudio. Nosotros generaremos el enlace web automáticamente.</p>
            
            <form action="{{ route('studios.store') }}" method="POST">
                @csrf
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Nombre Comercial</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej: Gravedad Zero" 
                               class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white {{ $errors->has('name') ? 'border-rose-300 bg-rose-50' : '' }}" required autofocus>
                        @error('name') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="document.getElementById('studioModal').classList.add('hidden')" class="flex-1 font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition text-sm">Cancelar</button>
                    <button type="submit" class="flex-1 bg-zinc-900 text-white font-medium py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition text-sm">Crear Espacio</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById('studioModal').classList.remove('hidden');
            });
        @endif
    </script>
</x-app-layout>
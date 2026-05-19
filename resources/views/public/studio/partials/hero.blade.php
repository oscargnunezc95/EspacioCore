<div class="relative w-full h-64 md:h-[350px] bg-zinc-900 overflow-hidden">
    @php
        $coverImage = $studio->logo_path; // Puedes luego agregar un cover_path específico
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
    @endphp
    
    @if($coverImage)
        <img src="{{ asset('storage/' . $coverImage) }}" class="absolute inset-0 w-full h-full object-cover opacity-30 blur-md scale-105" alt="Portada">
    @endif
    
    <div class="absolute inset-0 bg-gradient-to-t from-white via-zinc-900/40 to-transparent"></div>

    <div class="absolute inset-0 flex flex-col items-center justify-center px-4 mt-8 z-10">
        <div class="relative">
            @if($studio->icon_path)
                <img src="{{ asset('storage/' . $studio->icon_path) }}" alt="Logo de {{ $studio->name }}" 
                     class="w-24 h-24 md:w-32 md:h-32 rounded-3xl border-4 border-white shadow-2xl object-cover bg-white transform -rotate-2 hover:rotate-0 transition-transform duration-500">
            @else
                <div class="w-24 h-24 md:w-32 md:h-32 rounded-3xl border-4 border-white shadow-2xl bg-zinc-900 text-white flex items-center justify-center text-5xl font-black transform -rotate-2">
                    {{ substr($studio->name, 0, 1) }}
                </div>
            @endif
        </div>
        
        <h1 class="mt-6 text-3xl md:text-5xl font-black text-zinc-900 tracking-tighter text-center">
            {{ $studio->name }}
        </h1>

        <div class="flex items-center gap-2 mt-3 bg-white/90 backdrop-blur-md px-4 py-1.5 rounded-full border border-zinc-200 shadow-sm">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            <p class="text-zinc-600 font-bold text-[10px] md:text-xs uppercase tracking-widest">
                {{ $studio->subdomain }}.{{ $domain }}
            </p>
        </div>
    </div>
</div>
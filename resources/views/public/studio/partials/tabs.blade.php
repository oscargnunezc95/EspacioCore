{{-- Cambio 2: top-20 para respetar el font-size de 20px en móviles --}}
<div x-data="{ 
        isNavHidden: false,
        lastScrollY: 0
     }"
     @scroll.window="
        let currentScrollY = window.scrollY;
        if (currentScrollY > 80 && currentScrollY > lastScrollY) {
            isNavHidden = true;
        } else {
            isNavHidden = false;
        }
        lastScrollY = currentScrollY;
     "
     :class="isNavHidden ? 'top-0' : 'top-20'"
     class="sticky z-40 bg-white/95 backdrop-blur-md border-b border-zinc-200 shadow-sm transition-all duration-300 ease-in-out">
     
    <div class="max-w-4xl mx-auto px-0 sm:px-6 lg:px-8">
        <nav class="flex w-full justify-between overflow-x-auto hide-scrollbar" aria-label="Tabs">
            
            <button type="button" @click="activeTab = 'perfil'" 
                    :class="activeTab === 'perfil' ? 'text-zinc-900 border-b-2 border-zinc-900' : 'text-zinc-400 hover:text-zinc-900 hover:bg-zinc-50/50 border-b-2 border-transparent'"
                    class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-black text-[11px] sm:text-xs md:text-sm uppercase tracking-widest transition-all duration-200 flex items-center justify-center gap-2 outline-none">
                <svg class="w-5 h-5 md:w-4 md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span class="hidden md:inline">El Estudio</span>
            </button>

            <button type="button" @click="activeTab = 'talleres'" 
                    :class="activeTab === 'talleres' ? 'text-zinc-900 border-b-2 border-zinc-900' : 'text-zinc-400 hover:text-zinc-900 hover:bg-zinc-50/50 border-b-2 border-transparent'"
                    class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-black text-[11px] sm:text-xs md:text-sm uppercase tracking-widest transition-all duration-200 flex items-center justify-center gap-2 outline-none">
                <svg class="w-5 h-5 md:w-4 md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span class="hidden md:inline">Talleres</span>
            </button>

            <button type="button" @click="activeTab = 'promos'" 
                    :class="activeTab === 'promos' ? 'text-zinc-900 border-b-2 border-zinc-900' : 'text-zinc-400 hover:text-zinc-900 hover:bg-zinc-50/50 border-b-2 border-transparent'"
                    class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-black text-[11px] sm:text-xs md:text-sm uppercase tracking-widest transition-all duration-200 flex items-center justify-center gap-2 outline-none">
                <svg class="w-5 h-5 md:w-4 md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="hidden md:inline">Packs / Promos</span>
            </button>

            <button type="button" @click="activeTab = 'clases'" 
                    :class="activeTab === 'clases' ? 'text-zinc-900 border-b-2 border-zinc-900' : 'text-zinc-400 hover:text-zinc-900 hover:bg-zinc-50/50 border-b-2 border-transparent'"
                    class="flex-1 shrink-0 whitespace-nowrap py-4 px-2 font-black text-[11px] sm:text-xs md:text-sm uppercase tracking-widest transition-all duration-200 flex items-center justify-center gap-2 outline-none">
                <svg class="w-5 h-5 md:w-4 md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="hidden md:inline">Calendario</span>
            </button>
        </nav>
    </div>
</div>
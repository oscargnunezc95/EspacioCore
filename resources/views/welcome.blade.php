<x-guest-layout
    metaTitle="EstadoPrisma — Software de Gestion para Estudios de Danza, Circo y Arte"
    metaDescription="Centraliza reservas, automatiza la cobranza con Mercado Pago y ofrece una experiencia impecable a tus alumnos. El sistema operativo para tu academia, estudio o taller."
    ogType="website"
    metaRobots="index, follow"
>
    <x-slot name="structuredData">
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SoftwareApplication",
  "name": "EstadoPrisma",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "description": "Software de gestion para estudios de danza, circo y arte. Centraliza reservas, automatiza cobranza y ofrece portales para alumnos y profesores.",
  "offers": {
    "@@type": "Offer",
    "price": "0",
    "priceCurrency": "CLP"
  }
}
</script>
    </x-slot>

    <div class="bg-white min-h-screen selection:bg-red-100 selection:text-red-900 font-sans overflow-x-hidden">
        
        {{-- ========================================== --}}
        {{-- 1. HERO SECTION: ASIMÉTRICO & EDITORIAL    --}}
        {{-- ========================================== --}}
        <section class="bg-stone-950 text-white pt-24 pb-20 lg:pt-32 lg:pb-32 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-red-600/10 rounded-full blur-[120px] pointer-events-none"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    
                    <div class="lg:col-span-6 flex flex-col items-start text-left">
                        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-[1.05] mb-6">
                            Tu estudio, <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-rose-400 to-amber-400">
                                en perfecta sincronía.
                            </span>
                        </h1>

                        <p class="text-lg sm:text-xl text-stone-500 font-medium leading-relaxed mb-8 max-w-xl">
                            Elimina el caos del WhatsApp y las hojas de cálculo. Centraliza reservas, automatiza cobranzas con Mercado Pago y dale a tus profesores el control de su asistencia.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                            <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-red-600/20 transition-all duration-300 active:scale-95">
                                Empieza gratis hoy
                            </a>
                            <a href="{{ route('explore') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-stone-900 text-stone-300 hover:text-white hover:bg-stone-800 font-bold py-4 px-8 rounded-2xl border border-stone-900 transition-all duration-200 active:scale-95">
                                Ver vitrina pública
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-6 relative">
                        <div class="relative mx-auto max-w-lg lg:max-w-none">
                            <div class="absolute -inset-1 bg-gradient-to-r from-red-500 to-amber-500 rounded-[2.5rem] blur-xl opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                            <div class="relative rounded-3xl bg-stone-900 border border-stone-900 shadow-2xl overflow-hidden p-2">
                                <img src="{{ asset('images/dashboard.webp') }}" 
                                     alt="Dashboard Operativo EstadoPrisma" 
                                     width="1024" height="640" 
                                     fetchpriority="high" decoding="async" 
                                     class="w-full h-auto rounded-2xl object-cover shadow-inner">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- 2. CARRUSEL INFINITO: ECOSISTEMA ARTÍSTICO --}}
        {{-- ========================================== --}}
        <section class="bg-stone-900 py-20 border-y border-stone-900 overflow-hidden relative select-none">
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="text-red-500 font-black text-xs uppercase tracking-widest">El motor de tu academia</span>
                    <h2 class="text-2xl md:text-4xl font-black text-white mt-1">Un sistema para cualquier disciplina</h2>
                </div>
                <p class="text-stone-500 text-sm font-medium max-w-md">No importa qué disciplina sea. Si impartes una clase, EstadoPrisma es para ti. </p>
            </div>

            <div class="relative w-full overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-16 md:w-32 bg-gradient-to-r from-stone-900 to-transparent z-10 pointer-events-none"></div>
                <div class="absolute right-0 top-0 bottom-0 w-16 md:w-32 bg-gradient-to-l from-stone-900 to-transparent z-10 pointer-events-none"></div>

                <div class="flex gap-6 w-max animate-infinite-scroll hover:[animation-play-state:paused] py-4 px-3">
                    
                    {{-- ==================== SET 1 (9 TARJETAS) ==================== --}}
                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/rainier-ridao-GRDpPpKczdY-unsplash.webp') }}" alt="Danza y Teatro" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/jose-rueda-jqE4lolljeY-unsplash.webp') }}" alt="Circo y Telas Aéreas" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/vitaly-gariev-Lo3grEtkJ38-unsplash.webp') }}" alt="Artes Plásticas" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/leo_visions-erSSQSshQFg-unsplash.webp') }}" alt="Acroyoga" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/logan-weaver-lgnwvr-XKhHkbAFyPs-unsplash.webp') }}" alt="Yoga" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/danielle-cerullo-3ckWUnaCxzc-unsplash.webp') }}" alt="Ritmos y Zumba" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/pierre-goiffon-Vec8QTHIyu4-unsplash.webp') }}" alt="Música" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/david-gavi-KAN5y81kDqY-unsplash.webp') }}" alt="Danza Urbana" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl">
                        <img src="{{ asset('images/gabin-vallet-J154nEkpzlQ-unsplash (1).webp') }}" alt="Acondicionamiento Físico" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    {{-- ==================== SET 2 (DUPLICADO EXACTO PARA LOOP INFINITO) ==================== --}}
                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/rainier-ridao-GRDpPpKczdY-unsplash.webp') }}" alt="Danza y Teatro" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/jose-rueda-jqE4lolljeY-unsplash.webp') }}" alt="Circo y Telas Aéreas" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/vitaly-gariev-Lo3grEtkJ38-unsplash.webp') }}" alt="Artes Plásticas" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/leo_visions-erSSQSshQFg-unsplash.webp') }}" alt="Acroyoga" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/logan-weaver-lgnwvr-XKhHkbAFyPs-unsplash.webp') }}" alt="Yoga" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/danielle-cerullo-3ckWUnaCxzc-unsplash.webp') }}" alt="Ritmos y Zumba" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/pierre-goiffon-Vec8QTHIyu4-unsplash.webp') }}" alt="Música" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/david-gavi-KAN5y81kDqY-unsplash.webp') }}" alt="Danza Urbana" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                    <div class="w-72 sm:w-80 h-[400px] rounded-[2rem] overflow-hidden relative shrink-0 border border-stone-900 bg-stone-950 group/card shadow-xl" aria-hidden="true">
                        <img src="{{ asset('images/gabin-vallet-J154nEkpzlQ-unsplash (1).webp') }}" alt="Acondicionamiento Físico" width="320" height="400" loading="lazy" decoding="async" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover/card:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/20 to-transparent"></div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- 3. CARACTERÍSTICAS: MAGIC BENTO GRID       --}}
        {{-- ========================================== --}}
        <section class="bg-stone-950 py-24 relative border-t border-stone-900" id="magic-bento-container">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                
                <div class="mb-16">
                    <span class="text-red-500 font-black text-xs uppercase tracking-widest">Potencia operativa</span>
                    <h2 class="text-3xl md:text-5xl font-black text-white tracking-tight mt-1">Diseñado para desaparecer.</h2>
                    <p class="mt-4 text-lg text-stone-500 font-medium max-w-xl">La mejor herramienta es la que no tienes que gestionar. Automatizamos tu administración para que vuelvas a hacer lo que amas: enseñar.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 auto-rows-[minmax(250px,_auto)]">
                    
                    <div class="magic-bento bg-stone-900 p-8 rounded-[2rem] border border-stone-900 shadow-sm md:col-span-2 flex flex-col justify-between group transition-all duration-300 relative overflow-hidden">
                        <div class="magic-glow"></div>
                        <div class="relative z-10 mb-8">
                            <div class="w-12 h-12 bg-stone-950 rounded-2xl flex items-center justify-center mb-6 border border-stone-900 shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                <svg class="w-6 h-6 text-red-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Portal de alumnos y carrito 24/7</h3>
                            <p class="text-stone-500 font-medium mt-3 max-w-lg leading-relaxed">
                                Tus alumnos ingresan a su portal propio, agregan sus talleres o clases al carrito y pagan manualmente en segundos a través de Mercado Pago. Tu calendario y los aforos se actualizan solos al confirmarse el pago.
                            </p>
                        </div>
                    </div>

                    <div class="magic-bento bg-stone-900 p-8 rounded-[2rem] border border-stone-900 shadow-sm flex flex-col justify-between group transition-all duration-300 relative overflow-hidden">
                        <div class="magic-glow"></div>
                        <div class="relative z-10 mb-8">
                            <div class="w-12 h-12 bg-stone-950 rounded-2xl flex items-center justify-center mb-6 border border-stone-900 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-emerald-400" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-white tracking-tight">Tu servicio sin interrupciones</h3>
                            <p class="text-stone-500 text-sm font-medium mt-3 leading-relaxed">
                                Olvídate de hacer transferencias manuales mes a mes para usar el software. El cobro de tu plan o comisiones se procesa automáticamente para que tu estudio nunca deje de operar.
                            </p>
                        </div>
                    </div>

                    <div class="magic-bento bg-stone-900 p-8 rounded-[2rem] border border-stone-900 shadow-sm flex flex-col justify-between group transition-all duration-300 relative overflow-hidden">
                        <div class="magic-glow"></div>
                        <div class="relative z-10">
                            <div class="w-10 h-10 bg-stone-950 rounded-xl flex items-center justify-center mb-4 border border-stone-900 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 text-rose-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-white tracking-tight">Dashboard Financiero</h3>
                            <p class="text-stone-500 text-sm font-medium mt-2">Visualiza en tiempo real tus ingresos netos, ventas por taller y la tasa de retención de tu academia.</p>
                        </div>
                    </div>

                    <div class="magic-bento bg-stone-900 p-8 rounded-[2rem] border border-stone-900 shadow-sm md:col-span-2 flex flex-col justify-between group transition-all duration-300 relative overflow-hidden">
                        <div class="magic-glow"></div>
                        <div class="relative z-10">
                            <div class="w-10 h-10 bg-stone-950 rounded-xl flex items-center justify-center mb-4 border border-stone-900 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 text-red-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-white tracking-tight">Múltiples Profesores y Roles Privados</h3>
                            <p class="text-stone-500 text-sm font-medium mt-2 max-w-lg">Cada profesor accede exclusivamente a sus listas de asistencia y horarios asignados, manteniendo tu información comercial y financiera protegida y confidencial.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- 4. LA VITRINA                               --}}
        {{-- ========================================== --}}
        <section class="py-24 bg-stone-900 border-t border-stone-900 relative overflow-hidden text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    
                    <div class="lg:col-span-6 space-y-6">
                        <span class="bg-red-500/20 text-red-400 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full border border-red-500/30">Marketing Automático</span>
                        <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">Tu estudio ante nuevos alumnos.</h2>
                        <p class="text-lg text-stone-300 font-medium leading-relaxed">
                            Al publicar tus talleres en EstadoPrisma, ingresas automáticamente a nuestro explorador público. Atrae alumnos que buscan clases en tu ciudad y llena tus horarios valle sin gastar en publicidad.
                        </p>
                        <div class="pt-4">
                            <a href="{{ route('explore') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-red-600/20 transition-all duration-200 active:scale-95 border border-red-500/50">
                                Explorar vitrina en vivo
                                <svg class="w-4 h-4 text-white" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="relative mx-auto max-w-md lg:max-w-none">
                            <div class="absolute -inset-2 bg-gradient-to-r from-red-500 to-rose-500 rounded-3xl blur-2xl opacity-15 pointer-events-none"></div>
                            <img src="{{ asset('images/explorer.webp') }}" alt="Vitrina Explorador" width="800" height="600" loading="lazy" decoding="async" class="relative z-10 w-full h-auto rounded-2xl shadow-2xl border border-stone-700 object-cover">
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- 5. PRECIOS: MODELO DE COMISIÓN CON PISO    --}}
        {{-- ========================================== --}}
        <section class="py-24 bg-stone-950 text-white relative overflow-hidden border-t border-stone-900" id="precios">
            {{-- Resplandor de fondo sutil --}}
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-red-600/10 rounded-full blur-[140px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                
                <div class="text-center max-w-3xl mx-auto mb-20">
                    <span class="text-red-500 font-black text-xs uppercase tracking-widest">Modelo simple y predecible</span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mt-1">Comisión del 5% con un piso base accesible.</h2>
                    <p class="mt-4 text-lg text-stone-400 font-medium leading-relaxed">
                        Usas todo el poder de EstadoPrisma con alumnos, profesores y clases **100% ilimitadas**. Sostenemos tu infraestructura con una comisión del 5% sobre tus ventas online, con una tarifa base mínima de solo <strong class="text-white">$15.000 /mes</strong>.
                    </p>
                </div>

                {{-- GRILLA EXPLICATIVA DE 3 PASOS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch mb-16">
                    
                    {{-- PASO 1 --}}
                    <div class="bg-stone-900/80 rounded-[2rem] p-8 border border-stone-800/80 hover:border-stone-700 transition-all duration-300 flex flex-col justify-between group shadow-lg">
                        <div>
                            <div class="w-14 h-14 bg-stone-950 rounded-2xl flex items-center justify-center mb-6 border border-stone-800 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <span class="text-2xl font-black text-white">01</span>
                            </div>
                            <h3 class="text-xl font-black text-white tracking-tight">Todo ilimitado desde el día 1</h3>
                            <p class="text-stone-400 text-sm font-medium mt-3 leading-relaxed">
                                Te registras sin barreras de entrada. No te cobramos por volumen de estudiantes ni por agregar sedes o profesores. Tu estudio tiene libertad total para crecer.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-stone-800/60 flex items-center gap-2 text-xs font-bold text-stone-500">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Alumnos y talleres sin restricción
                        </div>
                    </div>

                    {{-- PASO 2 --}}
                    <div class="bg-stone-900/80 rounded-[2rem] p-8 border border-stone-800/80 hover:border-stone-700 transition-all duration-300 flex flex-col justify-between group shadow-lg">
                        <div>
                            <div class="w-14 h-14 bg-stone-950 rounded-2xl flex items-center justify-center mb-6 border border-stone-800 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <span class="text-2xl font-black text-red-500">02</span>
                            </div>
                            <h3 class="text-xl font-black text-white tracking-tight">Recibes el 100% en vivo</h3>
                            <p class="text-stone-400 text-sm font-medium mt-3 leading-relaxed">
                                Cuando un alumno paga en el portal, el dinero entra <strong class="text-stone-200">íntegro y directo</strong> a tu cuenta de Mercado Pago. La plataforma jamás toca ni retiene tu flujo de caja diario.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-stone-800/60 flex items-center gap-2 text-xs font-bold text-stone-500">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Liquidez inmediata para tu negocio
                        </div>
                    </div>

                    {{-- PASO 3 --}}
                    <div class="bg-stone-900/80 rounded-[2rem] p-8 border border-stone-800/80 hover:border-stone-700 transition-all duration-300 flex flex-col justify-between group shadow-lg">
                        <div>
                            <div class="w-14 h-14 bg-stone-950 rounded-2xl flex items-center justify-center mb-6 border border-stone-800 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <span class="text-2xl font-black text-white">03</span>
                            </div>
                            <h3 class="text-xl font-black text-white tracking-tight">Liquidación a mes vencido</h3>
                            <p class="text-stone-400 text-sm font-medium mt-3 leading-relaxed">
                                A fin de mes, calculamos el 5% de tus ventas online. Si tu comisión es menor a $15.000, solo pagas la tarifa base de <strong class="text-white">$15.000 (+ IVA)</strong> para cubrir el mantenimiento del sistema.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-stone-800/60 flex items-center gap-2 text-xs font-bold text-stone-500">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Sin sorpresas ni cobros ocultos
                        </div>
                    </div>

                </div>

                {{-- EJEMPLO PRÁCTICO DUAL + CTA --}}
                <div class="bg-stone-900 rounded-[2.5rem] p-8 sm:p-12 border border-stone-800 shadow-2xl flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="space-y-3 text-center lg:text-left flex-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-red-400">Cuentas Claras</span>
                        <h4 class="text-2xl sm:text-3xl font-black text-white">¿Cómo se ve en tu estudio?</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 max-w-2xl">
                            <div class="bg-stone-950/60 p-4 rounded-2xl border border-stone-800/80">
                                <p class="text-xs font-bold text-stone-400 uppercase">Si estás partiendo</p>
                                <p class="text-sm font-medium text-stone-300 mt-1">¿Vendes $200.000 en el mes? Solo pagas el piso base de <strong class="text-white font-black">$15.000</strong>.</p>
                            </div>
                            <div class="bg-stone-950/60 p-4 rounded-2xl border border-stone-800/80">
                                <p class="text-xs font-bold text-red-400 uppercase">Si estás en expansión</p>
                                <p class="text-sm font-medium text-stone-300 mt-1">¿Vendes $500.000 en el mes? Tu comisión es exactamente el 5%: <strong class="text-white font-black">$25.000</strong>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 w-full sm:w-auto">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-red-600/20 transition-all duration-300 active:scale-95 text-base">
                            Crear mi estudio gratis
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>

            </div>
        </section>

    </div>

    {{-- ESTILOS Y ANIMACIONES DEL CARRUSEL INFINITO --}}
    <style>
        @keyframes infiniteScroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        .animate-infinite-scroll {
            animation: infiniteScroll 45s linear infinite;
        }
    </style>
</x-guest-layout>
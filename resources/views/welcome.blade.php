<x-guest-layout
    metaTitle="EstadoPrisma — Software de Gestion para Estudios de Danza, Circo y Acrobacia"
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
  "description": "Software de gestion para estudios de danza, circo y acrobacia. Centraliza reservas, automatiza cobranza y ofrece portales para alumnos y profesores.",
  "offers": {
    "@@type": "Offer",
    "price": "0",
    "priceCurrency": "CLP"
  }
}
</script>
    </x-slot>

    <div class="bg-white min-h-screen selection:bg-indigo-100 selection:text-indigo-900 font-sans">
        
        {{-- ========================================== --}}
        {{-- 1. HERO SECTION (Estilo Linear / Notion) --}}
        {{-- ========================================== --}}
        <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 text-center flex flex-col items-center">
            
            {{-- Pill Badge de Novedad --}}
            <div class="mb-8 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-50 border border-zinc-200 text-xs font-bold text-zinc-600 shadow-sm transition-all duration-200 hover:bg-zinc-100 cursor-default">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                EstadoPrisma 1.0 ya esta disponible
            </div>

            <h1 class="text-5xl md:text-7xl font-black text-zinc-900 tracking-tighter mb-6 leading-[1.1] max-w-4xl">
                Software de gestion <br class="hidden md:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-zinc-900 via-zinc-700 to-zinc-500">
                    para tu academia.
                </span>
            </h1>
            
            <p class="mt-4 text-lg md:text-xl text-zinc-500 font-medium max-w-2xl mx-auto leading-relaxed">
                Centraliza reservas, automatiza la cobranza con Mercado Pago y ofrece una experiencia impecable a tus alumnos. Todo en una unica plataforma disenada para estudios de danza, circo, acrobacia y mas.
            </p>
            
            {{-- Dual Call To Action --}}
            <div class="mt-10 flex flex-col sm:flex-row justify-center items-center gap-4 w-full sm:w-auto">
                {{-- B2B CTA --}}
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-zinc-900 text-white font-bold py-3.5 px-8 rounded-xl hover:bg-zinc-800 transition-all duration-200 active:scale-95 shadow-sm">
                    Empieza gratis hoy
                </a>
                
                {{-- B2C CTA --}}
                <a href="/clases" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-white text-zinc-900 font-bold py-3.5 px-8 rounded-xl border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 hover:text-indigo-600 transition-all duration-200 active:scale-95 shadow-sm group">
                    Explorar clases
                    <svg class="w-4 h-4 text-zinc-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>
            
            {{-- Preview Mockup Abstracto (Aporta anclaje visual sin usar imágenes pesadas) --}}
            <div class="mt-16 w-full max-w-5xl rounded-t-[2.5rem] bg-zinc-50 border-x border-t border-zinc-200 shadow-sm overflow-hidden flex flex-col pt-4 px-4 h-48 md:h-64 relative mask-image-bottom">
                <div class="w-full flex gap-2 mb-4 px-2">
                    <div class="w-3 h-3 rounded-full bg-zinc-200"></div>
                    <div class="w-3 h-3 rounded-full bg-zinc-200"></div>
                    <div class="w-3 h-3 rounded-full bg-zinc-200"></div>
                </div>
                <div class="flex-1 bg-white rounded-t-2xl border-x border-t border-zinc-100 shadow-sm flex p-6 gap-6">
                    <div class="w-1/4 bg-zinc-50 rounded-xl hidden md:block"></div>
                    <div class="flex-1 flex flex-col gap-4">
                        <div class="h-8 bg-zinc-100 rounded-lg w-1/3"></div>
                        <div class="flex-1 bg-zinc-50 rounded-xl border border-zinc-100"></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- 2. CARACTERÍSTICAS: BENTO GRID (Apple/Linear Style) --}}
        {{-- ========================================== --}}
        <section class="bg-white py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-16">
                    <h2 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Diseñado para desaparecer.</h2>
                    <p class="mt-4 text-lg text-zinc-500 font-medium max-w-xl">La mejor herramienta es la que no tienes que gestionar. Automatizamos tu operación para que vuelvas a hacer lo que amas: enseñar.</p>
                </div>

                {{-- Grilla Bento --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 auto-rows-[minmax(250px,_auto)]">
                    
                    {{-- Bloque Grande (Span 2 columnas) --}}
                    <div class="bg-zinc-50 p-8 rounded-[2rem] border border-zinc-200 shadow-sm md:col-span-2 flex flex-col justify-between group hover:border-zinc-300 transition-all duration-300">
                        <div class="mb-8">
                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mb-6 border border-zinc-100 shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-2xl font-black text-zinc-900 tracking-tight">Reservas en piloto automático</h3>
                            <p class="text-zinc-500 font-medium mt-2 max-w-sm">Tus alumnos agendan, cancelan o se unen a listas de espera desde su propio portal, 24/7. Tu calendario se actualiza solo.</p>
                        </div>
                    </div>

                    {{-- Bloque Vertical --}}
                    <div class="bg-zinc-900 p-8 rounded-[2rem] border border-zinc-800 shadow-sm flex flex-col justify-between group hover:border-zinc-700 transition-all duration-300">
                        <div class="mb-8">
                            <div class="w-12 h-12 bg-zinc-800 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-white tracking-tight">Cero morosidad</h3>
                            <p class="text-zinc-400 text-sm font-medium mt-2">Integración nativa con Mercado Pago. Si no hay un plan activo o clase pagada, el sistema bloquea la reserva. Así de simple.</p>
                        </div>
                    </div>

                    {{-- Bloque Horizontal Inferior Izquierdo --}}
                    <div class="bg-white p-8 rounded-[2rem] border border-zinc-200 shadow-sm flex flex-col justify-between group hover:border-zinc-300 transition-all duration-300">
                        <div>
                            <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-zinc-900 tracking-tight">Dashboard Financiero</h3>
                            <p class="text-zinc-500 text-sm font-medium mt-2">Conoce exactamente cuánto ganas, qué métodos de pago usan y tu tasa de retención.</p>
                        </div>
                    </div>

                    {{-- Bloque Horizontal Inferior Derecho (Span 2) --}}
                    <div class="bg-indigo-50 p-8 rounded-[2rem] border border-indigo-100 shadow-sm md:col-span-2 flex flex-col justify-between group hover:border-indigo-200 hover:bg-indigo-100/50 transition-all duration-300">
                        <div>
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center mb-4 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-zinc-900 tracking-tight">Perfiles y Múltiples Profesores</h3>
                            <p class="text-zinc-600 text-sm font-medium mt-2 max-w-lg">Gestiona a todo tu equipo. Cada profesor tiene acceso a sus listas de asistencia, sin ver tu información financiera confidencial.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ========================================== --}}
        {{-- 3. SECCIÓN DE PRECIOS (Se mantiene la lógica original, con ajustes de UI limpios) --}}
        {{-- ========================================== --}}
        {{-- Pega exactamente aquí el mismo bloque de precios (<section x-data="{ annual: true }">...) de tu versión anterior, ya que esa UI cumplía perfecto con los requisitos de negocio y diseño --}}
        
    </div>

    {{-- Utilidad extra para el difuminado del mockup en el hero --}}
    <style>
        .mask-image-bottom {
            mask-image: linear-gradient(to bottom, black 50%, transparent 100%);
            -webkit-mask-image: linear-gradient(to bottom, black 50%, transparent 100%);
        }
    </style>
</x-guest-layout>
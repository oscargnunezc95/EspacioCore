<x-app-layout>
    
    {{-- 1. NAVEGACIÓN DEL ESTUDIO (Libre de paddings, pegado arriba) --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO (Contenedor maestro) --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- TÍTULO Y BREADCRUMBS (Sin x-slot) --}}
        <div class="mt-2 mb-8">
            <x-studio-header title="Dashboard principal" :breadcrumbs="[['name' => 'Panel principal']]"></x-studio-header>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- TARJETA: INGRESOS BRUTOS --}}
            <div class="bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm flex flex-col relative overflow-hidden group">
                {{-- Decoración de fondo sutil --}}
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-50 rounded-full opacity-50 blur-2xl group-hover:bg-emerald-100 transition-colors"></div>
                
                <div class="flex justify-between items-center mb-8 border-b border-zinc-100 pb-4 relative z-10">
                    <h4 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                        <div class="p-2 bg-emerald-50 rounded-xl">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        Ingresos Brutos
                    </h4>
                    <form method="GET" action="{{ route('dashboard', request()->route('subdomain')) }}" class="m-0">
                        <input type="month" name="period" value="{{ $period }}" onchange="this.form.submit()" 
                               class="rounded-xl border border-zinc-200 px-3 py-1.5 text-sm font-bold text-zinc-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer bg-white hover:bg-zinc-50 transition-colors shadow-sm">
                    </form>
                </div>
                
                <div class="flex-1 flex flex-col items-center justify-center relative z-10">
                    <span class="text-5xl font-black text-zinc-900 tracking-tighter mb-3">
                        ${{ number_format($monthlyRevenue, 0, ',', '.') }}
                    </span>
                    <p class="text-emerald-700 font-black text-[10px] uppercase tracking-widest bg-emerald-50 border border-emerald-100 px-3 py-1.5 rounded-full">
                        Recaudado en el periodo
                    </p>
                </div>
            </div>

            {{-- TARJETA: MOROSIDAD --}}
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm flex flex-col relative overflow-hidden group">
                {{-- Decoración de fondo sutil --}}
                <div class="absolute -right-12 -top-12 w-40 h-40 bg-rose-50 rounded-full opacity-50 blur-3xl group-hover:bg-rose-100 transition-colors pointer-events-none"></div>

                <div class="flex justify-between items-center mb-6 border-b border-zinc-100 pb-4 relative z-10">
                    <h4 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                        <div class="p-2 bg-rose-50 rounded-xl">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        Clases Impagas <span class="hidden sm:inline text-zinc-400 font-medium text-sm ml-1">(Alumnas Morosas)</span>
                    </h4>
                    <span class="bg-rose-100 text-rose-700 text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest border border-rose-200">
                        {{ $studentsWithDebt->count() }} Alumnas
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto max-h-[300px] custom-scrollbar pr-3 space-y-4 relative z-10">
                    @forelse($studentsWithDebt as $student)
                        @php
                            // Generamos un Avatar usando la inicial, pero con tonos rojos
                            $studentAvatar = 'https://ui-avatars.com/api/?name='.urlencode($student->name).'&color=be123c&background=ffe4e6&bold=true';
                        @endphp
                        
                        <div class="p-5 bg-white border border-rose-100/60 shadow-sm rounded-2xl hover:border-rose-300 transition-all duration-200 hover:shadow-md group/card">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="font-black text-zinc-900 text-sm flex items-center gap-3">
                                    <img src="{{ $studentAvatar }}" class="w-8 h-8 rounded-full border border-rose-100 shadow-sm" alt="Avatar">
                                    {{ $student->name }}
                                </h5>
                                
                                @php $whatsappLink = $student->phone ? "https://wa.me/{$student->phone}" : "#"; @endphp
                                <a href="{{ $whatsappLink }}" target="{{ $student->phone ? '_blank' : '_self' }}" class="text-[10px] font-black text-rose-600 hover:text-white uppercase tracking-widest bg-rose-50 hover:bg-rose-600 border border-rose-200 hover:border-rose-600 px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    Contactar
                                </a>
                            </div>
                            
                            <ul class="space-y-2 border-t border-rose-50 pt-3">
                                @foreach($student->attendances as $attendance)
                                    <li class="text-xs text-zinc-600 flex justify-between items-center bg-zinc-50/50 p-2 rounded-lg group-hover/card:bg-rose-50/30 transition-colors">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-2 w-2 relative shrink-0">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                            </span>
                                            <span class="font-bold text-zinc-800">{{ $attendance->classSession->workshop->name ?? 'Taller Eliminado' }}</span> 
                                            <span class="text-zinc-400 hidden sm:inline">&bull; {{ \Carbon\Carbon::parse($attendance->classSession->date)->translatedFormat('d \d\e F') }}</span>
                                        </div>
                                        <span class="font-black text-rose-700 text-[10px] uppercase tracking-widest">Pago Pendiente</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mb-4 border border-emerald-100 shadow-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <p class="text-zinc-900 font-black text-lg">¡Flujo de caja perfecto!</p>
                            <p class="text-zinc-500 text-sm mt-1 max-w-xs">No tienes alumnas con clases registradas pendientes de pago en este periodo.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #ffe4e6; border-radius: 20px; } 
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #fecdd3; } 
    </style>
</x-app-layout>
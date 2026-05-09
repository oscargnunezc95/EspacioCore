<x-app-layout>
    
    <x-slot name="header">
        <x-studio-tabs />
        <div class="mt-8">
            <x-studio-header title="Dashboard principal" :breadcrumbs="[['name' => 'Panel principal']]"></x-studio-header>
        </div>
    </x-slot>
    
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm flex flex-col">
                <div class="flex justify-between items-center mb-8 border-b border-zinc-100 pb-4">
                    <h4 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Ingresos Brutos
                    </h4>
                    <form method="GET" action="{{ route('dashboard', request()->route('subdomain')) }}" class="m-0">
                        <input type="month" name="period" value="{{ $period }}" onchange="this.form.submit()" 
                               class="rounded-xl border border-zinc-200 px-3 py-1.5 text-sm font-bold text-zinc-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer bg-zinc-50 hover:bg-zinc-100 transition-colors">
                    </form>
                </div>
                
                <div class="flex-1 flex flex-col items-center justify-center">
                    <span class="text-5xl font-black text-zinc-900 tracking-tighter mb-2">
                        ${{ number_format($monthlyRevenue, 0, ',', '.') }}
                    </span>
                    <p class="text-zinc-500 font-medium text-sm bg-zinc-100 px-3 py-1 rounded-full">Recaudado en el periodo</p>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm flex flex-col">
                <div class="flex justify-between items-center mb-6 border-b border-zinc-100 pb-4">
                    <h4 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Clases Impagas (Alumnas Morosas)
                    </h4>
                    <span class="bg-rose-100 text-rose-700 text-xs font-black px-2.5 py-1 rounded-lg uppercase tracking-wider">
                        {{ $studentsWithDebt->count() }} Alumnas
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto max-h-72 custom-scrollbar pr-2 space-y-4">
                    @forelse($studentsWithDebt as $student)
                        <div class="p-4 bg-rose-50/50 border border-rose-100 rounded-2xl hover:border-rose-200 transition-colors">
                            <div class="flex justify-between items-center mb-3">
                                <h5 class="font-bold text-zinc-900 text-sm flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-rose-200 text-rose-700 flex items-center justify-center text-xs">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    {{ $student->name }}
                                </h5>
                                <a href="#" class="text-[10px] font-black text-rose-500 hover:text-rose-700 uppercase tracking-widest bg-white border border-rose-200 px-2 py-1 rounded-md shadow-sm">
                                    Contactar
                                </a>
                            </div>
                            
                            <ul class="space-y-1.5 border-t border-rose-100/50 pt-2">
                                {{-- Ahora iteramos sobre las Asistencias que no tienen pago --}}
                                @foreach($student->attendances as $attendance)
                                    <li class="text-xs text-zinc-600 flex justify-between items-center">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            <span class="font-bold text-zinc-700">{{ $attendance->classSession->workshop->name ?? 'Taller Eliminado' }}</span> 
                                            <span class="text-zinc-500">&bull; {{ \Carbon\Carbon::parse($attendance->classSession->date)->translatedFormat('d \d\e F') }}</span>
                                        </div>
                                        <span class="font-bold text-rose-600">Pendiente</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center py-8">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <p class="text-zinc-900 font-bold">¡Todo al día!</p>
                            <p class="text-zinc-500 text-sm mt-1">No hay clases registradas sin pago.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #fce7f3; border-radius: 20px; } 
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #fbcfe8; } 
    </style>
</x-app-layout>
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">
    <div class="flex space-x-4 mb-8 border-b border-gray-200">
        <a href="{{ route('workshops.index') }}" class="py-2 px-6 font-medium text-gray-500 hover:text-blue-600 transition">Talleres (Configuración)</a>
        <button class="py-2 px-6 font-bold text-blue-600 border-b-2 border-blue-600">Entrenamientos (Meses)</button>
    </div>
    {{-- Cabecera y Navegación --}}
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <a href="{{ route('entrenamientos.index') }}" class="text-blue-600 font-bold flex items-center gap-2 mb-3 hover:text-blue-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver a la lista de meses
            </a>
            <h1 class="text-4xl font-extrabold text-gray-900 capitalize tracking-tight">{{ $monthDate->translatedFormat('F Y') }}</h1>
        </div>
        
        <form action="{{ route('entrenamientos.destroyMonth', $monthId) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('¿Estás segura de eliminar TODO el mes y sus registros?')" class="bg-white border border-red-200 text-red-600 font-bold px-5 py-2.5 rounded-xl text-sm hover:bg-red-50 hover:border-red-300 transition shadow-sm">
                Eliminar Mes Completo
            </button>
        </form>
    </div>

    {{-- CALENDARIO ALTO CONTRASTE --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        
        {{-- Días de la semana --}}
        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
            @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $d)
                <div class="py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200 last:border-0">
                    {{ $d }}
                </div>
            @endforeach
        </div>

        {{-- Cuadrícula del mes --}}
        <div class="grid grid-cols-7 gap-px bg-gray-200">
            @php
                $start = $monthDate->copy()->startOfMonth();
                $empty = $start->dayOfWeekIso - 1;
                $days = $monthDate->daysInMonth;
            @endphp

            {{-- Días vacíos --}}
            @for ($i = 0; $i < $empty; $i++) 
                <div class="bg-gray-50/50 min-h-[160px]"></div> 
            @endfor

            {{-- Días del mes --}}
            @for ($day = 1; $day <= $days; $day++)
                @php
                    $cur = $monthDate->copy()->day($day)->toDateString();
                    $sessionsInDay = $sessionsByDate->get($cur, collect());
                    $isToday = \Carbon\Carbon::parse($cur)->isToday();
                @endphp
                
                <div class="bg-white min-h-[160px] p-3 transition hover:bg-gray-50 {{ $isToday ? 'ring-2 ring-inset ring-blue-500 bg-blue-50/20' : '' }}">
                    
                    {{-- Número del día --}}
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-sm font-bold flex items-center justify-center h-8 w-8 rounded-full {{ $isToday ? 'bg-blue-600 text-white shadow-md' : 'text-gray-700' }}">
                            {{ $day }}
                        </span>
                        @if($sessionsInDay->count() > 0)
                            <span class="text-xs font-bold text-gray-400 mt-1">{{ $sessionsInDay->count() }} clases</span>
                        @endif
                    </div>
                    
                    {{-- Lista de clases (Estilo Tarjeta Blanca + Acento) --}}
                    <div class="space-y-2">
                        @foreach($sessionsInDay as $s)
                            @php 
                                $c = $s->workshop->color ?? 'blue'; 
                            @endphp
                            
                            <a href="{{ route('sessions.show', $s->id) }}" 
                               class="relative block p-2.5 pl-3.5 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md hover:border-blue-300 transition group overflow-hidden">
                                
                                {{-- Barra de Color Identificadora --}}
                                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-{{$c}}-500"></div>
                                
                                {{-- Hora (Alto Contraste) --}}
                                <div class="text-xs font-extrabold text-gray-900 group-hover:text-blue-600 transition">
                                    {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} hrs
                                </div>
                                
                                {{-- Nombre del Taller (Gris Oscuro Legible) --}}
                                <div class="text-xs font-medium text-gray-600 leading-tight mt-0.5 line-clamp-2">
                                    {{ $s->workshop->name }}
                                </div>

                                @if($s->is_cancelled)
                                    <div class="absolute right-2 top-2">
                                        <span class="flex h-2 w-2 rounded-full bg-red-500"></span>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>

                </div>
            @endfor

            {{-- Relleno final para mantener la grilla cuadrada --}}
            @php
                $totalCells = $empty + $days;
                $remainingCells = 7 - ($totalCells % 7);
                if ($remainingCells == 7) $remainingCells = 0;
            @endphp
            @for ($i = 0; $i < $remainingCells; $i++)
                <div class="bg-gray-50/50 min-h-[160px]"></div>
            @endfor
            
        </div>
    </div>
</div>
@endsection
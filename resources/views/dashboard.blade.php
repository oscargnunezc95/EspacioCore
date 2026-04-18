@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">
    
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900 leading-tight">Hola, Presidenta 👋</h1>
            <p class="text-gray-500 font-medium mt-1 uppercase tracking-widest text-xs">Hoy es {{ \Carbon\Carbon::now()->translatedFormat('l d \d\e F') }}</p>
        </div>

        {{-- FILTRO DE MES --}}
        <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-2xl shadow-sm border border-gray-200">
            <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()" 
                   class="border-0 focus:ring-0 font-bold text-gray-700 cursor-pointer">
            <span class="text-gray-300 pr-2">|</span>
            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </form>
    </div>

    {{-- Tarjeta de Ingresos del Mes --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl shadow-xl p-8 mb-10 text-white flex items-center justify-between overflow-hidden relative">
        <div class="relative z-10">
            <p class="text-blue-100 font-bold text-sm mb-1 uppercase tracking-widest">
                Ingresos de {{ \Carbon\Carbon::parse($selectedMonth.'-01')->translatedFormat('F Y') }}
            </p>
            <h2 class="text-6xl font-black italic tracking-tighter">${{ number_format($monthlyIncome, 0, ',', '.') }}</h2>
        </div>
        <div class="bg-white/10 p-6 rounded-full rotate-12 absolute -right-4 top-0 scale-150">
            <svg class="w-24 h-24 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- Clases de Hoy --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-xl font-black text-gray-800 uppercase tracking-tight">Clases para Hoy</h2>
                <span class="bg-blue-100 text-blue-700 text-[10px] font-black px-3 py-1 rounded-full">{{ $todaysClasses->count() }} CLASES</span>
            </div>
            
            <div class="p-4 space-y-3">
                @forelse($todaysClasses as $session)
                    <div class="flex items-center justify-between bg-white p-5 rounded-3xl border-2 border-gray-50 hover:border-blue-200 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="text-center bg-blue-50 px-3 py-2 rounded-xl group-hover:bg-blue-600 transition">
                                <span class="text-xs font-black text-blue-600 block group-hover:text-white">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-lg font-bold text-gray-800 block leading-tight">{{ $session->workshop->name }}</span>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $session->workshop->trainer ?? 'Sin instructor' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('sessions.show', $session->id) }}" class="bg-gray-900 hover:bg-blue-600 text-white font-black py-3 px-6 rounded-2xl text-xs uppercase transition shadow-md">
                            Pasar Lista
                        </a>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-gray-400 italic">No hay clases programadas para hoy.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pagos Pendientes (Lógica basada en asistencia sin pago) --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 bg-red-50/50 border-b border-red-100 flex items-center justify-between">
                <h2 class="text-xl font-black text-red-900 uppercase tracking-tight">Deudas por Cobrar</h2>
                <div class="bg-red-500 text-white p-2 rounded-xl shadow-lg shadow-red-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <div class="divide-y divide-gray-50 max-h-[500px] overflow-y-auto pr-1">
                @forelse($unpaidAttendances as $attendance)
                    <div class="p-6 flex justify-between items-center hover:bg-red-50/30 transition-colors group">
                        <div class="flex-1">
                            <p class="font-black text-gray-900 text-lg leading-tight">{{ $attendance->student->name }}</p>
                            <p class="text-[10px] font-black text-red-500 uppercase mt-1 tracking-wider">
                                Asistió el {{ \Carbon\Carbon::parse($attendance->classSession->date)->translatedFormat('d \d\e F') }}
                            </p>
                            <p class="text-xs font-bold text-gray-400 mt-0.5 italic">
                                Taller: {{ $attendance->classSession->workshop->name }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <a href="{{ route('sessions.show', $attendance->class_session_id) }}" 
                               class="text-[10px] font-black bg-red-600 text-white px-4 py-2 rounded-xl shadow-md hover:bg-gray-900 transition uppercase tracking-tighter">
                                Cobrar Ahora &rarr;
                            </a>
                            <a href="{{ route('students.calendar', $attendance->student_id) }}" class="text-[9px] font-bold text-gray-400 hover:text-blue-600 underline uppercase">Ver Perfil</a>
                        </div>
                    </div>
                @empty
                    <div class="p-16 text-center">
                        <div class="bg-green-100 text-green-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-gray-500 font-black uppercase text-sm tracking-widest">¡Finanzas al día!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
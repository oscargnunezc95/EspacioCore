<x-app-layout>
<div class="max-w-7xl mx-auto px-4">
    
    
    <x-slot name="header">
        <x-studio-tabs />
        <div class="mt-8">
        
            <x-studio-header 
                title="Perfil de Alumna" 
                :breadcrumbs="[
                    ['name' => 'alumnas/os', 'url' => route('students.index')],
                    ['name' => $student->name]
                ]"
            >
            </x-studio-header>
        </div>
    </x-slot>

    {{-- Controles del Mes --}}
    @php
        $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
    @endphp
    
    <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-200">
        <a href="{{ route('students.calendar', [$student->id, $prevMonth]) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl font-bold text-gray-600 transition">&larr; Mes Anterior</a>
        <h2 class="text-2xl font-black text-gray-800 capitalize">{{ $monthDate->translatedFormat('F Y') }}</h2>
        <a href="{{ route('students.calendar', [$student->id, $nextMonth]) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl font-bold text-gray-600 transition">Mes Siguiente &rarr;</a>
    </div>

    {{-- Calendario --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
            @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $d)
                <div class="py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200 last:border-0">{{ $d }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-px bg-gray-200">
            @php
                $start = $monthDate->copy()->startOfMonth();
                $empty = $start->dayOfWeekIso - 1;
                $days = $monthDate->daysInMonth;
            @endphp

            @for ($i = 0; $i < $empty; $i++) <div class="bg-gray-50/50 min-h-[140px]"></div> @endfor

            @for ($day = 1; $day <= $days; $day++)
                @php
                    $cur = $monthDate->copy()->day($day)->toDateString();
                    $dayAttendances = $attendancesByDate->get($cur, collect());
                    $isToday = \Carbon\Carbon::parse($cur)->isToday();
                @endphp
                
                <div class="bg-white min-h-[140px] p-2 transition {{ $isToday ? 'ring-2 ring-inset ring-blue-500 bg-blue-50/10' : '' }}">
                    <span class="text-sm font-bold flex items-center justify-center h-7 w-7 rounded-full mb-2 {{ $isToday ? 'bg-blue-600 text-white' : 'text-gray-500' }}">{{ $day }}</span>
                    
                    <div class="space-y-2">
                        @foreach($dayAttendances as $att)
                            @php 
                                // Si el ID de esta asistencia está en el arreglo de impagas, es roja. Si no, verde.
                                $isPaid = !in_array($att->id, $unpaidIds);
                            @endphp
                            
                            <div class="p-2 border rounded-lg shadow-sm {{ $isPaid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-300' }}">
                                <div class="flex justify-between items-start">
                                    <span class="text-[10px] font-black {{ $isPaid ? 'text-green-700' : 'text-red-700' }} uppercase">
                                        {{ \Carbon\Carbon::parse($att->classSession->start_time)->format('H:i') }}
                                    </span>
                                    @if($isPaid)
                                        <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @endif
                                </div>
                                <div class="text-[10px] font-bold text-gray-800 leading-tight mt-1 truncate">
                                    {{ $att->classSession->workshop->name }}
                                </div>
                                <div class="mt-1 text-[9px] font-black tracking-tighter uppercase {{ $isPaid ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $isPaid ? 'Pagada' : 'No Pagada' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endfor

            @php
                $remainingCells = 7 - (($empty + $days) % 7);
                if ($remainingCells == 7) $remainingCells = 0;
            @endphp
            @for ($i = 0; $i < $remainingCells; $i++) <div class="bg-gray-50/50 min-h-[140px]"></div> @endfor
            
        </div>
    </div>
    {{-- NUEVA SECCIÓN: HISTORIAL DE PAGOS --}}
    <div class="mt-12 mb-8">
        <h2 class="text-2xl font-black text-gray-800 mb-6">Historial de Pagos</h2>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 uppercase text-[10px] font-black text-gray-400 tracking-tighter">
                    <tr>
                        <th class="px-6 py-4 text-left">Fecha de Pago</th>
                        <th class="px-6 py-4 text-left">Monto</th>
                        <th class="px-6 py-4 text-left">Clases Cubiertas</th>
                        <th class="px-6 py-4 text-center">Comprobante</th>
                        <th class="px-6 py-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 italic">
                    {{-- Obtenemos los pagos de la alumna/oordenados del más reciente al más antiguo --}}
                    @forelse($student->payments()->latest()->get() as $payment)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                {{ $payment->created_at->translatedFormat('d M Y - H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-green-600">
                                ${{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <ul class="text-[10px] font-bold text-gray-500 uppercase list-disc list-inside">
                                    @foreach($payment->classSessions as $paidSession)
                                        <li>{{ $paidSession->workshop->name }} ({{ \Carbon\Carbon::parse($paidSession->date)->format('d/m') }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($payment->receipt_path)
                                    <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="text-[10px] font-black bg-blue-50 text-blue-600 px-3 py-1 rounded hover:bg-blue-100 transition uppercase">Ver Foto</a>
                                @else
                                    <span class="text-[10px] text-gray-400 font-bold">Sin foto</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('¿Estás segura de ANULAR este pago? Las clases volverán a marcarse como deuda.')" class="text-red-500 font-bold text-sm hover:text-red-700 hover:underline transition">
                                        Anular Pago
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-gray-400">Esta alumna/oaún no tiene pagos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-app-layout>
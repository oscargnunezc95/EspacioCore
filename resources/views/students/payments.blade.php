<x-app-layout>
    {{-- 1. NAVEGACIÓN DEL ESTUDIO (Libre de paddings, pegado arriba) --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Cabecera Unificada del Directorio --}}
        <div class="mt-2 mb-8 p-1">

            {{-- Breadcrumbs --}}
            <div class="flex text-xs font-bold text-amber-600 mb-3 gap-2 items-center">
                <a href="{{ route('students.index', ['subdomain' => request()->route('subdomain')]) }}" class="hover:text-stone-900 transition-colors">Alumnas/os</a>
                <span>/</span>
                <span class="text-amber-600">Perfil</span>
            </div>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">

                {{-- Título --}}
                <h1 class="text-2xl md:text-3xl font-black tracking-tight truncate flex-1 min-w-0">
                    {{ $student->name }}
                </h1>

            </div>
        </div>

        {{-- SUBMENÚ: Calendario / Pagos --}}
        <div class="flex space-x-1 bg-stone-100/80 p-1 rounded-xl w-fit border border-stone-200 mb-6">
            <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}"
               class="px-5 py-2 rounded-lg font-bold transition-all duration-200 text-sm {{ request()->routeIs('students.calendar') ? 'bg-white shadow-sm text-red-600' : 'text-stone-500 hover:text-stone-700' }}">
                Calendario
            </a>
            <a href="{{ route('students.payments', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}"
               class="px-5 py-2 rounded-lg font-bold transition-all duration-200 text-sm {{ request()->routeIs('students.payments') ? 'bg-white shadow-sm text-red-600' : 'text-stone-500 hover:text-stone-700' }}">
                Pagos
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- HISTORIAL DE PAGOS --}}
        <div class="mt-2 mb-8">
            <h2 class="text-2xl font-black text-amber-600 mb-6">Historial de Pagos</h2>

            <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-stone-50 uppercase text-[10px] font-black text-stone-400 tracking-tighter">
                        <tr>
                            <th class="px-4 md:px-6 py-4 text-left">Pago y Fecha</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-left">Método</th>
                            <th class="hidden md:table-cell px-4 md:px-6 py-4 text-left">Clases Cubiertas</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-center">Comprobante</th>
                            <th class="px-4 md:px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($payments as $payment)
                            @php
                                $method = $payment->payment_method ?? 'manual';
                            @endphp
                            <tr class="hover:bg-stone-50 transition-colors">

                                {{-- 1. COLUMNA PRINCIPAL (Visible siempre, agrupa datos en celular) --}}
                                <td class="px-4 md:px-6 py-4">
                                    <div class="text-sm font-black text-emerald-600">${{ number_format($payment->amount, 0, ',', '.') }}</div>
                                    <div class="text-xs font-bold text-stone-900 mt-0.5">{{ $payment->created_at->translatedFormat('d M Y') }}</div>

                                    {{-- Datos inyectados solo para celulares (sm:hidden) --}}
                                    <div class="sm:hidden mt-2 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider {{ $method === 'transferencia' ? 'text-teal-600' : ($method === 'efectivo' ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ $method }}
                                    </div>
                                    <div class="md:hidden mt-1.5 text-[10px] text-stone-500 leading-tight">
                                        {{ $payment->classSessions->count() }} {{ $payment->classSessions->count() === 1 ? 'Clase' : 'Clases' }}
                                    </div>
                                </td>

                                {{-- 2. MÉTODO (Oculto en celulares) --}}
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-tighter {{ $method === 'transferencia' ? 'text-teal-600' : ($method === 'efectivo' ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ $method }}
                                    </div>
                                </td>

                                {{-- 3. CLASES (Oculto en celulares y tablets pequeñas) --}}
                                <td class="hidden md:table-cell px-4 md:px-6 py-4">
                                    <ul class="text-[10px] font-bold text-stone-500 uppercase list-disc list-inside space-y-0.5">
                                        @foreach($payment->classSessions as $paidSession)
                                            <li>{{ $paidSession->workshop->name }} ({{ \Carbon\Carbon::parse($paidSession->date)->format('d/m') }})</li>
                                        @endforeach
                                    </ul>
                                </td>

                                {{-- 4. COMPROBANTE (Oculto en celular, se manda a acciones) --}}
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 text-center">
                                    @if($payment->receipt_path)
                                        <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="text-[10px] font-black bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition uppercase border border-blue-200">Ver Foto</a>
                                    @else
                                        <span class="text-[10px] text-stone-400 font-bold bg-stone-50 px-3 py-1.5 rounded-lg border border-stone-100">Sin foto</span>
                                    @endif
                                </td>

                                {{-- 5. ACCIONES --}}
                                <td class="px-4 md:px-6 py-4 text-right">
                                    <div class="flex flex-col items-end gap-2">
                                        {{-- Link de comprobante reubicado para celulares --}}
                                        @if($payment->receipt_path)
                                            <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="sm:hidden text-[10px] font-black text-blue-600 uppercase">Ver Comprobante</a>
                                        @endif

                                        <form action="{{ route('payments.destroy', ['subdomain' => request()->route('subdomain'), 'payment' => $payment->id]) }}" method="POST" class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('¿Estás segura de ANULAR este pago?')" class="text-rose-500 font-bold text-xs hover:text-rose-700 transition">
                                                Anular
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-sm font-bold text-stone-400">Esta alumna/o aún no tiene pagos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            @if($payments->hasPages())
                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

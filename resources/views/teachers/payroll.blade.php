@php
    $teacherUser = $teacher->user;
    $mpAvailable = $teacherUser && !empty($teacherUser->mp_access_token);
    $totalPaid = $report['payments']->where('status', 'paid')->sum('amount');
@endphp

<x-app-layout>
    <x-studio-tabs />

    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs --}}
        <nav class="flex text-xs font-bold text-zinc-500 mb-6 gap-2 items-center">
            <a href="{{ route('teachers.index', $subdomain) }}" class="hover:text-zinc-900 transition-colors">Profesores</a>
            <span>/</span>
            <span class="text-zinc-900">Liquidación de {{ $teacher->first_name }}</span>
        </nav>

        {{-- Cabecera del Profesor --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6 mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 font-black text-2xl shrink-0">
                        {{ strtoupper(substr($teacher->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-zinc-900">
                            Liquidación de <span class="capitalize">{{ $teacher->first_name }}</span>
                            <span class="uppercase">{{ $teacher->last_name }}</span>
                        </h1>
                        <p class="text-sm text-zinc-500 mt-1">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $monthYear)->translatedFormat('F Y') }}
                            &middot; {{ $report['sessions']->count() }} clases
                            &middot; {{ $report['subtotal'] }} asistencias
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-zinc-500">Total pagado:</span>
                    <span class="text-2xl font-black text-emerald-600">
                        ${{ number_format($totalPaid, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Selector de Mes (sin selector de profesor - el contexto ya está fijado) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 p-4 mb-8">
            <form method="GET" action="{{ route('teachers.payroll.show', ['subdomain' => $subdomain, 'teacher' => $teacher->id]) }}" class="flex items-end gap-3">
                <div class="w-48">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Mes</label>
                    <input type="month" name="month" value="{{ $monthYear }}"
                           class="w-full rounded-xl border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" 
                        class="px-5 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-xl text-sm font-bold transition-all shadow-sm active:scale-95">
                    Ver
                </button>
            </form>
        </div>

        {{-- Tabla de Clases del Mes --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden mb-8">
            <div class="px-5 py-4 border-b border-zinc-100">
                <h3 class="text-base font-black text-zinc-900">Clases del Mes</h3>
            </div>

            @if($report['sessions']->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 border-b border-zinc-200">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Fecha</th>
                                <th class="text-left px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Hora</th>
                                <th class="text-left px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Taller</th>
                                <th class="text-center px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Asistencias</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($report['sessions'] as $session)
                                <tr class="hover:bg-zinc-50/50">
                                    <td class="px-4 py-3 font-medium text-zinc-900">
                                        {{ \Carbon\Carbon::parse($session->date)->translatedFormat('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-zinc-600">
                                        {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-zinc-800">
                                        {{ $session->workshop->name }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold">
                                            {{ $session->attendances->count() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 border-t border-zinc-200">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right text-xs font-bold text-zinc-500 uppercase tracking-wider">
                                    Total Asistencias
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-900 text-white rounded-lg text-xs font-black">
                                        {{ $report['subtotal'] }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="p-10 text-center text-zinc-500 text-sm font-medium">
                    No hay clases registradas este mes para este profesor.
                </div>
            @endif
        </div>

        {{-- Historial de Pagos Realizados --}}
        @if($report['payments']->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden mb-8">
                <div class="px-5 py-4 border-b border-zinc-100">
                    <h3 class="text-base font-black text-zinc-900">Historial de Pagos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 border-b border-zinc-200">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Fecha</th>
                                <th class="text-left px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Método</th>
                                <th class="text-right px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Monto</th>
                                <th class="text-center px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Estado</th>
                                <th class="text-right px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($report['payments'] as $payment)
                                <tr>
                                    <td class="px-4 py-3 text-zinc-700">
                                        {{ $payment->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $payment->payment_method === 'mercadopago' ? 'bg-blue-50 text-blue-700' : 'bg-zinc-100 text-zinc-700' }}">
                                            {{ $payment->payment_method === 'mercadopago' ? 'Mercado Pago' : 'Manual' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-zinc-900">
                                        ${{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($payment->status === 'paid')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Pagado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        @if($payment->status === 'paid')
                                            <span class="text-xs text-zinc-400">—</span>
                                        @else
                                            <div class="flex items-center justify-end gap-2">
                                                @if($payment->payment_method === 'mercadopago')
                                                    <a href="{{ route('teachers.payroll.resume', ['subdomain' => $subdomain, 'teacher' => $teacher->id, 'payment' => $payment->id]) }}"
                                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all duration-200 active:scale-95 shadow-sm">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                        Retomar
                                                    </a>
                                                @endif
                                                <form method="POST" action="{{ route('teachers.payroll.destroy', ['subdomain' => $subdomain, 'teacher' => $teacher->id, 'payment' => $payment->id]) }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            onclick="return confirm('¿Cancelar este intento de pago? El registro se eliminará.')"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-white hover:bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-bold transition-all duration-200 active:scale-95">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        Cancelar
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Formulario de Pago (Dual: Manual vs Mercado Pago) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100">
                <h3 class="text-base font-black text-zinc-900">Realizar Pago</h3>
            </div>

            <form method="POST" 
                  action="{{ route('teachers.payroll.store', ['subdomain' => $subdomain, 'teacher' => $teacher->id]) }}" 
                  enctype="multipart/form-data" 
                  class="p-5 sm:p-6">
                @csrf
                <input type="hidden" name="month_year" value="{{ $monthYear }}">

                {{-- Monto editable --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Monto a Pagar (CLP)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-zinc-500 font-bold">$</span>
                        <input type="number" name="amount" 
                               value="{{ old('amount', $totalPaid ?: $report['subtotal'] * 5000) }}"
                               class="w-full pl-8 pr-4 py-3 rounded-xl border-zinc-300 text-lg font-bold focus:ring-indigo-500 focus:border-indigo-500"
                               min="1" step="1" required>
                    </div>
                    <p class="text-xs text-zinc-400 mt-1.5">El monto es editable. Ajústalo según lo acordado con el profesor.</p>
                </div>

                {{-- Selector de Método de Pago (Radio/Tabs) --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-3">Método de Pago</label>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Opción 1: Transferencia Manual --}}
                        <label class="relative flex cursor-pointer">
                            <input type="radio" name="payment_method" value="manual" checked
                                   class="peer sr-only"
                                   onchange="togglePayrollMethod()">
                            <div class="w-full p-4 rounded-xl border-2 border-zinc-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 transition-all duration-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-zinc-100 flex items-center justify-center text-zinc-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-zinc-900">Transferencia Manual</div>
                                        <div class="text-xs text-zinc-500">Sube el comprobante de pago</div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- Opción 2: Mercado Pago --}}
                        <label class="relative flex cursor-pointer {{ $mpAvailable ? '' : 'opacity-60' }}">
                            <input type="radio" name="payment_method" value="mercadopago"
                                   class="peer sr-only"
                                   {{ $mpAvailable ? '' : 'disabled' }}
                                   onchange="togglePayrollMethod()">
                            <div class="w-full p-4 rounded-xl border-2 border-zinc-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 transition-all duration-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-zinc-900">Pagar con Mercado Pago</div>
                                        <div class="text-xs text-zinc-500">Transferencia directa al profesor</div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>

                    @if(!$mpAvailable)
                        <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                            <p class="text-xs font-bold text-amber-700 flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                Este profesor aún no ha vinculado su cuenta de Mercado Pago.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Input de comprobante (solo modo Manual) --}}
                <div id="payrollReceiptSection" class="mb-5">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Comprobante de Pago</label>
                    <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="w-full text-sm text-zinc-600 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 file:cursor-pointer file:transition-colors">
                    <p class="text-xs text-zinc-400 mt-1.5">Formatos: JPG, PNG, WebP o PDF. Máx: 10MB.</p>
                </div>

                <button type="submit" 
                        class="w-full sm:w-auto px-8 py-3 bg-zinc-900 hover:bg-zinc-800 text-white rounded-xl text-sm font-bold transition-all shadow-sm active:scale-95">
                    Procesar Pago
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePayrollMethod() {
            const manualRadio = document.querySelector('input[value="manual"]');
            const receiptSection = document.getElementById('payrollReceiptSection');
            const receiptInput = document.querySelector('input[name="receipt"]');
            if (manualRadio.checked) {
                receiptSection.style.display = 'block';
                if (receiptInput) receiptInput.required = true;
            } else {
                receiptSection.style.display = 'none';
                if (receiptInput) receiptInput.required = false;
            }
        }
        togglePayrollMethod();
    </script>
</x-app-layout>

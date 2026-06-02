<x-app-layout>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Liquidaciones</h1>
            <p class="mt-2 text-zinc-500 font-medium">Gestiona los pagos a tus profesores.</p>
        </div>

        {{-- Selector de Profesor y Mes --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 p-5 mb-8">
            <form method="GET" action="{{ route('payroll.show', ['subdomain' => $subdomain]) }}" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Profesor</label>
                    <select name="teacher_id" class="w-full rounded-xl border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seleccionar profesor...</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>
                                {{ $t->first_name }} {{ $t->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Mes</label>
                    <input type="month" name="month_year" value="{{ $monthYear }}"
                           class="w-full rounded-xl border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-xl text-sm font-bold transition-all shadow-sm active:scale-95">
                    Ver Liquidación
                </button>
            </form>
        </div>

        @if($report)
            @php
                $teacherUser = $teacher->user;
                $mpAvailable = $teacherUser && !empty($teacherUser->mp_access_token);
            @endphp

            {{-- Resumen del Profesor --}}
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden mb-8">
                <div class="p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-zinc-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-black text-lg">
                            {{ strtoupper(substr($teacher->first_name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-zinc-900">{{ $teacher->first_name }} {{ $teacher->last_name }}</h2>
                            <p class="text-sm text-zinc-500">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $monthYear)->translatedFormat('F Y') }}
                                &middot; {{ $report['sessions']->count() }} clases
                                &middot; {{ $report['subtotal'] }} asistencias registradas
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-zinc-500">Total pagado:</span>
                        <span class="text-2xl font-black text-emerald-600">
                            ${{ number_format($report['payments']->where('status', 'paid')->sum('amount'), 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Tabla de Clases del Mes --}}
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
                                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $payment->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                                {{ $payment->status === 'paid' ? 'Pagado' : 'Pendiente' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Formulario de Pago --}}
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100">
                    <h3 class="text-base font-black text-zinc-900">Realizar Pago</h3>
                </div>

                <form method="POST" action="{{ route('payroll.store', ['subdomain' => $subdomain]) }}" enctype="multipart/form-data" class="p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="teacher_id" value="{{ $teacherId }}">
                    <input type="hidden" name="month_year" value="{{ $monthYear }}">

                    {{-- Monto editable --}}
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Monto a Pagar (CLP)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-zinc-500 font-bold">$</span>
                            <input type="number" name="amount" 
                                   value="{{ old('amount', $report['payments']->where('status', 'paid')->sum('amount') ?: $report['subtotal'] * 5000) }}"
                                   class="w-full pl-8 pr-4 py-3 rounded-xl border-zinc-300 text-lg font-bold focus:ring-indigo-500 focus:border-indigo-500"
                                   min="1" step="100" required>
                        </div>
                        <p class="text-xs text-zinc-400 mt-1.5">El monto es editable. Puedes ajustarlo según lo acordado con el profesor.</p>
                    </div>

                    {{-- Selector de Método de Pago (Radio/Tabs) --}}
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-3">Método de Pago</label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- Opción 1: Transferencia Manual --}}
                            <label class="relative flex cursor-pointer">
                                <input type="radio" name="payment_method" value="manual" checked
                                       class="peer sr-only"
                                       onchange="togglePaymentMethod()">
                                <div class="w-full p-4 rounded-xl border-2 border-zinc-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-zinc-100 flex items-center justify-center text-zinc-600 peer-checked:bg-indigo-100 peer-checked:text-indigo-600">
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
                                       onchange="togglePaymentMethod()">
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
                                    Este profesor aún no ha vinculado su cuenta de Mercado Pago. Pídele que configure sus cobros desde su portal de profesor.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Input de archivo (solo visible en modo Manual) --}}
                    <div id="receiptSection" class="mb-5">
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
        @elseif($teacherId)
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 p-10 text-center">
                <div class="w-12 h-12 rounded-full bg-zinc-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <p class="text-zinc-500 font-medium">Selecciona un profesor y un mes para ver la liquidación.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 p-10 text-center">
                <div class="w-12 h-12 rounded-full bg-zinc-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-zinc-500 font-medium">Selecciona un profesor y un mes para comenzar la liquidación.</p>
            </div>
        @endif
    </div>

    <script>
        function togglePaymentMethod() {
            const manualRadio = document.querySelector('input[value="manual"]');
            const receiptSection = document.getElementById('receiptSection');
            if (manualRadio.checked) {
                receiptSection.style.display = 'block';
                document.querySelector('input[name="receipt"]').required = true;
            } else {
                receiptSection.style.display = 'none';
                document.querySelector('input[name="receipt"]').required = false;
            }
        }
        // Inicializar
        togglePaymentMethod();
    </script>
</x-app-layout>

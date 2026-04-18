@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">
    <div class="flex space-x-4 mb-8 border-b border-gray-200">
        <a href="{{ route('workshops.index') }}" class="py-2 px-6 font-medium text-gray-500 hover:text-blue-600 transition">Talleres (Configuración)</a>
        <button class="py-2 px-6 font-bold text-blue-600 border-b-2 border-blue-600">Entrenamientos (Meses)</button>
    </div>
    
    <div class="mb-8">
        <a href="{{ route('entrenamientos.show', $monthId) }}" class="text-blue-600 font-bold mb-2 block hover:underline">&larr; Volver al calendario</a>
        
        <div class="flex items-center gap-3">
            <h1 class="text-4xl font-black text-gray-900 leading-tight">{{ $session->workshop->name }}</h1>
            
            @if($session->workshop->is_single_class)
                <span class="bg-indigo-100 text-indigo-700 text-xs px-3 py-1 rounded-full font-black uppercase tracking-widest mt-1">Clase Única</span>
            @endif
        </div>

        <p class="text-gray-500 font-bold uppercase tracking-widest text-xs mt-2">
            {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F') }} | {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg font-bold shadow-sm border-l-4 border-green-500">
            {{ session('success') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg font-bold shadow-sm border-l-4 border-red-500">
            Hubo un error al registrar. Revisa los datos e intenta nuevamente.
        </div>
    @endif

    <div class="bg-white rounded-[2rem] shadow-xl overflow-hidden border border-gray-200">
        
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-gray-800">Lista de Asistencia</h2>
                    @if(!$session->is_cancelled)
                        <form action="{{ route('sessions.cancel', $session->id) }}" method="POST">@csrf @method('PATCH')
                            <button class="text-xs font-bold text-red-500 hover:text-red-700 bg-red-50 px-3 py-1.5 rounded-lg border border-red-200 transition">Cancelar Clase</button>
                        </form>
                    @else
                        <span class="bg-red-500 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase">Cancelada</span>
                    @endif
                </div>
                
                {{-- NUEVO BOTÓN: Alumnas No Frecuentes --}}
                <button onclick="document.getElementById('infrequentModal').classList.remove('hidden')" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md text-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Alumnas No Frecuentes
                </button>
            </div>

            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchStudent" onkeyup="filterStudents()" placeholder="Buscar alumna activa por nombre..." 
                       class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-400 bg-white text-gray-800 font-medium placeholder-gray-500 focus:border-blue-500 focus:ring-0 transition">
            </div>
        </div>

        <ul class="divide-y divide-gray-100" id="studentsList">
            @foreach($students as $student)
                @php
                    $isPresent = $session->attendances->contains('student_id', $student->id);
                    $hasPaidThisClass = $student->payments()->whereHas('classSessions', function($q) use ($session) {
                        $q->where('class_sessions.id', $session->id);
                    })->exists();
                @endphp
                <li class="student-item p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 transition hover:bg-gray-50">
                    <div class="flex items-center gap-4">
                        <button onclick="toggleAttendance({{ $session->id }}, {{ $student->id }}, this)" class="relative inline-flex h-8 w-14 rounded-full transition-colors {{ $isPresent ? 'bg-green-500' : 'bg-gray-300' }}" aria-checked="{{ $isPresent ? 'true' : 'false' }}">
                            <span class="inline-block h-6 w-6 transform rounded-full bg-white transition mt-1 {{ $isPresent ? 'translate-x-7' : 'translate-x-1' }} shadow-sm"></span>
                        </button>
                        <div>
                            <p class="student-name font-bold text-gray-900">{{ $student->name }}</p>
                            @if($hasPaidThisClass)
                                <span class="text-[10px] font-black uppercase tracking-tighter text-green-600 bg-green-50 px-2 py-0.5 rounded">Clase Pagada</span>
                            @else
                                <span class="text-[10px] font-black uppercase tracking-tighter text-red-600 underline">Pendiente de pago</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openPaymentModal({{ $student->id }}, '{{ addslashes($student->name) }}')" class="text-xs bg-white border-2 border-green-500 text-green-600 px-5 py-2 rounded-xl font-black uppercase tracking-tighter hover:bg-green-50 transition shadow-sm">
                            Pagar Clases
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- MODAL PAGO CON FECHAS ESPECÍFICAS (Se mantiene igual) --}}
<div id="paymentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-[2rem] p-8 max-w-md w-full shadow-2xl border border-gray-200">
        <h3 class="text-2xl font-bold mb-1 text-gray-900">Registrar Pago</h3>
        <p class="text-sm font-bold text-indigo-600 mb-6" id="pModalStudentName"></p>
        
        <form id="quickPaymentForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="workshop_id" value="{{ $session->workshop_id }}">
            
            <div class="mb-5 bg-gray-50 p-4 rounded-xl border border-gray-200">
                <label class="block text-xs font-black text-gray-500 mb-3 uppercase tracking-wider">Fechas que está pagando</label>
                <div id="modal_loading_spinner" class="text-sm font-bold text-indigo-500 hidden">Buscando clases...</div>
                <div id="modal_sessions_list" class="space-y-2 max-h-40 overflow-y-auto pr-2"></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Monto Pagado ($)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500 font-bold">$</span>
                    <input type="number" name="amount" placeholder="Ej: 15000" class="w-full pl-8 rounded-xl border-2 border-gray-400 p-2 focus:border-green-500 focus:ring-0 font-bold" required>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-1">Comprobante (Imagen)</label>
                <input type="file" name="receipt" class="w-full text-sm text-gray-600 border-2 border-gray-400 rounded-xl p-2 bg-white focus:border-green-500" required>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="flex-1 font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 py-3 rounded-xl transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-green-600 text-white font-black py-3 rounded-xl shadow-lg hover:bg-green-700 transition">Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>

{{-- NUEVO MODAL: ALUMNAS NO FRECUENTES --}}
<div id="infrequentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full shadow-2xl border border-gray-200">
        <h3 class="text-2xl font-bold mb-2 text-gray-900">Alumnas No Frecuentes</h3>
        <p class="text-xs text-gray-500 mb-6 leading-tight">Registra la asistencia y pago sin alterar el directorio principal de alumnas.</p>
        
        <form action="{{ route('sessions.infrequent', $session->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Selector de Modalidad --}}
            <div class="mb-4 bg-purple-50 p-3 rounded-xl border border-purple-100 flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer flex-1">
                    <input type="radio" name="infrequent_mode" value="existing" checked onchange="toggleInfrequentMode()" class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                    <span class="font-bold text-gray-800 text-[11px] uppercase tracking-wider">Buscar Deshabilitada</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer flex-1">
                    <input type="radio" name="infrequent_mode" value="new" onchange="toggleInfrequentMode()" class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                    <span class="font-bold text-gray-800 text-[11px] uppercase tracking-wider">Crear Nueva</span>
                </label>
            </div>

            {{-- ZONA 1: BUSCAR EXISTENTE --}}
            <div id="mode_existing" class="mb-6">
                <input type="text" id="searchInactiveRut" onkeyup="filterInactive()" placeholder="Filtrar por RUT..." 
                       class="w-full rounded-xl border-2 border-gray-300 p-2 text-sm mb-3 focus:border-purple-500 focus:ring-0">
                
                <div class="max-h-32 overflow-y-auto border-2 border-gray-200 rounded-xl bg-gray-50 p-2 space-y-1">
                    @forelse($inactiveStudents as $inactive)
                        <label class="inactive-item flex items-center gap-3 p-2 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-purple-50 transition" data-rut="{{ $inactive->rut }}">
                            <input type="radio" name="student_id" value="{{ $inactive->id }}" class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 text-sm leading-none">{{ $inactive->name }}</span>
                                <span class="text-[10px] font-black text-gray-400 mt-1">{{ $inactive->rut }}</span>
                            </div>
                        </label>
                    @empty
                        <div class="text-xs text-gray-400 italic text-center py-2">No hay alumnas deshabilitadas.</div>
                    @endforelse
                </div>
                @error('student_id') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- ZONA 2: CREAR NUEVA --}}
            <div id="mode_new" class="mb-6 hidden space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">RUT</label>
                    <input type="text" name="rut" placeholder="12.345.678-9" oninput="formatRut(this)" maxlength="12"
                           class="w-full rounded-xl border-2 border-gray-400 p-3 focus:border-purple-500 focus:ring-0">
                    @error('rut') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo</label>
                    <input type="text" name="name" placeholder="Ej: Camila Rojas" 
                           class="w-full rounded-xl border-2 border-gray-400 p-3 focus:border-purple-500 focus:ring-0">
                    @error('name') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- CAMPOS COMUNES DE PAGO --}}
            <div class="mb-4 pt-4 border-t border-gray-200">
                <label class="block text-sm font-bold text-gray-700 mb-1">Monto Pagado ($)</label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-500 font-bold">$</span>
                    <input type="number" name="amount" placeholder="Ej: 5000" class="w-full pl-8 rounded-xl border-2 border-gray-400 p-3 focus:border-purple-500 focus:ring-0 font-bold" >
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-1">Comprobante (Imagen)</label>
                <input type="file" name="receipt" class="w-full text-sm text-gray-600 border-2 border-gray-400 rounded-xl p-2 bg-gray-50 focus:border-purple-500" >
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('infrequentModal').classList.add('hidden')" class="flex-1 font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 py-3 rounded-xl transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-purple-600 text-white font-black py-3 rounded-xl shadow-lg hover:bg-purple-700 transition">Guardar y Marcar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const currentSessionId = {{ $session->id }};
    const currentWorkshopId = {{ $session->workshop_id }};

    function filterStudents() {
        let input = document.getElementById('searchStudent').value.toLowerCase();
        let items = document.querySelectorAll('.student-item');
        items.forEach(item => {
            let name = item.querySelector('.student-name').innerText.toLowerCase();
            item.style.display = name.includes(input) ? 'flex' : 'none';
        });
    }

    // Buscador interno del Modal de Inactivas por RUT
    function filterInactive() {
        let input = document.getElementById('searchInactiveRut').value.toLowerCase().replace(/[^0-9kK]/g, '');
        let items = document.querySelectorAll('.inactive-item');
        items.forEach(item => {
            let rut = item.getAttribute('data-rut').toLowerCase().replace(/[^0-9kK]/g, '');
            item.style.display = rut.includes(input) ? 'flex' : 'none';
        });
    }

    // Alternar entre pestañas "Existente" y "Nueva"
    function toggleInfrequentMode() {
        const mode = document.querySelector('input[name="infrequent_mode"]:checked').value;
        const modeExisting = document.getElementById('mode_existing');
        const modeNew = document.getElementById('mode_new');

        if (mode === 'existing') {
            modeExisting.classList.remove('hidden');
            modeNew.classList.add('hidden');
        } else {
            modeExisting.classList.add('hidden');
            modeNew.classList.remove('hidden');
        }
    }

    // Formateador estricto de RUT (reutilizado)
    function formatRut(rutInput) {
        let valor = rutInput.value.replace(/[^0-9kK]/g, '').toUpperCase();
        if (valor.length === 0) { rutInput.value = ''; return; }
        let cuerpo = valor.slice(0, -1);
        let dv = valor.slice(-1);
        if (valor.length > 1) {
            cuerpo = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            rutInput.value = cuerpo + '-' + dv;
        } else {
            rutInput.value = valor;
        }
    }

    async function openPaymentModal(sid, studentName) {
        document.getElementById('quickPaymentForm').action = `/students/${sid}/payments`;
        document.getElementById('pModalStudentName').innerText = studentName;
        document.getElementById('paymentModal').classList.remove('hidden');

        const sessionsList = document.getElementById('modal_sessions_list');
        const spinner = document.getElementById('modal_loading_spinner');

        sessionsList.innerHTML = '';
        spinner.classList.remove('hidden');

        try {
            const response = await fetch(`/api/students/${sid}/available-sessions`);
            const sessions = await response.json();
            
            spinner.classList.add('hidden');

            if(sessions.length === 0) {
                sessionsList.innerHTML = '<p class="text-sm text-red-500 font-bold p-2 bg-red-50 rounded">No hay clases pendientes en este mes.</p>';
                return;
            }

            sessions.forEach(sess => {
                const label = document.createElement('label');
                label.className = "flex items-start gap-3 p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-green-50 transition";
                const isChecked = (sess.id == currentSessionId) ? 'checked' : '';

                label.innerHTML = `
                    <input type="checkbox" name="sessions[]" value="${sess.id}" ${isChecked} class="w-5 h-5 mt-0.5 text-green-600 rounded border-gray-300 focus:ring-green-500">
                    <div class="flex-1">
                        <div class="font-bold text-gray-800 text-sm leading-tight">${sess.workshop_name}</div>
                        <div class="text-xs font-bold text-gray-500 mt-0.5">${sess.formatted_date}</div>
                        <div class="text-[10px] font-black text-indigo-400 uppercase">A las ${sess.time}</div>
                    </div>
                `;
                sessionsList.appendChild(label);
            });

        } catch (error) {
            spinner.classList.add('hidden');
            sessionsList.innerHTML = '<p class="text-sm text-red-500 font-bold">Error al cargar el calendario.</p>';
        }
    }

    function toggleAttendance(sid, stid, btn) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const present = btn.getAttribute('aria-checked') === 'true';
        
        btn.classList.replace(present ? 'bg-green-500' : 'bg-gray-300', present ? 'bg-gray-300' : 'bg-green-500');
        btn.querySelector('span').classList.replace(present ? 'translate-x-7' : 'translate-x-1', present ? 'translate-x-1' : 'translate-x-7');
        btn.setAttribute('aria-checked', !present);

        fetch(`/sessions/${sid}/attendance/${stid}`, {
            method: 'POST', 
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
        });
    }
</script>
@endsection
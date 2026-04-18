@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4" x-data="{ activeTab: 'activos' }">
    
    {{-- Cabecera --}}
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Directorio de Alumnas</h1>
        <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition transform hover:scale-105">
            + Nueva Alumna
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg font-bold shadow-sm border-l-4 border-green-500">
            {{ session('success') }}
        </div>
    @endif

    {{-- Buscador y Pestañas --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        
        {{-- Pestañas --}}
        <div class="flex space-x-1 bg-gray-200 p-1 rounded-xl w-fit">
            <button @click="activeTab = 'activos'" :class="activeTab === 'activos' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'" class="px-6 py-2 rounded-lg font-bold transition-all">
                Activas ({{ $students->count() }})
            </button>
            <button @click="activeTab = 'inactivos'" :class="activeTab === 'inactivos' ? 'bg-white shadow text-red-600' : 'text-gray-500 hover:text-gray-700'" class="px-6 py-2 rounded-lg font-bold transition-all">
                Inactivas ({{ $inactiveStudents->count() }})
            </button>
        </div>

        {{-- Buscador por RUT (NUEVO) --}}
        <div class="relative w-full md:w-72">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="searchRut" onkeyup="filterByRut()" placeholder="Buscar por RUT..." 
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-gray-300 focus:border-blue-500 focus:ring-0 transition font-medium text-gray-700 placeholder-gray-400">
        </div>
    </div>

    {{-- TABLA ACTIVAS --}}
    <div x-show="activeTab === 'activos'" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">RUT</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Teléfono</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 italic">
                @forelse($students as $student)
                    {{-- Clase 'student-row' agregada para el buscador --}}
                    <tr class="student-row hover:bg-blue-50/50 transition-colors">
                        {{-- Clase 'rut-cell' agregada --}}
                        <td class="rut-cell px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-500">{{ $student->rut }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $student->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 text-right">{{ $student->phone ?? '—' }}</td>
                        <td class="px-6 py-4 text-right space-x-4">
                            <a href="{{ route('students.calendar', $student->id) }}" class="text-emerald-600 font-bold hover:underline">Asistencias y Pagos</a>
                            <button onclick='openEditModal({!! json_encode($student) !!})' class="text-blue-600 font-bold hover:underline">Editar</button>
                            <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('¿Desactivar alumna?')" class="text-red-400 font-bold hover:text-red-600">Desactivar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-10 text-center text-gray-400">Sin alumnas activas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TABLA INACTIVAS --}}
    <div x-show="activeTab === 'inactivos'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-red-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-red-600 uppercase">RUT</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-red-600 uppercase">Nombre</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-red-600 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 opacity-60">
                @forelse($inactiveStudents as $student)
                    <tr class="student-row hover:opacity-100 transition">
                        <td class="rut-cell px-6 py-4 text-sm font-bold text-gray-500">{{ $student->rut }}</td>
                        <td class="px-6 py-4 text-sm">{{ $student->name }}</td>
                        <td class="px-6 py-4 text-right space-x-4">
                            <a href="{{ route('students.calendar', $student->id) }}" class="text-emerald-600 font-bold hover:underline">Asistencias y Pagos</a>
                            <form action="{{ route('students.restore', $student->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-green-600 font-bold">Reactivar</button>
                            </form>
                            <form action="{{ route('students.force_delete', $student->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Eliminar permanentemente. ¿Estás segura?')" class="text-gray-400 hover:text-red-600">Borrar Físico</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-10 text-center text-gray-400 italic">Papelera vacía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL ESTUDIANTES --}}
<div id="studentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl border border-gray-200">
        <form id="studentForm" method="POST">
            @csrf
            <div id="methodField"></div>
            <h3 class="text-2xl font-bold mb-6 text-gray-900" id="modalTitle">Nueva Alumna</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">RUT</label>
                    {{-- EVENTO ONINPUT AGREGADO PARA FORMATEAR RUT --}}
                    <input type="text" name="rut" id="inputRut" value="{{ old('rut') }}" placeholder="12.345.678-9" oninput="formatRut(this)" maxlength="12"
                           class="w-full rounded-xl border-2 p-3 focus:ring-0 transition {{ $errors->has('rut') ? 'border-red-500 bg-red-50' : 'border-gray-400 focus:border-blue-500' }}" required>
                    @error('rut') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo</label>
                    <input type="text" name="name" id="inputName" value="{{ old('name') }}" placeholder="Ej: María José Pérez" 
                           class="w-full rounded-xl border-2 p-3 focus:ring-0 transition {{ $errors->has('name') ? 'border-red-500 bg-red-50' : 'border-gray-400 focus:border-blue-500' }}" required>
                    @error('name') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="phone" id="inputPhone" value="{{ old('phone') }}" placeholder="+56 9 1234 5678" 
                           class="w-full rounded-xl border-2 p-3 focus:ring-0 transition {{ $errors->has('phone') ? 'border-red-500 bg-red-50' : 'border-gray-400 focus:border-blue-500' }}">
                    @error('phone') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 py-3 rounded-xl transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-blue-700 transition">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --------------------------------------------------------
    // NUEVA FUNCIÓN: Formateador estricto de RUT
    // --------------------------------------------------------
    function formatRut(rutInput) {
        // 1. Quitar todo lo que no sea número o la letra K
        let valor = rutInput.value.replace(/[^0-9kK]/g, '').toUpperCase();
        
        if (valor.length === 0) {
            rutInput.value = '';
            return;
        }

        // 2. Separar el cuerpo del dígito verificador
        let cuerpo = valor.slice(0, -1);
        let dv = valor.slice(-1);

        // 3. Formatear con puntos y guion
        if (valor.length > 1) {
            cuerpo = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            rutInput.value = cuerpo + '-' + dv;
        } else {
            rutInput.value = valor;
        }
    }

    // --------------------------------------------------------
    // NUEVA FUNCIÓN: Buscador de RUT en tiempo real
    // --------------------------------------------------------
    function filterByRut() {
        // Obtenemos lo que escribió y le quitamos puntos y guiones para comparar puro número
        let inputSearch = document.getElementById('searchRut').value.toLowerCase().replace(/[^0-9kK]/g, '');
        let rows = document.querySelectorAll('.student-row');

        rows.forEach(row => {
            // Buscamos la celda del RUT y también le quitamos puntos/guiones
            let rutCell = row.querySelector('.rut-cell').innerText.toLowerCase().replace(/[^0-9kK]/g, '');
            
            if (rutCell.includes(inputSearch)) {
                row.style.display = ''; // Mostrar
            } else {
                row.style.display = 'none'; // Ocultar
            }
        });
    }

    // --------------------------------------------------------
    // Funciones de los Modales
    // --------------------------------------------------------
    function openCreateModal() {
        document.getElementById('studentForm').action = "{{ route('students.store') }}";
        document.getElementById('methodField').innerHTML = "";
        document.getElementById('modalTitle').innerText = "Nueva Alumna";
        
        @if(!$errors->any()) document.getElementById('studentForm').reset(); @endif
        
        document.getElementById('studentModal').classList.remove('hidden');
    }
    
    function openEditModal(student) {
        document.getElementById('studentForm').action = `/students/${student.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        document.getElementById('modalTitle').innerText = "Editar Alumna";
        document.getElementById('inputRut').value = student.rut;
        document.getElementById('inputName').value = student.name;
        document.getElementById('inputPhone').value = student.phone || '';
        document.getElementById('studentModal').classList.remove('hidden');
    }
    
    function closeModal() { 
        document.getElementById('studentModal').classList.add('hidden'); 
    }

    @if($errors->any())
        document.addEventListener("DOMContentLoaded", function() {
            openCreateModal();
        });
    @endif
</script>
@endsection
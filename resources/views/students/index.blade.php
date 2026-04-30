<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'activos' }">
        
        {{-- Cabecera --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 tracking-tight">Directorio de Alumnas</h1>
                <p class="mt-2 text-sm text-zinc-500 font-light">Gestiona tu comunidad, asistencias y pagos.</p>
            </div>
            <button onclick="openCreateModal()" class="bg-zinc-900 hover:bg-zinc-800 text-white font-medium py-3 px-6 rounded-xl shadow-sm transition active:scale-95">
                + Nueva Alumna
            </button>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Buscador y Pestañas --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            
            {{-- Pestañas --}}
            <div class="flex space-x-1 bg-zinc-100/80 p-1 rounded-xl w-fit border border-zinc-200">
                <button @click="activeTab = 'activos'" :class="activeTab === 'activos' ? 'bg-white shadow-sm text-zinc-900' : 'text-zinc-500 hover:text-zinc-700'" class="px-6 py-2 rounded-lg font-medium transition-all text-sm">
                    Activas ({{ $students->count() }})
                </button>
                <button @click="activeTab = 'inactivos'" :class="activeTab === 'inactivos' ? 'bg-white shadow-sm text-rose-600' : 'text-zinc-500 hover:text-zinc-700'" class="px-6 py-2 rounded-lg font-medium transition-all text-sm">
                    Inactivas ({{ $inactiveStudents->count() }})
                </button>
            </div>

            {{-- Buscador por RUT --}}
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchRut" onkeyup="filterByRut()" placeholder="Buscar por RUT..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-200 bg-white text-zinc-800 text-sm focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition placeholder-zinc-400 shadow-sm">
            </div>
        </div>

        {{-- TABLA ACTIVAS --}}
        <div x-show="activeTab === 'activos'" class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">RUT</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($students as $student)
                        <tr class="student-row hover:bg-zinc-50/50 transition-colors">
                            <td class="rut-cell px-6 py-4 whitespace-nowrap text-sm text-zinc-500">{{ $student->rut }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900">{{ $student->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 text-right">{{ $student->phone ?? '—' }}</td>
                            <td class="px-6 py-4 text-right space-x-4">
                                <a href="{{ route('students.calendar', $student->id) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition">Perfil & Pagos</a>
                                <button onclick='openEditModal({!! json_encode($student) !!})' class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition">Editar</button>
                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('¿Desactivar alumna?')" class="text-sm font-medium text-rose-500 hover:text-rose-700 transition">Desactivar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-10 text-center text-sm text-zinc-400 font-light">Sin alumnas activas en el directorio.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TABLA INACTIVAS --}}
        <div x-show="activeTab === 'inactivos'" x-cloak class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-rose-50/30">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-rose-600 uppercase tracking-wider">RUT</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-rose-600 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-rose-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 opacity-70 hover:opacity-100 transition-opacity duration-300">
                    @forelse($inactiveStudents as $student)
                        <tr class="student-row hover:bg-zinc-50/50 transition">
                            <td class="rut-cell px-6 py-4 text-sm text-zinc-500">{{ $student->rut }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-zinc-700">{{ $student->name }}</td>
                            <td class="px-6 py-4 text-right space-x-4">
                                <a href="{{ route('students.calendar', $student->id) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition">Historial</a>
                                <form action="{{ route('students.restore', $student->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition">Reactivar</button>
                                </form>
                                <form action="{{ route('students.force_delete', $student->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Esta acción borrará asistencias y pagos. ¿Estás segura?')" class="text-sm font-medium text-rose-400 hover:text-rose-600 transition">Borrar Definitivo</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-10 text-center text-sm text-zinc-400 font-light">La papelera está vacía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- MODAL ESTUDIANTES --}}
    <div id="studentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl border border-zinc-100">
            <form id="studentForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <h3 class="text-2xl font-bold mb-6 text-zinc-900 tracking-tight" id="modalTitle">Nueva Alumna</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">RUT</label>
                        <input type="text" name="rut" id="inputRut" value="{{ old('rut') }}" placeholder="12.345.678-9" oninput="formatRut(this)" maxlength="12"
                               class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white {{ $errors->has('rut') ? 'border-rose-300 bg-rose-50' : '' }}" required>
                        @error('rut') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Nombre Completo</label>
                        <input type="text" name="name" id="inputName" value="{{ old('name') }}" placeholder="Ej: María José Pérez" 
                               class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white {{ $errors->has('name') ? 'border-rose-300 bg-rose-50' : '' }}" required>
                        @error('name') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Teléfono</label>
                        <input type="text" name="phone" id="inputPhone" value="{{ old('phone') }}" placeholder="+56 9 1234 5678" 
                               class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white {{ $errors->has('phone') ? 'border-rose-300 bg-rose-50' : '' }}">
                        @error('phone') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition text-sm">Cancelar</button>
                    <button type="submit" class="flex-1 bg-zinc-900 text-white font-medium py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition text-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        function filterByRut() {
            let inputSearch = document.getElementById('searchRut').value.toLowerCase().replace(/[^0-9kK]/g, '');
            let rows = document.querySelectorAll('.student-row');
            rows.forEach(row => {
                let rutCell = row.querySelector('.rut-cell').innerText.toLowerCase().replace(/[^0-9kK]/g, '');
                row.style.display = rutCell.includes(inputSearch) ? '' : 'none';
            });
        }

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
            document.addEventListener("DOMContentLoaded", function() { openCreateModal(); });
        @endif
    </script>
</x-app-layout>
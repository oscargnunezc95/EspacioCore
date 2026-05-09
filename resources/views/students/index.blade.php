<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />

        <div class="mt-8">
            <x-studio-header 
                title="Directorio de alumnas/os"
                :breadcrumbs="[
                    ['name' => 'alumnas/os']
                ]"
            >
                <x-slot name="actions">
                    <button onclick="openCreateModal()" class="bg-zinc-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-zinc-800 focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nueva Alumna/o
                    </button>
                </x-slot>
            </x-studio-header>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'activos' }">
        
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex space-x-1 bg-zinc-100/80 p-1 rounded-xl w-fit border border-zinc-200">
                <button @click="activeTab = 'activos'" :class="activeTab === 'activos' ? 'bg-white shadow-sm text-zinc-900' : 'text-zinc-500 hover:text-zinc-700'" class="px-6 py-2 rounded-lg font-bold transition-all duration-200 text-sm">
                    Activas ({{ $students->count() }})
                </button>
                <button @click="activeTab = 'inactivos'" :class="activeTab === 'inactivos' ? 'bg-white shadow-sm text-rose-600' : 'text-zinc-500 hover:text-zinc-700'" class="px-6 py-2 rounded-lg font-bold transition-all duration-200 text-sm">
                    Inactivas ({{ $inactiveStudents->count() }})
                </button>
            </div>

            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchOmni" onkeyup="filterOmni()" placeholder="Buscar por nombre, correo, teléfono o RUT..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-300 bg-white text-zinc-900 text-sm focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900 transition-all outline-none shadow-sm">
            </div>
        </div>

        {{-- TABLA ACTIVAS --}}
        <div x-show="activeTab === 'activos'" class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Documento (RUT)</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Apellido</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Contacto</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-zinc-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100"> 
                        @forelse($students as $student)
                            <tr class="student-row hover:bg-zinc-50/80 transition-colors duration-200 group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 font-medium">
                                    {{ $student->formatted_national_id ?? ($student->national_id ?: '—') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-zinc-900 uppercase">{{ $student->last_name ?: '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-700 capitalize">{{ $student->first_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-600">{{ $student->email }}</div>
                                    <div class="text-xs text-zinc-400 mt-0.5">{{ $student->phone ?: 'Sin teléfono' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($student->user_id)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Vinculada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-zinc-100 text-zinc-600 border border-zinc-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span> Ficha local
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}" class="text-sm font-bold text-emerald-600 hover:text-emerald-800 transition-colors">Asistencias & Pagos</a>
                                    <button type="button" data-student="{{ $student->toJson() }}" onclick="openEditModal(this)" class="text-sm font-bold text-zinc-400 hover:text-zinc-900 transition-colors">Editar</button>
                                    <form action="{{ route('students.destroy', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Desactivar alumna?')" class="text-sm font-bold text-rose-400 hover:text-rose-600 transition-colors">Desactivar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-zinc-500 font-medium">Sin alumnas/os activas en el directorio.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABLA INACTIVAS --}}
        <div x-show="activeTab === 'inactivos'" x-cloak class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-rose-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Documento (RUT)</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Apellido</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Contacto</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-rose-600 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-rose-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 opacity-70 hover:opacity-100 transition-opacity duration-300">
                        @forelse($inactiveStudents as $student)
                            <tr class="student-row hover:bg-zinc-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 font-medium">
                                    {{ $student->formatted_national_id ?? ($student->national_id ?: '—') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-zinc-700 uppercase">{{ $student->last_name ?: '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-600 capitalize">{{ $student->first_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-500">{{ $student->email }}</div>
                                    <div class="text-xs text-zinc-400 mt-0.5">{{ $student->phone ?: 'Sin teléfono' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactiva
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}" class="text-sm font-bold text-zinc-400 hover:text-zinc-600 transition-colors">Ver Historial</a>
                                    <form action="{{ route('students.restore', ['subdomain' => request()->route('subdomain'), 'id' => $student->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-sm font-bold text-emerald-600 hover:text-emerald-800 transition-colors">Reactivar</button>
                                    </form>
                                    <form action="{{ route('students.force_delete', ['subdomain' => request()->route('subdomain'), 'id' => $student->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Esta acción borrará asistencias y pagos permanentemente. ¿Estás segura?')" class="text-sm font-bold text-rose-500 hover:text-rose-700 transition-colors">Borrar Definitivo</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-zinc-500 font-medium">La papelera está vacía.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- MODAL ESTUDIANTES --}}
    <div id="studentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity overflow-y-auto">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-lg w-full shadow-xl border border-zinc-100 my-8 transform transition-all relative">
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-zinc-900 tracking-tight" id="modalTitle">Nueva Alumna</h3>
                </div>
                <button type="button" onclick="closeModal()" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="studentForm" method="POST">
                @csrf
                <div id="methodField"></div>
                
                <div class="space-y-5">
                    
                    {{-- Nombres --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre *</label>
                            <input type="text" name="first_name" id="inputFirstName" value="{{ old('first_name') }}" placeholder="Ej: Camila" 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none {{ $errors->has('first_name') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}" required>
                            @error('first_name') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Apellido <span class="text-zinc-400 font-normal">(Opc.)</span></label>
                            <input type="text" name="last_name" id="inputLastName" value="{{ old('last_name') }}" placeholder="Ej: Rojas" 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none {{ $errors->has('last_name') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
                            @error('last_name') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- NUEVO: País y RUT/Documento en Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">País del Documento *</label>
                            <select name="country_id" id="inputCountryId" required class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none cursor-pointer {{ $errors->has('country_id') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
                                <option value="">Selecciona un país...</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">N° de Documento <span class="text-zinc-400 font-normal">(Opc.)</span></label>
                            <input type="text" name="national_id" id="inputNationalId" value="{{ old('national_id') }}" placeholder="Ej: 19.123.456-7" 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none {{ $errors->has('national_id') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
                            @error('national_id') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Correo --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Correo Electrónico</label>
                        <input type="email" name="email" id="inputEmail" value="{{ old('email') }}" placeholder="camila@ejemplo.com" 
                               class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none {{ $errors->has('email') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
                        @error('email') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Teléfono <span class="text-zinc-400 font-normal">(Opc.)</span></label>
                        <input type="text" name="phone" id="inputPhone" value="{{ old('phone') }}" placeholder="+56 9..." 
                               class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none {{ $errors->has('phone') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
                        @error('phone') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex gap-3 pt-4 border-t border-zinc-100">
                    <button type="button" onclick="closeModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all duration-200 active:scale-95 text-sm">Guardar Ficha</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Búsqueda en tiempo real
        function filterOmni() {
            let inputSearch = document.getElementById('searchOmni').value.toLowerCase();
            let rows = document.querySelectorAll('.student-row');
            
            rows.forEach(row => {
                let rowText = row.innerText.toLowerCase();
                row.style.display = rowText.includes(inputSearch) ? '' : 'none';
            });
        }

        // ==========================================
        // CONTROL DE CIERRE SEGURO DEL MODAL
        // ==========================================
        const modalBackdrop = document.getElementById('studentModal');
        let isMouseDownOnBackdrop = false;

        modalBackdrop.addEventListener('mousedown', function(e) {
            isMouseDownOnBackdrop = (e.target === modalBackdrop);
        });

        modalBackdrop.addEventListener('mouseup', function(e) {
            if (isMouseDownOnBackdrop && e.target === modalBackdrop) {
                closeModal();
            }
            isMouseDownOnBackdrop = false;
        });

        // Funciones del Modal
        function openCreateModal() {
            document.getElementById('studentForm').action = "{{ route('students.store', ['subdomain' => request()->route('subdomain')]) }}";
            document.getElementById('methodField').innerHTML = "";
            document.getElementById('modalTitle').innerText = "Nueva Alumna";
            
            @if(!$errors->any()) document.getElementById('studentForm').reset(); @endif
            
            // Set default country to CL (Assuming CL id is 1 or whatever you prefer. You can also leave it empty so they have to choose)
            // document.getElementById('inputCountryId').value = "1"; 

            document.body.style.overflow = 'hidden';
            document.getElementById('studentModal').classList.remove('hidden');
        }
        
        function openEditModal(buttonElement) {
            const student = JSON.parse(buttonElement.getAttribute('data-student'));
            
            let updateUrl = "{{ route('students.update', ['subdomain' => request()->route('subdomain'), 'student' => ':id']) }}";
            document.getElementById('studentForm').action = updateUrl.replace(':id', student.id);
            
            document.getElementById('methodField').innerHTML = '@method("PUT")';
            document.getElementById('modalTitle').innerText = "Editar Ficha";
            
            document.getElementById('inputFirstName').value = student.first_name || '';
            document.getElementById('inputLastName').value = student.last_name || '';
            document.getElementById('inputCountryId').value = student.country_id || '';
            document.getElementById('inputNationalId').value = student.national_id || '';
            document.getElementById('inputEmail').value = student.email || '';
            document.getElementById('inputPhone').value = student.phone || '';
            
            document.body.style.overflow = 'hidden';
            document.getElementById('studentModal').classList.remove('hidden');
        }
        
        function closeModal() { 
            document.body.style.overflow = '';
            document.getElementById('studentModal').classList.add('hidden'); 
        }

        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() { openCreateModal(); });
        @endif
    </script>
</x-app-layout>
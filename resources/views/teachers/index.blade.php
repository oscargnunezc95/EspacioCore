<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />
        <div class="mt-8">
            <x-studio-header title="Directorio del Equipo" :breadcrumbs="[['name' => 'Profesores']]">
                <x-slot name="actions">
                    <button onclick="openTeacherModal()" class="bg-zinc-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-zinc-800 transition-all active:scale-95 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nuevo Profesor
                    </button>
                </x-slot>
            </x-studio-header>
        </div>
    </x-slot>

    {{-- INYECTAMOS ALPINE.JS PARA LAS PESTAÑAS --}}
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'activos' }">
        
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- CONTROLES SUPERIORES (Tabs + Búsqueda) --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex space-x-1 bg-zinc-100/80 p-1 rounded-xl w-fit border border-zinc-200">
                <button @click="activeTab = 'activos'" :class="activeTab === 'activos' ? 'bg-white shadow-sm text-zinc-900' : 'text-zinc-500 hover:text-zinc-700'" class="px-6 py-2 rounded-lg font-bold transition-all duration-200 text-sm">
                    Activos ({{ $teachers->count() }})
                </button>
                <button @click="activeTab = 'inactivos'" :class="activeTab === 'inactivos' ? 'bg-white shadow-sm text-rose-600' : 'text-zinc-500 hover:text-zinc-700'" class="px-6 py-2 rounded-lg font-bold transition-all duration-200 text-sm">
                    Inactivos ({{ $inactiveTeachers->count() }})
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

        {{-- TABLA ACTIVOS --}}
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
                        @forelse($teachers as $teacher)
                            <tr class="teacher-row hover:bg-zinc-50/80 transition-colors duration-200 group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 font-medium">
                                    {{ $teacher->formatted_national_id ?? ($teacher->national_id ?: '—') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-zinc-900 uppercase">{{ $teacher->last_name ?: '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-700 capitalize">{{ $teacher->first_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-500 whitespace-nowrap">
                                    <div class="font-medium text-zinc-600">{{ $teacher->email }}</div>
                                    <div class="text-xs mt-0.5 text-zinc-400">{{ $teacher->phone ?: 'Sin teléfono' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($teacher->user_id)
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
                                    <button onclick="openEditTeacherModal({{ json_encode($teacher) }})" class="text-sm font-bold text-zinc-400 hover:text-zinc-900 transition-colors">Editar</button>
                                    <form action="{{ route('teachers.destroy', ['subdomain' => request()->route('subdomain'), 'teacher' => $teacher->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Desactivar a este profesor? Se desasignará de las clases futuras de forma automática.')" class="text-sm font-bold text-rose-400 hover:text-rose-600 transition-colors">Desactivar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-zinc-500 font-medium text-sm">No hay profesores activos en el equipo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABLA INACTIVOS --}}
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
                        @forelse($inactiveTeachers as $teacher)
                            <tr class="teacher-row hover:bg-zinc-50/80 transition-colors duration-200 group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 font-medium">
                                    {{ $teacher->formatted_national_id ?? ($teacher->national_id ?: '—') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-zinc-700 uppercase">{{ $teacher->last_name ?: '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-600 capitalize">{{ $teacher->first_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-500 whitespace-nowrap">
                                    <div class="font-medium text-zinc-500">{{ $teacher->email }}</div>
                                    <div class="text-xs mt-0.5 text-zinc-400">{{ $teacher->phone ?: 'Sin teléfono' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactivo
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                    <form action="{{ route('teachers.restore', ['subdomain' => request()->route('subdomain'), 'id' => $teacher->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-sm font-bold text-emerald-600 hover:text-emerald-800 transition-colors">Reactivar</button>
                                    </form>
                                    <form action="{{ route('teachers.force_delete', ['subdomain' => request()->route('subdomain'), 'id' => $teacher->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Esta acción es irreversible y podría dejar a clases pasadas sin profesor asignado. ¿Estás segura?')" class="text-sm font-bold text-rose-500 hover:text-rose-700 transition-colors">Borrar Definitivo</button>
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

    {{-- MODAL --}}
    <div id="teacherModal" class="fixed inset-0 z-50 hidden flex items-start justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto transition-opacity">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-lg w-full shadow-xl border border-zinc-100 my-auto transform transition-all relative">
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-zinc-900 tracking-tight" id="modalTitle">Nuevo Profesor</h3>
                </div>
                <button type="button" onclick="closeTeacherModal()" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Revisa los errores en el formulario.</span>
                </div>
            @endif

            <form id="teacherForm" method="POST">
                @csrf
                <div id="teacherMethod"></div>
                
                <div class="space-y-5">
                    
                    {{-- Nombres --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre *</label>
                            <input type="text" name="first_name" id="t_first_name" value="{{ old('first_name') }}" placeholder="Ej: Diego" required 
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all {{ $errors->has('first_name') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-zinc-300' }}">
                            @error('first_name') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Apellido <span class="text-zinc-400 font-normal">(Opcional)</span></label>
                            <input type="text" name="last_name" id="t_last_name" value="{{ old('last_name') }}" placeholder="Ej: Silva" 
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all {{ $errors->has('last_name') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-zinc-300' }}">
                            @error('last_name') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    {{-- NUEVO: País y RUT/Documento en Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">País del Documento *</label>
                            <select name="country_id" id="t_country_id" required class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none cursor-pointer {{ $errors->has('country_id') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
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
                            <input type="text" name="national_id" id="t_national_id" value="{{ old('national_id') }}" placeholder="Ej: 19.123.456-7" 
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all {{ $errors->has('national_id') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-zinc-300' }}">
                            @error('national_id') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Correo --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Correo Electrónico *</label>
                        <input type="email" name="email" id="t_email" value="{{ old('email') }}" required 
                               class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all {{ $errors->has('email') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-zinc-300' }}">
                        @error('email') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Teléfono <span class="text-zinc-400 font-normal">(Opcional)</span></label>
                        <input type="text" name="phone" id="t_phone" value="{{ old('phone') }}" placeholder="+56 9..."
                               class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all {{ $errors->has('phone') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-zinc-300' }}">
                        @error('phone') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex gap-3 pt-4 border-t border-zinc-100">
                    <button type="button" onclick="closeTeacherModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition-all duration-200 active:scale-95 text-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ==========================================
        // BÚSQUEDA EN TIEMPO REAL
        // ==========================================
        function filterOmni() {
            let inputSearch = document.getElementById('searchOmni').value.toLowerCase();
            let rows = document.querySelectorAll('.teacher-row');
            
            rows.forEach(row => {
                let rowText = row.innerText.toLowerCase();
                row.style.display = rowText.includes(inputSearch) ? '' : 'none';
            });
        }

        // ==========================================
        // CONTROL DE CIERRE SEGURO DEL MODAL
        // ==========================================
        const modalBackdrop = document.getElementById('teacherModal');
        let isMouseDownOnBackdrop = false;

        modalBackdrop.addEventListener('mousedown', function(e) {
            isMouseDownOnBackdrop = (e.target === modalBackdrop);
        });

        modalBackdrop.addEventListener('mouseup', function(e) {
            if (isMouseDownOnBackdrop && e.target === modalBackdrop) {
                closeTeacherModal();
            }
            isMouseDownOnBackdrop = false;
        });

        // ==========================================
        // FUNCIONES DEL MODAL
        // ==========================================
        function openTeacherModal() {
            document.getElementById('teacherForm').action = "{{ route('teachers.store', ['subdomain' => request()->route('subdomain')]) }}";
            document.getElementById('teacherMethod').innerHTML = "";
            document.getElementById('modalTitle').innerText = 'Nuevo Profesor';
            
            @if(!$errors->any()) 
                document.getElementById('teacherForm').reset(); 
            @endif
            
            document.body.style.overflow = 'hidden';
            document.getElementById('teacherModal').classList.remove('hidden');
        }

        function openEditTeacherModal(t) {
            let updateUrl = "{{ route('teachers.update', ['subdomain' => request()->route('subdomain'), 'teacher' => ':id']) }}";
            document.getElementById('teacherForm').action = updateUrl.replace(':id', t.id);
            document.getElementById('teacherMethod').innerHTML = '@method("PUT")';
            document.getElementById('modalTitle').innerText = 'Editar Profesor';
            
            document.getElementById('t_first_name').value = t.first_name || '';
            document.getElementById('t_last_name').value = t.last_name || '';
            document.getElementById('t_country_id').value = t.country_id || '';
            document.getElementById('t_national_id').value = t.national_id || '';
            document.getElementById('t_email').value = t.email || '';
            document.getElementById('t_phone').value = t.phone || '';
            
            document.body.style.overflow = 'hidden';
            document.getElementById('teacherModal').classList.remove('hidden');
        }

        function closeTeacherModal() {
            document.body.style.overflow = '';
            document.getElementById('teacherModal').classList.add('hidden');
        }

        // ==========================================
        // RECUPERACIÓN DE ERRORES
        // ==========================================
        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                openTeacherModal();
            });
        @endif
    </script>
</x-app-layout>
<x-app-layout>
    
    {{-- 1. NAVEGACIÓN DEL ESTUDIO (Libre de paddings, pegado arriba) --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO (Aquí mantenemos tu x-data para las pestañas internas) --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'activos' }">

        {{-- Cabecera Unificada del Directorio --}}
        <div class="mt-2 mb-8 p-1">

            {{-- Breadcrumbs --}}
            <div class="flex text-xs font-bold text-zinc-500 mb-3 gap-2 items-center">
                <span class="text-zinc-900">Alumnas/os</span>
            </div>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">
                
                {{-- Título --}}
                <h1 class="text-2xl md:text-3xl font-black text-zinc-900 truncate flex-1 min-w-0">
                    Directorio de alumnas/os
                </h1>

                {{-- Botón Responsivo --}}
                <button onclick="openCreateModal()" class="shrink-0 ml-auto bg-zinc-900 text-white px-3 sm:px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-zinc-800 focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2">
                    
                    {{-- Icono User-Plus --}}
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    
                    {{-- Texto oculto en móviles --}}
                    <span class="hidden sm:inline">Nueva Alumna/o</span>
                </button>
            </div>
        </div>

        
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
            <div class="overflow-x-auto hide-scrollbar">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            {{-- Desaparece en lg hacia abajo --}}
                            <th class="hidden lg:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Documento</th>
                            
                            <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Alumna/o</th>
                            
                            {{-- Desaparece en sm hacia abajo --}}
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Contacto</th>
                            
                            {{-- Desaparece en sm hacia abajo --}}
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">Estado</th>
                            
                            <th class="px-4 md:px-6 py-4 text-right text-xs font-bold text-zinc-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100"> 
                        @forelse($students as $student)
                            @php
                                $statusDot = $student->user_id ? 'bg-emerald-500' : 'bg-zinc-400';
                                $statusBadge = $student->user_id ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-zinc-100 border-zinc-200 text-zinc-600';
                                $statusText = $student->user_id ? 'Vinculada' : 'Local';
                                $rut = $student->formatted_national_id ?? ($student->national_id ?: '—');
                            @endphp
                            <tr class="student-row hover:bg-zinc-50/80 transition-colors duration-200 group">
                                
                                {{-- RUT (Solo Desktop lg+) --}}
                                <td class="hidden lg:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-sm text-zinc-500 font-medium">
                                    {{ $rut }}
                                </td>
                                
                                {{-- Alumno (Múltiples Breakpoints) --}}
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-zinc-900">
                                        <span class="capitalize">{{ $student->first_name }}</span> 
                                        <span class="uppercase">{{ $student->last_name }}</span>
                                    </div>
                                    <div class="flex flex-col gap-0.5 mt-1">
                                        {{-- RUT Inyectado: Se muestra de lg hacia abajo (lg:hidden) --}}
                                        <div class="lg:hidden flex items-center gap-1.5 text-xs text-zinc-500 font-medium">
                                            {{-- Punto de estado reubicado (Solo celular: sm:hidden) --}}
                                            <span class="sm:hidden w-1.5 h-1.5 rounded-full shrink-0 {{ $statusDot }}"></span>
                                            {{ $rut }}
                                        </div>
                                        {{-- Contacto Apilado: Se muestra de sm hacia abajo (sm:hidden) --}}
                                        <div class="sm:hidden text-xs text-zinc-500 font-medium ">
                                            {{ $student->email ?: ($student->phone ?: 'Sin contacto') }}
                                        </div>
                                    </div>
                                </td>
                                
                                {{-- Contacto Normal (Solo Desktop y Tablet sm+) --}}
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-600">{{ $student->email ?: '—' }}</div>
                                    <div class="text-xs text-zinc-400 mt-0.5">{{ $student->phone ?: 'Sin teléfono' }}</div>
                                </td>
                                
                                {{-- Estado Normal (Desaparece texto en md, desaparece completo en sm) --}}
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center gap-1.5 px-2 py-1 rounded-md text-[10px] md:text-xs font-bold border {{ $statusBadge }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span> 
                                        {{-- El texto desaparece en md hacia abajo --}}
                                        <span class="hidden md:inline">{{ $statusText }}</span>
                                    </span>
                                </td>
                                
                                {{-- Acciones --}}
                                <td class="px-4 md:px-6 py-4 text-right space-x-2 md:space-x-3 whitespace-nowrap">
                                    <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}" class="inline-block align-middle text-xs md:text-sm font-bold text-emerald-600 hover:text-emerald-800 transition-colors text-center leading-[1.2]">
                                        <span class="block lg:inline">Pagos y</span>
                                        <span class="block lg:inline">Asistencia</span>
                                    </a>
                                    
                                    <button type="button" data-student="{{ $student->toJson() }}" onclick="openEditModal(this)" class="inline-block align-middle text-xs md:text-sm font-bold text-zinc-400 hover:text-zinc-900 transition-colors">
                                        Editar
                                    </button>
                                    
                                    <form action="{{ route('students.destroy', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}" method="POST" class="inline-block align-middle m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Desactivar alumna?')" class="text-xs md:text-sm font-bold text-rose-400 hover:text-rose-600 transition-colors" title="Desactivar">
                                            {{-- El texto desaparece en lg hacia abajo, dejando el icono --}}
                                            <span class="hidden lg:inline">Desactivar</span>
                                            <svg class="w-4 h-4 lg:hidden inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-zinc-500 font-medium">Sin alumnas/os activas en el directorio.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABLA INACTIVAS --}}
        <div x-show="activeTab === 'inactivos'" x-cloak class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto hide-scrollbar">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-rose-50/50">
                        <tr>
                            <th class="hidden lg:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Documento</th>
                            <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Alumna/o</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Contacto</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-center text-xs font-bold text-rose-600 uppercase tracking-wider">Estado</th>
                            <th class="px-4 md:px-6 py-4 text-right text-xs font-bold text-rose-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 opacity-70 hover:opacity-100 transition-opacity duration-300">
                        @forelse($inactiveStudents as $student)
                            @php
                                $rut = $student->formatted_national_id ?? ($student->national_id ?: '—');
                            @endphp
                            <tr class="student-row hover:bg-zinc-50/80 transition-colors">
                                
                                <td class="hidden lg:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-sm text-zinc-500 font-medium">
                                    {{ $rut }}
                                </td>
                                
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-zinc-700">
                                        <span class="capitalize">{{ $student->first_name }}</span> 
                                        <span class="uppercase">{{ $student->last_name }}</span>
                                    </div>
                                    <div class="flex flex-col gap-0.5 mt-1">
                                        <div class="lg:hidden flex items-center gap-1.5 text-xs text-zinc-500 font-medium">
                                            <span class="sm:hidden w-1.5 h-1.5 rounded-full shrink-0 bg-rose-500"></span>
                                            {{ $rut }}
                                        </div>
                                        <div class="sm:hidden text-xs text-zinc-500 font-medium ">
                                            {{ $student->email ?: ($student->phone ?: 'Sin contacto') }}
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-500">{{ $student->email ?: '—' }}</div>
                                    <div class="text-xs text-zinc-400 mt-0.5">{{ $student->phone ?: 'Sin teléfono' }}</div>
                                </td>
                                
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center gap-1.5 px-2 py-1 rounded-md text-[10px] md:text-xs font-bold border bg-rose-50 border-rose-200 text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> 
                                        <span class="hidden md:inline">Inactiva</span>
                                    </span>
                                </td>
                                
                                <td class="px-4 md:px-6 py-4 text-right space-x-2 md:space-x-3 whitespace-nowrap">
                                    <a href="{{ route('students.calendar', ['subdomain' => request()->route('subdomain'), 'student' => $student->id]) }}" class="text-xs md:text-sm font-bold text-zinc-400 hover:text-zinc-600 transition-colors">
                                        <span class="hidden md:inline">Pagos y Asistencia</span>
                                        <span class="md:hidden">Pagos</span>
                                    </a>
                                    <form action="{{ route('students.restore', ['subdomain' => request()->route('subdomain'), 'id' => $student->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs md:text-sm font-bold text-emerald-600 hover:text-emerald-800 transition-colors" title="Reactivar">
                                            <span class="hidden lg:inline">Reactivar</span>
                                            <svg class="w-4 h-4 lg:hidden inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('students.force_delete', ['subdomain' => request()->route('subdomain'), 'id' => $student->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Esta acción borrará asistencias y pagos permanentemente. ¿Estás segura?')" class="text-xs md:text-sm font-bold text-rose-500 hover:text-rose-700 transition-colors" title="Borrar Definitivo">
                                            <span class="hidden lg:inline">Borrar Definitivo</span>
                                            <svg class="w-4 h-4 lg:hidden inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-zinc-500 font-medium">La papelera está vacía.</td></tr>
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

                    {{-- País y RUT/Documento en Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">País del Documento *</label>
                            <select name="country_id" id="inputCountryId" required class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none cursor-pointer {{ $errors->has('country_id') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
                                <option value="">Selecciona un país...</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ old('country_id', 1) == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">N° de Documento <span class="text-rose-500">*</span></label>
                            <input type="text" name="national_id" id="inputNationalId" value="{{ old('national_id') }}" placeholder="Ej: 19.123.456-7" required
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
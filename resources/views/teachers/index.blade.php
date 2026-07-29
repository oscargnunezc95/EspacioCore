<x-app-layout>
    
    {{-- 1. NAVEGACIÓN DEL ESTUDIO (Libre de paddings, pegado arriba) --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO (Contenedor maestro con x-data) --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'activos' }">

        {{-- Cabecera Unificada del Directorio de Profesores --}}
        <div class="mt-2 mb-8 p-1">

            {{-- Breadcrumbs --}}
            <div class="flex text-xs font-bold text-stone-500 mb-3 gap-2 items-center">
                <span class="text-amber-600">Profesores</span>
            </div>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">
                
                {{-- Título --}}
                <h1 class="text-2xl md:text-3xl font-black  truncate flex-1 min-w-0">
                    Directorio del Equipo
                </h1>

                {{-- Botón Responsivo --}}
                <button onclick="openTeacherModal()" class="shrink-0 ml-auto bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2">
                    
                    {{-- Icono User-Plus --}}
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    
                    {{-- Texto oculto en móviles --}}
                    <span class="hidden sm:inline">Nuevo Profesor</span>
                </button>
            </div>
        </div>

        {{-- A partir de aquí sigue intacto el resto de tu código (lista de profesores, modales, etc.) --}}
        
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- CONTROLES SUPERIORES (Tabs + Búsqueda) --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex space-x-1 bg-stone-100/80 p-1 rounded-xl w-fit border border-stone-200">
                <button @click="activeTab = 'activos'" :class="activeTab === 'activos' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500 hover:text-stone-700'" class="px-6 py-2 rounded-lg font-bold transition-all duration-200 text-sm">
                    Activos ({{ $teachers->count() }})
                </button>
                <button @click="activeTab = 'inactivos'" :class="activeTab === 'inactivos' ? 'bg-white shadow-sm text-rose-600' : 'text-stone-500 hover:text-stone-700'" class="px-6 py-2 rounded-lg font-bold transition-all duration-200 text-sm">
                    Inactivos ({{ $inactiveTeachers->count() }})
                </button>
            </div>

            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchOmni" onkeyup="filterOmni()" placeholder="Buscar por nombre, correo, teléfono o RUT..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-stone-300 bg-white text-stone-900 text-sm focus:border-stone-900 focus:ring-2 focus:ring-stone-900 transition-all outline-none shadow-sm">
            </div>
        </div>

        {{-- TABLA ACTIVOS --}}
        <div x-show="activeTab === 'activos'" class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="overflow-x-auto hide-scrollbar">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="hidden lg:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Documento</th>
                            <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Profesor</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Contacto</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-center text-xs font-bold text-stone-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 md:px-6 py-4 text-right text-xs font-bold text-stone-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($teachers as $teacher)
                            @php
                                $statusDot = $teacher->user_id ? 'bg-emerald-500' : 'bg-stone-400';
                                $statusBadge = $teacher->user_id ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-stone-100 border-stone-200 text-stone-600';
                                $statusText = $teacher->user_id ? 'Vinculado' : 'Local';
                                $rut = $teacher->formatted_national_id ?? ($teacher->national_id ?: '—');
                            @endphp
                            <tr class="teacher-row hover:bg-stone-50/80 transition-colors duration-200 group">
                                
                                {{-- RUT (Solo Desktop lg+) --}}
                                <td class="hidden lg:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-sm text-stone-500 font-medium">
                                    {{ $rut }}
                                </td>
                                
                                {{-- Profesor (Múltiples Breakpoints) --}}
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-stone-900">
                                        <span class="capitalize">{{ $teacher->first_name }}</span> 
                                        <span class="uppercase">{{ $teacher->last_name }}</span>
                                    </div>
                                    <div class="flex flex-col gap-0.5 mt-1">
                                        {{-- RUT Inyectado: Se muestra de lg hacia abajo (lg:hidden) --}}
                                        <div class="lg:hidden flex items-center gap-1.5 text-xs text-stone-500 font-medium">
                                            {{-- Punto de estado reubicado (Solo celular: sm:hidden) --}}
                                            <span class="sm:hidden w-1.5 h-1.5 rounded-full shrink-0 {{ $statusDot }}"></span>
                                            {{ $rut }}
                                        </div>
                                        {{-- Contacto Apilado: Se muestra de sm hacia abajo (sm:hidden) --}}
                                        <div class="sm:hidden mt-1 text-xs text-stone-500 font-medium whitespace-normal break-all">
                                            {{ $teacher->email ?: ($teacher->phone ?: 'Sin contacto') }}
                                        </div>
                                    </div>
                                </td>
                                
                                {{-- Contacto Normal (Solo Desktop y Tablet sm+) --}}
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-stone-600">{{ $teacher->email ?: '—' }}</div>
                                    <div class="text-xs text-stone-400 mt-0.5">{{ $teacher->phone ?: 'Sin teléfono' }}</div>
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
                                    <a href="{{ route('teachers.payroll.show', ['subdomain' => request()->route('subdomain'), 'teacher' => $teacher->id]) }}" 
                                       class="inline-flex items-center gap-1.5 text-xs md:text-sm font-bold text-red-600 hover:text-red-800 transition-colors align-middle"
                                       title="Ver liquidación">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span class="hidden lg:inline">Nómina</span>
                                    </a>
                                    <button type="button" onclick='openEditTeacherModal(@json($teacher))' class="inline-block align-middle text-xs md:text-sm font-bold text-stone-400 hover:text-stone-900 transition-colors">
                                        Editar
                                    </button>
                                    <form action="{{ route('teachers.destroy', ['subdomain' => request()->route('subdomain'), 'teacher' => $teacher->id]) }}" method="POST" class="inline-block align-middle m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Desactivar a este profesor? Se desasignará de las clases futuras de forma automática.')" class="text-xs md:text-sm font-bold text-rose-400 hover:text-rose-600 transition-colors" title="Desactivar">
                                            {{-- El texto desaparece en lg hacia abajo, dejando el icono --}}
                                            <span class="hidden lg:inline">Desactivar</span>
                                            <svg class="w-4 h-4 lg:hidden inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-stone-500 font-medium text-sm">No hay profesores activos en el equipo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABLA INACTIVOS --}}
        <div x-show="activeTab === 'inactivos'" x-cloak class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="overflow-x-auto hide-scrollbar">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-rose-50/50">
                        <tr>
                            <th class="hidden lg:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Documento</th>
                            <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Profesor</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-rose-600 uppercase tracking-wider">Contacto</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-center text-xs font-bold text-rose-600 uppercase tracking-wider">Estado</th>
                            <th class="px-4 md:px-6 py-4 text-right text-xs font-bold text-rose-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 opacity-70 hover:opacity-100 transition-opacity duration-300">
                        @forelse($inactiveTeachers as $teacher)
                            @php
                                $rut = $teacher->formatted_national_id ?? ($teacher->national_id ?: '—');
                            @endphp
                            <tr class="teacher-row hover:bg-stone-50/80 transition-colors duration-200 group">
                                
                                <td class="hidden lg:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-sm text-stone-500 font-medium">
                                    {{ $rut }}
                                </td>
                                
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-stone-700">
                                        <span class="capitalize">{{ $teacher->first_name }}</span> 
                                        <span class="uppercase">{{ $teacher->last_name }}</span>
                                    </div>
                                    <div class="flex flex-col gap-0.5 mt-1">
                                        <div class="lg:hidden flex items-center gap-1.5 text-xs text-stone-500 font-medium">
                                            <span class="sm:hidden w-1.5 h-1.5 rounded-full shrink-0 bg-rose-500"></span>
                                            {{ $rut }}
                                        </div>
                                        <div class="sm:hidden mt-1 text-xs text-stone-500 font-medium whitespace-normal break-all">
                                            {{ $teacher->email ?: ($teacher->phone ?: 'Sin contacto') }}
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-stone-500">{{ $teacher->email ?: '—' }}</div>
                                    <div class="text-xs text-stone-400 mt-0.5">{{ $teacher->phone ?: 'Sin teléfono' }}</div>
                                </td>
                                
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center gap-1.5 px-2 py-1 rounded-md text-[10px] md:text-xs font-bold border bg-rose-50 border-rose-200 text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> 
                                        <span class="hidden md:inline">Inactivo</span>
                                    </span>
                                </td>
                                
                                <td class="px-4 md:px-6 py-4 text-right space-x-2 md:space-x-3 whitespace-nowrap">
                                    <form action="{{ route('teachers.restore', ['subdomain' => request()->route('subdomain'), 'id' => $teacher->id]) }}" method="POST" class="inline-block align-middle m-0">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs md:text-sm font-bold text-emerald-600 hover:text-emerald-800 transition-colors" title="Reactivar">
                                            <span class="hidden lg:inline">Reactivar</span>
                                            <svg class="w-4 h-4 lg:hidden inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('teachers.force_delete', ['subdomain' => request()->route('subdomain'), 'id' => $teacher->id]) }}" method="POST" class="inline-block align-middle m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Esta acción es irreversible y podría dejar a clases pasadas sin profesor asignado. ¿Estás segura?')" class="text-xs md:text-sm font-bold text-rose-500 hover:text-rose-700 transition-colors" title="Borrar Definitivo">
                                            <span class="hidden lg:inline">Borrar Definitivo</span>
                                            <svg class="w-4 h-4 lg:hidden inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-stone-500 font-medium">La papelera está vacía.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div id="teacherModal" class="fixed inset-0 z-50 hidden flex items-start justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto transition-opacity">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-lg w-full shadow-xl border border-stone-100 my-auto transform transition-all relative">
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-stone-900 tracking-tight" id="modalTitle">Nuevo Profesor</h3>
                </div>
                <button type="button" onclick="closeTeacherModal()" class="text-stone-400 hover:text-stone-600 transition-colors">
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
                            <label class="block text-sm font-bold text-stone-700 mb-1.5">Nombre *</label>
                            <input type="text" name="first_name" id="t_first_name" value="{{ old('first_name') }}" placeholder="Ej: Diego" required 
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-stone-900 focus:border-stone-900 outline-none transition-all {{ $errors->has('first_name') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-stone-300' }}">
                            @error('first_name') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1.5">Apellido <span class="text-stone-400 font-normal">(Opcional)</span></label>
                            <input type="text" name="last_name" id="t_last_name" value="{{ old('last_name') }}" placeholder="Ej: Silva" 
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-stone-900 focus:border-stone-900 outline-none transition-all {{ $errors->has('last_name') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-stone-300' }}">
                            @error('last_name') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    {{-- País y RUT/Documento en Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-stone-700 mb-1.5">País del Documento *</label>
                            <select name="country_id" id="t_country_id" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-stone-900 focus:border-stone-900 outline-none cursor-pointer {{ $errors->has('country_id') ? 'border-rose-500 ring-1 ring-rose-500' : '' }}">
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
                            <label class="block text-sm font-bold text-stone-700 mb-1.5">N° de Documento <span class="text-rose-500">*</span></label>
                            <input type="text" name="national_id" id="t_national_id" value="{{ old('national_id') }}" placeholder="Ej: 19.123.456-7" required
                                   class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-stone-900 focus:border-stone-900 outline-none transition-all {{ $errors->has('national_id') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-stone-300' }}">
                            @error('national_id') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Correo --}}
                    <div>
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Correo Electrónico *</label>
                        <input type="email" name="email" id="t_email" value="{{ old('email') }}" required 
                               class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-stone-900 focus:border-stone-900 outline-none transition-all {{ $errors->has('email') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-stone-300' }}">
                        @error('email') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Teléfono <span class="text-stone-400 font-normal">(Opcional)</span></label>
                        <input type="text" name="phone" id="t_phone" value="{{ old('phone') }}" placeholder="+56 9..."
                               class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 focus:ring-stone-900 focus:border-stone-900 outline-none transition-all {{ $errors->has('phone') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-stone-300' }}">
                        @error('phone') <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex gap-3 pt-4 border-t border-stone-100">
                    <button type="button" onclick="closeTeacherModal()" class="w-full font-bold text-stone-700 bg-stone-100 hover:bg-stone-200 border border-stone-200 py-2.5 px-4 rounded-xl transition-all duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 text-sm">Guardar</button>
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
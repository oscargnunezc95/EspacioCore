<x-app-layout>
    {{-- 1. NAVEGACIÓN DEL ESTUDIO --}}
    <x-studio-tabs />

    {{-- 2. CONTENIDO PRINCIPAL --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        {{-- Cabecera Unificada de Talleres --}}
        <div class="mt-2 mb-8 p-1">
            <div class="flex text-xs font-bold text-stone-500 mb-3 gap-2 items-center">
                <span class="text-amber-600">Talleres</span>
            </div>

            <div class="flex flex-row items-center justify-between gap-4 w-full">
                <h1 class="text-2xl md:text-3xl font-black truncate flex-1 min-w-0">
                    Talleres
                </h1>

                <button type="button" onclick="openWorkshopModal()" class="shrink-0 ml-auto bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2 text-sm cursor-pointer">
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="hidden sm:inline">Nuevo Taller</span>
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif
        
        {{-- FILTROS EN TIEMPO REAL --}}
        <div class="mb-6 bg-white p-5 rounded-2xl border border-stone-200 shadow-sm">
            <h4 class="text-sm font-bold text-stone-700 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filtros de Búsqueda
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <select id="filter_teacher" onchange="applyFilters()" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-600 outline-none cursor-pointer">
                        <option value="">Todos los profesores</option>
                        <option value="unassigned">Sin profesor asignado</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select id="filter_area" onchange="updateFilterDisciplines(); applyFilters()" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-600 outline-none cursor-pointer">
                        <option value="">Todas las áreas</option>
                        @foreach($existingAreas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select id="filter_discipline" onchange="applyFilters()" disabled class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-600 outline-none disabled:bg-stone-100 disabled:text-stone-400 cursor-pointer">
                        <option value="">Todas las disciplinas</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <button type="button" onclick="clearFilters()" class="text-sm text-stone-500 hover:text-stone-900 font-medium transition-colors border border-transparent hover:border-stone-200 px-3 py-2 rounded-lg">
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLA DE TALLERES --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="overflow-x-auto hide-scrollbar">
                <table class="min-w-full divide-y divide-stone-200" id="workshops_table">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Taller / Profesor</th>
                            <th class="hidden sm:table-cell px-4 md:px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Área y Disciplina</th>
                            <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Horario / Fecha</th>
                            <th class="px-4 md:px-6 py-4 text-right text-xs font-bold text-stone-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($workshops as $workshop)
                            @php 
                                $borderColor = match($workshop->color) {
                                    'emerald' => 'border-emerald-500', 'rose' => 'border-rose-500', 'purple' => 'border-purple-500',
                                    'amber' => 'border-amber-500', 'indigo' => 'border-indigo-500', 'teal' => 'border-teal-500',
                                    'cyan' => 'border-cyan-500', 'fuchsia' => 'border-fuchsia-500', 'slate' => 'border-slate-500',
                                    default => 'border-blue-500',
                                };
                                
                                $imageUrl = $workshop->image_path 
                                    ? asset('storage/' . $workshop->image_path) 
                                    : 'https://ui-avatars.com/api/?name='.urlencode($workshop->name).'&color=4f46e5&background=e0e7ff&size=128';
                            @endphp
                            
                            <tr class="workshop-row hover:bg-stone-50/80 transition-colors duration-200 group"
                                data-teacher-id="{{ $workshop->teacher_id ?? 'unassigned' }}"
                                data-area-name="{{ $workshop->discipline->area->name ?? '' }}"
                                data-discipline-name="{{ $workshop->discipline->name ?? '' }}">
                                
                                {{-- Columna 1: Taller --}}
                                <td class="px-4 md:px-6 py-4 border-l-4 {{ $borderColor }}">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $imageUrl }}" class="w-10 h-10 md:w-12 md:h-12 rounded-lg object-cover border border-stone-200 shadow-sm shrink-0">
                                        <div class="min-w-0">
                                            <div class="font-bold text-stone-900 flex flex-wrap items-center gap-1.5 md:gap-2 text-sm">
                                                <span class="truncate max-w-[150px] sm:max-w-none">{{ $workshop->name }}</span>
                                                @if($workshop->is_single_class) 
                                                    <span class="bg-stone-100 text-stone-600 border border-stone-200 text-[9px] md:text-[10px] px-1.5 md:px-2 py-0.5 rounded uppercase tracking-widest font-black shrink-0">Clase Única</span> 
                                                @endif
                                                @if($workshop->max_students) 
                                                    <span class="bg-stone-50 text-stone-500 border border-stone-200 text-[9px] md:text-[10px] px-1.5 md:px-2 py-0.5 rounded uppercase tracking-widest font-bold flex items-center gap-1 shrink-0" title="Cupo Máximo">
                                                        Max {{ $workshop->max_students }}
                                                    </span> 
                                                @endif
                                            </div>
                                            <div class="text-xs text-stone-500 mt-1 md:mt-1.5 flex items-center gap-1.5 font-medium">
                                                <span class="truncate max-w-[130px] sm:max-w-none">{{ $workshop->teacher ? $workshop->teacher->first_name . ' ' . $workshop->teacher->last_name : 'Sin profesor' }}</span>
                                            </div>
                                            @if($workshop->discipline)
                                                <div class="sm:hidden mt-1.5">
                                                    <span class="text-[9px] font-black text-red-500 uppercase tracking-widest bg-red-50 px-1.5 py-0.5 rounded border border-red-100">
                                                        {{ $workshop->discipline->name }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Columna 2: Área y Disciplina --}}
                                <td class="hidden sm:table-cell px-4 md:px-6 py-4">
                                    @if($workshop->discipline)
                                        <div class="inline-flex flex-col items-start">
                                            <span class="text-[10px] font-black text-red-500 uppercase tracking-widest bg-red-50 px-2 py-0.5 rounded border border-red-100 mb-1">
                                                {{ $workshop->discipline->area->name ?? 'Sin Área' }}
                                            </span>
                                            <span class="text-sm font-bold text-stone-700">
                                                {{ $workshop->discipline->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-stone-400 italic">No configurado</span>
                                    @endif
                                </td>

                                {{-- Columna 3: Horario --}}
                                <td class="px-4 md:px-6 py-4 text-sm font-bold text-stone-700">
                                    @if($workshop->is_single_class && $workshop->specific_date)
                                        <span class="text-stone-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($workshop->specific_date)->translatedFormat('d \d\e F') }}</span> 
                                        <div class="text-xs text-stone-500 font-medium mt-0.5">
                                            a las <span class="text-stone-900 font-bold">{{ \Carbon\Carbon::parse($workshop->start_time)->format('H:i') }}</span>
                                        </div>
                                    @else
                                        @if($workshop->schedules && $workshop->schedules->count() > 0)
                                            <div class="space-y-1.5">
                                                @foreach($workshop->schedules->sortBy('day_of_week') as $schedule)
                                                    <div class="flex items-center gap-2">
                                                        <span class="bg-stone-100 text-stone-700 px-1.5 py-0.5 rounded text-[10px] border border-stone-200 uppercase font-black w-9 md:w-10 text-center shrink-0">
                                                            {{ ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'][$schedule->day_of_week] ?? '' }}
                                                        </span>
                                                        <span class="text-stone-900 font-bold text-xs">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</span>
                                                        @if($schedule->max_students)
                                                            <span class="hidden md:inline text-[10px] text-stone-500 font-medium">(Cupo: {{ $schedule->max_students }})</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-rose-500 italic text-xs font-normal">Sin horarios</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Columna 4: Acciones --}}
                                <td class="px-4 md:px-6 py-4 text-right space-x-2 md:space-x-3 whitespace-nowrap">
                                    <button type="button" data-workshop="{{ $workshop->toJson() }}" onclick="openEditWorkshopModal(this)" class="inline-block align-middle text-xs md:text-sm font-bold text-stone-500 hover:text-stone-900 bg-stone-50 border border-stone-200 px-2.5 md:px-3 py-1.5 rounded-lg transition-colors duration-200 cursor-pointer" title="Editar">
                                        <span class="hidden lg:inline">Editar</span>
                                        <svg class="w-4 h-4 lg:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <form action="{{ route('workshops.destroy', ['subdomain' => request()->route('subdomain'), 'workshop' => $workshop->id]) }}" method="POST" class="inline-block align-middle m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Eliminar este taller permanentemente?')" class="text-xs md:text-sm font-bold text-rose-500 hover:text-white bg-white hover:bg-rose-500 border border-rose-200 hover:border-rose-500 px-2.5 md:px-3 py-1.5 rounded-lg transition-colors duration-200 cursor-pointer" title="Eliminar">
                                            <span class="hidden lg:inline">Eliminar</span>
                                            <svg class="w-4 h-4 lg:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-sm font-bold text-stone-400">No hay talleres configurados</td></tr>
                        @endforelse
                        <tr id="no_results_row" style="display: none;"><td colspan="4" class="px-6 py-8 text-center text-sm font-bold text-stone-400">No hay talleres que coincidan con los filtros</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 🚀 BLINDAJE QA: MODAL CON z-[9999] FUERA DE TODO CONTENEDOR ANIDADO --}}
    <div id="workshopModal" class="fixed inset-0 z-[9999] hidden flex items-start justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto transition-opacity custom-scrollbar">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-3xl w-full shadow-xl border border-stone-100 my-auto transform transition-all relative z-10">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-stone-900 tracking-tight" id="modalWorkshopTitle">Configurar Taller</h3>
                <button type="button" onclick="closeWorkshopModal()" class="text-stone-400 hover:text-stone-600 bg-stone-50 hover:bg-stone-100 p-2 rounded-full transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Hay errors en el formulario. Por favor revisa los campos en rojo.</span>
                </div>
            @endif

            <form id="workshopForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="workshopMethod"></div>
                <input type="hidden" name="workshop_id" id="w_id" value="{{ old('workshop_id') }}">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    {{-- Nombre --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Nombre del Taller *</label>
                        <input type="text" name="name" id="w_name" value="{{ old('name') }}" placeholder="Ej: Telas Principiante" 
                            class="w-full rounded-xl border {{ $errors->has('name') ? 'border-rose-300 ring-1 ring-rose-300' : 'border-stone-300' }} px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none transition-all bg-stone-50 focus:bg-white" required>
                        @error('name') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Descripción --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Descripción de la Clase <span class="text-stone-400 font-normal">(Opcional)</span></label>
                        <textarea name="description" id="w_description" rows="3" placeholder="Cuéntale a tus alumnos de qué trata esta clase..." 
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 transition-all outline-none bg-stone-50 focus:bg-white">{{ old('description') }}</textarea>
                        @error('description') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Video Promocional --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Video Promocional <span class="text-stone-400 font-normal">(YouTube o Instagram)</span></label>
                        <input type="url" name="promo_video_url" id="w_promo_video_url" value="{{ old('promo_video_url') }}" placeholder="Ej: https://www.instagram.com/reel/xyz/" 
                            class="w-full rounded-xl border {{ $errors->has('promo_video_url') ? 'border-rose-300 ring-1 ring-rose-300' : 'border-stone-300' }} px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none transition-all bg-stone-50 focus:bg-white">
                        @error('promo_video_url') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Subida de Imagen --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Imagen del Taller <span class="text-stone-400 font-normal">(Opcional, máx 12MB)</span></label>
                        <input type="file" name="image" id="w_image" accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="w-full rounded-xl border {{ $errors->has('image') ? 'border-rose-300 ring-1 ring-rose-300' : 'border-stone-300' }} px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-600 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer bg-white">
                        @error('image') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Área y Disciplina --}}
                    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <label class="block text-sm font-bold text-stone-700 mb-1.5">Área General *</label>
                            <div class="relative">
                                <input type="text" name="area" id="w_area" value="{{ old('area') }}" placeholder="Ej: Circo, Baile..." required autocomplete="off"
                                    class="w-full rounded-xl border {{ $errors->has('area') ? 'border-rose-300 ring-1 ring-rose-300' : 'border-stone-300' }} py-3 pl-4 pr-10 text-sm focus:ring-2 focus:ring-red-600 outline-none bg-white">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-stone-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <ul id="custom_area_list" class="absolute z-50 w-full mt-1 bg-white border border-stone-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden py-1 custom-scrollbar"></ul>
                            @error('area') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-bold text-stone-700 mb-1.5">Disciplina Específica *</label>
                            <div class="relative">
                                <input type="text" name="discipline" id="w_discipline" value="{{ old('discipline') }}" placeholder="Primero selecciona un Área..." required autocomplete="off"
                                    class="w-full rounded-xl border {{ $errors->has('discipline') ? 'border-rose-300 ring-1 ring-rose-300' : 'border-stone-300' }} py-3 pl-4 pr-10 text-sm focus:ring-2 focus:ring-red-600 outline-none disabled:bg-stone-100 disabled:text-stone-400 disabled:cursor-not-allowed bg-white">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-stone-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <ul id="custom_discipline_list" class="absolute z-50 w-full mt-1 bg-white border border-stone-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden py-1 custom-scrollbar"></ul>
                            @error('discipline') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Público Objetivo --}}
                    <div>
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Público Objetivo *</label>
                        <select name="target_audience" id="w_target_audience" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 transition-all outline-none cursor-pointer bg-stone-50 focus:bg-white">
                            <option value="all" {{ old('target_audience') == 'all' ? 'selected' : '' }}>Todas las edades</option>
                            <option value="kids" {{ old('target_audience') == 'kids' ? 'selected' : '' }}>Niñas/os (hasta 12 años)</option>
                            <option value="teens" {{ old('target_audience') == 'teens' ? 'selected' : '' }}>Adolescentes (13 - 17 años)</option>
                            <option value="adults" {{ old('target_audience', 'adults') == 'adults' ? 'selected' : '' }}>Adultos (+18 años)</option>
                        </select>
                        @error('target_audience') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Entrenador --}}
                    <div>
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Entrenador(a) Principal</label>
                        <select name="teacher_id" id="w_teacher_id" class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none cursor-pointer bg-stone-50 focus:bg-white">
                            <option value="">-- Sin profesor asignado --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- 🚀 CORREGIDO: CHECKBOX PRESENTE + MAPA E INPUT SE MUESTRAN O OCULTAN JUNTOS --}}
                    <div class="col-span-1 md:col-span-2 bg-stone-50 p-5 rounded-xl border border-stone-200 mt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <label class="block text-[11px] font-black text-stone-400 uppercase tracking-widest">Lugar de Clases</label>
                        </div>
                        
                        <label class="flex items-center gap-2 cursor-pointer group mb-4">
                            <input type="hidden" name="use_main_location" value="0">
                            <input type="checkbox" name="use_main_location" id="w_use_main_location" value="1" onchange="toggleLocationFields()" class="w-4 h-4 text-stone-900 border-stone-300 rounded focus:ring-red-600 cursor-pointer" {{ old('use_main_location', '1') == '1' ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-stone-700 group-hover:text-stone-900">Usar la sede principal del Estudio</span>
                        </label>

                        {{-- Contenedor que agrupa el input de dirección Y el mapa --}}
                        <div id="container_custom_location" class="hidden pt-4 border-t border-stone-200/60">
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="block text-sm font-bold text-stone-700">Dirección Específica <span class="text-stone-400 font-normal">(Para el Mapa)</span></label>
                                @if(isset($studio) && $studio->address)
                                <button type="button" onclick="copyStudioLocation()" class="text-[11px] font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-2.5 py-1 rounded-lg transition-all flex items-center gap-1 cursor-pointer">
                                    <span>📍 Copiar de sede principal</span>
                                </button>
                                @endif
                            </div>
                            <p class="text-xs text-stone-500 mb-3">Escribe la dirección o arrastra el marcador rojo en el mapa para ajustar la posición milimétrica.</p>
                            
                            <div class="relative mb-3">
                                <input type="text" name="address" id="w_address" value="{{ old('address') }}" placeholder="Ej: Pasaje 46, Población René Schneider, Puerto Montt..." autocomplete="off"
                                       class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none bg-white relative z-10">
                                <ul id="w_address_results" class="absolute z-50 w-full mt-1 bg-white border border-stone-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden py-1 custom-scrollbar"></ul>
                            </div>

                            <input type="hidden" name="latitude" id="w_latitude" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="w_longitude" value="{{ old('longitude') }}">
                            <input type="hidden" name="city" id="w_city" value="{{ old('city') }}">
                            <input type="hidden" name="region" id="w_region" value="{{ old('region') }}">
                            <input type="hidden" name="country" id="w_country" value="{{ old('country') }}">

                            {{-- MAPA: Visible inmediatamente al abrir container_custom_location --}}
                            <div id="map" class="w-full h-64 rounded-xl border border-stone-300 shadow-inner bg-stone-100 relative z-0"></div>
                        </div>

                        <input type="text" name="room_location" id="w_room_location" value="{{ old('room_location') }}" placeholder="Detalle extra del salón (Ej: Sala 2, Piso 4, Cancha Techada...)" 
                               class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none mt-4 bg-white shadow-sm">
                    </div>

                    {{-- Color --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-stone-700 mb-1.5">Color (Calendario) *</label>
                        <select name="color" id="w_color" class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none cursor-pointer bg-stone-50 focus:bg-white" required>
                            @foreach(['blue'=>'Azul Intenso','emerald'=>'Verde Esmeralda','teal'=>'Turquesa','cyan'=>'Celeste','indigo'=>'Índigo','purple'=>'Púrpura','fuchsia'=>'Fucsia','rose'=>'Rosa / Rojo','amber'=>'Ámbar / Naranja','slate'=>'Gris Oscuro'] as $val => $label)
                                <option value="{{ $val }}" {{ old('color') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('color') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>



                    {{-- TIPO DE CLASE Y HORARIOS --}}
                    <div class="col-span-1 md:col-span-2 bg-stone-50 p-5 rounded-xl border border-stone-200 mt-2">
                        <label class="block text-[11px] font-black text-stone-400 mb-3 uppercase tracking-widest">Frecuencia del Taller</label>
                        <div class="flex flex-col sm:flex-row gap-6 mb-4">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="is_single_class" value="0" id="type_monthly" onchange="toggleDateFields()" class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-red-600" {{ old('is_single_class', '0') == '0' ? 'checked' : '' }}>
                                <span class="text-sm font-bold text-stone-700 group-hover:text-stone-900">Mensual (Repetitivo)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="is_single_class" value="1" id="type_single" onchange="toggleDateFields()" class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-red-600" {{ old('is_single_class') == '1' ? 'checked' : '' }}>
                                <span class="text-sm font-bold text-stone-700 group-hover:text-stone-900">Clase Única (Masterclass)</span>
                            </label>
                        </div>

                        {{-- Contenedor Horarios Múltiples --}}
                        <div id="container_schedules" class="mt-2 border-t border-stone-200/60 pt-4">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h4 class="text-sm font-bold text-stone-900">Horarios Semanales</h4>
                                    <p class="text-xs text-stone-500">Agrega todos los bloques que necesites para este taller.</p>
                                </div>
                                <button type="button" onclick="addScheduleRow()" class="text-xs font-bold bg-white text-stone-900 border border-stone-200 px-3 py-2 rounded-lg hover:bg-stone-100 transition-colors shadow-sm cursor-pointer">
                                    + Agregar Horario
                                </button>
                            </div>
                            <div id="schedules_container" class="space-y-3"></div>
                            @error('schedules') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Contenedor Clase Única --}}
                        <div id="container_single_class_details" class="hidden mt-2 border-t border-stone-200/60 pt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-1.5">Fecha Exacta *</label>
                                <input type="date" name="specific_date" id="w_specific_date" value="{{ old('specific_date') }}" onclick="try { this.showPicker(); } catch(e) {}"
                                    class="w-full rounded-xl border {{ $errors->has('specific_date') ? 'border-rose-300' : 'border-stone-300' }} px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none cursor-pointer bg-white">
                                @error('specific_date') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-1.5">Hora de Inicio *</label>
                                <input type="time" name="start_time" id="w_start_time" value="{{ old('start_time') }}" onclick="try { this.showPicker(); } catch(e) {}"
                                    class="w-full rounded-xl border {{ $errors->has('start_time') ? 'border-rose-300' : 'border-stone-300' }} px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none cursor-pointer bg-white">
                                @error('start_time') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-1.5">Cupo Máximo <span class="text-stone-400 font-normal">(Opc.)</span></label>
                                <input type="number" name="max_students" id="w_max_students" value="{{ old('max_students') }}" placeholder="Ej: 15" min="1"
                                    class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-1.5">Precio (CLP) *</label>
                                <input type="number" name="single_class_price" id="w_single_price" value="{{ old('single_class_price') }}" placeholder="Ej: 15000" min="0"
                                    class="w-full rounded-xl border {{ $errors->has('single_class_price') ? 'border-rose-300' : 'border-stone-300' }} px-4 py-3 text-sm focus:ring-2 focus:ring-red-600 outline-none bg-white">
                                @error('single_class_price') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- PLANES DE PRECIOS (Time-Bound Packs) --}}
                    <div id="container_prices" class="col-span-1 md:col-span-2 border-t border-stone-200 pt-5 mt-2">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h4 class="text-sm font-bold text-stone-900">Planes de Precios</h4>
                                <p class="text-xs text-stone-500">Configura los paquetes estándar y los descuentos de bienvenida.</p>
                            </div>
                            <button type="button" onclick="addPriceRow()" class="text-xs font-bold bg-stone-100 text-stone-900 border border-stone-200 px-3 py-2 rounded-lg hover:bg-stone-200 transition-colors shadow-sm cursor-pointer">
                                + Agregar Plan
                            </button>
                        </div>
                        <div id="prices_container" class="space-y-4"></div>

                        {{-- Errores de validación de precios — se muestran como banner agrupado --}}
                        @php
                            $priceErrors = [];
                            foreach ($errors->getMessages() as $field => $msgs) {
                                if (str_starts_with($field, 'prices.')) {
                                    $priceErrors[$field] = $msgs;
                                }
                            }
                        @endphp
                        @if(!empty($priceErrors))
                            <div class="mt-4 p-4 bg-rose-50 border border-rose-200 rounded-xl">
                                <p class="text-sm font-bold text-rose-700 mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Revisa los precios:
                                </p>
                                <ul class="space-y-1">
                                    @foreach($priceErrors as $field => $msgs)
                                        @foreach($msgs as $msg)
                                            <li class="text-xs text-rose-600 font-medium pl-5">• {{ $msg }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                </div>

                <div class="mt-8 flex gap-3 pt-4 border-t border-stone-100">
                    <button type="button" onclick="closeWorkshopModal()" class="w-full bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95 text-sm cursor-pointer">Cancelar</button>
                    <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 text-sm cursor-pointer">Guardar Taller</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e4e4e7; border-radius: 20px; }
    </style>

    @php
        // Extraemos errores de precios para pasarlos al JS (los campos se generan dinámicamente)
        $priceErrors = [];
        foreach ($errors->getMessages() as $field => $msgs) {
            if (str_starts_with($field, 'prices.')) {
                $priceErrors[$field] = $msgs;
            }
        }
    @endphp
    <script>
        const categoryTree = @json($categoryTree ?? []);
        const studioMasterData = @json($studio ?? null);
        let priceIndex = 0;
        let scheduleIndex = 0;

        // Errores de validación de precios (para resaltar campos en rojo tras submit fallido)
        window.PriceErrors = @json($priceErrors);

        let map = null;
        let marker = null;

        const areaInput = document.getElementById('w_area');
        const areaList = document.getElementById('custom_area_list');
        const disciplineInput = document.getElementById('w_discipline');
        const disciplineList = document.getElementById('custom_discipline_list');

        function renderDropdown(input, listEl, dataArray, callback) {
            if (!listEl || !input) return;
            listEl.innerHTML = '';
            listEl.classList.remove('hidden');
            
            const filter = input.value.toLowerCase().trim();
            const filtered = (dataArray || []).filter(item => item.toLowerCase().includes(filter));

            if (filtered.length === 0) {
                const li = document.createElement('li');
                li.className = 'px-4 py-2.5 text-stone-400 italic text-sm cursor-default';
                li.textContent = filter ? `Se creará: "${input.value}"` : 'Sin opciones';
                listEl.appendChild(li);
                return;
            }

            filtered.forEach(item => {
                const li = document.createElement('li');
                li.className = 'px-4 py-2.5 hover:bg-stone-100 cursor-pointer text-stone-700 text-sm transition-colors';
                li.textContent = item;
                li.onmousedown = (e) => { 
                    e.preventDefault(); 
                    input.value = item;
                    listEl.classList.add('hidden');
                    if (callback) callback();
                };
                listEl.appendChild(li);
            });
        }

        function updateDisciplines() {
            if (!areaInput || !disciplineInput) return;
            const areaVal = areaInput.value.trim();
            if (areaVal === '') {
                disciplineInput.disabled = true;
                disciplineInput.placeholder = "Primero selecciona un Área...";
                if (document.activeElement === areaInput) disciplineInput.value = '';
            } else {
                disciplineInput.disabled = false;
                disciplineInput.placeholder = "Selecciona o escribe una disciplina...";
            }
        }

        if (areaInput) {
            areaInput.addEventListener('focus', () => renderDropdown(areaInput, areaList, Object.keys(categoryTree || {}), updateDisciplines));
            areaInput.addEventListener('input', () => {
                renderDropdown(areaInput, areaList, Object.keys(categoryTree || {}), updateDisciplines);
                updateDisciplines();
            });
            areaInput.addEventListener('blur', () => setTimeout(() => areaList?.classList.add('hidden'), 150));
        }

        if (disciplineInput) {
            disciplineInput.addEventListener('focus', () => {
                const areaKey = Object.keys(categoryTree || {}).find(key => key.toLowerCase() === (areaInput?.value || '').trim().toLowerCase());
                const disciplines = areaKey ? categoryTree[areaKey] : [];
                renderDropdown(disciplineInput, disciplineList, disciplines, null);
            });
            disciplineInput.addEventListener('input', () => {
                const areaKey = Object.keys(categoryTree || {}).find(key => key.toLowerCase() === (areaInput?.value || '').trim().toLowerCase());
                const disciplines = areaKey ? categoryTree[areaKey] : [];
                renderDropdown(disciplineInput, disciplineList, disciplines, null);
            });
            disciplineInput.addEventListener('blur', () => setTimeout(() => disciplineList?.classList.add('hidden'), 150));
        }

        function updateFilterDisciplines() {
            const areaVal = document.getElementById('filter_area')?.value;
            const discSelect = document.getElementById('filter_discipline');
            if (!discSelect) return;
            
            discSelect.innerHTML = '<option value="">Todas las disciplinas</option>';
            if (!areaVal) { discSelect.disabled = true; return; }
            discSelect.disabled = false;
            
            const areaKey = Object.keys(categoryTree || {}).find(key => key === areaVal);
            if (areaKey && categoryTree[areaKey]) {
                categoryTree[areaKey].forEach(disc => {
                    const opt = document.createElement('option');
                    opt.value = disc;
                    opt.textContent = disc;
                    discSelect.appendChild(opt);
                });
            }
        }

        function applyFilters() {
            const t = document.getElementById('filter_teacher')?.value || '';
            const a = (document.getElementById('filter_area')?.value || '').toLowerCase();
            const d = (document.getElementById('filter_discipline')?.value || '').toLowerCase();
            let visibleCount = 0;
            
            document.querySelectorAll('.workshop-row').forEach(row => {
                const rowT = row.getAttribute('data-teacher-id');
                const rowA = (row.getAttribute('data-area-name') || '').toLowerCase();
                const rowD = (row.getAttribute('data-discipline-name') || '').toLowerCase();
                const matchT = (t === '') || (t === rowT);
                const matchA = (a === '') || (a === rowA);
                const matchD = (d === '') || (d === rowD);
                
                if (matchT && matchA && matchD) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            const noResults = document.getElementById('no_results_row');
            if (noResults) noResults.style.display = (visibleCount === 0) ? '' : 'none';
        }

        function clearFilters() {
            if (document.getElementById('filter_teacher')) document.getElementById('filter_teacher').value = '';
            if (document.getElementById('filter_area')) document.getElementById('filter_area').value = '';
            updateFilterDisciplines();
            applyFilters();
        }

        function toggleIntroPrice(checkbox, index) {
            const container = document.getElementById(`intro_container_${index}`);
            const input = document.getElementById(`intro_input_${index}`);
            if (!container || !input) return;
            
            if(checkbox.checked) {
                container.classList.remove('hidden');
                input.setAttribute('required', 'required');
            } else {
                container.classList.add('hidden');
                input.removeAttribute('required');
                input.value = '';
            }
        }

        function addPriceRow(count = '', price = '', validityMonths = '1', validityType = 'calendar', allowsRetroactive = true, introPrice = '', isIntroActive = false) {
            const container = document.getElementById('prices_container');
            if (!container) return;

            const idx = priceIndex; // capturamos el índice actual
            const errPrice = window.PriceErrors?.[`prices.${idx}.price`];
            const errIntro = window.PriceErrors?.[`prices.${idx}.introductory_price`];
            const hasErrPrice = Array.isArray(errPrice) && errPrice.length > 0;
            const hasErrIntro = Array.isArray(errIntro) && errIntro.length > 0;

            const retroChecked = allowsRetroactive ? 'checked' : '';
            const calendarChecked = validityType === 'calendar' ? 'checked' : '';
            const rollingChecked = validityType === 'rolling' ? 'checked' : '';

            const html = `
                <div class="p-5 bg-white border ${hasErrPrice || hasErrIntro ? 'border-rose-300 bg-rose-50/30' : 'border-stone-200'} rounded-xl relative group transition-all duration-200 hover:border-stone-300 shadow-sm">
                    {{-- Fila 1: Clases, Precio, Vigencia --}}
                    <div class="flex flex-col sm:flex-row items-end gap-4">
                        <div class="w-full sm:w-1/5">
                            <label class="block text-xs font-bold text-stone-600 mb-1.5">N° Clases</label>
                            <input type="number" name="prices[${priceIndex}][class_count]" value="${count}" placeholder="Ej: 4" min="1" required class="w-full rounded-lg border border-stone-300 p-2.5 text-sm focus:ring-2 focus:ring-red-600 outline-none transition-all duration-200 bg-stone-50 focus:bg-white">
                        </div>
                        <div class="w-full sm:w-1/5">
                            <label class="block text-xs font-bold text-stone-600 mb-1.5">Precio Regular ($)</label>
                            <input type="number" name="prices[${priceIndex}][price]" value="${price}" placeholder="Ej: 25000" min="0" required class="w-full rounded-lg border ${hasErrPrice ? 'border-rose-400 bg-rose-50' : 'border-stone-300'} p-2.5 text-sm focus:ring-2 focus:ring-red-600 outline-none transition-all duration-200 bg-stone-50 focus:bg-white">
                            ${hasErrPrice ? `<p class="text-[11px] text-rose-500 font-bold mt-1">${errPrice[0]}</p>` : ''}
                        </div>
                        <div class="w-full sm:w-1/5">
                            <label class="block text-xs font-bold text-stone-600 mb-1.5">Vigencia del Pack</label>
                            <select name="prices[${priceIndex}][validity_months]" class="w-full rounded-lg border border-stone-300 p-2.5 text-sm focus:ring-2 focus:ring-red-600 outline-none transition-all duration-200 bg-stone-50 focus:bg-white cursor-pointer">
                                <option value="1" ${validityMonths == '1' ? 'selected' : ''}>1 Mes</option>
                                <option value="3" ${validityMonths == '3' ? 'selected' : ''}>3 Meses</option>
                                <option value="6" ${validityMonths == '6' ? 'selected' : ''}>6 Meses</option>
                                <option value="12" ${validityMonths == '12' ? 'selected' : ''}>12 Meses (Anual)</option>
                                <option value="0" ${validityMonths == '0' ? 'selected' : ''}>0 (Sin Límite / Vitalicio)</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-2/5">
                            <label class="block text-xs font-bold text-stone-600 mb-1.5">Modo de Vigencia</label>
                            <div class="flex gap-2">
                                <label class="flex-1 relative flex items-start gap-2 p-2.5 rounded-lg border ${validityType === 'calendar' ? 'border-red-300 bg-red-50 ring-1 ring-red-200' : 'border-stone-200 bg-stone-50 hover:border-stone-300'} cursor-pointer transition-all duration-200">
                                    <input type="radio" name="prices[${priceIndex}][validity_type]" value="calendar" ${calendarChecked} class="w-3.5 h-3.5 text-red-600 border-stone-300 focus:ring-red-600 cursor-pointer mt-0.5 shrink-0">
                                    <div>
                                        <span class="text-xs font-bold text-stone-700">Calendario</span>
                                        <p class="text-[9px] text-stone-400 mt-0.5 leading-tight">Estricto por mes (1 al 31). Ideal para cuotas escolares.</p>
                                    </div>
                                </label>
                                <label class="flex-1 relative flex items-start gap-2 p-2.5 rounded-lg border ${validityType === 'rolling' ? 'border-red-300 bg-red-50 ring-1 ring-red-200' : 'border-stone-200 bg-stone-50 hover:border-stone-300'} cursor-pointer transition-all duration-200">
                                    <input type="radio" name="prices[${priceIndex}][validity_type]" value="rolling" ${rollingChecked} class="w-3.5 h-3.5 text-red-600 border-stone-300 focus:ring-red-600 cursor-pointer mt-0.5 shrink-0">
                                    <div>
                                        <span class="text-xs font-bold text-stone-700">Continuo</span>
                                        <p class="text-[9px] text-stone-400 mt-0.5 leading-tight">Ventana flotante por días. Ideal para fitness o yoga.</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Fila 2: Retroactivo --}}
                    <div class="mt-4 pt-4 border-t border-stone-100">
                        <label class="flex items-start gap-2 cursor-pointer group/check transition-all duration-200">
                            <input type="checkbox" name="prices[${priceIndex}][allows_retroactive]" value="1" ${retroChecked} class="w-4 h-4 text-stone-900 rounded border-stone-300 focus:ring-red-600 transition-all duration-200 mt-0.5 shrink-0">
                            <div>
                                <span class="text-xs font-bold text-stone-700 group-hover/check:text-stone-900">Upgrade Retroactivo</span>
                                <p class="text-[10px] text-stone-400 mt-0.5 leading-tight">Descuenta lo ya pagado dentro de la misma ventana de vigencia.</p>
                            </div>
                        </label>
                    </div>

                    {{-- Fila 3: Precio Introductorio --}}
                    <div class="mt-4 pt-4 border-t border-stone-100 flex flex-col sm:flex-row gap-4 items-center bg-emerald-50/50 p-4 rounded-lg border border-emerald-100/50">
                        <div class="w-full sm:w-1/2 flex items-center gap-2">
                            <input type="checkbox" name="prices[${priceIndex}][is_introductory_active]" value="1" ${isIntroActive ? 'checked' : ''} onchange="toggleIntroPrice(this, ${priceIndex})" class="w-4 h-4 text-emerald-600 rounded border-emerald-300 focus:ring-emerald-600 cursor-pointer transition-all duration-200">
                            <label class="text-xs font-bold text-emerald-800 cursor-pointer transition-all duration-200" onclick="this.previousElementSibling.click()">Ofrecer Promo "Alumno Nuevo"</label>
                        </div>
                        <div class="w-full sm:w-1/2 ${isIntroActive ? '' : 'hidden'}" id="intro_container_${priceIndex}">
                            <div class="flex items-center gap-3">
                                <label class="text-xs font-bold text-emerald-700 whitespace-nowrap">Precio Descuento ($)</label>
                                <input type="number" name="prices[${priceIndex}][introductory_price]" id="intro_input_${priceIndex}" value="${introPrice}" placeholder="Ej: 15000" min="0" class="w-full rounded-lg border ${hasErrIntro ? 'border-rose-400 bg-rose-50' : 'border-emerald-200'} p-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all duration-200 bg-white">
                                ${hasErrIntro ? `<p class="text-[11px] text-rose-500 font-bold mt-1">${errIntro[0]}</p>` : ''}
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="this.parentElement.remove()" class="absolute -top-3 -right-3 bg-white border border-rose-200 text-rose-500 hover:bg-rose-50 hover:text-rose-700 w-8 h-8 rounded-full flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-all duration-200 z-10" title="Eliminar Plan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            if(isIntroActive && document.getElementById(`intro_input_${priceIndex}`)) {
                document.getElementById(`intro_input_${priceIndex}`).setAttribute('required', 'required');
            }
            priceIndex++;
        }

        function addScheduleRow(day = '', time = '', maxStudents = '') {
            const container = document.getElementById('schedules_container');
            if (!container) return;
            const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            
            let options = days.map((name, index) => `<option value="${index}" ${day !== '' && day == index ? 'selected' : ''}>${name}</option>`).join('');

            const html = `
                <div class="flex items-center gap-3 bg-white p-3 rounded-xl border border-stone-200 group relative transition-all hover:border-stone-300 shadow-sm">
                    <div class="w-1/3">
                        <select name="schedules[${scheduleIndex}][day]" required class="w-full rounded-lg border-stone-300 text-sm focus:ring-red-600 cursor-pointer">
                            <option value="">Día...</option>
                            ${options}
                        </select>
                    </div>
                    <div class="w-1/3">
                        <input type="time" name="schedules[${scheduleIndex}][time]" value="${time}" required class="w-full rounded-lg border-stone-300 text-sm focus:ring-red-600 cursor-pointer" onclick="try { this.showPicker(); } catch(e) {}">
                    </div>
                    <div class="w-1/3">
                        <input type="number" name="schedules[${scheduleIndex}][max_students]" value="${maxStudents}" placeholder="Cupos (Opc.)" min="1" class="w-full rounded-lg border-stone-300 text-sm focus:ring-red-600 bg-stone-50 focus:bg-white">
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="absolute -right-2 -top-2 bg-white border border-rose-200 text-rose-500 hover:bg-rose-50 hover:text-rose-700 w-6 h-6 rounded-full flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-all z-10" title="Eliminar Horario">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            scheduleIndex++;
        }

        function toggleDateFields() {
            const isSingle = document.getElementById('type_single')?.checked;
            const containerSchedules = document.getElementById('container_schedules');
            const containerSingle = document.getElementById('container_single_class_details');
            const containerPrices = document.getElementById('container_prices');
            const inputDate = document.getElementById('w_specific_date');
            const inputTime = document.getElementById('w_start_time');
            const inputPrice = document.getElementById('w_single_price');

            if (!containerSchedules || !containerSingle || !containerPrices) return;

            if (isSingle) {
                containerSchedules.classList.add('hidden');
                containerSingle.classList.remove('hidden');
                containerPrices.classList.add('hidden');
                if (inputDate) inputDate.setAttribute('required', 'required');
                if (inputTime) inputTime.setAttribute('required', 'required');
                if (inputPrice) inputPrice.setAttribute('required', 'required');
                containerSchedules.querySelectorAll('input, select').forEach(el => el.disabled = true);
                containerPrices.querySelectorAll('input, select').forEach(el => el.disabled = true);
            } else {
                containerSchedules.classList.remove('hidden');
                containerSingle.classList.add('hidden');
                containerPrices.classList.remove('hidden');
                if (inputDate) inputDate.removeAttribute('required');
                if (inputTime) inputTime.removeAttribute('required');
                if (inputPrice) inputPrice.removeAttribute('required');
                containerSchedules.querySelectorAll('input, select').forEach(el => el.disabled = false);
                containerPrices.querySelectorAll('input, select').forEach(el => el.disabled = false);
            }
        }

        // 🚀 LÓGICA CORREGIDA: Al desmarcar el check, aparece la dirección y el mapa al mismo tiempo
        function toggleLocationFields() {
            const checkbox = document.getElementById('w_use_main_location');
            const container = document.getElementById('container_custom_location');
            if (!checkbox || !container) return;
            
            if (checkbox.checked) {
                container.classList.add('hidden');
            } else {
                container.classList.remove('hidden');
                // Al mostrarse el contenedor, redimensionamos Leaflet inmediatamente para no mostrar cuadros grises
                if (typeof map !== 'undefined' && map && typeof map.invalidateSize === 'function') {
                    setTimeout(() => {
                        try {
                            map.invalidateSize();
                            if (marker && marker.getLatLng()) {
                                map.setView(marker.getLatLng(), map.getZoom() || 15);
                            } else if (studioMasterData && studioMasterData.latitude && studioMasterData.longitude && marker) {
                                const pos = [parseFloat(studioMasterData.latitude), parseFloat(studioMasterData.longitude)];
                                marker.setLatLng(pos);
                                map.setView(pos, 15);
                            }
                        } catch(e) {}
                    }, 150);
                }
            }
        }

        function copyStudioLocation() {
            if (!studioMasterData) return;
            if (document.getElementById('w_address')) document.getElementById('w_address').value = studioMasterData.address || '';
            if (document.getElementById('w_latitude')) document.getElementById('w_latitude').value = studioMasterData.latitude || '';
            if (document.getElementById('w_longitude')) document.getElementById('w_longitude').value = studioMasterData.longitude || '';
            if (document.getElementById('w_city')) document.getElementById('w_city').value = studioMasterData.city || '';
            if (document.getElementById('w_region')) document.getElementById('w_region').value = studioMasterData.region || '';
            if (document.getElementById('w_country')) document.getElementById('w_country').value = studioMasterData.country || '';

            if (studioMasterData.latitude && studioMasterData.longitude && typeof marker !== 'undefined' && marker && typeof map !== 'undefined' && map) {
                const pos = [parseFloat(studioMasterData.latitude), parseFloat(studioMasterData.longitude)];
                try {
                    marker.setLatLng(pos);
                    map.setView(pos, 17);
                } catch(e) {}
            }
        }

        const modalBackdrop = document.getElementById('workshopModal');
        let isMouseDownOnBackdrop = false;

        if (modalBackdrop) {
            modalBackdrop.addEventListener('mousedown', function(e) {
                isMouseDownOnBackdrop = (e.target === modalBackdrop);
            });

            modalBackdrop.addEventListener('mouseup', function(e) {
                if (isMouseDownOnBackdrop && e.target === modalBackdrop) {
                    closeWorkshopModal();
                }
                isMouseDownOnBackdrop = false;
            });
        }

        function openWorkshopModal() {
            const form = document.getElementById('workshopForm');
            if (!form) return;
            
            form.action = "{{ route('workshops.store', ['subdomain' => request()->route('subdomain')]) }}";
            if (document.getElementById('workshopMethod')) document.getElementById('workshopMethod').innerHTML = "";
            if (document.getElementById('modalWorkshopTitle')) document.getElementById('modalWorkshopTitle').innerText = 'Nuevo Taller';
            if (document.getElementById('w_id')) document.getElementById('w_id').value = '';
            
            @if(!$errors->any() || old('workshop_id')) 
                form.reset(); 
                if (document.getElementById('w_image')) document.getElementById('w_image').value = ''; 
                if (document.getElementById('w_teacher_id')) document.getElementById('w_teacher_id').value = ''; 
                if (document.getElementById('type_monthly')) document.getElementById('type_monthly').checked = true;
                
                if (document.getElementById('w_area')) document.getElementById('w_area').value = '';
                if (document.getElementById('w_discipline')) document.getElementById('w_discipline').value = '';
                updateDisciplines();
                if (document.getElementById('w_target_audience')) document.getElementById('w_target_audience').value = 'adults';
                
                if (document.getElementById('w_use_main_location')) document.getElementById('w_use_main_location').checked = true;
                if (document.getElementById('w_address')) document.getElementById('w_address').value = '';
                if (document.getElementById('w_latitude')) document.getElementById('w_latitude').value = '';
                if (document.getElementById('w_longitude')) document.getElementById('w_longitude').value = '';
                if (document.getElementById('w_city')) document.getElementById('w_city').value = '';
                if (document.getElementById('w_region')) document.getElementById('w_region').value = '';
                if (document.getElementById('w_country')) document.getElementById('w_country').value = '';
                if (document.getElementById('w_room_location')) document.getElementById('w_room_location').value = '';
                if (document.getElementById('w_description')) document.getElementById('w_description').value = '';
                if (document.getElementById('w_promo_video_url')) document.getElementById('w_promo_video_url').value = '';
                toggleLocationFields();
                
                if (document.getElementById('w_specific_date')) document.getElementById('w_specific_date').value = '';
                if (document.getElementById('w_start_time')) document.getElementById('w_start_time').value = '';
                if (document.getElementById('w_max_students')) document.getElementById('w_max_students').value = '';
                if (document.getElementById('w_single_price')) document.getElementById('w_single_price').value = '';

                if (document.getElementById('schedules_container')) document.getElementById('schedules_container').innerHTML = '';
                scheduleIndex = 0;
                addScheduleRow();

                if (document.getElementById('prices_container')) document.getElementById('prices_container').innerHTML = '';
                priceIndex = 0;
                addPriceRow();

                toggleDateFields();
            @endif
            
            document.body.style.overflow = 'hidden';
            if (modalBackdrop) modalBackdrop.classList.remove('hidden');
        }

        function openEditWorkshopModal(buttonElement) {
            if (!buttonElement) return;
            const w = JSON.parse(buttonElement.getAttribute('data-workshop') || '{}');
            const form = document.getElementById('workshopForm');
            if (!form || !w.id) return;
            
            let updateUrl = "{{ route('workshops.update', ['subdomain' => request()->route('subdomain'), 'workshop' => ':id']) }}";
            form.action = updateUrl.replace(':id', w.id);
            if (document.getElementById('workshopMethod')) document.getElementById('workshopMethod').innerHTML = '@method("PUT")';
            if (document.getElementById('modalWorkshopTitle')) document.getElementById('modalWorkshopTitle').innerText = 'Editar Taller';
            if (document.getElementById('w_id')) document.getElementById('w_id').value = w.id;
            if (document.getElementById('w_image')) document.getElementById('w_image').value = ''; 
            
            if (document.getElementById('w_name')) document.getElementById('w_name').value = w.name || '';
            if (document.getElementById('w_color')) document.getElementById('w_color').value = w.color || 'blue';
            if (document.getElementById('w_teacher_id')) document.getElementById('w_teacher_id').value = w.teacher_id || '';
            if (document.getElementById('w_target_audience')) document.getElementById('w_target_audience').value = w.target_audience || 'adults';
            if (document.getElementById('w_description')) document.getElementById('w_description').value = w.description || '';
            if (document.getElementById('w_promo_video_url')) document.getElementById('w_promo_video_url').value = w.promo_video_url || '';
            
            if (w.discipline) {
                if (document.getElementById('w_area')) document.getElementById('w_area').value = w.discipline.area ? w.discipline.area.name : '';
                updateDisciplines(); 
                if (document.getElementById('w_discipline')) document.getElementById('w_discipline').value = w.discipline.name;
            } else {
                if (document.getElementById('w_area')) document.getElementById('w_area').value = '';
                updateDisciplines(); 
                if (document.getElementById('w_discipline')) document.getElementById('w_discipline').value = '';
            }

            if (document.getElementById('w_use_main_location')) document.getElementById('w_use_main_location').checked = !!w.use_main_location;
            if (document.getElementById('w_address')) document.getElementById('w_address').value = w.address || '';
            if (document.getElementById('w_latitude')) document.getElementById('w_latitude').value = w.latitude || '';
            if (document.getElementById('w_longitude')) document.getElementById('w_longitude').value = w.longitude || '';
            if (document.getElementById('w_city')) document.getElementById('w_city').value = w.city || '';
            if (document.getElementById('w_region')) document.getElementById('w_region').value = w.region || '';
            if (document.getElementById('w_country')) document.getElementById('w_country').value = w.country || '';
            if (document.getElementById('w_room_location')) document.getElementById('w_room_location').value = w.room_location || '';
            toggleLocationFields();

            if (!w.use_main_location && w.latitude && w.longitude && typeof marker !== 'undefined' && marker && typeof map !== 'undefined' && map) {
                const pos = [parseFloat(w.latitude), parseFloat(w.longitude)];
                try {
                    marker.setLatLng(pos);
                    map.setView(pos, 17);
                } catch(e) {}
            }

            if (document.getElementById('w_specific_date')) document.getElementById('w_specific_date').value = w.specific_date || '';
            if (document.getElementById('w_start_time')) document.getElementById('w_start_time').value = w.start_time || '';
            if (document.getElementById('w_max_students')) document.getElementById('w_max_students').value = w.max_students || '';

            const schedCont = document.getElementById('schedules_container');
            if (schedCont) {
                schedCont.innerHTML = '';
                scheduleIndex = 0;
                if (!w.is_single_class && w.schedules && w.schedules.length > 0) {
                    w.schedules.forEach(sch => {
                        addScheduleRow(sch.day_of_week, sch.start_time, sch.max_students || '');
                    });
                } else {
                    addScheduleRow();
                }
            }

            if (w.is_single_class) {
                if (document.getElementById('type_single')) document.getElementById('type_single').checked = true;
                const singlePrice = w.prices?.find(p => p.class_count == 1);
                if (document.getElementById('w_single_price')) document.getElementById('w_single_price').value = singlePrice ? singlePrice.price : '';
            } else {
                if (document.getElementById('type_monthly')) document.getElementById('type_monthly').checked = true;
            }
            toggleDateFields();

            const pricesCont = document.getElementById('prices_container');
            if (pricesCont) {
                pricesCont.innerHTML = '';
                priceIndex = 0;
                if (w.prices && w.prices.length > 0) {
                    w.prices.forEach(p => addPriceRow(p.class_count, p.price, p.validity_months ?? 1, p.validity_type ?? 'calendar', p.allows_retroactive ?? true, p.introductory_price, p.is_introductory_active));
                } else {
                    addPriceRow();
                }
            }

            document.body.style.overflow = 'hidden';
            if (modalBackdrop) modalBackdrop.classList.remove('hidden');
        }

        function closeWorkshopModal() { 
            document.body.style.overflow = '';
            if (modalBackdrop) modalBackdrop.classList.add('hidden'); 
        }
    
        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                const oldId = "{{ old('workshop_id') }}";
                const form = document.getElementById('workshopForm');
                if (!form) return;

                if (oldId) {
                    let errorUrl = "{{ route('workshops.update', ['subdomain' => request()->route('subdomain'), 'workshop' => ':id']) }}";
                    form.action = errorUrl.replace(':id', oldId);
                    if (document.getElementById('workshopMethod')) document.getElementById('workshopMethod').innerHTML = '@method("PUT")';
                    if (document.getElementById('modalWorkshopTitle')) document.getElementById('modalWorkshopTitle').innerText = 'Editar Taller';
                } else {
                    form.action = "{{ route('workshops.store', ['subdomain' => request()->route('subdomain')]) }}";
                    if (document.getElementById('workshopMethod')) document.getElementById('workshopMethod').innerHTML = "";
                    if (document.getElementById('modalWorkshopTitle')) document.getElementById('modalWorkshopTitle').innerText = 'Nuevo Taller';
                }

                const oldSchedules = @json(old('schedules', []));
                const schedCont = document.getElementById('schedules_container');
                if (schedCont) {
                    schedCont.innerHTML = '';
                    scheduleIndex = 0;
                    if(oldSchedules && Object.keys(oldSchedules).length > 0) {
                        Object.values(oldSchedules).forEach(sch => {
                            addScheduleRow(sch.day, sch.time, sch.max_students || '');
                        });
                    } else {
                        addScheduleRow();
                    }
                }

                const oldPrices = @json(old('prices', []));
                const pricesCont = document.getElementById('prices_container');
                if (pricesCont) {
                    pricesCont.innerHTML = '';
                    priceIndex = 0;
                    if(oldPrices && Object.keys(oldPrices).length > 0) {
                        Object.values(oldPrices).forEach(p => {
                            addPriceRow(
                                p.class_count,
                                p.price,
                                p.validity_months ?? 1,
                                p.validity_type ?? 'calendar',
                                p.allows_retroactive ?? true,
                                p.introductory_price,
                                p.is_introductory_active
                            );
                        });
                    } else {
                        addPriceRow();
                    }
                }

                updateDisciplines();
                toggleDateFields();
                toggleLocationFields();
                if (modalBackdrop) modalBackdrop.classList.remove('hidden');
            });
        @endif

        // ==========================================
        // MAPAS BIDIRECCIONALES: Leaflet + Nominatim
        // ==========================================
        let nominatimTimeout = null;
        const addressInput = document.getElementById('w_address');
        const addressResults = document.getElementById('w_address_results');
        const mapContainer = document.getElementById('map');

        async function searchNominatim(query) {
            if (!query || query.length < 3) return [];
            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=cl&q=${encodeURIComponent(query)}`
                );
                return await res.json();
            } catch (e) {
                return [];
            }
        }

        async function nominatimReverse(lat, lng) {
            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
                );
                return await res.json();
            } catch (e) { return null; }
        }

        function showAddressDropdown(results) {
            if (!addressResults) return;
            addressResults.innerHTML = '';
            if (!results || results.length === 0) {
                addressResults.classList.add('hidden');
                return;
            }
            results.forEach((place) => {
                const li = document.createElement('li');
                li.className = 'px-4 py-2.5 hover:bg-stone-100 cursor-pointer text-stone-700 text-sm transition-colors border-b border-stone-100 last:border-0';
                li.textContent = place.display_name;
                li.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectNominatimPlace(place);
                });
                addressResults.appendChild(li);
            });
            addressResults.classList.remove('hidden');
        }

        function selectNominatimPlace(place) {
            if (!addressInput || !addressResults) return;
            addressInput.value = place.display_name;
            addressResults.classList.add('hidden');

            const lat = parseFloat(place.lat);
            const lng = parseFloat(place.lon);
            const pos = [lat, lng];

            if (document.getElementById('w_latitude')) document.getElementById('w_latitude').value = lat.toFixed(8);
            if (document.getElementById('w_longitude')) document.getElementById('w_longitude').value = lng.toFixed(8);

            const addr = place.address || {};
            if (document.getElementById('w_city')) document.getElementById('w_city').value = addr.city || addr.town || addr.village || addr.municipality || '';
            if (document.getElementById('w_region')) document.getElementById('w_region').value = addr.state || '';
            if (document.getElementById('w_country')) document.getElementById('w_country').value = addr.country || '';

            if (typeof map !== 'undefined' && map && typeof map.invalidateSize === 'function') {
                try {
                    map.invalidateSize();
                    map.setView(pos, 17);
                    if (marker) marker.setLatLng(pos);
                } catch(e) {}
            }
        }

        if (addressInput) {
            addressInput.addEventListener('input', function () {
                clearTimeout(nominatimTimeout);
                const query = this.value.trim();
                if (query.length < 3) {
                    if (addressResults) addressResults.classList.add('hidden');
                    return;
                }
                nominatimTimeout = setTimeout(async () => {
                    const results = await searchNominatim(query);
                    showAddressDropdown(results);
                }, 350);
            });

            addressInput.addEventListener('blur', () => {
                setTimeout(() => addressResults?.classList.add('hidden'), 200);
            });
        }

        function initLeafletMap() {
            if (!mapContainer || typeof L === 'undefined') return;

            const defaultPos = [-41.4693, -72.9424];
            map = L.map(mapContainer, {
                center: defaultPos,
                zoom: 14,
                zoomControl: true,
                attributionControl: true
            });

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const customIcon = L.divIcon({
                className: 'custom-map-pin',
                html: `<div class="w-8 h-8 bg-red-600 border-2 border-white rounded-full shadow-md flex items-center justify-center text-white cursor-pointer hover:scale-110 transition-transform">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                       </div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            marker = L.marker(defaultPos, { icon: customIcon, draggable: true }).addTo(map);

            async function syncCoordsToAddress(pos) {
                if (document.getElementById('w_latitude')) document.getElementById('w_latitude').value = pos.lat.toFixed(8);
                if (document.getElementById('w_longitude')) document.getElementById('w_longitude').value = pos.lng.toFixed(8);
                
                if (addressInput) {
                    addressInput.value = "Obteniendo dirección exacta...";
                    const place = await nominatimReverse(pos.lat, pos.lng);
                    if (place && place.display_name) {
                        addressInput.value = place.display_name;
                        const addr = place.address || {};
                        if (document.getElementById('w_city')) document.getElementById('w_city').value = addr.city || addr.town || addr.village || '';
                        if (document.getElementById('w_region')) document.getElementById('w_region').value = addr.state || '';
                        if (document.getElementById('w_country')) document.getElementById('w_country').value = addr.country || '';
                    } else {
                        addressInput.value = `${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`;
                    }
                }
            }

            marker.on('dragend', function () {
                syncCoordsToAddress(marker.getLatLng());
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                syncCoordsToAddress(e.latlng);
            });
        }

        document.addEventListener('DOMContentLoaded', initLeafletMap);
    </script>
</x-app-layout>
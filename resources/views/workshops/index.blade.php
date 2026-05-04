<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />
        <div class="mt-8">
            <x-studio-header title="Configuración de Clases" :breadcrumbs="[['name' => 'Talleres']]">
                <x-slot name="actions">
                    <button onclick="openWorkshopModal()" class="bg-zinc-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-zinc-800 focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95 whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nuevo Taller
                    </button>
                </x-slot>
            </x-studio-header>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif
        
        {{-- FILTROS EN TIEMPO REAL --}}
        <div class="mb-6 bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
            <h4 class="text-sm font-bold text-zinc-700 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filtros de Búsqueda
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <select id="filter_teacher" onchange="applyFilters()" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                        <option value="">Todos los profesores</option>
                        <option value="unassigned">Sin profesor asignado</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select id="filter_area" onchange="updateFilterDisciplines(); applyFilters()" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                        <option value="">Todas las áreas</option>
                        @foreach($existingAreas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select id="filter_discipline" onchange="applyFilters()" disabled class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-zinc-900 outline-none disabled:bg-zinc-100 disabled:text-zinc-400 cursor-pointer">
                        <option value="">Todas las disciplinas</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <button onclick="clearFilters()" class="text-sm text-zinc-500 hover:text-zinc-900 font-medium transition-colors border border-transparent hover:border-zinc-200 px-3 py-2 rounded-lg">
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLA DE TALLERES --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200" id="workshops_table">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Taller / Profesor</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Área y Disciplina</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Horario / Fecha</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-zinc-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($workshops as $workshop)
                            @php 
                                $diasStr = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']; 
                                $borderColor = match($workshop->color) {
                                    'emerald' => 'border-emerald-500', 'rose' => 'border-rose-500', 'purple' => 'border-purple-500',
                                    'amber' => 'border-amber-500', 'indigo' => 'border-indigo-500', 'teal' => 'border-teal-500',
                                    'cyan' => 'border-cyan-500', 'fuchsia' => 'border-fuchsia-500', 'slate' => 'border-slate-500',
                                    default => 'border-blue-500',
                                };
                            @endphp
                            
                            <tr class="workshop-row hover:bg-zinc-50/80 transition-colors duration-200 group"
                                data-teacher-id="{{ $workshop->teacher_id ?? 'unassigned' }}"
                                data-area-name="{{ $workshop->discipline->area->name ?? '' }}"
                                data-discipline-name="{{ $workshop->discipline->name ?? '' }}">
                                
                                {{-- Columna 1: Taller y Profesor --}}
                                <td class="px-6 py-4 border-l-4 {{ $borderColor }}">
                                    <div class="font-bold text-zinc-900 flex flex-wrap items-center gap-2 text-sm">
                                        {{ $workshop->name }}
                                        @if($workshop->is_single_class) <span class="bg-zinc-100 text-zinc-600 border border-zinc-200 text-[10px] px-2 py-0.5 rounded uppercase tracking-widest font-black">Clase Única</span> @endif
                                        @if($workshop->max_students) <span class="bg-zinc-50 text-zinc-500 border border-zinc-200 text-[10px] px-2 py-0.5 rounded uppercase tracking-widest font-bold flex items-center gap-1" title="Cupo Máximo"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> Max {{ $workshop->max_students }}</span> @endif
                                    </div>
                                    <div class="text-xs text-zinc-500 mt-1.5 flex items-center gap-1.5 font-medium">
                                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $workshop->teacher ? $workshop->teacher->name : 'Sin profesor asignado' }} 
                                    </div>
                                </td>

                                {{-- Columna 2: Área y Disciplina --}}
                                <td class="px-6 py-4">
                                    @if($workshop->discipline)
                                        <div class="inline-flex flex-col items-start">
                                            <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 mb-1">
                                                {{ $workshop->discipline->area->name ?? 'Sin Área' }}
                                            </span>
                                            <span class="text-sm font-bold text-zinc-700">
                                                {{ $workshop->discipline->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400 italic">No configurado</span>
                                    @endif
                                </td>

                                {{-- Columna 3: Horario --}}
                                <td class="px-6 py-4 text-sm font-bold text-zinc-700">
                                    @if($workshop->is_single_class && $workshop->specific_date)
                                        <span class="text-zinc-900">{{ \Carbon\Carbon::parse($workshop->specific_date)->translatedFormat('d \d\e F') }}</span> 
                                    @else
                                        @if(is_array($workshop->repeat_days))
                                            {{ implode(', ', array_map(fn($d) => $diasStr[$d], $workshop->repeat_days)) }}
                                        @else
                                            <span class="text-rose-500 italic">Sin días</span>
                                        @endif
                                    @endif
                                    <div class="text-xs text-zinc-500 font-medium mt-0.5">
                                        a las <span class="text-zinc-900 font-bold">{{ \Carbon\Carbon::parse($workshop->start_time)->format('H:i') }}</span>
                                    </div>
                                </td>

                                {{-- Columna 4: Acciones --}}
                                <td class="px-6 py-4 text-right space-x-3 flex justify-end items-center">
                                    <button type="button" data-workshop="{{ json_encode($workshop) }}" onclick="openEditWorkshopModal(this)" class="text-sm font-bold text-zinc-500 hover:text-zinc-900 bg-zinc-50 border border-zinc-200 px-3 py-1.5 rounded-lg transition-colors duration-200">
                                        Editar
                                    </button>
                                    <form action="{{ route('workshops.destroy', ['subdomain' => request()->route('subdomain'), 'workshop' => $workshop->id]) }}" method="POST" class="inline m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Eliminar este taller permanentemente?')" class="text-sm font-bold text-rose-500 hover:text-white bg-white hover:bg-rose-500 border border-rose-200 hover:border-rose-500 px-3 py-1.5 rounded-lg transition-colors duration-200">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-sm font-bold text-zinc-400">No hay talleres configurados</td></tr>
                        @endforelse
                        <tr id="no_results_row" style="display: none;"><td colspan="4" class="px-6 py-12 text-center text-sm font-bold text-zinc-400">No hay talleres que coincidan con los filtros</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL DEL TALLER --}}
    <div id="workshopModal" onclick="if(event.target === this) closeWorkshopModal()" class="fixed inset-0 z-50 hidden flex items-start justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto transition-opacity custom-scrollbar">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-3xl w-full shadow-xl border border-zinc-100 my-auto transform transition-all">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-zinc-900 tracking-tight" id="modalWorkshopTitle">Configurar Taller</h3>
                <button type="button" onclick="closeWorkshopModal()" class="text-zinc-400 hover:text-zinc-600 bg-zinc-50 hover:bg-zinc-100 p-2 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Hay errores en el formulario. Por favor revisa los campos en rojo.</span>
                </div>
            @endif

            <form id="workshopForm" method="POST">
                @csrf
                <div id="workshopMethod"></div>
                <input type="hidden" name="workshop_id" id="w_id" value="{{ old('workshop_id') }}">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    {{-- Nombre --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre del Taller *</label>
                        <input type="text" name="name" id="w_name" value="{{ old('name') }}" placeholder="Ej: Telas Principiante" 
                            class="w-full rounded-xl border {{ $errors->has('name') ? 'border-rose-300 ring-1 ring-rose-300' : 'border-zinc-300' }} px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all" required>
                        @error('name') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Área y Disciplina --}}
                    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Área General *</label>
                            <div class="relative">
                                <input type="text" name="area" id="w_area" value="{{ old('area') }}" placeholder="Ej: Circo, Baile..." required autocomplete="off"
                                    class="w-full rounded-xl border {{ $errors->has('area') ? 'border-rose-300 ring-1 ring-rose-300' : 'border-zinc-300' }} py-3 pl-4 pr-10 text-sm focus:ring-2 focus:ring-zinc-900 outline-none bg-white">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-zinc-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <ul id="custom_area_list" class="absolute z-50 w-full mt-1 bg-white border border-zinc-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden py-1 custom-scrollbar"></ul>
                            @error('area') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Disciplina Específica *</label>
                            <div class="relative">
                                <input type="text" name="discipline" id="w_discipline" value="{{ old('discipline') }}" placeholder="Primero selecciona un Área..." required autocomplete="off"
                                    class="w-full rounded-xl border {{ $errors->has('discipline') ? 'border-rose-300 ring-1 ring-rose-300' : 'border-zinc-300' }} py-3 pl-4 pr-10 text-sm focus:ring-2 focus:ring-zinc-900 outline-none disabled:bg-zinc-100 disabled:text-zinc-400 disabled:cursor-not-allowed bg-white">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-zinc-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            <ul id="custom_discipline_list" class="absolute z-50 w-full mt-1 bg-white border border-zinc-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden py-1 custom-scrollbar"></ul>
                            @error('discipline') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Público Objetivo --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Público Objetivo (Edad aprox) *</label>
                        <select name="target_audience" id="w_target_audience" required class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 transition-all outline-none cursor-pointer">
                            <option value="all" {{ old('target_audience') == 'all' ? 'selected' : '' }}>Todas las edades</option>
                            <option value="kids" {{ old('target_audience') == 'kids' ? 'selected' : '' }}>Niñas/os (hasta 12 años)</option>
                            <option value="teens" {{ old('target_audience') == 'teens' ? 'selected' : '' }}>Adolescentes (13 - 17 años)</option>
                            <option value="adults" {{ old('target_audience', 'adults') == 'adults' ? 'selected' : '' }}>Adultos (+18 años)</option>
                        </select>
                        @error('target_audience') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- SELECTOR DE PROFESOR --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Entrenador(a) Principal</label>
                        <select name="teacher_id" id="w_teacher_id" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                            <option value="">-- Sin profesor asignado --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- UBICACIÓN --}}
                    <div class="col-span-1 md:col-span-2 bg-zinc-50 p-5 rounded-xl border border-zinc-200 mt-2">
                        <label class="block text-[11px] font-black text-zinc-400 mb-3 uppercase tracking-widest">Lugar de Clases</label>
                        
                        <label class="flex items-center gap-2 cursor-pointer group mb-4">
                            <input type="hidden" name="use_main_location" value="0">
                            <input type="checkbox" name="use_main_location" id="w_use_main_location" value="1" onchange="toggleLocationFields()" class="w-4 h-4 text-zinc-900 border-zinc-300 rounded focus:ring-zinc-900" {{ old('use_main_location', '1') == '1' ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-zinc-700 group-hover:text-zinc-900">Usar la sede principal del Estudio</span>
                        </label>

                        <div id="container_custom_location" class="hidden pt-4 border-t border-zinc-200/60">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Dirección Específica <span class="text-zinc-400 font-normal">(Para el Mapa)</span></label>
                            <input type="text" name="address" id="w_address" value="{{ old('address') }}" placeholder="Empieza a escribir la dirección..." 
                                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none bg-white">
                            
                            <input type="hidden" name="latitude" id="w_latitude" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="w_longitude" value="{{ old('longitude') }}">
                            <input type="hidden" name="city" id="w_city" value="{{ old('city') }}">
                            <input type="hidden" name="region" id="w_region" value="{{ old('region') }}">
                            <input type="hidden" name="country" id="w_country" value="{{ old('country') }}">
                            
                            <div id="map" class="w-full h-64 mt-3 rounded-xl border border-zinc-300 shadow-inner bg-zinc-100 hidden relative z-0"></div>
                        </div>

                        <input type="text" name="room_location" id="w_room_location" value="{{ old('room_location') }}" placeholder="Detalle extra (Ej: Sala 2, Piso 4...)" 
                               class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none mt-4 bg-white shadow-sm">
                    </div>

                    {{-- Color --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Color (Etiqueta de Calendario) *</label>
                        <select name="color" id="w_color" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer" required>
                            @foreach(['blue'=>'Azul Intenso','emerald'=>'Verde Esmeralda','teal'=>'Turquesa','cyan'=>'Celeste','indigo'=>'Índigo','purple'=>'Púrpura','fuchsia'=>'Fucsia','rose'=>'Rosa / Rojo','amber'=>'Ámbar / Naranja','slate'=>'Gris Oscuro'] as $val => $label)
                                <option value="{{ $val }}" {{ old('color') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('color') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- TIPO DE CLASE --}}
                    <div class="col-span-1 md:col-span-2 bg-zinc-50 p-5 rounded-xl border border-zinc-200 mt-2">
                        <label class="block text-[11px] font-black text-zinc-400 mb-3 uppercase tracking-widest">Frecuencia del Taller</label>
                        <div class="flex flex-col sm:flex-row gap-6 mb-4">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="is_single_class" value="0" id="type_monthly" onchange="toggleDateFields()" class="w-4 h-4 text-zinc-900 border-zinc-300 focus:ring-zinc-900" {{ old('is_single_class', '0') == '0' ? 'checked' : '' }}>
                                <span class="text-sm font-bold text-zinc-700 group-hover:text-zinc-900">Mensual (Repetitivo)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="is_single_class" value="1" id="type_single" onchange="toggleDateFields()" class="w-4 h-4 text-zinc-900 border-zinc-300 focus:ring-zinc-900" {{ old('is_single_class') == '1' ? 'checked' : '' }}>
                                <span class="text-sm font-bold text-zinc-700 group-hover:text-zinc-900">Clase Única (Masterclass)</span>
                            </label>
                        </div>

                        <div id="container_repeat_day">
                            <label class="block text-sm font-bold text-zinc-700 mb-2">Selecciona los días que se dicta *</label>
                            <div class="flex flex-wrap gap-3">
                                @foreach(['1'=>'Lun', '2'=>'Mar', '3'=>'Mié', '4'=>'Jue', '5'=>'Vie', '6'=>'Sáb', '0'=>'Dom'] as $val => $label)
                                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border {{ $errors->has('repeat_days') ? 'border-rose-300' : 'border-zinc-200' }} cursor-pointer hover:border-zinc-400 hover:shadow-sm transition-all">
                                        <input type="checkbox" name="repeat_days[]" value="{{ $val }}" class="day-checkbox w-4 h-4 text-zinc-900 border-zinc-300 rounded focus:ring-zinc-900" {{ in_array($val, old('repeat_days', [])) ? 'checked' : '' }}>
                                        <span class="text-sm font-bold text-zinc-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('repeat_days') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div id="container_specific_date" class="hidden">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Fecha Exacta *</label>
                            <input type="date" name="specific_date" id="w_specific_date" value="{{ old('specific_date') }}" onclick="try { this.showPicker(); } catch(e) {}"
                                class="w-full md:w-1/2 rounded-xl border {{ $errors->has('specific_date') ? 'border-rose-300' : 'border-zinc-300' }} px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer">
                            @error('specific_date') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    {{-- Hora --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Hora de Inicio *</label>
                        <input type="time" name="start_time" id="w_start_time" value="{{ old('start_time') }}" onclick="try { this.showPicker(); } catch(e) {}"
                            class="w-full rounded-xl border {{ $errors->has('start_time') ? 'border-rose-300' : 'border-zinc-300' }} px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none cursor-pointer" required>
                        @error('start_time') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Cupo Máximo --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Cupo Máximo <span class="text-zinc-400 font-normal">(Opcional)</span></label>
                        <input type="number" name="max_students" id="w_max_students" value="{{ old('max_students') }}" placeholder="Ej: 15" min="1"
                            class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 outline-none">
                    </div>
                    
                    {{-- PLANES DE PRECIOS --}}
                    <div class="col-span-1 md:col-span-2 border-t border-zinc-200 pt-5 mt-2">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h4 class="text-sm font-bold text-zinc-900">Planes de Precios</h4>
                                <p class="text-xs text-zinc-500">Configura cuánto cuestan los paquetes de clases para este taller.</p>
                            </div>
                            <button type="button" onclick="addPriceRow()" class="text-xs font-bold bg-zinc-100 text-zinc-900 px-3 py-2 rounded-lg hover:bg-zinc-200 transition-colors shadow-sm">
                                + Agregar Plan
                            </button>
                        </div>
                        <div id="prices_container" class="space-y-3"></div>
                        @error('prices') <p class="text-xs text-rose-500 font-bold mt-1">Error en la configuración de precios.</p> @enderror
                    </div>

                    {{-- Info Pago --}}
                    <div class="col-span-1 md:col-span-2 mt-2">
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Datos de Transferencia (Opcional)</label>
                        <textarea name="payment_info" id="w_payment" rows="2" placeholder="Ej: Banco Estado, Cuenta RUT..." 
                                class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 transition-all outline-none">{{ old('payment_info') }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex gap-3 pt-4 border-t border-zinc-100">
                    <button type="button" onclick="closeWorkshopModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition-colors text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition-all active:scale-95 text-sm">Guardar Taller</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e4e4e7; border-radius: 20px; }
    </style>

    <script>
        const categoryTree = @json($categoryTree ?? []);
        let priceIndex = 0;
        let map;
        let marker;

        const areaInput = document.getElementById('w_area');
        const areaList = document.getElementById('custom_area_list');
        const disciplineInput = document.getElementById('w_discipline');
        const disciplineList = document.getElementById('custom_discipline_list');

        function renderDropdown(input, listEl, dataArray, callback) {
            listEl.innerHTML = '';
            listEl.classList.remove('hidden');
            
            const filter = input.value.toLowerCase().trim();
            const filtered = dataArray.filter(item => item.toLowerCase().includes(filter));

            if (filtered.length === 0) {
                const li = document.createElement('li');
                li.className = 'px-4 py-2.5 text-zinc-400 italic text-sm cursor-default';
                li.textContent = filter ? `Se creará: "${input.value}"` : 'Sin opciones';
                listEl.appendChild(li);
                return;
            }

            filtered.forEach(item => {
                const li = document.createElement('li');
                li.className = 'px-4 py-2.5 hover:bg-zinc-100 cursor-pointer text-zinc-700 text-sm transition-colors';
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

        areaInput.addEventListener('focus', () => renderDropdown(areaInput, areaList, Object.keys(categoryTree), updateDisciplines));
        areaInput.addEventListener('input', () => {
            renderDropdown(areaInput, areaList, Object.keys(categoryTree), updateDisciplines);
            updateDisciplines();
        });
        areaInput.addEventListener('blur', () => setTimeout(() => areaList.classList.add('hidden'), 150));

        disciplineInput.addEventListener('focus', () => {
            const areaKey = Object.keys(categoryTree).find(key => key.toLowerCase() === areaInput.value.trim().toLowerCase());
            const disciplines = areaKey ? categoryTree[areaKey] : [];
            renderDropdown(disciplineInput, disciplineList, disciplines, null);
        });
        disciplineInput.addEventListener('input', () => {
            const areaKey = Object.keys(categoryTree).find(key => key.toLowerCase() === areaInput.value.trim().toLowerCase());
            const disciplines = areaKey ? categoryTree[areaKey] : [];
            renderDropdown(disciplineInput, disciplineList, disciplines, null);
        });
        disciplineInput.addEventListener('blur', () => setTimeout(() => disciplineList.classList.add('hidden'), 150));

        function updateFilterDisciplines() {
            const areaVal = document.getElementById('filter_area').value;
            const discSelect = document.getElementById('filter_discipline');
            discSelect.innerHTML = '<option value="">Todas las disciplinas</option>';
            if (!areaVal) { discSelect.disabled = true; return; }
            discSelect.disabled = false;
            
            const areaKey = Object.keys(categoryTree).find(key => key === areaVal);
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
            const t = document.getElementById('filter_teacher').value;
            const a = document.getElementById('filter_area').value.toLowerCase();
            const d = document.getElementById('filter_discipline').value.toLowerCase();
            let visibleCount = 0;
            
            document.querySelectorAll('.workshop-row').forEach(row => {
                const rowT = row.getAttribute('data-teacher-id');
                const rowA = row.getAttribute('data-area-name').toLowerCase();
                const rowD = row.getAttribute('data-discipline-name').toLowerCase();
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
            document.getElementById('no_results_row').style.display = (visibleCount === 0) ? '' : 'none';
        }

        function clearFilters() {
            document.getElementById('filter_teacher').value = '';
            document.getElementById('filter_area').value = '';
            updateFilterDisciplines();
            applyFilters();
        }

        function addPriceRow(count = '', price = '', isMonthly = false) {
            const container = document.getElementById('prices_container');
            const html = `
                <div class="flex flex-col sm:flex-row items-end gap-3 p-4 bg-zinc-50 border border-zinc-200 rounded-xl relative group transition-all hover:border-zinc-300">
                    <div class="w-full sm:w-1/4">
                        <label class="block text-xs font-bold text-zinc-600 mb-1.5">N° Clases</label>
                        <input type="number" name="prices[${priceIndex}][class_count]" value="${count}" placeholder="Ej: 4" min="1" required class="w-full rounded-lg border border-zinc-300 p-2.5 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all">
                    </div>
                    <div class="w-full sm:w-1/3">
                        <label class="block text-xs font-bold text-zinc-600 mb-1.5">Precio Total ($)</label>
                        <input type="number" name="prices[${priceIndex}][price]" value="${price}" placeholder="Ej: 25000" min="0" required class="w-full rounded-lg border border-zinc-300 p-2.5 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all">
                    </div>
                    <div class="w-full sm:w-1/3 pb-3">
                        <label class="flex items-center gap-2 cursor-pointer group/check">
                            <input type="checkbox" name="prices[${priceIndex}][is_monthly]" value="1" ${isMonthly ? 'checked' : ''} class="w-4 h-4 text-zinc-900 rounded border-zinc-300 focus:ring-zinc-900 transition-all">
                            <span class="text-xs font-bold text-zinc-700 group-hover/check:text-zinc-900">Regla Mensualidad</span>
                        </label>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="absolute -top-3 -right-3 bg-white border border-rose-200 text-rose-500 hover:bg-rose-50 hover:text-rose-700 w-7 h-7 rounded-full flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            priceIndex++;
        }

        function toggleDateFields() {
            const isSingle = document.getElementById('type_single').checked;
            const containerDay = document.getElementById('container_repeat_day');
            const containerDate = document.getElementById('container_specific_date');
            const inputDate = document.getElementById('w_specific_date');

            if (isSingle) {
                containerDay.classList.add('hidden');
                containerDate.classList.remove('hidden');
                inputDate.setAttribute('required', 'required');
            } else {
                containerDay.classList.remove('hidden');
                containerDate.classList.add('hidden');
                inputDate.removeAttribute('required');
            }
        }

        function toggleLocationFields() {
            const checkbox = document.getElementById('w_use_main_location');
            const container = document.getElementById('container_custom_location');
            
            if (checkbox.checked) {
                container.classList.add('hidden');
            } else {
                container.classList.remove('hidden');
                if (typeof map !== 'undefined' && map) {
                    setTimeout(() => {
                        google.maps.event.trigger(map, 'resize');
                        if (marker && marker.getPosition()) {
                            map.setCenter(marker.getPosition());
                        }
                    }, 50);
                }
            }
        }

        function openWorkshopModal() {
            document.getElementById('workshopForm').action = "{{ route('workshops.store', ['subdomain' => request()->route('subdomain')]) }}";
            document.getElementById('workshopMethod').innerHTML = "";
            document.getElementById('modalWorkshopTitle').innerText = 'Nuevo Taller';
            document.getElementById('w_id').value = '';
            
            // Solo resetear completamente si no venimos de un error de "Nuevo Taller"
            @if(!$errors->any() || old('workshop_id')) 
                document.getElementById('workshopForm').reset(); 
                document.getElementById('w_max_students').value = ''; 
                document.getElementById('w_teacher_id').value = ''; 
                document.getElementById('type_monthly').checked = true;
                
                document.getElementById('w_area').value = '';
                document.getElementById('w_discipline').value = '';
                updateDisciplines();
                document.getElementById('w_target_audience').value = 'adults';
                
                document.getElementById('w_use_main_location').checked = true;
                document.getElementById('w_address').value = '';
                document.getElementById('w_latitude').value = '';
                document.getElementById('w_longitude').value = '';
                document.getElementById('w_city').value = '';
                document.getElementById('w_region').value = '';
                document.getElementById('w_country').value = '';
                document.getElementById('w_room_location').value = '';
                toggleLocationFields();
                
                document.querySelectorAll('.day-checkbox').forEach(cb => cb.checked = false);
                document.getElementById('prices_container').innerHTML = '';
                priceIndex = 0;
                addPriceRow(); 
                toggleDateFields();
            @endif
            
            document.body.style.overflow = 'hidden';
            document.getElementById('workshopModal').classList.remove('hidden');
        }

        function openEditWorkshopModal(buttonElement) {
            const w = JSON.parse(buttonElement.getAttribute('data-workshop'));
            
            document.getElementById('workshopForm').action = `/workshops/${w.id}`;
            document.getElementById('workshopMethod').innerHTML = '@method("PUT")';
            document.getElementById('modalWorkshopTitle').innerText = 'Editar Taller';
            document.getElementById('w_id').value = w.id;
            
            document.getElementById('w_name').value = w.name;
            document.getElementById('w_color').value = w.color || 'blue';
            document.getElementById('w_start_time').value = w.start_time;
            document.getElementById('w_max_students').value = w.max_students || '';
            document.getElementById('w_payment').value = w.payment_info || '';
            document.getElementById('w_teacher_id').value = w.teacher_id || '';
            document.getElementById('w_target_audience').value = w.target_audience || 'adults';
            
            if (w.discipline) {
                document.getElementById('w_area').value = w.discipline.area ? w.discipline.area.name : '';
                updateDisciplines(); 
                document.getElementById('w_discipline').value = w.discipline.name;
            } else {
                document.getElementById('w_area').value = '';
                updateDisciplines(); 
                document.getElementById('w_discipline').value = '';
            }

            document.getElementById('w_use_main_location').checked = !!w.use_main_location;
            document.getElementById('w_address').value = w.address || '';
            document.getElementById('w_latitude').value = w.latitude || '';
            document.getElementById('w_longitude').value = w.longitude || '';
            document.getElementById('w_city').value = w.city || '';
            document.getElementById('w_region').value = w.region || '';
            document.getElementById('w_country').value = w.country || '';
            document.getElementById('w_room_location').value = w.room_location || '';
            toggleLocationFields();

            if (!w.use_main_location && w.latitude && w.longitude && marker && map) {
                const pos = { lat: parseFloat(w.latitude), lng: parseFloat(w.longitude) };
                marker.setPosition(pos);
                map.setCenter(pos);
                document.getElementById('map').classList.remove('hidden');
                setTimeout(() => google.maps.event.trigger(map, 'resize'), 50);
            }
            
            document.querySelectorAll('.day-checkbox').forEach(cb => cb.checked = false);
            if (w.repeat_days && Array.isArray(w.repeat_days)) {
                w.repeat_days.forEach(day => {
                    const cb = document.querySelector(`.day-checkbox[value="${day}"]`);
                    if(cb) cb.checked = true;
                });
            }

            if (w.is_single_class) {
                document.getElementById('type_single').checked = true;
                document.getElementById('w_specific_date').value = w.specific_date;
            } else {
                document.getElementById('type_monthly').checked = true;
            }
            toggleDateFields();

            document.getElementById('prices_container').innerHTML = '';
            priceIndex = 0;
            if (w.prices && w.prices.length > 0) {
                w.prices.forEach(p => addPriceRow(p.class_count, p.price, p.is_monthly));
            } else {
                addPriceRow();
            }
            
            document.body.style.overflow = 'hidden';
            document.getElementById('workshopModal').classList.remove('hidden');
        }

        function closeWorkshopModal() { 
            document.body.style.overflow = '';
            document.getElementById('workshopModal').classList.add('hidden'); 
        }

        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                const oldId = "{{ old('workshop_id') }}";
                if (oldId) {
                    document.getElementById('workshopForm').action = `/{{ request()->route('subdomain') }}/workshops/${oldId}`;
                    document.getElementById('workshopMethod').innerHTML = '@method("PUT")';
                    document.getElementById('modalWorkshopTitle').innerText = 'Editar Taller';
                } else {
                    document.getElementById('workshopForm').action = "{{ route('workshops.store', ['subdomain' => request()->route('subdomain')]) }}";
                    document.getElementById('workshopMethod').innerHTML = "";
                    document.getElementById('modalWorkshopTitle').innerText = 'Nuevo Taller';
                }

                // Restaurar Precios enviados si hay error
                const oldPrices = @json(old('prices', []));
                document.getElementById('prices_container').innerHTML = '';
                priceIndex = 0;
                if(oldPrices && Object.keys(oldPrices).length > 0) {
                    Object.values(oldPrices).forEach(p => addPriceRow(p.class_count, p.price, p.is_monthly));
                } else {
                    addPriceRow();
                }

                updateDisciplines();
                toggleDateFields();
                toggleLocationFields();
                document.getElementById('workshopModal').classList.remove('hidden');
            });
        @endif

        function initMapAutocomplete() {
            const addressInput = document.getElementById('w_address');
            const mapContainer = document.getElementById('map');
            if(!addressInput) return;

            const defaultPos = { lat: -33.4489, lng: -70.6693 };
            map = new google.maps.Map(mapContainer, {
                center: defaultPos, zoom: 15, mapTypeControl: false, streetViewControl: false, fullscreenControl: true
            });
            marker = new google.maps.Marker({
                map: map, position: defaultPos, draggable: true, animation: google.maps.Animation.DROP
            });

            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                componentRestrictions: { country: "cl" },
                fields: ["formatted_address", "geometry", "name", "address_components"],
                types: ["geocode", "establishment"]
            });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) { mapContainer.classList.add('hidden'); return; }

                mapContainer.classList.remove('hidden');
                google.maps.event.trigger(map, 'resize');
                map.setCenter(place.geometry.location);
                map.setZoom(17);
                marker.setPosition(place.geometry.location);

                document.getElementById('w_latitude').value = place.geometry.location.lat();
                document.getElementById('w_longitude').value = place.geometry.location.lng();

                let city = '', region = '', country = '';
                if(place.address_components) {
                    for (const component of place.address_components) {
                        const type = component.types[0];
                        if(type === "locality") city = component.long_name;
                        if(type === "administrative_area_level_1") region = component.long_name;
                        if(type === "country") country = component.long_name;
                    }
                }
                document.getElementById('w_city').value = city;
                document.getElementById('w_region').value = region;
                document.getElementById('w_country').value = country;
                addressInput.value = place.formatted_address;
            });

            marker.addListener('dragend', function() {
                const newPos = marker.getPosition();
                document.getElementById('w_latitude').value = newPos.lat();
                document.getElementById('w_longitude').value = newPos.lng();
            });
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.key') }}&libraries=places&callback=initMapAutocomplete"></script>
</x-app-layout>
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">
    
    <div class="flex space-x-4 mb-8 border-b border-gray-200">
        <button class="py-2 px-6 font-bold text-blue-600 border-b-2 border-blue-600">Talleres (Configuración)</button>
        <a href="{{ route('entrenamientos.index') }}" class="py-2 px-6 font-medium text-gray-500 hover:text-blue-600 transition">Entrenamientos (Meses)</a>
    </div>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tus Talleres</h1>
        <button onclick="openWorkshopModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition transform hover:scale-105">
            + Nuevo Taller
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg font-bold shadow-sm border-l-4 border-green-500">
            {{ session('success') }}
        </div>
    @endif

    {{-- TABLA DE TALLERES --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 uppercase text-[10px] font-black text-gray-400 tracking-tighter">
                <tr>
                    <th class="px-6 py-4 text-left">Taller / Profesor</th>
                    <th class="px-6 py-4 text-left">Horario / Fecha</th>
                    <th class="px-6 py-4 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 italic">
                @forelse($workshops as $workshop)
                    @php 
                        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']; 
                        
                        $borderColor = match($workshop->color) {
                            'emerald' => 'border-emerald-500',
                            'rose' => 'border-rose-500',
                            'purple' => 'border-purple-500',
                            'amber' => 'border-amber-500',
                            'indigo' => 'border-indigo-500',
                            'teal' => 'border-teal-500',
                            'cyan' => 'border-cyan-500',
                            'fuchsia' => 'border-fuchsia-500',
                            'slate' => 'border-slate-500',
                            default => 'border-blue-500',
                        };
                    @endphp
                    
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 border-l-8 {{ $borderColor }}">
                            <div class="font-bold text-gray-900 flex items-center gap-2">
                                {{ $workshop->name }}
                                @if($workshop->is_single_class)
                                    <span class="bg-indigo-100 text-indigo-700 text-[9px] px-2 py-0.5 rounded-full font-black not-italic uppercase">Clase Única</span>
                                @endif
                            </div>
                            <div class="text-[10px] uppercase font-bold text-gray-500 mt-1">
                                {{ $workshop->trainer }} 
                                @if($workshop->trainer_phone)
                                    <span class="text-blue-500 ml-1">({{ $workshop->trainer_phone }})</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-700">
                            @if($workshop->is_single_class && $workshop->specific_date)
                                <span class="font-bold text-indigo-600">{{ \Carbon\Carbon::parse($workshop->specific_date)->translatedFormat('d \d\e F') }}</span> 
                            @else
                                {{ $dias[$workshop->repeat_day] }} 
                            @endif
                            a las {{ \Carbon\Carbon::parse($workshop->start_time)->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <button onclick='openEditWorkshopModal({!! json_encode($workshop) !!})' class="text-indigo-600 font-bold text-sm hover:underline">Editar</button>
                            <form action="{{ route('workshops.destroy', $workshop->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('¿Eliminar este taller?')" class="text-red-400 font-bold text-sm hover:text-red-600">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-10 text-center italic text-gray-400">Sin talleres creados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL PARA CREAR/EDITAR TALLERES --}}
<div id="workshopModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-3xl p-8 max-w-2xl w-full shadow-2xl border border-gray-200 my-8">
        <form id="workshopForm" method="POST">
            @csrf
            <div id="workshopMethod"></div>
            <h3 class="text-2xl font-bold mb-6 text-gray-900" id="modalWorkshopTitle">Configurar Taller</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {{-- Nombre --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nombre del Taller</label>
                    <input type="text" name="name" id="w_name" value="{{ old('name') }}" placeholder="Ej: Telas Principiante" 
                           class="w-full rounded-xl border-2 p-3 focus:ring-0 transition {{ $errors->has('name') ? 'border-red-500 bg-red-50' : 'border-gray-400 focus:border-blue-500' }}" required>
                    @error('name') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Entrenador --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Entrenador(a)</label>
                    <input type="text" name="trainer" id="w_trainer" value="{{ old('trainer') }}" placeholder="Nombre profesor(a)" 
                           class="w-full rounded-xl border-2 p-3 focus:ring-0 transition {{ $errors->has('trainer') ? 'border-red-500 bg-red-50' : 'border-gray-400 focus:border-blue-500' }}" required>
                </div>

                {{-- Teléfono Entrenador --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Teléfono Profe</label>
                    <input type="text" name="trainer_phone" id="w_phone" value="{{ old('trainer_phone') }}" placeholder="+56 9..." 
                           class="w-full rounded-xl border-2 border-gray-400 p-3 focus:border-blue-500 focus:ring-0">
                </div>

                {{-- Color --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Color (Calendario)</label>
                    <select name="color" id="w_color" class="w-full rounded-xl border-2 border-gray-400 p-3 focus:border-blue-500 focus:ring-0" required>
                        <option value="blue" {{ old('color') == 'blue' ? 'selected' : '' }}>Azul Intenso</option>
                        <option value="emerald" {{ old('color') == 'emerald' ? 'selected' : '' }}>Verde Esmeralda</option>
                        <option value="teal" {{ old('color') == 'teal' ? 'selected' : '' }}>Turquesa</option>
                        <option value="cyan" {{ old('color') == 'cyan' ? 'selected' : '' }}>Celeste</option>
                        <option value="indigo" {{ old('color') == 'indigo' ? 'selected' : '' }}>Índigo</option>
                        <option value="purple" {{ old('color') == 'purple' ? 'selected' : '' }}>Púrpura</option>
                        <option value="fuchsia" {{ old('color') == 'fuchsia' ? 'selected' : '' }}>Fucsia</option>
                        <option value="rose" {{ old('color') == 'rose' ? 'selected' : '' }}>Rosa / Rojo</option>
                        <option value="amber" {{ old('color') == 'amber' ? 'selected' : '' }}>Ámbar / Naranja</option>
                        <option value="slate" {{ old('color') == 'slate' ? 'selected' : '' }}>Gris Oscuro</option>
                    </select>
                </div>

                {{-- TIPO DE CLASE (MENSUAL O ÚNICA) --}}
                <div class="col-span-1 md:col-span-2 bg-indigo-50 p-4 rounded-xl border-2 border-indigo-100 my-2">
                    <label class="block text-sm font-black text-indigo-900 mb-3 uppercase tracking-wider">Tipo de Taller</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_single_class" value="0" id="type_monthly" onchange="toggleDateFields()" class="w-5 h-5 text-indigo-600" checked>
                            <span class="font-bold text-gray-800">Mensual (Repetitivo)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_single_class" value="1" id="type_single" onchange="toggleDateFields()" class="w-5 h-5 text-indigo-600">
                            <span class="font-bold text-gray-800">Clase Única (Masterclass)</span>
                        </label>
                    </div>
                </div>
                
                {{-- CONTENEDOR DINÁMICO: Día (Mensual) --}}
                <div id="container_repeat_day">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Día de Clases</label>
                    <select name="repeat_day" id="w_repeat_day" class="w-full rounded-xl border-2 border-gray-400 p-3 focus:border-blue-500 focus:ring-0">
                        <option value="">-- Seleccionar --</option>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                        <option value="0">Domingo</option>
                    </select>
                </div>

                {{-- CONTENEDOR DINÁMICO: Fecha Exacta (Única) --}}
                <div id="container_specific_date" class="hidden">
                    <label class="block text-sm font-bold text-indigo-700 mb-1">Fecha Exacta</label>
                    <input type="date" name="specific_date" id="w_specific_date" 
                           class="w-full rounded-xl border-2 border-indigo-400 p-3 focus:border-indigo-600 focus:ring-0 bg-white">
                </div>
                
                {{-- Hora --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Hora de Inicio</label>
                    <input type="time" name="start_time" id="w_start_time" value="{{ old('start_time') }}" 
                           class="w-full rounded-xl border-2 p-3 focus:ring-0 transition border-gray-400 focus:border-blue-500" required>
                </div>
                
                {{-- Info Pago --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Datos de Transferencia (Opcional)</label>
                    <textarea name="payment_info" id="w_payment" rows="2" placeholder="Ej: Banco Estado, Cuenta RUT..." 
                              class="w-full rounded-xl border-2 border-gray-400 p-3 focus:border-blue-500 focus:ring-0">{{ old('payment_info') }}</textarea>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeWorkshopModal()" class="flex-1 font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 py-3 rounded-xl transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-indigo-600 text-white font-black py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition">Guardar Taller</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDateFields() {
        const isSingle = document.getElementById('type_single').checked;
        const containerDay = document.getElementById('container_repeat_day');
        const containerDate = document.getElementById('container_specific_date');
        
        const inputDay = document.getElementById('w_repeat_day');
        const inputDate = document.getElementById('w_specific_date');

        if (isSingle) {
            containerDay.classList.add('hidden');
            containerDate.classList.remove('hidden');
            
            inputDay.removeAttribute('required');
            inputDate.setAttribute('required', 'required');
        } else {
            containerDay.classList.remove('hidden');
            containerDate.classList.add('hidden');
            
            inputDay.setAttribute('required', 'required');
            inputDate.removeAttribute('required');
        }
    }

    function openWorkshopModal() {
        document.getElementById('workshopForm').action = "{{ route('workshops.store') }}";
        document.getElementById('workshopMethod').innerHTML = "";
        document.getElementById('modalWorkshopTitle').innerText = 'Nuevo Taller';
        
        @if(!$errors->any()) 
            document.getElementById('workshopForm').reset(); 
            document.getElementById('type_monthly').checked = true;
            toggleDateFields();
        @endif
        
        document.getElementById('workshopModal').classList.remove('hidden');
    }

    function openEditWorkshopModal(w) {
        document.getElementById('workshopForm').action = `/workshops/${w.id}`;
        document.getElementById('workshopMethod').innerHTML = '@method("PUT")';
        document.getElementById('modalWorkshopTitle').innerText = 'Editar Taller';
        
        document.getElementById('w_name').value = w.name;
        document.getElementById('w_trainer').value = w.trainer;
        document.getElementById('w_phone').value = w.trainer_phone || '';
        document.getElementById('w_color').value = w.color || 'blue';
        
        if (w.is_single_class) {
            document.getElementById('type_single').checked = true;
            document.getElementById('w_specific_date').value = w.specific_date;
        } else {
            document.getElementById('type_monthly').checked = true;
            document.getElementById('w_repeat_day').value = w.repeat_day;
        }
        toggleDateFields();

        document.getElementById('w_start_time').value = w.start_time;
        document.getElementById('w_payment').value = w.payment_info;
        
        document.getElementById('workshopModal').classList.remove('hidden');
    }

    function closeWorkshopModal() { 
        document.getElementById('workshopModal').classList.add('hidden'); 
    }

    @if($errors->any())
        document.addEventListener("DOMContentLoaded", function() {
            toggleDateFields();
            openWorkshopModal();
        });
    @endif
</script>
@endsection
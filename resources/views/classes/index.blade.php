<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Pestañas de Navegación de Clases --}}
        <div class="flex space-x-8 mb-8 border-b border-zinc-200">
            <button class="py-4 px-1 font-medium text-zinc-900 border-b-2 border-zinc-900">Talleres (Configuración)</button>
            <a href="{{ route('entrenamientos.index') }}" class="py-4 px-1 font-medium text-zinc-500 hover:text-zinc-800 transition">Entrenamientos (Meses)</a>
        </div>

        {{-- Cabecera --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 tracking-tight">Tus Talleres</h1>
                <p class="mt-2 text-sm text-zinc-500 font-light">Configura las disciplinas, horarios y entrenadores base.</p>
            </div>
            <button onclick="openWorkshopModal()" class="bg-zinc-900 hover:bg-zinc-800 text-white font-medium py-3 px-6 rounded-xl shadow-sm transition active:scale-95 whitespace-nowrap">
                + Nuevo Taller
            </button>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- TABLA DE TALLERES --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Taller / Profesor</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Horario / Fecha</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
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
                        
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 border-l-4 {{ $borderColor }}">
                                <div class="font-medium text-zinc-900 flex items-center gap-2">
                                    {{ $workshop->name }}
                                    @if($workshop->is_single_class)
                                        <span class="bg-zinc-100 text-zinc-600 border border-zinc-200 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider">Clase Única</span>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    {{ $workshop->trainer ?: 'Sin profesor asignado' }} 
                                    @if($workshop->trainer_phone)
                                        <span class="text-zinc-400">({{ $workshop->trainer_phone }})</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-zinc-700">
                                @if($workshop->is_single_class && $workshop->specific_date)
                                    <span class="text-zinc-900">{{ \Carbon\Carbon::parse($workshop->specific_date)->translatedFormat('d \d\e F') }}</span> 
                                @else
                                    {{ $dias[$workshop->repeat_day] }} 
                                @endif
                                a las {{ \Carbon\Carbon::parse($workshop->start_time)->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-4">
                                <button onclick='openEditWorkshopModal({!! json_encode($workshop) !!})' class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition">Editar</button>
                                <form action="{{ route('workshops.destroy', $workshop->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('¿Eliminar este taller?')" class="text-sm font-medium text-rose-500 hover:text-rose-700 transition">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-10 text-center text-sm text-zinc-400 font-light">Sin talleres configurados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL PARA CREAR/EDITAR TALLERES --}}
    <div id="workshopModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/40 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-2xl p-8 max-w-2xl w-full shadow-2xl border border-zinc-100 my-8">
            <form id="workshopForm" method="POST">
                @csrf
                <div id="workshopMethod"></div>
                <h3 class="text-2xl font-bold mb-6 text-zinc-900 tracking-tight" id="modalWorkshopTitle">Configurar Taller</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    {{-- Nombre --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Nombre del Taller</label>
                        <input type="text" name="name" id="w_name" value="{{ old('name') }}" placeholder="Ej: Telas Principiante" 
                               class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white {{ $errors->has('name') ? 'border-rose-300 bg-rose-50' : '' }}" required>
                        @error('name') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Entrenador --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Entrenador(a)</label>
                        <input type="text" name="trainer" id="w_trainer" value="{{ old('trainer') }}" placeholder="Nombre profesor(a)" 
                               class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white {{ $errors->has('trainer') ? 'border-rose-300 bg-rose-50' : '' }}" required>
                    </div>

                    {{-- Teléfono Entrenador --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Teléfono Profe (Opcional)</label>
                        <input type="text" name="trainer_phone" id="w_phone" value="{{ old('trainer_phone') }}" placeholder="+56 9..." 
                               class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white">
                    </div>

                    {{-- Color --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Color (Etiqueta de Calendario)</label>
                        <select name="color" id="w_color" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white" required>
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
                    <div class="col-span-1 md:col-span-2 bg-zinc-50 p-4 rounded-xl border border-zinc-200 my-2">
                        <label class="block text-xs font-semibold text-zinc-500 mb-3 uppercase tracking-wider">Tipo de Taller</label>
                        <div class="flex flex-col sm:flex-row gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="is_single_class" value="0" id="type_monthly" onchange="toggleDateFields()" class="w-4 h-4 text-zinc-900 border-zinc-300 focus:ring-zinc-900" checked>
                                <span class="text-sm font-medium text-zinc-800">Mensual (Repetitivo)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="is_single_class" value="1" id="type_single" onchange="toggleDateFields()" class="w-4 h-4 text-zinc-900 border-zinc-300 focus:ring-zinc-900">
                                <span class="text-sm font-medium text-zinc-800">Clase Única (Masterclass)</span>
                            </label>
                        </div>
                    </div>
                    
                    {{-- CONTENEDOR DINÁMICO: Día (Mensual) --}}
                    <div id="container_repeat_day">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Día de Clases</label>
                        <select name="repeat_day" id="w_repeat_day" class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white">
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
                        <label class="block text-sm font-medium text-zinc-900 mb-1">Fecha Exacta</label>
                        <input type="date" name="specific_date" id="w_specific_date" 
                               class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:outline-none focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 transition bg-white">
                    </div>
                    
                    {{-- Hora --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Hora de Inicio</label>
                        <input type="time" name="start_time" id="w_start_time" value="{{ old('start_time') }}" 
                               class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white" required>
                    </div>
                    
                    {{-- Info Pago --}}
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Datos de Transferencia (Opcional)</label>
                        <textarea name="payment_info" id="w_payment" rows="2" placeholder="Ej: Banco Estado, Cuenta RUT..." 
                                  class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:outline-none focus:border-zinc-400 focus:ring-2 focus:ring-zinc-100 transition bg-zinc-50 focus:bg-white">{{ old('payment_info') }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeWorkshopModal()" class="flex-1 font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition text-sm">Cancelar</button>
                    <button type="submit" class="flex-1 bg-zinc-900 text-white font-medium py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition text-sm">Guardar Taller</button>
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
</x-app-layout>
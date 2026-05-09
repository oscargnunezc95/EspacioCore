<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />

        <div class="mt-8">
            <x-studio-header 
                title="{{ $session->workshop->name }}" 
                :breadcrumbs="[
                    ['name' => 'Planificación', 'url' => route('trainingmonth.index', ['subdomain' => request()->route('subdomain')])],
                    ['name' => ucfirst(\Carbon\Carbon::parse($session->date)->translatedFormat('F')), 'url' => route('trainingmonth.show', ['subdomain' => request()->route('subdomain'), 'month' => $monthId])],
                    ['name' => 'Lista de Clase']
                ]"
            >
                <x-slot name="actions">
                    <button onclick="document.getElementById('enrollModal').classList.remove('hidden')" class="bg-zinc-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-zinc-800 focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Inscribir Alumna
                    </button>
                </x-slot>
            </x-studio-header>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Info de la Clase -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex items-center gap-2 text-zinc-600 font-medium bg-white px-4 py-2 rounded-lg border border-zinc-200 shadow-sm inline-flex">
                <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="capitalize">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F') }}</span>
                <span class="text-zinc-300 mx-1">|</span>
                <span class="font-bold text-zinc-900">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs</span>
            </div>
            
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg border border-zinc-200 shadow-sm inline-flex">
                <span class="text-zinc-500 font-medium text-sm">Inscritas:</span>
                <span class="font-black text-zinc-900">{{ $students->count() }} @if($session->workshop->max_students) / {{ $session->workshop->max_students }} @endif</span>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif
        
        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Hubo un error en tu solicitud. Revisa el formulario.
            </div>
        @endif

        <!-- CONTENEDOR PRINCIPAL: LISTA DE ASISTENCIA -->
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            
            <div class="p-5 md:p-6 bg-zinc-50 border-b border-zinc-200">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    
                    <div class="w-full md:w-1/2 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" id="searchStudent" onkeyup="filterStudents()" placeholder="Buscar en la lista..." 
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-300 bg-white text-zinc-900 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none shadow-sm">
                    </div>

                    <div class="flex items-center gap-4">
                        @if($session->is_cancelled)
                            <span class="bg-rose-100 text-rose-700 text-xs font-black px-3 py-1 rounded-md uppercase tracking-wider border border-rose-200 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Cancelada
                            </span>
                        @endif
                        
                        {{-- BOTÓN EDITAR SESIÓN --}}
                        <button onclick="document.getElementById('editSessionModal').classList.remove('hidden')" class="text-sm font-bold text-zinc-600 hover:text-zinc-900 transition-colors px-4 py-2 rounded-xl border border-zinc-200 shadow-sm flex items-center gap-2 bg-white hover:bg-zinc-50 active:scale-95">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Editar Clase
                        </button>
                    </div>
                </div>
            </div>

            <ul class="divide-y divide-zinc-100" id="studentsList">
                @forelse($students as $student)
                    @php
                        $isPresent = $session->attendances->contains('student_id', $student->id);
                        $hasPaidThisClass = in_array($student->id, $paidStudentIds);
                    @endphp
                    <li class="student-item p-4 md:p-6 flex items-center gap-5 transition-colors hover:bg-zinc-50/80">
                        
                        <!-- Toggle Switch de Asistencia -->
                        <button onclick="toggleAttendance({{ $session->id }}, {{ $student->id }}, this)" 
                                class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 {{ $isPresent ? 'bg-emerald-500' : 'bg-zinc-200' }}" 
                                role="switch" aria-checked="{{ $isPresent ? 'true' : 'false' }}">
                            <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isPresent ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                        
                        <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <p class="student-name font-bold text-zinc-900 text-sm md:text-base">{{ $student->name }}</p>
                            
                            <!-- Badges de Estado -->
                            <div>
                                @if($hasPaidThisClass)
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md inline-block">Pagada</span>
                                @elseif($isPresent && !$hasPaidThisClass)
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md inline-block" data-status="debe-pago">Debe Pago</span>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm font-medium text-zinc-500">Nadie se ha inscrito a esta clase todavía.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- MODAL 1: EDITAR SESIÓN --}}
    <div id="editSessionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-md w-full shadow-xl border border-zinc-100 transform transition-all">
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-zinc-900">Editar Sesión</h3>
                    <p class="text-xs text-zinc-500 mt-1 leading-tight">Modifica la hora, asigna un reemplazo o suspende.</p>
                </div>
                <button type="button" onclick="document.getElementById('editSessionModal').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="{{ route('sessions.update', ['subdomain' => request()->route('subdomain'), 'session' => $session->id]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-5 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Input NUEVO: Fecha --}}
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Día de la Clase</label>
                            <input type="date" name="date" value="{{ \Carbon\Carbon::parse($session->date)->format('Y-m-d') }}" 
                                   class="w-full rounded-xl border border-zinc-300 p-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all" required>
                        </div>

                        {{-- Input: Hora --}}
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Hora de Inicio</label>
                            <input type="time" name="start_time" value="{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}" 
                                   class="w-full rounded-xl border border-zinc-300 p-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all" required>
                        </div>
                    </div>

                    {{-- Select: Profesor Reemplazante --}}
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Profesor (Opcional)</label>
                        <select name="teacher_id" class="w-full rounded-xl border border-zinc-300 p-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all cursor-pointer">
                            <option value="">Mantener original ({{ $session->workshop->teacher->name ?? 'Sin asignar' }})</option>
                            @if(isset($teachers))
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ $session->teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Checkbox: Suspender --}}
                    <label class="flex items-start gap-3 p-4 rounded-xl border border-rose-200 bg-rose-50 cursor-pointer group transition-colors hover:bg-rose-100">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="is_cancelled" value="1" {{ $session->is_cancelled ? 'checked' : '' }} class="w-4 h-4 text-rose-600 border-rose-300 rounded focus:ring-rose-600 cursor-pointer">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-rose-800">Suspender Clase</span>
                            <span class="text-xs text-rose-600 mt-1">La clase aparecerá cancelada y el profesor no podrá pasar lista.</span>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-2 border-t border-zinc-100">
                    <button type="button" onclick="document.getElementById('editSessionModal').classList.add('hidden')" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all duration-200 active:scale-95 text-sm">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: INSCRIBIR ALUMNA MANUALMENTE (Se mantiene igual) --}}
    <div id="enrollModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-md w-full shadow-xl border border-zinc-100 transform transition-all">
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-zinc-900">Inscribir a la Clase</h3>
                    <p class="text-xs text-zinc-500 mt-1 leading-tight">Agrega manualmente a una alumna a esta sesión.</p>
                </div>
                <button type="button" onclick="document.getElementById('enrollModal').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="{{ route('sessions.enroll', ['subdomain' => request()->route('subdomain'), 'session' => $session->id]) }}" method="POST">
                @csrf
                
                {{-- Selector de Modalidad --}}
                <div class="mb-5 bg-zinc-100 p-1.5 rounded-xl flex gap-1">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="enroll_mode" value="existing" checked onchange="toggleEnrollMode()" class="peer sr-only">
                        <div class="text-center font-bold text-xs py-2 px-3 rounded-lg text-zinc-500 transition-all peer-checked:bg-white peer-checked:text-zinc-900 peer-checked:shadow-sm">Buscar en Estudio</div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="enroll_mode" value="new" onchange="toggleEnrollMode()" class="peer sr-only">
                        <div class="text-center font-bold text-xs py-2 px-3 rounded-lg text-zinc-500 transition-all peer-checked:bg-white peer-checked:text-zinc-900 peer-checked:shadow-sm">Nueva Alumna</div>
                    </label>
                </div>

                {{-- ZONA 1: BUSCAR EXISTENTE --}}
                <div id="mode_existing" class="mb-6">
                    <input type="text" id="searchOtherStudent" onkeyup="filterOther()" placeholder="Buscar por nombre o correo..." 
                           class="w-full rounded-xl border border-zinc-300 p-3 text-sm mb-3 focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all">
                    
                    <div class="max-h-48 overflow-y-auto border border-zinc-200 rounded-xl bg-zinc-50 p-2 space-y-1 custom-scrollbar">
                        @forelse($otherStudents as $other)
                            <label class="other-item flex items-center gap-3 p-3 bg-white border border-zinc-200 rounded-lg cursor-pointer hover:border-zinc-400 transition-all">
                                <input type="radio" name="student_id" value="{{ $other->id }}" class="w-4 h-4 text-zinc-900 focus:ring-zinc-900 border-zinc-300">
                                <div class="flex flex-col">
                                    <span class="other-name font-bold text-zinc-900 text-sm leading-none">{{ $other->name }}</span>
                                    <span class="text-xs font-medium text-zinc-500 mt-1">{{ $other->email ?: 'Sin correo' }}</span>
                                </div>
                            </label>
                        @empty
                            <div class="text-sm text-zinc-400 italic text-center py-4">No hay más alumnas en el estudio.</div>
                        @endforelse
                    </div>
                    @error('student_id') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- ZONA 2: CREAR NUEVA --}}
                <div id="mode_new" class="mb-6 hidden space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre</label>
                            <input type="text" name="first_name" placeholder="Ej: Camila" 
                                   class="w-full rounded-xl border border-zinc-300 p-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all {{ $errors->has('first_name') ? 'border-red-500 ring-1 ring-red-500' : '' }}">
                            @error('first_name') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Apellido <span class="text-zinc-400 font-normal">(Opc.)</span></label>
                            <input type="text" name="last_name" placeholder="Ej: Rojas" 
                                   class="w-full rounded-xl border border-zinc-300 p-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Correo Electrónico</label>
                            <input type="email" name="email" placeholder="camila@ejemplo.com" 
                                   class="w-full rounded-xl border border-zinc-300 p-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all {{ $errors->has('email') ? 'border-red-500 ring-1 ring-red-500' : '' }}">
                            @error('email') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-2 border-t border-zinc-100">
                    <button type="button" onclick="document.getElementById('enrollModal').classList.add('hidden')" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all duration-200 active:scale-95 text-sm">Inscribir y Asistir</button>
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
        function filterStudents() {
            let input = document.getElementById('searchStudent').value.toLowerCase();
            let items = document.querySelectorAll('.student-item');
            items.forEach(item => {
                let name = item.querySelector('.student-name').innerText.toLowerCase();
                item.style.display = name.includes(input) ? 'flex' : 'none';
            });
        }

        function filterOther() {
            let input = document.getElementById('searchOtherStudent').value.toLowerCase();
            let items = document.querySelectorAll('.other-item');
            items.forEach(item => {
                let name = item.querySelector('.other-name').innerText.toLowerCase();
                item.style.display = name.includes(input) ? 'flex' : 'none';
            });
        }

        function toggleEnrollMode() {
            const mode = document.querySelector('input[name="enroll_mode"]:checked').value;
            const modeExisting = document.getElementById('mode_existing');
            const modeNew = document.getElementById('mode_new');

            if (mode === 'existing') {
                modeExisting.classList.remove('hidden');
                modeNew.classList.add('hidden');
            } else {
                modeExisting.classList.add('hidden');
                modeNew.classList.remove('hidden');
            }
        }

        function toggleAttendance(sid, stid, btn) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const present = btn.getAttribute('aria-checked') === 'true';
            
            btn.classList.replace(present ? 'bg-emerald-500' : 'bg-zinc-200', present ? 'bg-zinc-200' : 'bg-emerald-500');
            btn.querySelector('span').classList.replace(present ? 'translate-x-5' : 'translate-x-0', present ? 'translate-x-0' : 'translate-x-5');
            btn.setAttribute('aria-checked', !present);

            const badgeContainer = btn.nextElementSibling.querySelector('div');
            const hasPaidBadge = badgeContainer.querySelector('span.text-emerald-700'); 
            
            if(!hasPaidBadge) {
                if(!present) {
                    badgeContainer.innerHTML = '<span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md inline-block" data-status="debe-pago">Debe Pago</span>';
                } else {
                    badgeContainer.innerHTML = '';
                }
            }

            fetch(`/sessions/${sid}/attendance/${stid}`, {
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            });
        }

        @if($errors->any() && !$errors->has('start_time'))
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById('enrollModal').classList.remove('hidden');
                toggleEnrollMode();
            });
        @endif
        
        @if($errors->has('start_time'))
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById('editSessionModal').classList.remove('hidden');
            });
        @endif
    </script>
</x-app-layout>
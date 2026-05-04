<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />

        <div class="mt-8">
            <x-studio-header 
                title="Inscribir alumnas/os" 
                :breadcrumbs="[
                    ['name' => 'Talleres', 'url' => route('workshops.index')],
                    ['name' => $workshop->name]
                ]"
            />
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-6">
            <h2 class="text-2xl font-black text-zinc-900 tracking-tight">{{ $workshop->name }}</h2>
            <p class="text-sm text-zinc-500 mt-1">Selecciona las alumnas/os que pertenecen a este taller. Esto te permitirá gestionar sus asistencias y saldos de clases.</p>
        </div>

        <form action="{{ route('workshops.sync_students', $workshop->id) }}" method="POST">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden flex flex-col">
                
                <!-- Buscador de alumnas/os -->
                <div class="p-4 md:p-6 bg-zinc-50 border-b border-zinc-200 flex justify-between items-center gap-4 shrink-0">
                    <div class="relative w-full md:w-1/2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <!-- Modificado para ser un Omnibox -->
                        <input type="text" id="searchStudent" onkeyup="filterStudents()" placeholder="Buscar por apellido, nombre o correo..." 
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-300 bg-white text-zinc-900 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none shadow-sm">
                    </div>
                    
                    <div class="text-sm font-bold text-zinc-500">
                        Total en estudio: <span class="text-zinc-900">{{ $students->count() }}</span>
                    </div>
                </div>

                <!-- Tabla de Selección Vertical -->
                <div class="max-h-[60vh] overflow-y-auto custom-scrollbar bg-zinc-50/30">
                    <table class="min-w-full divide-y divide-zinc-200 text-left border-collapse">
                        <thead class="bg-zinc-100/80 sticky top-0 z-10 backdrop-blur-sm">
                            <tr>
                                <th class="px-6 py-3 w-16"></th>
                                <th class="px-6 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Apellidos</th>
                                <th class="px-6 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Nombres</th>
                                <th class="px-6 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wider">Contacto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100" id="studentsList">
                            @forelse($students as $student)
                                @php
                                    $isEnrolled = in_array($student->id, $enrolledIds);
                                @endphp
                                
                                <!-- La fila completa es cliqueable gracias al onclick -->
                                <tr onclick="toggleFromRow(event, 'check-{{ $student->id }}')" 
                                    class="student-row cursor-pointer transition-colors duration-200 {{ $isEnrolled ? 'row-selected' : 'row-unselected' }}">
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" id="check-{{ $student->id }}" name="students[]" value="{{ $student->id }}" {{ $isEnrolled ? 'checked' : '' }} 
                                                   onchange="updateRowStyle(this)"
                                                   class="w-5 h-5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 focus:ring-offset-0 transition-colors cursor-pointer {{ $isEnrolled ? 'border-white' : '' }}">
                                        </div>
                                    </td>
                                    
                                    <!-- Apellido a la izquierda (en negrita para destacar) -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold row-main-text">
                                            {{ $student->last_name ?: '—' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Nombre -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium row-main-text">
                                            {{ $student->first_name }}
                                        </span>
                                    </td>
                                    
                                    <!-- Contacto -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs row-sub-text">
                                            {{ $student->email ?: ($student->phone ?: 'Sin contacto') }}
                                        </span>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center">
                                        <span class="block text-sm font-bold text-zinc-400">No hay alumnas/os registradas en tu estudio.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer con Botones -->
                <div class="p-4 md:p-6 bg-white border-t border-zinc-200 flex justify-end gap-3 shrink-0">
                    <a href="{{ route('workshops.index') }}" class="font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 px-6 py-3 rounded-xl transition-colors duration-200 text-sm">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-zinc-900 text-white font-bold px-8 py-3 rounded-xl shadow-sm hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all duration-200 active:scale-95 text-sm">
                        Guardar Lista
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Estilos para el scroll y la magia visual de las filas --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e4e4e7; border-radius: 20px; }
        
        /* Magia visual controlada por CSS puro para mayor rendimiento */
        .row-selected { background-color: #18181b; /* zinc-900 */ }
        .row-selected .row-main-text { color: white; }
        .row-selected .row-sub-text { color: #d4d4d8; /* zinc-300 */ }
        
        .row-unselected { background-color: white; }
        .row-unselected:hover { background-color: #fafafa; /* zinc-50 */ }
        .row-unselected .row-main-text { color: #18181b; /* zinc-900 */ }
        .row-unselected .row-sub-text { color: #71717a; /* zinc-500 */ }
    </style>

    <script>
        // Buscador en tiempo real adaptado a filas (Omnibox)
        function filterStudents() {
            let input = document.getElementById('searchStudent').value.toLowerCase();
            let rows = document.querySelectorAll('.student-row');
            
            rows.forEach(row => {
                let textContent = row.innerText.toLowerCase();
                if (textContent.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Permite hacer click en cualquier parte de la fila para marcar el checkbox
        function toggleFromRow(event, checkboxId) {
            // Si el usuario hizo clic directamente en el checkbox, dejamos que el evento onchange nativo actúe
            if(event.target.tagName.toLowerCase() === 'input') return;
            
            const checkbox = document.getElementById(checkboxId);
            checkbox.checked = !checkbox.checked;
            
            // Disparamos el evento manualmente para que cambie el color
            checkbox.dispatchEvent(new Event('change'));
        }

        // Cambia las clases de la fila dependiendo del estado del checkbox
        function updateRowStyle(checkbox) {
            const row = checkbox.closest('tr');

            if (checkbox.checked) {
                row.classList.remove('row-unselected');
                row.classList.add('row-selected');
                checkbox.classList.add('border-white');
            } else {
                row.classList.remove('row-selected');
                row.classList.add('row-unselected');
                checkbox.classList.remove('border-white');
            }
        }
    </script>
</x-app-layout>
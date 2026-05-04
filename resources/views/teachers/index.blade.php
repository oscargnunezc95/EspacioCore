<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />
        <div class="mt-8">
            <x-studio-header title="Directorio del Equipo" :breadcrumbs="[['name' => 'Profesores']]">
                <x-slot name="actions">
                    <button onclick="openTeacherModal()" class="bg-zinc-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-zinc-800 transition-all active:scale-95 flex items-center gap-2">
                        + Nuevo Profesor
                    </button>
                </x-slot>
            </x-studio-header>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Profesor</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Estado de Cuenta</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-zinc-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($teachers as $teacher)
                        <tr class="hover:bg-zinc-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-zinc-900">{{ $teacher->name }}</td>
                            <td class="px-6 py-4 text-sm text-zinc-500">
                                <div>{{ $teacher->email }}</div>
                                <div class="text-xs">{{ $teacher->phone ?: 'Sin teléfono' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($teacher->user_id)
                                    <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-1 rounded-md font-bold uppercase tracking-wider">Cuenta Vinculada</span>
                                @else
                                    <span class="bg-zinc-100 text-zinc-500 text-[10px] px-2 py-1 rounded-md font-bold uppercase tracking-wider">Pendiente Registro</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <button onclick="openEditTeacherModal({{ json_encode($teacher) }})" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">Editar</button>
                                <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="inline m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('¿Eliminar a este profesor?')" class="text-sm font-bold text-rose-400 hover:text-rose-600">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-zinc-400 font-bold text-sm">No hay profesores registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL --}}
    <div id="teacherModal" onclick="if(event.target === this) closeTeacherModal()" class="fixed inset-0 z-50 hidden flex items-start justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-md w-full shadow-xl border border-zinc-100 my-auto">
            <h3 class="text-xl font-bold text-zinc-900 mb-6" id="modalTitle">Nuevo Profesor</h3>
            <form id="teacherForm" method="POST">
                @csrf
                <div id="teacherMethod"></div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1">Nombre Completo</label>
                        <input type="text" name="name" id="t_name" required class="w-full rounded-xl border-zinc-300 px-4 py-3 text-sm focus:ring-zinc-900 focus:border-zinc-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1">Correo Electrónico</label>
                        <input type="email" name="email" id="t_email" required class="w-full rounded-xl border-zinc-300 px-4 py-3 text-sm focus:ring-zinc-900 focus:border-zinc-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1">Teléfono (Opcional)</label>
                        <input type="text" name="phone" id="t_phone" class="w-full rounded-xl border-zinc-300 px-4 py-3 text-sm focus:ring-zinc-900 focus:border-zinc-900 outline-none">
                    </div>
                </div>
                <div class="mt-8 flex gap-3 pt-4 border-t border-zinc-100">
                    <button type="button" onclick="closeTeacherModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 py-3 rounded-xl">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openTeacherModal() {
            document.getElementById('teacherForm').action = "{{ route('teachers.store') }}";
            document.getElementById('teacherMethod').innerHTML = "";
            document.getElementById('modalTitle').innerText = 'Nuevo Profesor';
            document.getElementById('teacherForm').reset();
            document.getElementById('teacherModal').classList.remove('hidden');
        }

        function openEditTeacherModal(t) {
            document.getElementById('teacherForm').action = `/teachers/${t.id}`;
            document.getElementById('teacherMethod').innerHTML = '@method("PUT")';
            document.getElementById('modalTitle').innerText = 'Editar Profesor';
            document.getElementById('t_name').value = t.name;
            document.getElementById('t_email').value = t.email;
            document.getElementById('t_phone').value = t.phone || '';
            document.getElementById('teacherModal').classList.remove('hidden');
        }

        function closeTeacherModal() {
            document.getElementById('teacherModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
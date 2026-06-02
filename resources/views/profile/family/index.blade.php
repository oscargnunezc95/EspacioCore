<x-app-layout>
    <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24" x-data="{ 
        openModal: {{ $errors->any() || session('conflict_type') ? 'true' : 'false' }},
        editMode: false,
        actionUrl: '{{ route('profile.family.store') }}',
        formData: { 
            first_name: '{{ old('first_name') }}', 
            last_name: '{{ old('last_name') }}', 
            country_id: '{{ old('country_id', '1') }}',
            national_id: '{{ old('national_id') }}', 
            relationship: '{{ old('relationship', 'Hijo/a') }}' 
        },
        // Modal de conflicto
        conflictType: '{{ session('conflict_type') }}',
        conflictData: @json(session('conflict_data')),
    }">
        
        {{-- Encabezado --}}
        <div class="text-center mb-12">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Mi Familia</h1>
                <p class="mt-3 text-zinc-500 font-medium text-base md:text-lg">Gestiona los perfiles de tus dependientes para inscribirlos en clases.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabla de Familiares --}}
        <div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-zinc-200 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 font-black uppercase text-[10px] tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Nombre Completo</th>
                        <th class="px-6 py-4">RUT / Doc</th>
                        <th class="px-6 py-4">Parentesco</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($dependents as $dependent)
                        <tr class="hover:bg-zinc-50/50 transition-colors group">
                            <td class="px-6 py-4 font-bold text-zinc-900">
                                {{ $dependent->first_name }} {{ $dependent->last_name }}
                            </td>
                            <td class="px-6 py-4 text-zinc-500 font-medium">
                                {{ $dependent->national_id ?? 'No registrado' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-zinc-100 text-zinc-700 px-2.5 py-1 rounded-lg font-bold text-[11px] uppercase tracking-wider">
                                    {{ $dependent->relationship }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="
                                        editMode = true;
                                        actionUrl = '/profile/familia/{{ $dependent->id }}';
                                        formData = { 
                                            first_name: '{{ $dependent->first_name }}', 
                                            last_name: '{{ $dependent->last_name }}', 
                                            country_id: '{{ $dependent->country_id ?? '' }}',
                                            national_id: '{{ $dependent->national_id }}', 
                                            relationship: '{{ $dependent->relationship }}' 
                                        };
                                        openModal = true"
                                        class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <form action="{{ route('profile.family.destroy', $dependent->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este perfil?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-zinc-400">
                                No has registrado familiares aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Botón Agregar --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-8 gap-4 mb-8">
            <div></div>
            <button @click="
                editMode = false; 
                actionUrl = '{{ route('profile.family.store') }}';
                conflictType = '';
                formData = { first_name: '', last_name: '', country_id: '1', national_id: '', relationship: 'Hijo/a' };
                openModal = true" 
                class="bg-zinc-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm hover:bg-zinc-800 transition-all active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Agregar Familiar
            </button>
        </div>

        {{-- MODAL CREAR/EDITAR --}}
        <div x-show="openModal && !conflictType" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm"
             x-cloak
             x-transition>
            
            <div @click.away="openModal = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-6 border-b border-zinc-100 flex justify-between items-center">
                    <h3 class="text-xl font-black text-zinc-900" x-text="editMode ? 'Modificar Familiar' : 'Nuevo Familiar'"></h3>
                    <button @click="openModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form :action="actionUrl" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">Nombre</label>
                            <input type="text" name="first_name" x-model="formData.first_name" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all">
                            @error('first_name') <p class="text-xs text-rose-600 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">Apellido</label>
                            <input type="text" name="last_name" x-model="formData.last_name" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all">
                            @error('last_name') <p class="text-xs text-rose-600 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">País del Documento</label>
                        <select name="country_id" x-model="formData.country_id" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all bg-white">
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('country_id') <p class="text-xs text-rose-600 mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">RUT / Documento</label>
                        <input type="text" name="national_id" x-model="formData.national_id" placeholder="Ej: 12345678-9" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all">
                        @error('national_id') <p class="text-xs text-rose-600 mt-1 font-bold leading-tight">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">Parentesco</label>
                        <select name="relationship" x-model="formData.relationship" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all bg-white">
                            <option value="Hijo/a">Hijo/a</option>
                            <option value="Pareja">Pareja</option>
                            <option value="Hermano/a">Hermano/a</option>
                            <option value="Padre/Madre">Padre/Madre</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('relationship') <p class="text-xs text-rose-600 mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-zinc-800 transition-all active:scale-[0.98] mt-4">
                        <span x-text="editMode ? 'Guardar Cambios' : 'Registrar Familiar'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- MODAL DE CONFIRMACIÓN: TRANSFERENCIA --}}
        <div x-show="conflictType === 'transfer'" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm"
             x-cloak
             x-transition>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-6 border-b border-amber-100 bg-amber-50">
                    <h3 class="text-lg font-black text-amber-800 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        Familiar ya registrado por otra persona
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-zinc-700">
                        <strong x-text="conflictData?.dependent_name || 'Esta persona'"></strong> 
                        ya figura como familiar de 
                        <strong class="text-indigo-600" x-text="conflictData?.owner_name || 'otro usuario'"></strong>.
                    </p>
                    <p class="text-xs text-zinc-500">
                        Si continúas, le enviaremos un correo a esa persona pidiéndole que libere el vínculo.
                        Mientras tanto, el familiar quedará registrado en tu cuenta.
                    </p>

                    <form action="{{ route('profile.family.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="confirmed" value="transfer">
                        <input type="hidden" name="owner_id" value="{{ session('conflict_data')['owner_id'] ?? '' }}">
                        <input type="hidden" name="first_name" value="{{ old('first_name') }}">
                        <input type="hidden" name="last_name" value="{{ old('last_name') }}">
                        <input type="hidden" name="country_id" value="{{ old('country_id') }}">
                        <input type="hidden" name="national_id" value="{{ old('national_id') }}">
                        <input type="hidden" name="relationship" value="{{ old('relationship') }}">

                        <button type="submit" class="w-full bg-amber-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-amber-700 transition-all active:scale-[0.98]">
                            Sí, solicitar transferencia
                        </button>
                    </form>

                    <button @click="conflictType = ''; openModal = true" class="w-full bg-zinc-100 text-zinc-600 font-bold py-2.5 rounded-xl hover:bg-zinc-200 transition-all text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL DE CONFIRMACIÓN: USUARIO GLOBAL --}}
        <div x-show="conflictType === 'link'" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm"
             x-cloak
             x-transition>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-6 border-b border-indigo-100 bg-indigo-50">
                    <h3 class="text-lg font-black text-indigo-800 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Ya existe un usuario con este documento
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-zinc-700">
                        El documento ingresado pertenece a 
                        <strong class="text-indigo-600" x-text="conflictData?.user_name || 'un usuario'"></strong>,
                        que ya tiene una cuenta en EstadoPrisma.
                    </p>
                    <p class="text-xs text-zinc-500">
                        Si continúas, esta persona será notificada de que la agregaste como familiar.
                        Podrá gestionar el vínculo desde su perfil.
                    </p>

                    <form action="{{ route('profile.family.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="confirmed" value="link">
                        <input type="hidden" name="target_user_id" value="{{ session('conflict_data')['user_id'] ?? '' }}">
                        <input type="hidden" name="first_name" value="{{ old('first_name') }}">
                        <input type="hidden" name="last_name" value="{{ old('last_name') }}">
                        <input type="hidden" name="country_id" value="{{ old('country_id') }}">
                        <input type="hidden" name="national_id" value="{{ old('national_id') }}">
                        <input type="hidden" name="relationship" value="{{ old('relationship') }}">

                        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition-all active:scale-[0.98]">
                            Sí, agregar como familiar
                        </button>
                    </form>

                    <button @click="conflictType = ''; openModal = true" class="w-full bg-zinc-100 text-zinc-600 font-bold py-2.5 rounded-xl hover:bg-zinc-200 transition-all text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

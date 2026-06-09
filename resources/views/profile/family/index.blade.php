<x-app-layout>
    <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24" x-data="{ 
        activeTab: 'mis-dependientes',
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
        conflictType: '{{ session('conflict_type') }}',
        conflictData: @js(session('conflict_data')),
    }">
        
        {{-- Encabezado --}}
        <div class="text-center mb-10">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Mi Familia</h1>
                <p class="mt-3 text-zinc-500 font-medium text-base md:text-lg">Gestiona a tus dependientes o revisa las familias a las que perteneces.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 bg-rose-50 text-rose-700 rounded-xl text-sm font-bold border border-rose-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- NAVEGACIÓN DE PESTAÑAS --}}
        <div class="border-b border-zinc-200 mb-6 flex gap-6 overflow-x-auto custom-scrollbar">
            <button @click="activeTab = 'mis-dependientes'" 
                    :class="activeTab === 'mis-dependientes' ? 'border-zinc-900 text-zinc-900' : 'border-transparent text-zinc-400 hover:text-zinc-600'" 
                    class="pb-3 border-b-2 font-black text-sm transition-colors whitespace-nowrap">
                A quiénes administro
            </button>
            <button @click="activeTab = 'membresias'" 
                    :class="activeTab === 'membresias' ? 'border-zinc-900 text-zinc-900' : 'border-transparent text-zinc-400 hover:text-zinc-600'" 
                    class="pb-3 border-b-2 font-black text-sm transition-colors flex items-center gap-2 whitespace-nowrap">
                Familias a las que pertenezco
                @if($memberships->where('status', 'pending')->count() > 0)
                    <span class="bg-amber-500 text-white text-[10px] px-2 py-0.5 rounded-full shadow-sm">{{ $memberships->where('status', 'pending')->count() }}</span>
                @endif
            </button>
        </div>

        {{-- ======================================================= --}}
        {{-- PESTAÑA 1: MIS DEPENDIENTES                             --}}
        {{-- ======================================================= --}}
        <div x-show="activeTab === 'mis-dependientes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-zinc-200 overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 font-black uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Nombre Completo</th>
                            <th class="px-6 py-4">RUT / Doc</th>
                            <th class="px-6 py-4">Parentesco</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($dependents as $dependent)
                            <tr class="hover:bg-zinc-50/50 transition-colors group {{ $dependent->status === 'pending' ? 'bg-amber-50/30' : '' }}">
                                <td class="px-6 py-4 font-bold text-zinc-900">
                                    <div class="flex items-center gap-2">
                                        {{ $dependent->first_name }} {{ $dependent->last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-zinc-500 font-medium">{{ $dependent->national_id ?? 'No registrado' }}</td>
                                <td class="px-6 py-4">
                                    <span class="bg-zinc-100 text-zinc-700 px-2.5 py-1 rounded-lg font-bold text-[11px] uppercase tracking-wider">{{ $dependent->relationship }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($dependent->status === 'active')
                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg font-bold text-[11px] uppercase tracking-wider border border-emerald-200">Activo</span>
                                    @elseif($dependent->status === 'pending')
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2.5 py-1 rounded-lg font-bold text-[11px] uppercase tracking-wider border border-amber-200">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($dependent->status === 'active')
                                            <button @click="editMode = true; actionUrl = '/profile/familia/{{ $dependent->id }}'; formData = { first_name: '{{ $dependent->first_name }}', last_name: '{{ $dependent->last_name }}', country_id: '{{ $dependent->country_id ?? '' }}', national_id: '{{ $dependent->national_id }}', relationship: '{{ $dependent->relationship }}' }; openModal = true" class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                        @endif
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
                                <td colspan="5" class="px-6 py-8 text-center text-zinc-400">No administras familiares.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end py-8">
                <button @click="editMode = false; actionUrl = '{{ route('profile.family.store') }}'; conflictType = ''; formData = { first_name: '', last_name: '', country_id: '1', national_id: '', relationship: 'Hijo/a' }; openModal = true" class="bg-zinc-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm hover:bg-zinc-800 transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Agregar Familiar
                </button>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- PESTAÑA 2: FAMILIAS A LAS QUE PERTENEZCO                --}}
        {{-- ======================================================= --}}
        <div x-show="activeTab === 'membresias'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="bg-white rounded-2xl md:rounded-3xl shadow-sm border border-zinc-200 overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-400 font-black uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Apoderado / Administrador</th>
                            <th class="px-6 py-4">Te registró como</th>
                            <th class="px-6 py-4">Estado del Vínculo</th>
                            <th class="px-6 py-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($memberships as $membership)
                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-zinc-900">{{ $membership->user->name }}</p>
                                    <p class="text-[11px] text-zinc-500 font-medium">{{ $membership->user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-zinc-100 text-zinc-700 px-2.5 py-1 rounded-lg font-bold text-[11px] uppercase tracking-wider">{{ $membership->relationship }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($membership->status === 'active')
                                        <span class="inline-flex items-center gap-1 text-emerald-600 font-bold text-[11px] uppercase tracking-wider"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Vinculado</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-600 font-bold text-[11px] uppercase tracking-wider"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Pendiente (Revisa tu correo)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($membership->status === 'pending')
                                        <div class="flex justify-end gap-2">
                                            <form action="{{ route('profile.family.accept-membership', $membership->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-lg transition-colors">
                                                    Aceptar Vínculo
                                                </button>
                                            </form>
                                            <form action="{{ route('profile.family.reject-membership', $membership->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de rechazar esta solicitud de vínculo familiar?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-lg transition-colors">
                                                    Rechazar
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <form action="{{ route('profile.family.leave', $membership->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de salir de esta familia? Si tenías clases pagadas por este apoderado, se transferirán a tu cuenta.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-lg transition-colors">
                                                Salir de la familia
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-zinc-400">
                                    <svg class="w-12 h-12 mx-auto text-zinc-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Ningún usuario te ha agregado como su familiar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- MODALES (Crear, Editar, Conflictos)                     --}}
        {{-- ======================================================= --}}
        <div x-show="openModal && !conflictType" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm" x-cloak x-transition>
            <div @click.away="openModal = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-6 border-b border-zinc-100 flex justify-between items-center">
                    <h3 class="text-xl font-black text-zinc-900" x-text="editMode ? 'Modificar Familiar' : 'Nuevo Familiar'"></h3>
                    <button @click="openModal = false" class="text-zinc-400 hover:text-zinc-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form :action="actionUrl" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">Nombre</label>
                            <input type="text" name="first_name" x-model="formData.first_name" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">Apellido</label>
                            <input type="text" name="last_name" x-model="formData.last_name" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">País del Documento</label>
                        <select name="country_id" x-model="formData.country_id" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all bg-white">
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-zinc-400 uppercase tracking-widest mb-1">RUT / Documento</label>
                        <input type="text" name="national_id" x-model="formData.national_id" placeholder="Ej: 12345678-9" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:ring-zinc-900 outline-none transition-all">
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
                    </div>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-zinc-800 transition-all mt-4">
                        <span x-text="editMode ? 'Guardar Cambios' : 'Registrar Familiar'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Conflicto 1: Transferencia --}}
        <div x-show="conflictType === 'transfer'" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm" x-cloak>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-6 border-b border-amber-100 bg-amber-50">
                    <h3 class="text-lg font-black text-amber-800 flex items-center gap-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg> Familiar ya registrado</h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-zinc-700"><strong x-text="conflictData?.dependent_name || 'Esta persona'"></strong> ya figura como familiar de <strong class="text-indigo-600" x-text="conflictData?.owner_name || 'otro usuario'"></strong>.</p>
                    <form action="{{ route('profile.family.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="confirmed" value="transfer">
                        <input type="hidden" name="owner_id" :value="conflictData?.owner_id">
                        <input type="hidden" name="first_name" :value="formData.first_name">
                        <input type="hidden" name="last_name" :value="formData.last_name">
                        <input type="hidden" name="country_id" :value="formData.country_id">
                        <input type="hidden" name="national_id" :value="formData.national_id">
                        <input type="hidden" name="relationship" :value="formData.relationship">
                        <button type="submit" class="w-full bg-amber-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-amber-700">Sí, solicitar transferencia</button>
                    </form>
                    <button @click="conflictType = ''; openModal = true" class="w-full bg-zinc-100 text-zinc-600 font-bold py-2.5 rounded-xl hover:bg-zinc-200">Cancelar</button>
                </div>
            </div>
        </div>

        {{-- Conflicto 2: Usuario Global --}}
        <div x-show="conflictType === 'link'" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm" x-cloak>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-6 border-b border-indigo-100 bg-indigo-50">
                    <h3 class="text-lg font-black text-indigo-800 flex items-center gap-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Usuario Independiente</h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-zinc-700">El documento ingresado pertenece a <strong class="text-indigo-600" x-text="conflictData?.user_name || 'un usuario'"></strong>, que ya tiene una cuenta propia.</p>
                    <form action="{{ route('profile.family.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="confirmed" value="link">
                        <input type="hidden" name="target_user_id" :value="conflictData?.user_id">
                        <input type="hidden" name="first_name" :value="formData.first_name">
                        <input type="hidden" name="last_name" :value="formData.last_name">
                        <input type="hidden" name="country_id" :value="formData.country_id">
                        <input type="hidden" name="national_id" :value="formData.national_id">
                        <input type="hidden" name="relationship" :value="formData.relationship">
                        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-indigo-700">Enviar solicitud de autorización</button>
                    </form>
                    <button @click="conflictType = ''; openModal = true" class="w-full bg-zinc-100 text-zinc-600 font-bold py-2.5 rounded-xl hover:bg-zinc-200">Cancelar</button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
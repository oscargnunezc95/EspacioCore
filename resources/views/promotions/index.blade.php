<x-app-layout>
    
    {{-- 1. NAVEGACIÓN DEL ESTUDIO --}}
    <x-studio-tabs />

    {{-- 2. EL RESTO DEL CONTENIDO --}}
    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Cabecera Unificada de Promociones --}}
        <div class="mt-2 mb-8 p-1"> {{-- p-1 evita recortes si aplicas rounded global --}}

            {{-- Breadcrumbs (Coherentes con el estilo de la plataforma) --}}
            <div class="flex text-xs font-bold text-zinc-500 mb-3 gap-2 items-center">
                <span class="text-zinc-900">Promociones</span>
            </div>

            {{-- Contenedor del Título y el Botón (Flex horizontal estricto) --}}
            <div class="flex flex-row items-center justify-between gap-4 w-full">
                
                {{-- Título (Ocupa el espacio disponible y trunca si es muy largo) --}}
                <h1 class="text-2xl md:text-3xl font-black text-zinc-900 truncate flex-1 min-w-0">
                    Promociones y Combos
                </h1>

                {{-- Botón Responsivo (shrink-0 y ml-auto forzan la posición a la derecha) --}}
                <button onclick="openPromoModal()" class="shrink-0 ml-auto bg-zinc-900 text-white px-3 sm:px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-zinc-800 focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2">
                    
                    {{-- Icono representativo (Etiqueta de precio con un Más integrado) --}}
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v6m-3-3h6"></path>
                    </svg>
                    
                    {{-- Texto que desaparece en móviles (hidden sm:inline) --}}
                    <span class="hidden sm:inline">Nueva Regla</span>
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        
        <div class="grid grid-cols-1 gap-6">
            @forelse($promotions as $promo)
                <div class="bg-white border border-zinc-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-zinc-300 transition-colors">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-black text-zinc-900">{{ $promo->name }}</h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider {{ $promo->type == 'specific_combo' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                                {{ $promo->type == 'specific_combo' ? 'Combo Específico' : 'Taller Adicional' }}
                            </span>
                        </div>
                        
                        @if($promo->type == 'specific_combo')
                            <p class="text-sm text-zinc-500 font-medium">
                                Mezcla de: 
                                <span class="text-zinc-800 font-bold">
                                    {{ $promo->workshopPrices->map(function($pack) {
                                        return $pack->workshop->name . ' (' . $pack->class_count . ' clases)';
                                    })->implode(' + ') }}
                                </span>
                            </p>
                            <p class="text-sm text-indigo-600 font-bold mt-1">Precio Fijo Total: ${{ number_format($promo->total_price, 0, ',', '.') }}</p>
                        @else
                            <p class="text-sm text-zinc-500 font-medium">Todo taller extra de <span class="font-bold text-zinc-800">{{ $promo->class_count }} clases/mes</span> cuesta:</p>
                            <p class="text-sm text-emerald-600 font-bold mt-1">Precio Unitario Extra: ${{ number_format($promo->additional_price, 0, ',', '.') }}</p>
                        @endif
                    </div>

                    {{-- BOTONES DE ACCIÓN (Editar y Eliminar) --}}
                    <div class="flex items-center gap-4 shrink-0">
                        @php
                            // Preparamos los datos limpios para inyectarlos en JS
                            $promoData = [
                                'id' => $promo->id,
                                'name' => $promo->name,
                                'type' => $promo->type,
                                'total_price' => $promo->total_price,
                                'class_count' => $promo->class_count,
                                'additional_price' => $promo->additional_price,
                                'prices' => $promo->type == 'specific_combo' ? $promo->workshopPrices->pluck('id')->toArray() : []
                            ];
                        @endphp
                        <button onclick='openEditPromoModal(@json($promoData))' class="text-sm font-bold text-zinc-500 hover:text-zinc-900 transition-colors">
                            Editar
                        </button>

                        <form action="{{ route('promotions.destroy', ['subdomain' => request()->route('subdomain'), 'promotion' => $promo->id]) }}" method="POST" class="m-0">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('¿Eliminar esta regla?')" class="text-sm font-bold text-rose-500 hover:text-rose-700 transition-colors">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-zinc-50 rounded-3xl border-2 border-dashed border-zinc-200 flex flex-col items-center justify-center">
                    <svg class="w-12 h-12 text-zinc-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <p class="text-zinc-500 font-bold text-lg">No has configurado reglas de descuento aún.</p>
                    <p class="text-zinc-400 text-sm mt-1">Crea combos específicos o aplica descuentos por talleres adicionales.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL (AHORA DINÁMICO PARA CREAR/EDITAR) --}}
    <div id="promoModal" onclick="if(event.target === this) closePromoModal()" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-lg w-full shadow-xl border border-zinc-100 my-auto transform transition-all">
            
            <div class="flex justify-between items-start mb-6 shrink-0">
                <div>
                    <h3 id="modalTitle" class="text-xl font-bold text-zinc-900">Nueva Regla de Descuento</h3>
                    <p class="text-xs text-zinc-500 mt-1 leading-tight">Configura precios especiales para incentivar más inscripciones.</p>
                </div>
                <button type="button" onclick="closePromoModal()" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="promoForm" action="{{ route('promotions.store', ['subdomain' => request()->route('subdomain')]) }}" method="POST">
                @csrf
                <div id="methodContainer"></div> {{-- Aquí inyectaremos el @method('PUT') con JS --}}
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre de la Promoción</label>
                        <input type="text" name="name" id="promoName" placeholder="Ej: Promo 2 Talleres de 4 Clases" required class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-3">Tipo de Regla</label>
                        <div class="grid grid-cols-2 gap-3 bg-zinc-100 p-1.5 rounded-xl">
                            <label class="cursor-pointer relative">
                                <input type="radio" name="type" value="specific_combo" id="typeCombo" checked onchange="togglePromoType()" class="peer sr-only">
                                <div class="text-center py-2.5 px-3 rounded-lg text-zinc-500 font-bold text-xs peer-checked:bg-white peer-checked:text-zinc-900 peer-checked:shadow-sm transition-all">Combo Específico</div>
                            </label>
                            <label class="cursor-pointer relative">
                                <input type="radio" name="type" value="additional_discount" id="typeAdditional" onchange="togglePromoType()" class="peer sr-only">
                                <div class="text-center py-2.5 px-3 rounded-lg text-zinc-500 font-bold text-xs peer-checked:bg-white peer-checked:text-zinc-900 peer-checked:shadow-sm transition-all">Taller Adicional</div>
                            </label>
                        </div>
                    </div>

                    {{-- CAMPOS DINÁMICOS PARA COMBO ESPECÍFICO --}}
                    <div id="section_combo" class="space-y-4">
                        <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-200">
                            <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-3">Selecciona los Packs del Combo</label>
                            <div class="max-h-56 overflow-y-auto space-y-4 custom-scrollbar pr-2">
                                @foreach($workshops as $w)
                                    @if($w->prices->count() > 0)
                                        <div>
                                            <p class="text-xs font-bold text-zinc-500 mb-2">{{ $w->name }}</p>
                                            <div class="space-y-1.5 pl-2 border-l-2 border-zinc-200">
                                                @foreach($w->prices as $price)
                                                    <label class="flex items-center gap-3 p-2.5 bg-white border border-zinc-200 rounded-lg cursor-pointer hover:border-zinc-400 transition-colors shadow-sm group">
                                                        <input type="checkbox" name="workshop_price_ids[]" value="{{ $price->id }}" class="promo-checkbox w-4 h-4 text-zinc-900 rounded border-zinc-300 focus:ring-zinc-900 cursor-pointer">
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-bold text-zinc-700 group-hover:text-zinc-900 transition-colors">
                                                                Pack {{ $price->class_count }} clases - ${{ number_format($price->price, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-zinc-700 mb-1.5">Precio Fijo Total ($)</label>
                            <input type="number" name="total_price" id="promoTotalPrice" placeholder="Ej: 35000" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none">
                        </div>
                    </div>

                    {{-- CAMPOS DINÁMICOS PARA DESCUENTO ADICIONAL --}}
                    <div id="section_additional" class="hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-zinc-700 mb-1.5">Aplica a packs de:</label>
                                <select name="class_count" id="promoClassCount" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none cursor-pointer">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($packTypes as $count)
                                        <option value="{{ $count }}">{{ $count }} clases / mes</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-zinc-700 mb-1.5">Costo Taller Extra ($)</label>
                                <input type="number" name="additional_price" id="promoAdditionalPrice" placeholder="Ej: 12000" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex gap-3 pt-4 border-t border-zinc-100">
                    <button type="button" onclick="closePromoModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 py-3 rounded-xl hover:bg-zinc-200 transition-all text-sm">Cancelar</button>
                    <button type="submit" id="btnSubmit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition-all active:scale-95 text-sm">Guardar Regla</button>
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
        const storeUrl = "{{ route('promotions.store', ['subdomain' => request()->route('subdomain')]) }}";
        const baseUrl = "{{ route('promotions.index', ['subdomain' => request()->route('subdomain')]) }}";

        function togglePromoType() {
            const type = document.querySelector('input[name="type"]:checked').value;
            const sectionCombo = document.getElementById('section_combo');
            const sectionAdditional = document.getElementById('section_additional');
            const totalPrice = document.getElementById('promoTotalPrice');

            if(type === 'specific_combo') {
                sectionCombo.classList.remove('hidden');
                sectionAdditional.classList.add('hidden');
                totalPrice.required = true;
            } else {
                sectionCombo.classList.add('hidden');
                sectionAdditional.classList.remove('hidden');
                totalPrice.required = false;
            }
        }

        // LÓGICA PARA CREAR
        function openPromoModal() {
            document.getElementById('modalTitle').innerText = 'Nueva Regla de Descuento';
            document.getElementById('btnSubmit').innerText = 'Crear Regla';
            document.getElementById('promoForm').action = storeUrl;
            document.getElementById('methodContainer').innerHTML = ''; // Limpiar PUT
            document.getElementById('promoForm').reset();
            
            // Limpiar checkboxes
            document.querySelectorAll('.promo-checkbox').forEach(cb => cb.checked = false);

            togglePromoType();
            document.body.style.overflow = 'hidden';
            document.getElementById('promoModal').classList.remove('hidden');
        }

        // LÓGICA PARA EDITAR
        function openEditPromoModal(promo) {
            document.getElementById('modalTitle').innerText = 'Editar Regla de Descuento';
            document.getElementById('btnSubmit').innerText = 'Actualizar Regla';
            
            // Cambiar la ruta hacia Update
            document.getElementById('promoForm').action = `${baseUrl}/${promo.id}`;
            
            // Inyectar el método PUT que Laravel necesita
            document.getElementById('methodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';

            // Llenar campos
            document.getElementById('promoName').value = promo.name;
            
            if(promo.type === 'specific_combo') {
                document.getElementById('typeCombo').checked = true;
                document.getElementById('promoTotalPrice').value = promo.total_price;
                
                // Marcar los checkboxes correctos
                document.querySelectorAll('.promo-checkbox').forEach(cb => {
                    cb.checked = promo.prices.includes(parseInt(cb.value));
                });
            } else {
                document.getElementById('typeAdditional').checked = true;
                document.getElementById('promoClassCount').value = promo.class_count;
                document.getElementById('promoAdditionalPrice').value = promo.additional_price;
            }

            togglePromoType();
            document.body.style.overflow = 'hidden';
            document.getElementById('promoModal').classList.remove('hidden');
        }

        function closePromoModal() {
            document.body.style.overflow = '';
            document.getElementById('promoModal').classList.add('hidden');
        }

        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                togglePromoType();
                document.body.style.overflow = 'hidden';
                document.getElementById('promoModal').classList.remove('hidden');
            });
        @endif
    </script>
</x-app-layout>
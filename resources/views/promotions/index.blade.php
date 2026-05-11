<x-app-layout>
    <x-slot name="header">
        <x-studio-tabs />
        <div class="mt-8">
            <x-studio-header title="Promociones y Combos" :breadcrumbs="[['name' => 'Promociones']]">
                <x-slot name="actions">
                    <button onclick="openPromoModal()" class="bg-zinc-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-zinc-800 transition-all active:scale-95 flex items-center gap-2">
                        + Nueva Regla de Precio
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
        
        <div class="grid grid-cols-1 gap-6">
            @forelse($promotions as $promo)
                <div class="bg-white border border-zinc-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
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

                    <form action="{{ route('promotions.destroy', ['subdomain' => request()->route('subdomain'), 'promotion' => $promo->id]) }}" method="POST" class="m-0">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('¿Eliminar esta regla?')" class="text-sm font-bold text-rose-400 hover:text-rose-600 transition-colors">Eliminar</button>
                    </form>
                </div>
            @empty
                <div class="text-center py-20 bg-zinc-50 rounded-3xl border-2 border-dashed border-zinc-200">
                    <p class="text-zinc-400 font-bold">No has configurado reglas de descuento aún.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL --}}
    <div id="promoModal" onclick="if(event.target === this) closePromoModal()" class="fixed inset-0 z-50 hidden flex items-start justify-center p-4 sm:p-6 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-lg w-full shadow-xl border border-zinc-100 my-auto transform transition-all">
            <h3 class="text-xl font-bold text-zinc-900 mb-6">Nueva Regla de Descuento</h3>
            
            <form action="{{ route('promotions.store', ['subdomain' => request()->route('subdomain')]) }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre de la Promoción</label>
                        <input type="text" name="name" placeholder="Ej: Promo 2 Talleres de 4 Clases" required class="w-full rounded-xl border-zinc-300 px-4 py-3 text-sm focus:ring-zinc-900 focus:border-zinc-900 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-3">Tipo de Regla</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="specific_combo" checked onchange="togglePromoType()" class="peer sr-only">
                                <div class="text-center p-3 rounded-xl border border-zinc-200 bg-zinc-50 text-zinc-500 font-bold text-xs peer-checked:bg-zinc-900 peer-checked:text-white peer-checked:border-zinc-900 transition-all">Combo Específico</div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="additional_discount" onchange="togglePromoType()" class="peer sr-only">
                                <div class="text-center p-3 rounded-xl border border-zinc-200 bg-zinc-50 text-zinc-500 font-bold text-xs peer-checked:bg-zinc-900 peer-checked:text-white peer-checked:border-zinc-900 transition-all">Taller Adicional</div>
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
                                                    <label class="flex items-center gap-3 p-2.5 bg-white border border-zinc-200 rounded-lg cursor-pointer hover:border-zinc-400 transition-colors shadow-sm">
                                                        <input type="checkbox" name="workshop_price_ids[]" value="{{ $price->id }}" class="w-4 h-4 text-zinc-900 rounded border-zinc-300 focus:ring-zinc-900">
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-bold text-zinc-700">
                                                                Pack {{ $price->class_count }} clases - ${{ number_format($price->price, 0, ',', '.') }}
                                                            </span>
                                                            @if($price->is_monthly)
                                                                <span class="text-[10px] text-emerald-600 font-bold tracking-wide uppercase">Aplica Regla Mensual</span>
                                                            @endif
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
                            <input type="number" name="total_price" placeholder="Ej: 35000" class="w-full rounded-xl border-zinc-300 px-4 py-3 text-sm focus:ring-zinc-900 outline-none" required>
                        </div>
                    </div>

                    {{-- CAMPOS DINÁMICOS PARA DESCUENTO ADICIONAL --}}
                    <div id="section_additional" class="hidden">
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 mb-4">
                            <p class="text-xs text-emerald-800 font-medium leading-relaxed">Esta regla aplicará a todos los talleres adicionales, siempre y cuando la alumna compre un paquete del mismo tamaño (cantidad de clases).</p>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-zinc-700 mb-1.5">Aplica a packs de:</label>
                                <select name="class_count" class="w-full rounded-xl border-zinc-300 px-4 py-3 text-sm focus:ring-zinc-900 outline-none cursor-pointer">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($packTypes as $count)
                                        <option value="{{ $count }}">{{ $count }} clases / mes</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-zinc-700 mb-1.5">Costo Taller Extra ($)</label>
                                <input type="number" name="additional_price" placeholder="Ej: 12000" class="w-full rounded-xl border-zinc-300 px-4 py-3 text-sm focus:ring-zinc-900 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex gap-3 pt-4 border-t border-zinc-100">
                    <button type="button" onclick="closePromoModal()" class="w-full font-bold text-zinc-600 bg-zinc-100 py-3 rounded-xl hover:bg-zinc-200 transition-all">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 transition-all active:scale-95">Guardar Regla</button>
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
        function togglePromoType() {
            const type = document.querySelector('input[name="type"]:checked').value;
            const sectionCombo = document.getElementById('section_combo');
            const sectionAdditional = document.getElementById('section_additional');

            if(type === 'specific_combo') {
                sectionCombo.classList.remove('hidden');
                sectionAdditional.classList.add('hidden');
            } else {
                sectionCombo.classList.add('hidden');
                sectionAdditional.classList.remove('hidden');
            }
        }

        function openPromoModal() {
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
                openPromoModal();
            });
        @endif
    </script>
</x-app-layout>
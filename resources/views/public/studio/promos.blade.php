{{-- ============================================================ --}}
{{-- Vista: Promos y Packs del Estudio                          --}}
{{-- Controlador: StudioPublicController@promos                 --}}
{{-- ============================================================ --}}
<x-guest-layout>
    <div class="min-h-screen bg-transparent relative z-10">

        @include('public.studio._studio-nav', ['activeTab' => 'promos'])

        <div class="w-full pt-8 pb-32 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 md:px-8">

                {{-- ========================================== --}}
                {{-- CONTENIDO: Promos y Packs                  --}}
                {{-- ========================================== --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($studio->promotions as $promo)
                        <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm hover:border-zinc-900 transition-colors flex flex-col relative overflow-hidden group">
                            <div class="absolute top-0 right-0 bg-zinc-900 text-white text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-bl-xl shadow-sm">
                                {{ $promo->type == 'specific_combo' ? 'Combo Especial' : 'Descuento Extra' }}
                            </div>
                            <h3 class="text-xl font-black text-stone-900 mt-2 mb-4">{{ $promo->name }}</h3>

                            @if($promo->type == 'specific_combo')
                                <p class="text-sm text-stone-600 font-medium mb-4">Inscríbete en estos talleres juntos y obtén un precio preferencial.</p>
                                <div class="space-y-2 mb-6 flex-1">
                                    @foreach($promo->workshopPrices as $pack)
                                        <div class="flex items-center gap-3 bg-stone-50 p-2.5 rounded-xl border border-stone-100">
                                            <div class="w-8 h-8 rounded-lg bg-white border border-stone-200 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-stone-900 text-sm leading-tight">{{ $pack->workshop->name }}</span>
                                                <span class="text-[10px] text-stone-500 font-bold uppercase tracking-wider">{{ $pack->class_count }} Clases</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-auto pt-4 border-t border-stone-100 flex items-end justify-between">
                                    <span class="text-xs font-bold uppercase tracking-widest text-stone-400 mb-1">Precio Total</span>
                                    <span class="text-3xl font-black text-stone-900 tracking-tighter">${{ number_format($promo->total_price, 0, ',', '.') }}</span>
                                </div>
                            @else
                                <div class="flex-1 flex flex-col justify-center items-center text-center py-4">
                                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mb-5 border border-emerald-100 shadow-inner">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </div>

                                    <p class="text-stone-600 text-sm px-4 leading-relaxed">
                                        Si estás tomando un plan de <span class="font-black text-stone-900">{{ $promo->class_count }} clases</span>, puedes sumar un pack equivalente ({{ $promo->class_count }} clases) de otro taller por este valor preferencial.
                                    </p>
                                </div>
                                <div class="mt-auto pt-4 border-t border-stone-100 flex items-end justify-between">
                                    <span class="text-xs font-bold uppercase tracking-widest text-stone-400 mb-1">Valor Taller Extra</span>
                                    <span class="text-3xl font-black text-stone-900 tracking-tighter">${{ number_format($promo->additional_price, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full text-center py-20 bg-stone-50 border border-stone-200 rounded-3xl">
                            <p class="text-stone-500 font-bold">No hay promociones especiales disponibles en este momento.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

        @include('public.studio._mini-cart')

    </div>
</x-guest-layout>

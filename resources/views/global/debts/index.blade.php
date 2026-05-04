<x-app-layout>
    <div class="py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-zinc-900 tracking-tight">Panel de Deudas</h1>
            <p class="mt-3 text-zinc-500 font-light text-lg">Revisa tus pagos pendientes y mantén tus cuentas al día.</p>
        </div>

        @php
            $hasDebts = false;
            foreach($studentProfiles as $profile) {
                foreach($profile->workshops as $workshop) {
                    if($workshop->pivot->credits_available < 0) {
                        $hasDebts = true;
                        break 2;
                    }
                }
            }
        @endphp

        @if($hasDebts)
            <div class="space-y-4">
                @foreach($studentProfiles as $profile)
                    @foreach($profile->workshops as $workshop)
                        @if($workshop->pivot->credits_available < 0)
                            <div class="bg-white border-l-4 border-rose-500 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-lg font-black text-zinc-900">{{ $workshop->name }}</h3>
                                        <span class="px-2 py-0.5 bg-zinc-100 text-zinc-600 text-[10px] font-bold rounded uppercase tracking-wider">
                                            {{ $profile->studio->name ?? 'Estudio' }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-zinc-500">Perfil registrado: {{ $profile->first_name }} {{ $profile->last_name }}</p>
                                </div>

                                <div class="flex items-center gap-6">
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-1">Clases Adeudadas</p>
                                        <p class="text-2xl font-black text-rose-600">{{ abs($workshop->pivot->credits_available) }}</p>
                                    </div>
                                    
                                    <a href="{{ route('dashboard', ['subdomain' => $profile->studio->subdomain ?? '']) }}" class="px-6 py-3 bg-zinc-900 text-white text-sm font-bold rounded-xl hover:bg-zinc-800 transition-colors">
                                        Regularizar
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        @else
            <!-- Estado Vacío: Sin Deudas -->
            <div class="bg-white rounded-3xl border border-zinc-200 py-24 px-6 text-center shadow-sm">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-50 border border-emerald-100 mb-6">
                    <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900 mb-2">¡Todo al día!</h3>
                <p class="text-zinc-500 max-w-md mx-auto text-sm leading-relaxed mb-8">
                    Actualmente no tienes deudas ni pagos pendientes registrados en ninguno de tus estudios. Tus cuentas están impecables.
                </p>
                <a href="{{ route('studios.index') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-zinc-900 hover:bg-zinc-800 transition-colors duration-200 active:scale-95 shadow-sm">
                    Ir a mis estudios
                </a>
            </div>
        @endif

    </div>
</x-app-layout>
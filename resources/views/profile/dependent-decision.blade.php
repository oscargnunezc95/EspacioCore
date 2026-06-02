<x-app-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-zinc-50">
        <div class="max-w-lg w-full space-y-8">
            
            {{-- Icono --}}
            <div class="text-center">
                <div class="mx-auto w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                
                <h2 class="text-2xl font-black text-zinc-900 tracking-tight">
                    ¡Ya tenías un perfil familiar!
                </h2>
                <p class="mt-3 text-zinc-500 font-medium">
                    <strong class="text-indigo-600">{{ $ownerName }}</strong> te tenía registrada/o como familiar en su cuenta.
                </p>
            </div>

            {{-- Opciones --}}
            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6 space-y-4">

                {{-- Opción 1: Independizarse --}}
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <h3 class="font-bold text-emerald-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Opción 1 — Independizarme
                    </h3>
                    <p class="text-xs text-emerald-700 mt-1.5">
                        Transfiere <strong>todas tus clases</strong> a tu nueva cuenta y 
                        <strong>te desvincula</strong> del grupo familiar de {{ $ownerName }}.
                        Dejas de ser su dependiente y gestionas todo por tu cuenta.
                    </p>

                    <form action="{{ route('profile.dependent.unlink') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-2.5 rounded-xl hover:bg-emerald-700 transition-all active:scale-[0.98] text-sm">
                            Transferir mis clases y desvincularme
                        </button>
                    </form>
                </div>

                {{-- Opción 2: Mantener vínculo --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <h3 class="font-bold text-blue-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>
                        Opción 2 — Mantener el vínculo familiar
                    </h3>
                    <p class="text-xs text-blue-700 mt-1.5">
                        <strong>Sigues siendo familiar</strong> de {{ $ownerName }}.
                        No verás sus clases ni las tuyas anteriores — tu cuenta empieza limpia.
                        {{ $ownerName }} podrá seguir inscribiéndote como dependiente.
                    </p>

                    <form action="{{ route('profile.dependent.share') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-xl hover:bg-blue-700 transition-all active:scale-[0.98] text-sm">
                            Mantener el vínculo familiar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

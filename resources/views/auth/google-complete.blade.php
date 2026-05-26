<x-guest-layout>
<div class="flex flex-col justify-center min-h-screen py-8 bg-zinc-50 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-3xl font-bold tracking-tight text-center text-zinc-900">
            Casi listo
        </h2>
        <p class="mt-2 text-sm text-center text-zinc-500 font-medium">
            Solo necesitamos un par de datos más para conectar tu cuenta.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="px-8 py-10 bg-white border border-zinc-200 shadow-sm rounded-2xl">
            <form action="{{ route('auth.google.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-zinc-400">Correo vinculado</label>
                    <div class="mt-1.5">
                        <input type="text" disabled value="{{ session('google_user_data')['email'] ?? auth()->user()->email }}"
                            class="block w-full px-4 py-3 bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-400 sm:text-sm cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label for="country_id" class="block text-sm font-bold text-zinc-700">País</label>
                    <div class="mt-1.5">
                        <select id="country_id" name="country_id" required
                            class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 focus:outline-none focus:bg-white focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900 transition-all duration-200 sm:text-sm">
                            <option value="">Selecciona tu país</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="national_id" class="block text-sm font-bold text-zinc-700">Documento de Identidad (RUT)</label>
                    <p class="text-xs text-zinc-500 mb-1.5 mt-0.5 font-medium">Usa el mismo RUT que entregaste en tu estudio.</p>
                    <div class="mt-1.5">
                        <input id="national_id" name="national_id" type="text" required placeholder="Ej: 12.345.678-9"
                            class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900 transition-all duration-200 sm:text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="flex w-full justify-center px-4 py-3 text-sm font-bold text-white bg-zinc-900 rounded-xl hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 active:scale-[0.98] transition-all duration-200 shadow-sm">
                        Finalizar y entrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-guest-layout>
<section>
    <header>
        <h2 class="text-xl sm:text-2xl font-black text-stone-900">
            Información del Perfil
        </h2>
        <p class="mt-1 text-sm font-medium text-stone-500">
            Actualiza la información de tu cuenta y dirección de correo electrónico.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('patch')

        {{-- RUT (Solo lectura) --}}
        <div>
            <label class="block text-sm font-bold text-stone-700 mb-1.5">RUT / Documento de Identidad</label>
            <input type="text" value="{{ $user->formatted_national_id ?: 'No registrado' }}" disabled
                   class="w-full rounded-xl border border-stone-200 bg-stone-100 px-4 py-3 text-sm text-stone-500 cursor-not-allowed shadow-inner font-medium">
            <p class="text-[10px] font-bold uppercase tracking-widest text-stone-400 mt-1.5">El documento de identidad no puede modificarse por seguridad.</p>
        </div>

        {{-- Nombre --}}
        <div>
            <label for="name" class="block text-sm font-bold text-stone-700 mb-1.5">Nombre Completo</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all outline-none">
            @error('name') <p class="text-xs text-rose-500 font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-bold text-stone-700 mb-1.5">Correo Electrónico</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all outline-none">
            @error('email') <p class="text-xs text-rose-500 font-bold mt-1.5">{{ $message }}</p> @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 p-4 bg-amber-50 rounded-xl border border-amber-200">
                    <p class="text-sm text-amber-800 font-medium">
                        Tu dirección de correo electrónico no está verificada.
                        <button form="send-verification" class="underline font-bold text-amber-900 hover:text-amber-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 mt-1 block">
                            Haz clic aquí para reenviar el correo de verificación.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 font-bold text-sm text-emerald-600">
                            Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-6 border-t border-stone-100">
            <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all duration-300 active:scale-95 text-sm">
                Guardar Cambios
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-sm font-bold text-emerald-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Guardado
                </p>
            @endif
        </div>
    </form>
</section>
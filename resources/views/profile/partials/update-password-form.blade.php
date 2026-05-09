<section>
    <header>
        <h2 class="text-2xl font-black text-zinc-900">
            Actualizar Contraseña
        </h2>

        <p class="mt-1 text-sm font-medium text-zinc-500">
            Asegúrate de que tu cuenta use una contraseña larga y aleatoria para mantenerte segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-bold text-zinc-700 mb-1.5">Contraseña Actual</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none">
            @error('current_password', 'updatePassword') <p class="text-xs text-rose-500 font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-bold text-zinc-700 mb-1.5">Nueva Contraseña</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none">
            @error('password', 'updatePassword') <p class="text-xs text-rose-500 font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-bold text-zinc-700 mb-1.5">Confirmar Contraseña</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-all outline-none">
            @error('password_confirmation', 'updatePassword') <p class="text-xs text-rose-500 font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-zinc-100">
            <button type="submit" class="bg-zinc-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm hover:bg-zinc-800 transition-all duration-200 active:scale-95 text-sm">
                Guardar Contraseña
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-sm font-bold text-emerald-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Contraseña actualizada.
                </p>
            @endif
        </div>
    </form>
</section>
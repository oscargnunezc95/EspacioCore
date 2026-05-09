<section class="space-y-6">
    <header>
        <h2 class="text-2xl font-black text-rose-600">
            Eliminar Cuenta
        </h2>

        <p class="mt-1 text-sm font-medium text-zinc-600">
            Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar tu cuenta, por favor descarga cualquier dato o información que desees conservar.
        </p>
    </header>

    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" 
            class="bg-rose-600 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm hover:bg-rose-700 transition-all duration-200 active:scale-95 text-sm">
        Eliminar Cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8">
            @csrf
            @method('delete')

            <h2 class="text-xl font-black text-zinc-900">
                ¿Estás segura de que quieres eliminar tu cuenta?
            </h2>

            <p class="mt-2 text-sm font-medium text-zinc-500">
                Una vez que se elimine tu cuenta, todos sus recursos y datos se perderán de forma irreversible. Por favor, ingresa tu contraseña para confirmar que deseas eliminar tu cuenta permanentemente.
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">Contraseña</label>
                <input id="password" name="password" type="password" placeholder="Tu contraseña"
                       class="w-full sm:w-3/4 rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all outline-none">
                
                @error('password', 'userDeletion') <p class="text-xs text-rose-500 font-bold mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-zinc-100">
                <button type="button" x-on:click="$dispatch('close')" 
                        class="bg-zinc-100 text-zinc-600 font-bold py-2.5 px-6 rounded-xl hover:bg-zinc-200 transition-colors duration-200 text-sm">
                    Cancelar
                </button>

                <button type="submit" 
                        class="bg-rose-600 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm hover:bg-rose-700 transition-all duration-200 active:scale-95 text-sm">
                    Eliminar Cuenta
                </button>
            </div>
        </form>
    </x-modal>
</section>
<section class="space-y-6">
    <header>
        <h2 class="text-xl sm:text-2xl font-black text-rose-600">
            Eliminar Cuenta
        </h2>
        <p class="mt-1 text-sm font-medium text-stone-600">
            Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar tu cuenta, por favor descarga cualquier dato o información que desees conservar.
        </p>
    </header>

    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="w-full sm:w-auto border border-red-500 text-red-600 hover:bg-red-50 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95">
        Eliminar Cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="text-lg sm:text-xl font-black text-stone-900 leading-tight">
                ¿Estás segura de que quieres eliminar tu cuenta?
            </h2>

            <p class="mt-3 text-sm font-medium text-stone-500 leading-relaxed">
                Una vez que se elimine tu cuenta, todos sus recursos y datos se perderán de forma irreversible. Por favor, ingresa tu contraseña para confirmar que deseas eliminar tu cuenta permanentemente.
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">Contraseña</label>
                <input id="password" name="password" type="password" placeholder="Tu contraseña"
                       class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all outline-none">

                @error('password', 'userDeletion')
                    <p class="text-xs text-rose-500 font-bold mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Botones responsivos: apilados en móvil, alineados a la derecha en PC --}}
            <div class="mt-8 flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-stone-100">
                <button type="button" x-on:click="$dispatch('close')"
                        class="w-full sm:w-auto bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95 text-sm text-center">
                    Cancelar
                </button>

                <button type="submit"
                        class="w-full sm:w-auto border border-red-500 text-red-600 hover:bg-red-50 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95 text-sm text-center">
                    Confirmar Eliminación
                </button>
            </div>
        </form>
    </x-modal>
</section>
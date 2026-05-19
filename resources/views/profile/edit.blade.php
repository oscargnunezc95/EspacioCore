<x-app-layout>
    {{-- Restringimos a max-w-4xl para que los formularios no se estiren de forma grotesca en PC --}}
    <div class="py-8 md:py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        
        <div class="text-center mb-10 md:mb-14">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Mi Perfil</h1>
            <p class="mt-3 text-zinc-500 font-medium text-base md:text-lg">Gestiona tu información personal y la seguridad de tu cuenta.</p>
        </div>

        {{-- CONTENEDOR DE SEPARACIÓN (Evita el amontonamiento) --}}
        <div class="space-y-8 md:space-y-12">
            
            {{-- Tarjeta 1: Información --}}
            <div class="p-6 sm:p-10 bg-white shadow-sm rounded-3xl border border-zinc-200">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Tarjeta 2: Contraseña --}}
            <div class="p-6 sm:p-10 bg-white shadow-sm rounded-3xl border border-zinc-200">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Tarjeta 3: Eliminar Cuenta --}}
            <div class="p-6 sm:p-10 bg-rose-50/30 shadow-sm rounded-3xl border border-rose-100">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
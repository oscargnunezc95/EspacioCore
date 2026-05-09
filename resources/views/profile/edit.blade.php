<x-app-layout>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-24">
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-zinc-900 tracking-tight">Mi Perfil</h1>
            <p class="mt-3 text-zinc-500 font-light text-lg">Gestiona tu información personal y la seguridad de tu cuenta.</p>
        </div>
        <div class="py-6 w-7xl sm:p-10 bg-white shadow-sm rounded-3xl border border-zinc-200">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="py-6 w-7xl sm:p-10 bg-white shadow-sm rounded-3xl border border-zinc-200">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="py-6 w-7xl sm:p-10 bg-rose-50/30 shadow-sm rounded-3xl border border-rose-100">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
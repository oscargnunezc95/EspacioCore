<x-guest-layout
    metaTitle="Soporte &mdash; EstadoPrisma"
    metaDescription="¿Tienes dudas? Contáctanos o agenda una demo gratuita por videollamada."
>
<div class="py-8 md:py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">


        {{-- Cabecera --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">
                ¿Cómo podemos ayudarte?
            </h1>
            <p class="mt-3 text-zinc-500 font-medium">
                Escríbenos tu consulta o agenda una videollamada gratuita para conocer la plataforma.
            </p>
        </div>

        {{-- Mensaje de éxito --}}
        @if (session('success'))
            <div class="mb-8 p-5 rounded-2xl bg-emerald-50 border border-emerald-100 flex gap-3 items-start">
                <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Errores de validación --}}
        @if ($errors->any())
            <div class="mb-8 p-5 rounded-2xl bg-rose-50 border border-rose-100 flex gap-3">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <ul class="text-sm text-rose-700 font-medium list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tarjeta principal --}}
        <div class="bg-white rounded-2xl border border-zinc-200/60 shadow-sm overflow-hidden"
             x-data="{
                type: 'inquiry',
                meetingDate: '',
                minDate: '',
                init() {
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    this.minDate = tomorrow.toISOString().split('T')[0];
                },
                get isInquiry() { return this.type === 'inquiry'; },
                get isDemo() { return this.type === 'demo'; },
             }">

            {{-- Toggle de tipo de solicitud --}}
            <div class="grid grid-cols-2 bg-zinc-100/80 p-1.5 m-4 rounded-xl">
                <button type="button"
                        x-on:click="type = 'inquiry'"
                        :class="isInquiry
                            ? 'bg-white text-zinc-900 shadow-sm'
                            : 'text-zinc-500 hover:text-zinc-700'"
                        class="py-3 px-4 text-sm font-bold rounded-lg transition-all duration-200">
                    📩 Consulta
                </button>
                <button type="button"
                        x-on:click="type = 'demo'"
                        :class="isDemo
                            ? 'bg-white text-indigo-600 shadow-sm'
                            : 'text-zinc-500 hover:text-zinc-700'"
                        class="py-3 px-4 text-sm font-bold rounded-lg transition-all duration-200">
                    🎥 Agendar Demo
                </button>
            </div>

            {{-- Formulario --}}
            <form action="{{ route('support.store') }}" method="POST" class="px-6 pb-6 space-y-5">
                @csrf
                <input type="hidden" name="type" x-model="type">

                {{-- Nombre --}}
                <div>
                    <label for="name" class="block text-sm font-bold text-zinc-700 mb-1.5">
                        Nombre completo
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name"
                           class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 sm:text-sm"
                           placeholder="María González">
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-bold text-zinc-700 mb-1.5">
                        Correo electrónico
                    </label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                           class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 sm:text-sm"
                           placeholder="maria@ejemplo.com">
                </div>

                {{-- Mensaje (solo consulta) --}}
                <div x-show="isInquiry" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                    <label for="message" class="block text-sm font-bold text-zinc-700 mb-1.5">
                        Tu mensaje
                    </label>
                    <textarea id="message" name="message" rows="4"
                              :disabled="!isInquiry"
                              class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 sm:text-sm resize-none disabled:opacity-50 disabled:cursor-not-allowed"
                              placeholder="Cuéntanos cómo podemos ayudarte...">{{ old('message') }}</textarea>
                </div>

                {{-- Campos de demo (solo videollamada) --}}
                <div x-show="isDemo" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">
                    {{-- Fecha --}}
                    <div>
                        <label for="meeting_date" class="block text-sm font-bold text-zinc-700 mb-1.5">
                            Fecha de la reunión
                        </label>
                        <input id="meeting_date" name="meeting_date" type="date"
                               :disabled="!isDemo"
                               :min="minDate"
                               x-model="meetingDate"
                               value="{{ old('meeting_date') }}"
                               class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <p class="mt-1.5 text-xs text-zinc-400 font-medium">Selecciona a partir de mañana.</p>
                    </div>

                    {{-- Hora --}}
                    <div>
                        <label for="meeting_time" class="block text-sm font-bold text-zinc-700 mb-1.5">
                            Hora de la reunión
                        </label>
                        <select id="meeting_time" name="meeting_time"
                                :disabled="!isDemo"
                                class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Selecciona una hora</option>
                            @foreach (range(9, 22) as $h)
                                <option value="{{ sprintf('%02d:00', $h) }}" {{ old('meeting_time') === sprintf('%02d:00', $h) ? 'selected' : '' }}>
                                    {{ sprintf('%02d:00', $h) }} hrs
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-zinc-400 font-medium">Horario de Chile continental (GMT-4).</p>
                    </div>

                    {{-- Mensaje opcional para demo --}}
                    <div>
                        <label for="demo_message" class="block text-sm font-bold text-zinc-700 mb-1.5">
                            Mensaje <span class="text-zinc-400 font-normal">(opcional)</span>
                        </label>
                        <textarea id="demo_message" name="message" rows="3"
                                  :disabled="!isDemo"
                                  class="block w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-zinc-900 placeholder-zinc-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 sm:text-sm resize-none disabled:opacity-50 disabled:cursor-not-allowed"
                                  placeholder="¿Qué te gustaría ver en la demo?">{{ old('message') }}</textarea>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                            x-text="isInquiry ? 'Enviar consulta' : 'Agendar videollamada gratuita'"
                            :class="isInquiry
                                ? 'bg-zinc-900 hover:bg-zinc-800 focus:ring-zinc-900 text-white'
                                : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-600 text-white'"
                            class="flex w-full justify-center px-6 py-3.5 text-sm font-bold rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-[0.98] transition-all duration-200 shadow-sm">
                    </button>
                </div>
            </form>
        </div>

        {{-- Footer sutil --}}
        <p class="mt-8 text-center text-xs text-zinc-400 font-medium">
            También puedes escribirnos directamente a <a href="mailto:oscar@estadoprisma.test" class="text-indigo-600 hover:text-indigo-500 transition-colors duration-200">oscar@estadoprisma.test</a>
        </p>

</div>
</x-guest-layout>

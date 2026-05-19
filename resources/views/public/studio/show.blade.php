<x-guest-layout>
    {{-- Cambio 1: Lógica de detección de filtros en el estado inicial --}}
    <div x-data="{ 
            activeTab: (window.location.hash === '#calendario' || window.location.search.includes('day=') || window.location.search.includes('workshop=')) ? 'clases' : 'perfil',
            pendingCount: 0 
         }" 
         class="min-h-screen bg-white">
        
        @include('public.studio.partials.hero')

        {{-- Aquí se llama al partial que te pongo abajo --}}
        @include('public.studio.partials.tabs')

        <div class="max-w-7xl mx-auto px-4 md:px-8 pt-8 pb-32 min-h-screen">
            <div x-show="activeTab === 'perfil'" x-cloak x-transition:enter="transition ease-out duration-200">
                @include('public.studio.partials.tab-perfil')
            </div>

            <div x-show="activeTab === 'talleres'" x-cloak x-transition:enter="transition ease-out duration-200">
                @include('public.studio.partials.tab-talleres')
            </div>

            <div x-show="activeTab === 'promos'" x-cloak x-transition:enter="transition ease-out duration-200">
                @include('public.studio.partials.tab-promos')
            </div>

            <div x-show="activeTab === 'clases'" x-cloak x-transition:enter="transition ease-out duration-200">
                @include('public.studio.partials.tab-calendario')
            </div>
        </div>

        {{-- BARRA FLOTANTE --}}
        <div id="floating-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 translate-y-24 opacity-0 transition-all duration-300 z-50 pointer-events-none">
            <div class="bg-zinc-900 text-white px-6 py-4 rounded-full shadow-2xl flex items-center gap-6 border border-zinc-700">
                <div class="flex items-center gap-3">
                    <span id="selected-count" class="bg-emerald-500 text-white font-black text-sm w-7 h-7 flex items-center justify-center rounded-full shadow-inner">0</span>
                    <span class="font-bold text-sm">Clases seleccionadas</span>
                </div>
                <button onclick="confirmReservations()" id="floating-confirm-btn" class="bg-emerald-500 hover:bg-emerald-400 text-white px-5 py-2.5 rounded-full font-bold text-sm transition-colors active:scale-95 flex items-center gap-2 pointer-events-auto shadow-sm">
                    Confirmar Reservas
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- LÓGICA JAVASCRIPT (Filtros y Carrito) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const params = new URLSearchParams(new FormData(this));
                    window.location.href = this.action + '?' + params.toString() + '#calendario';
                });
            }
        });

        let pendingClasses = new Set();
        const isLoggedIn = @json(auth()->check());

        function toggleSelection(sessionId, btnElement) {
            const initialState = btnElement.getAttribute('data-initial-state');
            if (pendingClasses.has(sessionId)) {
                pendingClasses.delete(sessionId);
                resetButtonStyles(btnElement, initialState);
            } else {
                pendingClasses.add(sessionId);
                setButtonSelectedStyles(btnElement, initialState);
            }
            toggleFloatingBar();
        }

        function setButtonSelectedStyles(btn, initialState) {
            if (initialState === 'enrolled') {
                btn.className = "interest-btn w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-black transition-all bg-rose-50 text-rose-600 border border-rose-200";
                btn.innerHTML = `<svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover`;
            } else {
                btn.className = "interest-btn w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-black transition-all bg-emerald-500 text-white border border-emerald-600";
                btn.innerHTML = `<svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Seleccionada`;
            }
        }

        function resetButtonStyles(btn, initialState) {
            if (initialState === 'enrolled') {
                btn.className = "interest-btn w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-black transition-all bg-emerald-500 text-white border border-emerald-600";
                btn.innerHTML = `En mi Portal`;
            } else {
                btn.className = "interest-btn w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-black transition-all bg-zinc-100 text-zinc-900";
                btn.innerHTML = `Reservar Clase`;
            }
        }

        function toggleFloatingBar() {
            const bar = document.getElementById('floating-bar');
            const countLabel = document.getElementById('selected-count');
            const count = pendingClasses.size;
            countLabel.innerText = count;
            if (count > 0) {
                bar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                bar.classList.add('translate-y-0', 'opacity-100');
            } else {
                bar.classList.remove('translate-y-0', 'opacity-100');
                bar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
            }
        }

        async function confirmReservations() {
            if (!isLoggedIn) {
                localStorage.setItem('estadoprisma_cart', JSON.stringify(Array.from(pendingClasses)));
                window.location.href = "{{ route('register') }}"; 
                return;
            }
            const btn = document.getElementById('floating-confirm-btn');
            btn.innerHTML = `Procesando...`;
            btn.disabled = true;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch("/global/student/enroll/bulk", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ session_ids: Array.from(pendingClasses) })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) { 
                    alert(data.message); 
                    btn.innerHTML = `Confirmar Reservas`;
                    btn.disabled = false;
                } else {
                    sessionStorage.setItem('cart_auto_open', 'true');
                    window.location.href = "{{ route('cart.index') }}";
                }
            })
            .catch(error => {
                btn.innerHTML = `Confirmar Reservas`;
                btn.disabled = false;
            });
        }
    </script>
    {{-- Asegúrate de tener esto al final de show.blade.php --}}
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.key') }}"></script>
</x-guest-layout>
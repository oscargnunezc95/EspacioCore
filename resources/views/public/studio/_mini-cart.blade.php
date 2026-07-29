@auth
<div class="fixed bottom-6 right-6 z-[60]">
    <div id="miniCartPanel" class="hidden absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-red-100 overflow-hidden transition-all transform origin-bottom-right opacity-0 scale-95">
        <div class="p-5 bg-gradient-to-r from-red-600 to-rose-700 text-white flex justify-between items-center">
            <div>
                <h4 class="font-black text-lg leading-none">Tus Reservas</h4>
                <p class="text-xs text-red-200 mt-1">Pendientes de pago</p>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->pending_reservations_count > 0)
                    <span class="text-sm bg-rose-400 text-white shadow-inner px-3 py-1 rounded-full font-black">
                        {{ auth()->user()->pending_reservations_count }}
                    </span>
                @endif
                <button onclick="toggleMiniCart()" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            </div>
        </div>

        <div class="p-5 max-h-64 overflow-y-auto custom-scrollbar">
            @if(auth()->user()->pending_reservations_count > 0)
                <p class="text-sm text-stone-500 mb-4 leading-relaxed">
                    Tienes cupos reservados que aún no han sido pagados.
                    <strong class="text-stone-800">Asegura tu lugar antes de que se llenen los cupos.</strong>
                </p>
                <div class="bg-amber-50 border border-amber-100 p-3.5 rounded-2xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs text-amber-800 font-medium leading-relaxed">Agregar las clases a Mis Reservas hará que las puedas ver en tu portal de estudiante. Sin embargo, el cupo solo se asegurará al completar el pago.</p>
                </div>
            @else
                <div class="text-center py-6">
                    <span class="text-4xl block mb-3">🛒</span>
                    <p class="text-sm font-bold text-stone-400">No tienes reservas pendientes.</p>
                </div>
            @endif
        </div>

        <div class="p-4 border-t border-stone-100 bg-stone-50/50">
            <a href="{{ route('cart.index') }}"
                class="w-full {{ auth()->user()->pending_reservations_count > 0 ? 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 shadow-lg shadow-red-200' : 'bg-stone-200 pointer-events-none' }} text-white font-bold py-3.5 rounded-2xl transition-all duration-300 active:scale-95 text-sm flex items-center justify-center gap-2">
                Ir a Pagar Mis Clases
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
    </div>

    <button onclick="toggleMiniCart()" id="btnMiniCart"
        class="relative bg-gradient-to-br from-red-600 to-rose-700 text-white p-4 rounded-full shadow-[0_10px_40px_-10px_rgba(239,68,68,0.5)] hover:shadow-[0_15px_50px_-10px_rgba(239,68,68,0.7)] hover:scale-110 transition-all duration-300 active:scale-95 border border-white/10 focus:outline-none focus:ring-4 focus:ring-red-300/50 group">
        <svg class="w-6 h-6 transform group-hover:-rotate-12 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        @if(auth()->user()->pending_reservations_count > 0)
            <span class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-rose-400 border-2 border-white text-[11px] font-black text-white shadow-md animate-pulse">
                {{ auth()->user()->pending_reservations_count }}
            </span>
        @endif
    </button>
</div>

<script>
    function toggleMiniCart() {
        const panel = document.getElementById('miniCartPanel');
        if (!panel) return;
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            setTimeout(() => {
                panel.classList.remove('opacity-0', 'scale-95');
                panel.classList.add('opacity-100', 'scale-100');
            }, 10);
        } else {
            panel.classList.remove('opacity-100', 'scale-100');
            panel.classList.add('opacity-0', 'scale-95');
            setTimeout(() => { panel.classList.add('hidden'); }, 300);
        }
    }

    document.addEventListener('click', function(event) {
        const panel = document.getElementById('miniCartPanel');
        const btn = document.getElementById('btnMiniCart');
        if (panel && btn && !panel.classList.contains('hidden') && !panel.contains(event.target) && !btn.contains(event.target)) {
            toggleMiniCart();
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        if (sessionStorage.getItem('cart_auto_open') === 'true') {
            sessionStorage.removeItem('cart_auto_open');
            setTimeout(() => { if (document.getElementById('miniCartPanel')) toggleMiniCart(); }, 600);
        }
    });
</script>
@endauth
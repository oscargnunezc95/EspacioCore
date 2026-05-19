<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-24"> 
        
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-zinc-900 tracking-tight">Mi Portal de Pagos</h1>
            <p class="mt-4 text-zinc-500 text-lg">Selecciona las clases que deseas confirmar. La promoción o pack genererá el descuento automáticamente al seleccionar las clases. Has click en Ver Ofertas para ver las promociones.</p>
        </div>

        {{-- CONTENEDOR DE ESTUDIOS --}}
        <div id="classes-container">
            @auth
                @if($groupedSessions->isEmpty())
                    <div class="py-12 text-center border-2 border-dashed border-zinc-200 rounded-3xl">
                        <p class="text-zinc-500 font-medium">No tienes clases pendientes de pago.</p>
                        <a href="{{ route('explore') }}" class="inline-block mt-4 text-indigo-600 font-bold hover:underline">Explorar Catálogo</a>
                    </div>
                @else
                    @include('cart.partials.studio-groups', ['groupedSessions' => $groupedSessions])
                @endif
            @endauth
        </div>

    </div>

    {{-- MODAL DE GUEST CHECKOUT --}}
    <div id="guestCheckoutModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-md w-full shadow-xl border border-zinc-100 transform transition-all">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-black text-zinc-900">Datos de tu Reserva</h3>
                    <p class="text-xs text-zinc-500 mt-1 leading-tight">Ingresa tus datos para asegurar tu cupo antes de pagar.</p>
                </div>
                <button type="button" onclick="document.getElementById('guestCheckoutModal').classList.add('hidden')" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="guestPaymentForm" action="/payments/checkout" method="POST">
                @csrf
                <input type="hidden" name="studio_id" id="guest_studio_id">
                <input type="hidden" name="session_ids" id="guest_session_ids">
                <input type="hidden" name="is_guest_checkout" value="1">
                
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Nombre Completo</label>
                        <input type="text" name="guest_name" required placeholder="Ej: Camila Rojas" 
                               class="w-full rounded-xl border border-zinc-300 p-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-zinc-700 mb-1.5">Correo Electrónico</label>
                        <input type="email" name="guest_email" required placeholder="camila@ejemplo.com" 
                               class="w-full rounded-xl border border-zinc-300 p-3 text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition-all">
                        <p class="text-[10px] text-zinc-500 mt-1.5">A este correo te enviaremos el comprobante y el acceso a tu clase.</p>
                    </div>
                </div>

                <div class="flex gap-3 pt-2 border-t border-zinc-100">
                    <button type="button" onclick="document.getElementById('guestCheckoutModal').classList.add('hidden')" class="w-full font-bold text-zinc-600 bg-zinc-100 hover:bg-zinc-200 py-3 rounded-xl transition-colors duration-200 text-sm">Cancelar</button>
                    <button type="submit" class="w-full bg-zinc-900 text-white font-bold py-3 rounded-xl shadow-sm hover:bg-zinc-800 focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-all duration-200 active:scale-95 text-sm flex items-center justify-center gap-2">
                        Ir a Pagar
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ==========================================
        // VARIABLES GLOBALES
        // ==========================================
        const isLoggedIn = @json(Auth::check());

        // ==========================================
        // 1. INICIALIZACIÓN
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            if (!isLoggedIn) {
                loadGuestCart();
            }
            updateBadge(); 
        });

        // ==========================================
        // 2. GLOBO DE NOTIFICACIÓN (BADGE)
        // ==========================================
        function updateBadge() {
            const badge = document.getElementById('portal-badge');
            if (!badge) return;

            let count = 0;
            if (isLoggedIn) {
                count = parseInt(badge.innerText) || 0;
            } else {
                const cart = JSON.parse(localStorage.getItem('estadoprisma_cart')) || [];
                count = cart.length; 
            }

            badge.innerText = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }

        // ==========================================
        // 3. CARRITO DE INVITADOS (AJAX)
        // ==========================================
        function loadGuestCart() {
            const container = document.getElementById('classes-container');
            const cartIds = JSON.parse(localStorage.getItem('estadoprisma_cart')) || [];
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');

            if (cartIds.length === 0) {
                container.innerHTML = `
                    <div class="py-12 text-center border-2 border-dashed border-zinc-200 rounded-3xl">
                        <p class="text-zinc-500 font-medium">Tu carrito está vacío.</p>
                        <a href="/explorar" class="inline-block mt-4 text-indigo-600 font-bold hover:underline">Explorar Catálogo</a>
                    </div>`;
                return;
            }

            if (!tokenMeta) return;

            container.innerHTML = `<div class="text-center py-10 text-zinc-400 font-bold animate-pulse">Calculando tus clases...</div>`;

            fetch('/api/cart/guest-sessions', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': tokenMeta.getAttribute('content'), 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ ids: cartIds })
            })
            .then(res => res.json())
            .then(data => {
                if(data.html) {
                    container.innerHTML = data.html;
                } else {
                    container.innerHTML = `<div class="py-12 text-center text-rose-500 font-medium">No se pudieron cargar las clases.</div>`;
                }
            })
            .catch(error => {
                console.error("Error al cargar carrito:", error);
                container.innerHTML = `<div class="py-12 text-center text-rose-500 font-medium">Ocurrió un error. Intenta recargar la página.</div>`;
            });
        }

        // ==========================================
        // 4. ELIMINAR CLASES DEL CARRITO (Orquestador)
        // ==========================================
        function removeCartItem(sessionId, btnElement) {
            // ADVERTENCIA DE SEGURIDAD (UX)
            if (!confirm('¿Estás segura/o de que deseas remover esta clase de tus reservas pendientes?')) {
                return; // Si el usuario cancela, detenemos la ejecución aquí mismo
            }

            if (!isLoggedIn) {
                removeFromGuestCart(sessionId);
                return;
            }

            // UI de carga mientras se elimina en servidor
            const originalHtml = btnElement.innerHTML;
            btnElement.innerHTML = `<svg class="animate-spin h-5 w-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
            btnElement.disabled = true;

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Reutilizamos el endpoint toggle que creamos antes
            fetch("{{ route('global.student.enroll.toggle') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ class_session_id: sessionId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert("No se pudo remover la clase: " + data.message);
                    btnElement.innerHTML = originalHtml;
                    btnElement.disabled = false;
                } else {
                    // Magia UI: Animamos salida del elemento
                    const li = btnElement.closest('li');
                    const ul = li.closest('ul');
                    const group = li.closest('.studio-cart-group');
                    
                    li.style.opacity = '0';
                    setTimeout(() => {
                        li.remove();
                        // Destruir el grupo entero si ya no hay clases
                        if (ul.querySelectorAll('li').length === 0) {
                            group.remove();
                        }
                        
                        // Si no quedan grupos en todo el contenedor, mostramos estado vacío
                        if (document.querySelectorAll('.studio-cart-group').length === 0) {
                            document.getElementById('classes-container').innerHTML = `
                                <div class="py-12 text-center border-2 border-dashed border-zinc-200 rounded-3xl animate-fade-in">
                                    <p class="text-zinc-500 font-medium">No tienes clases pendientes de pago.</p>
                                    <a href="{{ route('explore') }}" class="inline-block mt-4 text-indigo-600 font-bold hover:underline">Explorar Catálogo</a>
                                </div>`;
                        }

                        // Recalcular precios
                        calculateCart();
                        
                        // Refrescar el badge global del menú
                        if (data.cart_count !== undefined) {
                            const badge = document.getElementById('portal-badge');
                            if (badge) {
                                badge.innerText = data.cart_count;
                                badge.style.display = data.cart_count > 0 ? 'flex' : 'none';
                            }
                        }
                    }, 200);
                }
            })
            .catch(err => {
                console.error("Error al remover:", err);
                alert("Hubo un error de conexión.");
                btnElement.innerHTML = originalHtml;
                btnElement.disabled = false;
            });
        }

        function removeFromGuestCart(sessionId) {
            let cart = JSON.parse(localStorage.getItem('estadoprisma_cart')) || [];
            cart = cart.filter(id => id !== parseInt(sessionId));
            localStorage.setItem('estadoprisma_cart', JSON.stringify(cart));
            
            updateBadge();
            loadGuestCart();
        }

        // ==========================================
        // 5. MOTOR DE PRECIOS Y CHECKBOXES
        // ==========================================
        function toggleStudioSelection(masterCheckbox, studioId) {
            const checkboxes = document.querySelectorAll(`input.session-checkbox[data-studio-id="${studioId}"]`);
            checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
            calculateCart();
        }

        // ==========================================
        // 5. MOTOR DE PRECIOS Y CHECKBOXES
        // ==========================================
        function toggleStudioSelection(masterCheckbox, studioId) {
            const checkboxes = document.querySelectorAll(`input.session-checkbox[data-studio-id="${studioId}"]`);
            checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
            calculateCart();
        }

        function calculateCart() {
            const studioGroups = document.querySelectorAll('.studio-cart-group');
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            studioGroups.forEach(group => {
                const studioId = group.getAttribute('data-studio-id');
                const checkedBoxes = group.querySelectorAll(`input.session-checkbox:checked`);
                
                const btnPay = document.getElementById(`btn-pay-${studioId}`);
                const totalEl = document.getElementById(`total-${studioId}`);
                const breakdownEl = document.getElementById(`breakdown-${studioId}`);

                // Si no hay nada seleccionado, apagamos todo y salimos
                if (checkedBoxes.length === 0) {
                    btnPay.disabled = true;
                    btnPay.innerHTML = `Pagar Selección`;
                    totalEl.innerText = "$0";
                    breakdownEl.innerHTML = "<span class='text-zinc-400'>0 clases seleccionadas</span>";
                    return;
                }

                // 1. BLOQUEO UI: Desactivamos el botón y ponemos el spinner
                btnPay.disabled = true; 
                btnPay.innerHTML = `<svg class="animate-spin h-5 w-5 text-white mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                
                totalEl.innerHTML = `<span class="animate-pulse text-zinc-400 text-lg">Calculando...</span>`;

                const sessionIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));

                // 2. FETCH AL SERVIDOR
                fetch("{{ route('api.cart.calculate') }}", {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': token, 
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify({ 
                        studio_id: parseInt(studioId), 
                        session_ids: sessionIds 
                    })
                })
                .then(res => res.json())
                .then(data => {
                    // BLINDAJE UI: Buscamos data.error (nuestro) o data.message (nativo de Laravel)
                    if (data.error || data.message) {
                        alert(data.error || data.message || "Error de servidor: No se pudo calcular el precio.");
                        totalEl.innerText = "$0";
                        breakdownEl.innerHTML = "<span class='text-rose-500 font-bold'>Error de cálculo</span>";
                        btnPay.innerHTML = `Pagar Selección`;
                    } else {
                        // 3. DESBLOQUEO UI: Restauramos el botón y pintamos datos
                        breakdownEl.innerHTML = data.breakdown_html;
                        totalEl.innerText = data.total_formatted;
                        btnPay.innerHTML = `Pagar Selección`;
                        btnPay.disabled = false; // Solo se habilita cuando ya tenemos el total real
                    }
                })
                .catch(err => {
                    console.error("Error al calcular:", err);
                    totalEl.innerText = "Error";
                    breakdownEl.innerHTML = "<span class='text-rose-500 font-bold'>Error de conexión</span>";
                    btnPay.innerHTML = `Pagar Selección`;
                });
            });
        }

        // ==========================================
        // 6. CONTROLADORES DEL MODAL DE PROMOCIONES
        // ==========================================
        function openPromoModal(modalId) {
            const modal = document.getElementById(modalId);
            const card = modal.querySelector('.modal-card');
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closePromoModal(modalId) {
            const modal = document.getElementById(modalId);
            const card = modal.querySelector('.modal-card');
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }

        // ==========================================
        // 7. ORQUESTADOR DE PAGOS (CHECKOUT)
        // ==========================================
        function payStudio(studioId) {
            const checkedBoxes = document.querySelectorAll(`input.session-checkbox[data-studio-id="${studioId}"]:checked`);
            const sessionIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
            
            if (sessionIds.length === 0) return;
            
            // 1. Lógica para Invitados (Abre el Modal)
            if (!isLoggedIn) {
                document.getElementById('guest_studio_id').value = studioId;
                document.getElementById('guest_session_ids').value = JSON.stringify(sessionIds);
                document.getElementById('guestCheckoutModal').classList.remove('hidden');
                return;
            }

            // 2. Lógica para Alumnos Logueados (Conexión a Mercado Pago)
            const btnPay = document.getElementById(`btn-pay-${studioId}`);
            
            // UI: Estado de Carga (Protección contra doble clic)
            btnPay.disabled = true;
            btnPay.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span class="ml-2">Conectando...</span>`;

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Enviamos la petición a nuestro futuro CheckoutController
            fetch("/pagos/generar-checkout", {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': token, 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ 
                    studio_id: parseInt(studioId), 
                    session_ids: sessionIds 
                })
            })
            .then(res => res.json())
            .then(data => {
                // Si el backend o Mercado Pago arrojan un error (Ej: Estudio sin cuenta vinculada)
                if (data.error || data.message) {
                    alert(data.error || data.message || "No se pudo generar el enlace de pago.");
                    btnPay.disabled = false;
                    btnPay.innerHTML = `Pagar Selección`;
                    return;
                }

                // Magia Pura: Si Mercado Pago responde con éxito, nos da el init_point
                if (data.init_point) {
                    // Redirigimos al navegador hacia la pasarela oficial de MP
                    window.location.href = data.init_point;
                }
            })
            .catch(err => {
                console.error("Error fatal en checkout:", err);
                alert("Hubo un problema de red al intentar conectar con el banco.");
                btnPay.disabled = false;
                btnPay.innerHTML = `Pagar Selección`;
            });
        }
    </script>
</x-app-layout>
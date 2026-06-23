<x-app-layout>
    <div class="py-8 md:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        
        <div class="text-center mb-10 md:mb-14">
            <h1 class="text-3xl md:text-4xl font-black text-zinc-900 tracking-tight">Mi Portal de Pagos</h1>
            <p class="mt-3 text-zinc-500 font-medium text-base md:text-lg">Selecciona las clases que deseas confirmar. La promoción o pack genererá el descuento automáticamente al seleccionar las clases. Has click en Ver Ofertas para ver las promociones.</p>
        </div>
        {{-- CAPA 2: BANNER DE ALERTA DE CUPOS --}}
        @if(($hasStockIssues ?? false) && Auth::check())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 mb-6 flex items-start gap-3 max-w-7xl mx-auto">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>
                <p class="text-sm font-bold text-amber-800">Alerta de cupos limitados</p>
                <p class="text-xs text-amber-700 mt-1">Algunas clases en tu carrito han agotado sus cupos. Por favor, deselecciona asistentes para poder continuar. Las clases afectadas se muestran en <span class="font-bold text-rose-600">rojo</span>.</p>
            </div>
        </div>
        @endif
        {{-- CONTENEDOR DE ESTUDIOS --}}
        <div id="classes-container">
            @auth
                @if($groupedSessions->isEmpty())
                    <div class="py-8 text-center border-2 border-dashed border-zinc-200 rounded-3xl">
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
        window.AppConfig = {
            isLoggedIn: @json(Auth::check()),
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
        // CAPA 2: Mapa de capacidad por sesión para validación client-side
        window.SessionCapacityMap = @json($sessionCapacity ?? []);

        // ==========================================
        // 1. INICIALIZACIÓN
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            // Usamos window.AppConfig.isLoggedIn en lugar de isLoggedIn
            if (!window.AppConfig.isLoggedIn) {
                loadGuestCart();
            } else {
                setTimeout(() => {
                    calculateCart();
                }, 100);
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
            if (window.AppConfig.isLoggedIn) {
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
                    <div class="py-8 text-center border-2 border-dashed border-zinc-200 rounded-3xl">
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
                    
                    // 🔥 SOLUCIÓN PARA INVITADOS: Calcular apenas se inyecten los checkboxes
                    setTimeout(() => {
                        calculateCart();
                    }, 100);

                } else {
                    container.innerHTML = `<div class="py-8 text-center text-rose-500 font-medium">No se pudieron cargar las clases.</div>`;
                }
            })
            .catch(error => {
                console.error("Error al cargar carrito:", error);
                container.innerHTML = `<div class="py-8 text-center text-rose-500 font-medium">Ocurrió un error. Intenta recargar la página.</div>`;
            });
        }

        // ==========================================
        // 4. ELIMINAR CLASES DEL CARRITO (Multi-Familiar)
        // ==========================================
        function removeCartItem(sessionId, dependentId, btnElement) {
            if (!confirm('¿Estás segura/o de que deseas remover esta clase de tus reservas pendientes?')) {
                return; 
            }

            if (!window.AppConfig.isLoggedIn) {
                removeFromGuestCart(sessionId);
                return;
            }

            // UI de carga
            const originalHtml = btnElement.innerHTML;
            btnElement.innerHTML = `<svg class="animate-spin h-5 w-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
            btnElement.disabled = true;

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('global.student.enroll.toggle') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ 
                    class_session_id: sessionId,
                    dependent_id: dependentId 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert("No se pudo remover la clase: " + data.message);
                    btnElement.innerHTML = originalHtml;
                    btnElement.disabled = false;
                } else {
                    // Animación de salida solo de la fila del estudiante
                    const label = btnElement.closest('label');
                    const li = label.closest('li');
                    const ul = li.closest('ul');
                    const group = li.closest('.studio-cart-group');
                    
                    label.style.opacity = '0';
                    setTimeout(() => {
                        label.remove();
                        // Si no quedan labels, eliminamos el <li> entero de la clase
                        if (li.querySelectorAll('label').length === 0) li.remove();
                        // Si no quedan <li>, eliminamos el grupo del estudio
                        if (ul.querySelectorAll('li').length === 0) group.remove();
                        
                        // Si no quedan grupos en todo el contenedor, mostramos estado vacío
                        if (document.querySelectorAll('.studio-cart-group').length === 0) {
                            document.getElementById('classes-container').innerHTML = `
                                <div class="py-8 text-center border-2 border-dashed border-zinc-200 rounded-3xl animate-fade-in">
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
        // 5. MOTOR DE PRECIOS Y CHECKBOXES (Multi-Familiar)
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

                if (checkedBoxes.length === 0) {
                    btnPay.disabled = true;
                    btnPay.innerHTML = `Pagar Selección`;
                    totalEl.innerText = "$0";
                    breakdownEl.innerHTML = "<span class='text-zinc-400'>0 clases seleccionadas</span>";
                    return;
                }

                btnPay.disabled = true; 
                btnPay.innerHTML = `<svg class="animate-spin h-5 w-5 text-white mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                totalEl.innerHTML = `<span class="animate-pulse text-zinc-400 text-lg">Calculando...</span>`;

                // Desarmamos el string "14-5" (session_id-student_id)
                const selections = Array.from(checkedBoxes).map(cb => {
                    const parts = cb.value.split('-');
                    return { session_id: parseInt(parts[0]), student_id: parseInt(parts[1]) };
                });

                fetch("{{ route('api.cart.calculate') }}", {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': token, 
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify({ 
                        studio_id: parseInt(studioId), 
                        selections: selections 
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error || data.message) {
                        showCartToast(data.error || data.message || "Error de servidor: No se pudo calcular el precio.", 'error');
                        totalEl.innerText = "$0";
                        breakdownEl.innerHTML = "<span class='text-rose-500 font-bold'>Error de cálculo</span>";
                        btnPay.innerHTML = `Pagar Selección`;
                    } else {
                        breakdownEl.innerHTML = data.breakdown_html;
                        totalEl.innerText = data.total_formatted;
                        btnPay.innerHTML = `Pagar Selección`;

                        // ─── CAPA 2: Verificar cupos por sesión ─────────
                        const capacityMap = window.SessionCapacityMap || {};
                        const selectedBySession = {};
                        selections.forEach(s => {
                            selectedBySession[s.session_id] = (selectedBySession[s.session_id] || 0) + 1;
                        });

                        let hasIssue = false;
                        for (const [sid, count] of Object.entries(selectedBySession)) {
                            if (capacityMap[sid] && capacityMap[sid].available < count) {
                                hasIssue = true;
                                break;
                            }
                        }

                        if (hasIssue) {
                            btnPay.disabled = true;
                            btnPay.classList.add('opacity-50', 'cursor-not-allowed');
                            btnPay.title = 'Hay más selecciones que cupos disponibles en una o más clases';
                        } else {
                            btnPay.disabled = false;
                            btnPay.classList.remove('opacity-50', 'cursor-not-allowed');
                            btnPay.removeAttribute('title');
                        }
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
        // 7. ORQUESTADOR DE PAGOS (CHECKOUT Multi-Familiar)
        // ==========================================
        function payStudio(studioId) {
            const checkedBoxes = document.querySelectorAll(`input.session-checkbox[data-studio-id="${studioId}"]:checked`);

            // Desarmamos el string "14-5" (session_id-student_id)
            const selections = Array.from(checkedBoxes).map(cb => {
                const parts = cb.value.split('-');
                return { session_id: parseInt(parts[0]), student_id: parseInt(parts[1]) };
            });

            if (selections.length === 0) return;

            // ─── CAPA 2: Client-side stock check antes de checkout ─────────
            const capacityMap = window.SessionCapacityMap || {};
            const selectedBySession = {};
            selections.forEach(s => {
                selectedBySession[s.session_id] = (selectedBySession[s.session_id] || 0) + 1;
            });
            for (const [sid, count] of Object.entries(selectedBySession)) {
                if (capacityMap[sid] && capacityMap[sid].available < count) {
                    showCartToast('No puedes continuar: hay más selecciones que cupos disponibles. Desmarca algunos asistentes.', 'error');
                    return;
                }
            }

            // Lógica para Invitados (Abre el Modal)
            if (!window.AppConfig.isLoggedIn) {
                document.getElementById('guest_studio_id').value = studioId;
                // Como los invitados no tienen familiares, podemos extraer solo los session_ids únicos
                const uniqueSessionIds = Array.from(new Set(selections.map(s => s.session_id)));
                document.getElementById('guest_session_ids').value = JSON.stringify(uniqueSessionIds);
                document.getElementById('guestCheckoutModal').classList.remove('hidden');
                return;
            }

            // Lógica para Alumnos Logueados (Conexión a Mercado Pago)
            const btnPay = document.getElementById(`btn-pay-${studioId}`);

            btnPay.disabled = true;
            btnPay.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span class="ml-2">Conectando...</span>`;

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("/pagos/generar-checkout", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    studio_id: parseInt(studioId),
                    selections: selections
                })
            })
            .then(async res => {
                if (!res.ok && res.status === 422) {
                    const errData = await res.json();
                    showCartToast(errData.message || 'Los cupos de una clase acaban de agotarse. Actualiza tu selección.', 'error');
                    btnPay.disabled = false;
                    btnPay.innerHTML = `Pagar Selección`;
                    return null;
                }
                return res.json();
            })
            .then(data => {
                if (!data) return; // handled by 422 branch
                if (data.error || data.message) {
                    showCartToast(data.error || data.message || "No se pudo generar el enlace de pago.", 'error');
                    btnPay.disabled = false;
                    btnPay.innerHTML = `Pagar Selección`;
                    return;
                }

                if (data.init_point) {
                    window.location.href = data.init_point;
                }
            })
            .catch(err => {
                console.error("Error fatal en checkout:", err);
                showCartToast("Hubo un problema de red al intentar conectar con el banco.", 'error');
                btnPay.disabled = false;
                btnPay.innerHTML = `Pagar Selección`;
            });
        }

        // ─── Toast simple para el carrito ─────────────────────────────
        function showCartToast(message, type = 'success') {
            const container = document.getElementById('cart-toast-container') || (() => {
                const div = document.createElement('div');
                div.id = 'cart-toast-container';
                div.className = 'fixed top-6 right-6 z-[200] flex flex-col gap-3';
                document.body.appendChild(div);
                return div;
            })();

            const toast = document.createElement('div');
            const bgClass = type === 'error'
                ? 'bg-gradient-to-r from-rose-600 to-red-500'
                : 'bg-gradient-to-r from-emerald-500 to-teal-500';

            toast.className = `${bgClass} text-white px-5 py-3 rounded-2xl font-bold text-sm transition-all duration-300 transform translate-x-full opacity-0 flex items-center gap-2.5 min-w-[280px] max-w-[420px]`;

            const iconPath = type === 'error'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';

            toast.innerHTML = `
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">${iconPath}</svg>
                <span class="flex-1">${message}</span>
            `;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            });

            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300);
            }, 4000);
        }
    </script>
</x-app-layout>
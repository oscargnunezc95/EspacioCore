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

        {{-- ============================================================ --}}
        {{-- BARRA FLOTANTE DE CONFIRMACIÓN --}}
        {{-- ============================================================ --}}
        <div id="floating-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 translate-y-24 opacity-0 transition-all duration-500 z-50 pointer-events-none">
            <div class="bg-gradient-to-r from-indigo-900 to-purple-900 text-white px-6 py-4 rounded-full shadow-2xl shadow-indigo-500/30 flex items-center gap-6 border border-white/10">
                <div class="flex items-center gap-3">
                    <span id="selected-count" class="bg-emerald-400 text-indigo-900 font-black text-sm w-7 h-7 flex items-center justify-center rounded-full shadow-inner">0</span>
                    <span class="font-bold text-sm">Cambios detectados</span>
                </div>
                <button onclick="confirmReservations()" id="floating-confirm-btn"
                    class="bg-gradient-to-r from-emerald-400 to-teal-400 hover:from-emerald-300 hover:to-teal-300 text-indigo-900 px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-300 active:scale-95 flex items-center gap-2 pointer-events-auto shadow-lg shadow-emerald-500/30">
                    Confirmar Cambios
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        </div>

        @auth
            {{-- ============================================================ --}}
            {{-- MINI CARRITO FLOTANTE --}}
            {{-- ============================================================ --}}
            <div class="fixed bottom-6 right-6 z-[60]">
                <div id="miniCartPanel" class="hidden absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-indigo-100 overflow-hidden transition-all transform origin-bottom-right opacity-0 scale-95">
                    <div class="p-5 bg-gradient-to-r from-indigo-700 to-purple-700 text-white flex justify-between items-center">
                        <div>
                            <h4 class="font-black text-lg leading-none">Tus Reservas</h4>
                            <p class="text-xs text-indigo-200 mt-1">Pendientes de pago</p>
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
                            class="w-full {{ auth()->user()->pending_reservations_count > 0 ? 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-200' : 'bg-stone-200 pointer-events-none' }} text-white font-bold py-3.5 rounded-2xl transition-all duration-300 active:scale-95 text-sm flex items-center justify-center gap-2">
                            Ir a Pagar Mis Clases
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>

                <button onclick="toggleMiniCart()" id="btnMiniCart"
                    class="relative bg-gradient-to-br from-indigo-600 to-purple-700 text-white p-4 rounded-full shadow-[0_10px_40px_-10px_rgba(99,102,241,0.5)] hover:shadow-[0_15px_50px_-10px_rgba(99,102,241,0.7)] hover:scale-110 transition-all duration-300 active:scale-95 border border-white/10 focus:outline-none focus:ring-4 focus:ring-indigo-300/50 group">
                    <svg class="w-6 h-6 transform group-hover:-rotate-12 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    @if(auth()->user()->pending_reservations_count > 0)
                        <span class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-rose-400 border-2 border-white text-[11px] font-black text-white shadow-md animate-pulse">
                            {{ auth()->user()->pending_reservations_count }}
                        </span>
                    @endif
                </button>
            </div>
        @endauth

        {{-- ============================================================ --}}
        {{-- MODAL DE DETALLES DEL TALLER --}}
        {{-- ============================================================ --}}
        <div id="detailModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-stone-900/80 backdrop-blur-sm transition-opacity" onclick="closeDetailModal()"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md md:max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[95vh]" id="detailModalCard">
                <div class="h-40 sm:h-48 w-full bg-stone-200 relative shrink-0">
                    <img id="m_image" src="" alt="Cover" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-indigo-900/70 via-indigo-900/10 to-transparent"></div>
                    <button onclick="closeDetailModal()" class="absolute top-4 right-4 p-2 text-indigo-700 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full transition-colors focus:outline-none shadow-md z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 md:p-8 overflow-y-auto flex-1 custom-scrollbar">
                    <div class="mb-6">
                        <a href="#" id="m_studio_link" target="_blank"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition-colors text-[10px] font-black rounded-lg tracking-widest uppercase mb-3">
                            <span id="m_studio">Estudio</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                        <h3 id="m_title" class="text-2xl font-black text-stone-900 leading-tight">Clase</h3>
                    </div>

                    <div id="m_video_container" class="hidden mb-6 rounded-2xl overflow-hidden shadow-md border border-indigo-100 bg-stone-900 relative group transition-all duration-300 mx-auto">
                        <iframe id="m_video_frame" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>

                    <div id="m_description_container" class="hidden mb-8">
                        <h4 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1 h-4 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></span>
                            Acerca de la clase
                        </h4>
                        <p id="m_description" class="text-sm text-stone-600 leading-relaxed whitespace-pre-line"></p>
                    </div>

                    <div class="space-y-3 mb-4">
                        <div class="flex items-center gap-3 text-stone-600 bg-indigo-50/50 p-3 rounded-2xl border border-indigo-100">
                            <div class="bg-white p-2.5 rounded-xl shadow-sm border border-indigo-100 text-indigo-500 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p id="m_date" class="text-sm font-bold text-stone-900 capitalize">Fecha</p>
                                <p id="m_time" class="text-xs font-medium text-stone-500">Hora</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-stone-600 bg-emerald-50/50 p-3 rounded-2xl border border-emerald-100">
                            <div class="bg-white p-2.5 rounded-xl shadow-sm border border-emerald-100 text-emerald-500 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-0.5">Profesor/a</p>
                                <p id="m_teacher" class="text-sm font-bold text-stone-900 leading-tight truncate">Nombre</p>
                                <a href="#" id="m_teacher_email" class="hidden text-[11px] font-medium text-indigo-600 hover:text-indigo-800 transition-colors mt-0.5 truncate"></a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 text-stone-600 bg-rose-50/50 p-3 rounded-2xl border border-rose-100">
                            <div class="bg-white p-2.5 rounded-xl shadow-sm border border-rose-100 text-rose-500 mt-1 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-0.5">Ubicación</p>
                                <p id="m_address" class="text-sm font-bold text-stone-900 mb-2 leading-tight">Dirección</p>
                                <a href="#" id="m_map_link" target="_blank" class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                    Cómo llegar en Maps <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL MULTI-SELECCIÓN FAMILIAR --}}
        {{-- ============================================================ --}}
        @auth
        <div id="familySelectionModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col" id="familySelectionCard">
                <div class="p-6 border-b border-indigo-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-stone-900 leading-tight">¿Quiénes asistirán?<br><span class="text-sm font-medium text-stone-500">Selecciona uno o más</span></h3>
                    <button onclick="closeFamilyModal()" class="text-stone-400 hover:text-stone-600 bg-stone-50 p-2 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
                    <button type="button" onclick="toggleModalSelection('titular')" id="modal_opt_titular"
                        class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-indigo-100 hover:border-indigo-300 transition-all group">
                        <div class="flex flex-col text-left">
                            <span class="font-bold text-stone-900 text-sm">Yo ({{ Auth::user()->name }})</span>
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mt-0.5">Titular</span>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 border-stone-200 flex items-center justify-center check-icon transition-colors">
                            <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest hidden"></span>
                    </button>

                    @foreach($activeDependents as $dependent)
                        <button type="button" onclick="toggleModalSelection({{ $dependent->id }})" id="modal_opt_{{ $dependent->id }}"
                            class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-indigo-100 hover:border-emerald-300 transition-all group">
                            <div class="flex flex-col text-left">
                                <span class="font-bold text-stone-900 text-sm">{{ $dependent->first_name }} {{ $dependent->last_name }}</span>
                                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mt-0.5">{{ $dependent->relationship ?? 'Familiar' }}</span>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 border-stone-200 flex items-center justify-center check-icon transition-colors">
                                <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest hidden"></span>
                        </button>
                    @endforeach
                </div>

                <div class="p-4 bg-white border-t border-indigo-50 flex flex-col gap-3">
                    <button onclick="saveModalSelection()"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3.5 rounded-2xl shadow-md shadow-indigo-200 hover:from-indigo-500 hover:to-purple-500 transition-all duration-300 active:scale-95 text-sm">
                        Guardar Selección
                    </button>
                    <a href="{{ route('profile.family.index') }}" class="text-xs font-bold text-stone-500 hover:text-indigo-600 transition-colors flex items-center justify-center gap-1">
                        Administrar familia
                    </a>
                </div>
            </div>
        </div>
        @endauth

        {{-- LÓGICA JAVASCRIPT COMPLETA --}}
        <script>
            // ==========================================
            // 1. LÓGICA DEL MODAL DE DETALLE
            // ==========================================
            function openDetailModal(data) {
                document.getElementById('m_title').innerText = data.title;
                document.getElementById('m_studio').innerText = data.studio;
                document.getElementById('m_studio_link').href = data.studio_url;
                document.getElementById('m_image').src = data.image;
                document.getElementById('m_date').innerText = data.date;
                document.getElementById('m_time').innerText = data.time + ' hrs';
                document.getElementById('m_address').innerText = data.address;
                document.getElementById('m_teacher').innerText = data.teacher;

                const emailEl = document.getElementById('m_teacher_email');
                if (data.teacher_email) {
                    emailEl.innerText = data.teacher_email;
                    emailEl.href = 'mailto:' + data.teacher_email;
                    emailEl.classList.remove('hidden');
                } else {
                    emailEl.classList.add('hidden');
                }

                const descContainer = document.getElementById('m_description_container');
                const descText = document.getElementById('m_description');
                if (data.description && data.description.trim() !== '') {
                    descText.innerText = data.description;
                    descContainer.classList.remove('hidden');
                } else {
                    descContainer.classList.add('hidden');
                    descText.innerText = '';
                }

                const videoContainer = document.getElementById('m_video_container');
                const videoFrame = document.getElementById('m_video_frame');
                if (data.video_url) {
                    videoFrame.src = data.video_url;
                    videoContainer.classList.remove('hidden', 'aspect-video', 'aspect-[9/16]', 'w-full', 'w-[280px]', 'sm:w-[320px]', 'w-[340px]', 'sm:w-[380px]');
                    if (data.video_url.includes('instagram.com')) {
                        videoContainer.classList.add('aspect-[9/16]','sm:w-[380px]');
                    } else {
                        videoContainer.classList.add('aspect-video', 'w-full');
                    }
                } else {
                    videoContainer.classList.add('hidden');
                    videoFrame.src = '';
                }

                const encodedAddress = encodeURIComponent(data.address);
                document.getElementById('m_map_link').href = `https://www.google.com/maps/search/?api=1&query=${encodedAddress}`;

                const modal = document.getElementById('detailModal');
                const card = document.getElementById('detailModalCard');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    card.classList.remove('scale-95', 'opacity-0');
                    card.classList.add('scale-100', 'opacity-100');
                }, 10);
                document.body.style.overflow = 'hidden';
            }

            function closeDetailModal() {
                const modal = document.getElementById('detailModal');
                const card = document.getElementById('detailModalCard');
                card.classList.remove('scale-100', 'opacity-100');
                card.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                    const videoFrame = document.getElementById('m_video_frame');
                    if (videoFrame) videoFrame.src = '';
                }, 300);
            }

            // ==========================================
            // 2. LÓGICA DE MULTI-SELECCIÓN (ÓRDENES CLARAS)
            // ==========================================
            const isLoggedIn = @json(Auth::check());
            const hasDependents = @json(isset($activeDependents) ? $activeDependents->count() > 0 : false);

            let pendingClasses = new Map();
            let currentModalSessionId = null;
            let currentModalBtn = null;
            let currentModalSelections = new Set();

            // HELPER PARA EVITAR EL ERROR DE TIPOS (STRING VS NUMBER)
            function getParsedDbSet(btnElement) {
                const dbState = JSON.parse(btnElement.getAttribute('data-db-selections') || '{}');
                const dbSet = new Set();
                Object.keys(dbState).forEach(k => dbSet.add(k === 'titular' ? 'titular' : parseInt(k)));
                return { dbState, dbSet };
            }

            function handleInterestClick(sessionId, btnElement) {
                if (!isLoggedIn) {
                    window.location.href = "{{ route('register') }}";
                    return;
                }

                const { dbState, dbSet } = getParsedDbSet(btnElement);

                if (hasDependents) {
                    currentModalSessionId = sessionId;
                    currentModalBtn = btnElement;

                    currentModalSelections = new Set(dbSet);
                    const changes = pendingClasses.get(sessionId) || [];
                    changes.forEach(change => {
                        if (change.action === 'add') currentModalSelections.add(change.id);
                        if (change.action === 'remove') currentModalSelections.delete(change.id);
                    });

                    renderModalUI();
                    openFamilyModal();
                } else {
                    let isEnrolled = dbSet.has('titular');
                    const changes = pendingClasses.get(sessionId) || [];

                    changes.forEach(c => {
                        if (c.action === 'add') isEnrolled = true;
                        if (c.action === 'remove') isEnrolled = false;
                    });

                    const newChanges = [];
                    if (isEnrolled) {
                        if (dbSet.has('titular')) newChanges.push({id: 'titular', action: 'remove'});
                    } else {
                        if (!dbSet.has('titular')) newChanges.push({id: 'titular', action: 'add'});
                    }

                    if (newChanges.length > 0) pendingClasses.set(sessionId, newChanges);
                    else pendingClasses.delete(sessionId);

                    updateButtonUI(sessionId, btnElement);
                    toggleFloatingBar();
                }
            }

            function toggleModalSelection(id) {
                if (currentModalSelections.has(id)) currentModalSelections.delete(id);
                else currentModalSelections.add(id);
                renderModalUI();
            }

            function renderModalUI() {
                if (!currentModalBtn) return;
                const dbState = JSON.parse(currentModalBtn.getAttribute('data-db-selections') || '{}');

                document.querySelectorAll('[id^="modal_opt_"]').forEach(btn => {
                    const isTitular = btn.id === 'modal_opt_titular';
                    const idValue = isTitular ? 'titular' : parseInt(btn.id.replace('modal_opt_', ''));
                    const checkCircle = btn.querySelector('.check-icon');
                    const checkMark = checkCircle.querySelector('svg');
                    const statusLabel = btn.querySelector('span:last-child');

                    if (dbState[idValue] === 'paid') {
                        btn.classList.add('border-blue-500', 'bg-blue-50', 'pointer-events-none');
                        btn.classList.remove('border-indigo-100', 'hover:border-emerald-300');
                        statusLabel.innerText = "YA PAGADO";
                        statusLabel.classList.replace('text-indigo-600', 'text-blue-700');
                        statusLabel.classList.replace('text-emerald-600', 'text-blue-700');
                        statusLabel.classList.remove('hidden');
                        checkCircle.classList.add('bg-blue-500', 'border-blue-500');
                        checkMark.classList.remove('opacity-0');
                        return;
                    } else {
                        statusLabel.classList.add('hidden');
                    }

                    if (currentModalSelections.has(idValue)) {
                        btn.classList.add('border-emerald-600', 'bg-emerald-50/50');
                        btn.classList.remove('border-indigo-100');
                        checkCircle.classList.add('bg-emerald-600', 'border-emerald-600');
                        checkCircle.classList.remove('border-stone-200');
                        checkMark.classList.remove('opacity-0');
                    } else {
                        btn.classList.remove('border-emerald-600', 'bg-emerald-50/50');
                        btn.classList.add('border-indigo-100');
                        checkCircle.classList.remove('bg-emerald-600', 'border-emerald-600');
                        checkCircle.classList.add('border-stone-200');
                        checkMark.classList.add('opacity-0');
                    }
                });
            }

            function saveModalSelection() {
                if (!currentModalBtn) return;
                const { dbState, dbSet } = getParsedDbSet(currentModalBtn);
                const changes = [];

                currentModalSelections.forEach(val => {
                    if (!dbSet.has(val)) changes.push({id: val, action: 'add'});
                });
                dbSet.forEach(val => {
                    if (!currentModalSelections.has(val) && dbState[val] !== 'paid') {
                        changes.push({id: val, action: 'remove'});
                    }
                });

                if (changes.length > 0) pendingClasses.set(currentModalSessionId, changes);
                else pendingClasses.delete(currentModalSessionId);

                updateButtonUI(currentModalSessionId, currentModalBtn);
                toggleFloatingBar();
                closeFamilyModal();
            }

            function openFamilyModal() {
                const modal = document.getElementById('familySelectionModal');
                const card = document.getElementById('familySelectionCard');
                modal.classList.remove('hidden');
                setTimeout(() => { card.classList.remove('scale-95', 'opacity-0'); card.classList.add('scale-100', 'opacity-100'); }, 10);
            }

            function closeFamilyModal() {
                const modal = document.getElementById('familySelectionModal');
                const card = document.getElementById('familySelectionCard');
                card.classList.remove('scale-100', 'opacity-100');
                card.classList.add('scale-95', 'opacity-0');
                setTimeout(() => { modal.classList.add('hidden'); }, 300);
            }

            function updateButtonUI(sessionId, btnElement) {
                const { dbSet } = getParsedDbSet(btnElement);
                const changes = pendingClasses.get(sessionId) || [];

                let finalCount = dbSet.size;
                changes.forEach(c => {
                    if (c.action === 'add') finalCount++;
                    if (c.action === 'remove') finalCount--;
                });

                if (finalCount > 0) {
                    const countText = finalCount === 1 ? (hasDependents ? '1 Seleccionado' : 'En Portal') : `${finalCount} en Portal`;
                    const actionIcon = hasDependents
                        ? `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.762z"></path></svg> Modificar`
                        : `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover`;

                    btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[130px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 active:scale-95 flex items-center justify-center shadow-sm bg-gradient-to-r from-indigo-600 to-purple-600 text-white border-0 hover:from-indigo-500 hover:to-purple-500 hover:shadow-md hover:shadow-indigo-200 group/btn";
                    btnElement.innerHTML = `
                        <div class="relative flex items-center justify-center w-full">
                            <span class="flex items-center gap-2 transition-opacity duration-200 opacity-100 group-hover/btn:opacity-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ${countText}
                            </span>
                            <span class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity duration-200 opacity-0 group-hover/btn:opacity-100">${actionIcon}</span>
                        </div>`;
                } else {
                    if (dbSet.size > 0 && finalCount === 0) {
                        btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[130px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center bg-rose-500 text-white border border-rose-600 hover:bg-rose-600";
                        btnElement.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Remover Todo`;
                    } else {
                        btnElement.className = "interest-btn flex-1 sm:flex-none sm:w-[130px] px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm active:scale-95 flex items-center justify-center bg-zinc-100 text-zinc-700 border border-zinc-200 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:border-indigo-200 hover:text-indigo-700";
                        btnElement.innerHTML = `<span class="flex items-center gap-1.5">✨ Me Interesa</span>`;
                    }
                }
            }

            // ==========================================
            // 3. ENVÍO AL BACKEND
            // ==========================================
            function toggleFloatingBar() {
                const bar = document.getElementById('floating-bar');
                const countLabel = document.getElementById('selected-count');

                let totalCount = 0;
                pendingClasses.forEach(changes => { totalCount += changes.length; });

                countLabel.innerText = totalCount;
                if (totalCount > 0) {
                    bar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                    bar.classList.add('translate-y-0', 'opacity-100');
                } else {
                    bar.classList.remove('translate-y-0', 'opacity-100');
                    bar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
                }
            }

            async function confirmReservations() {
                const btn = document.getElementById('floating-confirm-btn');

                btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-indigo-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
                btn.disabled = true;

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const formattedSessions = [];
                pendingClasses.forEach((changes, sessionId) => {
                    changes.forEach(c => {
                        formattedSessions.push({
                            session_id: sessionId,
                            dependent_id: c.id === 'titular' ? null : c.id,
                            action: c.action
                        });
                    });
                });

                const payload = { enrollments: formattedSessions };

                try {
                    const response = await fetch("{{ route('global.student.enroll.bulk') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok && response.status === 422) {
                        const data = await response.json();
                        showToast(data.message || 'Algunas clases ya no tienen cupos disponibles.', 'error');
                        resetConfirmButton(btn);
                        return;
                    }

                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(text);
                    }

                    const data = await response.json();

                    if (data.error) {
                        showToast(data.message, 'error');
                        resetConfirmButton(btn);
                    } else {
                        pendingClasses.clear();
                        sessionStorage.setItem('cart_auto_open', 'true');
                        window.location.reload();
                    }
                } catch (error) {
                    console.error("Error en bulk:", error);
                    showToast("Hubo un error de conexión al procesar tus reservas.", 'error');
                    resetConfirmButton(btn);
                }
            }

            function resetConfirmButton(btn) {
                btn.innerHTML = `Confirmar Cambios <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
                btn.disabled = false;
            }

            // ==========================================
            // 4. LÓGICA DEL MINI-CARRITO FLOTANTE
            // ==========================================
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

            // ==========================================
            // 5. FILTROS DEL CALENDARIO
            // ==========================================
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

            // ==========================================
            // 6. SISTEMA DE TOASTS (Notificaciones elegantes)
            // ==========================================
            function createToastContainer() {
                const div = document.createElement('div');
                div.id = 'toast-container';
                div.className = 'fixed top-6 right-6 z-[200] flex flex-col gap-3';
                document.body.appendChild(div);
                return div;
            }

            function showToast(message, type = 'success') {
                const container = document.getElementById('toast-container') || createToastContainer();
                const toast = document.createElement('div');

                const bgClass = type === 'error'
                    ? 'bg-gradient-to-r from-rose-600 to-red-500'
                    : 'bg-gradient-to-r from-emerald-500 to-teal-500';

                const iconPath = type === 'error'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';

                toast.className = `${bgClass} text-white px-5 py-3 rounded-2xl font-bold text-sm transition-all duration-300 transform translate-x-full opacity-0 flex items-center gap-2.5 min-w-[280px] max-w-[420px]`;

                toast.innerHTML = `
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">${iconPath}</svg>
                    <span class="flex-1">${escapeHtml(message)}</span>
                    <button onclick="this.parentElement.remove()" class="shrink-0 hover:opacity-70 transition-opacity duration-150">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                `;

                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove('translate-x-full', 'opacity-0');
                    toast.classList.add('translate-x-0', 'opacity-100');
                });

                const dismissTimeout = setTimeout(() => {
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300);
                }, 4000);

                toast.addEventListener('click', (e) => {
                    if (e.target.tagName === 'BUTTON') return;
                    clearTimeout(dismissTimeout);
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300);
                });
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        </script>
    </div>

    {{-- Asegúrate de tener esto al final de show.blade.php --}}
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.key') }}"></script>
</x-guest-layout>

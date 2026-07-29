{{-- BARRA FLOTANTE DE CONFIRMACIÓN --}}
<div id="floating-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 translate-y-24 opacity-0 transition-all duration-500 z-50 pointer-events-none">
    <div class="bg-gradient-to-r from-red-900 to-rose-900 text-white px-6 py-4 rounded-full shadow-2xl shadow-red-500/30 flex items-center gap-6 border border-white/10">
        <div class="flex items-center gap-3">
            <span id="selected-count" class="bg-emerald-400 text-red-900 font-black text-sm w-7 h-7 flex items-center justify-center rounded-full shadow-inner">0</span>
            <span class="font-bold text-sm">Cambios detectados</span>
        </div>
        <button onclick="confirmReservations()" id="floating-confirm-btn"
            class="bg-gradient-to-r from-emerald-400 to-teal-400 hover:from-emerald-300 hover:to-teal-300 text-red-900 px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-300 active:scale-95 flex items-center gap-2 pointer-events-auto shadow-lg shadow-emerald-500/30">
            Confirmar Cambios
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </button>
    </div>
</div>

{{-- MODAL DE DETALLES DEL TALLER --}}
<div id="detailModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 sm:p-6">
    <div class="absolute inset-0 bg-stone-900/80 backdrop-blur-sm transition-opacity" onclick="closeDetailModal()"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md md:max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[95vh]" id="detailModalCard">
        <div class="h-40 sm:h-48 w-full bg-stone-200 relative shrink-0">
            <img id="m_image" src="" alt="Cover" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-red-900/70 via-red-900/10 to-transparent"></div>
            <button onclick="closeDetailModal()" class="absolute top-4 right-4 p-2 text-red-700 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full transition-colors focus:outline-none shadow-md z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 md:p-8 overflow-y-auto flex-1 custom-scrollbar">
            <div class="mb-6">
                <a href="#" id="m_studio_link" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 transition-colors text-[10px] font-black rounded-lg tracking-widest uppercase mb-3">
                    <span id="m_studio">Estudio</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 00-2-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
                <h3 id="m_title" class="text-2xl font-black text-stone-900 leading-tight">Clase</h3>
            </div>

            <div id="m_video_container" class="hidden mb-6 rounded-2xl overflow-hidden shadow-md border border-red-100 bg-stone-900 relative group transition-all duration-300 mx-auto">
                <iframe id="m_video_frame" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>

            <div id="m_description_container" class="hidden mb-8">
                <h4 class="text-xs font-black text-red-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                    <span class="w-1 h-4 bg-gradient-to-b from-red-500 to-rose-500 rounded-full"></span>
                    Acerca de la clase
                </h4>
                <p id="m_description" class="text-sm text-stone-600 leading-relaxed whitespace-pre-line"></p>
            </div>

            <div class="space-y-3 mb-4">
                <div class="flex items-center gap-3 text-stone-600 bg-red-50/50 p-3 rounded-2xl border border-red-100">
                    <div class="bg-white p-2.5 rounded-xl shadow-sm border border-red-100 text-red-500 shrink-0">
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
                        <a href="#" id="m_teacher_email" class="hidden text-[11px] font-medium text-red-600 hover:text-red-800 transition-colors mt-0.5 truncate"></a>
                    </div>
                </div>

                <div class="flex items-start gap-3 text-stone-600 bg-rose-50/50 p-3 rounded-2xl border border-rose-100">
                    <div class="bg-white p-2.5 rounded-xl shadow-sm border border-rose-100 text-rose-500 mt-1 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-0.5">Ubicación</p>
                        <p id="m_address" class="text-sm font-bold text-stone-900 mb-2 leading-tight">Dirección</p>
                        <a href="#" id="m_map_link" target="_blank" class="inline-flex items-center text-xs font-bold text-red-600 hover:text-red-800 transition-colors">
                            Cómo llegar en Maps <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 00-2-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL MULTI-SELECCIÓN FAMILIAR --}}
@auth
<div id="familySelectionModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col" id="familySelectionCard">
        <div class="p-6 border-b border-red-50 flex justify-between items-center">
            <h3 class="text-lg font-black text-stone-900 leading-tight">¿Quiénes asistirán?<br><span class="text-sm font-medium text-stone-500">Selecciona uno o más</span></h3>
            <button onclick="closeFamilyModal()" class="text-stone-400 hover:text-stone-600 bg-stone-50 p-2 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
            <button type="button" onclick="toggleModalSelection('titular')" id="modal_opt_titular"
                class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-red-100 hover:border-red-300 transition-all group">
                <div class="flex flex-col text-left">
                    <span class="font-bold text-stone-900 text-sm">Yo ({{ Auth::user()->name }})</span>
                    <span class="text-[10px] font-black text-red-600 uppercase tracking-widest mt-0.5">Titular</span>
                </div>
                <div class="w-6 h-6 rounded-full border-2 border-stone-200 flex items-center justify-center check-icon transition-colors">
                    <svg class="w-3.5 h-3.5 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest hidden"></span>
            </button>

            @foreach($activeDependents as $dependent)
                <button type="button" onclick="toggleModalSelection({{ $dependent->id }})" id="modal_opt_{{ $dependent->id }}"
                    class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-red-100 hover:border-emerald-300 transition-all group">
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

        <div class="p-4 bg-white border-t border-red-50 flex flex-col gap-3">
            <button onclick="saveModalSelection()"
                class="w-full bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold py-3.5 rounded-2xl shadow-md shadow-red-200 hover:from-red-500 hover:to-rose-500 transition-all duration-300 active:scale-95 text-sm">
                Guardar Selección
            </button>
            <a href="{{ route('profile.family.index') }}" class="text-xs font-bold text-stone-500 hover:text-red-600 transition-colors flex items-center justify-center gap-1">
                Administrar familia
            </a>
        </div>
    </div>
</div>
@endauth

{{-- ============================================================ --}}
{{-- SCRIPTS DEL CALENDARIO (Catálogo Aligerado y Eventos)      --}}
{{-- ============================================================ --}}
<script>
    // 1. Catálogo JS en Memoria
    window.workshopsCatalog = @json($workshopsCatalog ?? []);
    window.userSelectionsMap = @json($dbSelectionsBySession ?? []);

    // 2. Lógica del modal de detalle
    window.openDetailModal = function(workshopId, dateStr, timeStr) {
        const data = window.workshopsCatalog[workshopId];
        if (!data) return;
        
        document.getElementById('m_title').innerText = data.title || 'Clase';
        document.getElementById('m_studio').innerText = data.studio || 'Estudio';
        if (document.getElementById('m_studio_link')) document.getElementById('m_studio_link').href = data.studio_url || '#';
        if (document.getElementById('m_image')) document.getElementById('m_image').src = data.image || '';
        document.getElementById('m_date').innerText = dateStr || '';
        document.getElementById('m_time').innerText = (timeStr ? timeStr + ' hrs' : '');
        document.getElementById('m_address').innerText = data.address || 'Dirección no especificada';
        document.getElementById('m_teacher').innerText = data.teacher || 'Staff';

        const emailEl = document.getElementById('m_teacher_email');
        if (emailEl) {
            if (data.teacher_email) {
                emailEl.innerText = data.teacher_email;
                emailEl.href = 'mailto:' + data.teacher_email;
                emailEl.classList.remove('hidden');
            } else {
                emailEl.classList.add('hidden');
            }
        }

        const descContainer = document.getElementById('m_description_container');
        const descText = document.getElementById('m_description');
        if (descContainer && descText) {
            if (data.description && data.description.trim() !== '') {
                descText.innerText = data.description;
                descContainer.classList.remove('hidden');
            } else {
                descContainer.classList.add('hidden');
                descText.innerText = '';
            }
        }

        const videoContainer = document.getElementById('m_video_container');
        const videoFrame = document.getElementById('m_video_frame');
        if (videoContainer && videoFrame) {
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
        }

        if (document.getElementById('m_map_link') && data.address) {
            const encodedAddress = encodeURIComponent(data.address);
            document.getElementById('m_map_link').href = `https://www.google.com/maps/search/?api=1&query=${encodedAddress}`;
        }

        const modal = document.getElementById('detailModal');
        const card = document.getElementById('detailModalCard');
        if (modal && card) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeDetailModal = function() {
        const modal = document.getElementById('detailModal');
        const card = document.getElementById('detailModalCard');
        if (!modal || !card) return;
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            const videoFrame = document.getElementById('m_video_frame');
            if (videoFrame) videoFrame.src = '';
        }, 300);
    };

    // 3. Selección y carrito blindado
    const isLoggedIn = @json(Auth::check());
    const hasDependents = @json(isset($activeDependents) ? $activeDependents->count() > 0 : false);

    let pendingClasses = new Map();
    let currentModalSessionId = null;
    let currentModalBtn = null;
    let currentModalSelections = new Set();

    // 🚀 LÓGICA BLINDADA: Lee primero la memoria JS y como fallback el DOM
    function getParsedDbSet(sessionId, btnElement) {
        let dbState = window.userSelectionsMap[sessionId];
        if (!dbState && btnElement) {
            try {
                dbState = JSON.parse(btnElement.getAttribute('data-db-selections') || '{}');
            } catch(e) { dbState = {}; }
        }
        dbState = dbState || {};
        const dbSet = new Set();
        Object.keys(dbState).forEach(k => dbSet.add(k === 'titular' ? 'titular' : parseInt(k)));
        return { dbState, dbSet };
    }

    window.handleInterestClick = function(sessionId, btnElement) {
        if (!typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
            window.location.href = "{{ route('register') }}";
            return;
        }

        const { dbState, dbSet } = getParsedDbSet(sessionId, btnElement);

        if (typeof hasDependents !== 'undefined' && hasDependents) {
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
    };

    function toggleModalSelection(id) {
        if (currentModalSelections.has(id)) currentModalSelections.delete(id);
        else currentModalSelections.add(id);
        renderModalUI();
    }

    // 🚀 SANITIZACIÓN QUIRÚRGICA DEL MODAL FAMILIAR
    function renderModalUI() {
        if (!currentModalSessionId) return;
        const { dbState } = getParsedDbSet(currentModalSessionId, currentModalBtn);

        document.querySelectorAll('[id^="modal_opt_"]').forEach(btn => {
            const isTitular = btn.id === 'modal_opt_titular';
            const idValue = isTitular ? 'titular' : parseInt(btn.id.replace('modal_opt_', ''));
            const checkCircle = btn.querySelector('.check-icon');
            const checkMark = checkCircle ? checkCircle.querySelector('svg') : null;
            const statusLabel = btn.querySelector('span:last-child');

            // 1. SANITIZACIÓN ABSOLUTA (Limpieza total antes de evaluar)
            btn.classList.remove(
                'border-blue-500', 'bg-blue-50', 'pointer-events-none',
                'border-emerald-600', 'bg-emerald-50/50',
                'border-red-100', 'hover:border-emerald-300', 'hover:border-red-300'
            );
            if (checkCircle) {
                checkCircle.classList.remove(
                    'bg-blue-500', 'border-blue-500',
                    'bg-emerald-600', 'border-emerald-600',
                    'border-stone-200'
                );
            }
            if (checkMark) checkMark.classList.add('opacity-0');
            if (statusLabel) {
                statusLabel.classList.add('hidden');
                statusLabel.classList.remove('text-blue-700', 'text-red-600', 'text-emerald-600');
            }

            // 2. ASIGNACIÓN ESTRICTA DEL ESTADO EN VIVO
            if (dbState[idValue] === 'paid') {
                btn.classList.add('border-blue-500', 'bg-blue-50', 'pointer-events-none');
                if (statusLabel) {
                    statusLabel.innerText = "YA PAGADO";
                    statusLabel.classList.add('text-blue-700');
                    statusLabel.classList.remove('hidden');
                }
                if (checkCircle) checkCircle.classList.add('bg-blue-500', 'border-blue-500');
                if (checkMark) checkMark.classList.remove('opacity-0');
                return;
            }

            if (currentModalSelections.has(idValue)) {
                btn.classList.add('border-emerald-600', 'bg-emerald-50/50');
                if (checkCircle) checkCircle.classList.add('bg-emerald-600', 'border-emerald-600');
                if (checkMark) checkMark.classList.remove('opacity-0');
            } else {
                btn.classList.add('border-red-100', isTitular ? 'hover:border-red-300' : 'hover:border-emerald-300');
                if (checkCircle) checkCircle.classList.add('border-stone-200');
            }
        });
    }

    function saveModalSelection() {
        if (!currentModalSessionId || !currentModalBtn) return;
        const { dbState, dbSet } = getParsedDbSet(currentModalSessionId, currentModalBtn);
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

    // 🚀 SINCRONIZACIÓN ENTRE VISTAS: Actualiza todos los botones de la misma sesión
    function updateButtonUI(sessionId, btnElement) {
        const { dbSet } = getParsedDbSet(sessionId, btnElement);
        const changes = pendingClasses.get(sessionId) || [];

        let finalCount = dbSet.size;
        changes.forEach(c => {
            if (c.action === 'add') finalCount++;
            if (c.action === 'remove') finalCount--;
        });

        const allMatchingButtons = document.querySelectorAll(`button[data-session-id="${sessionId}"]`);
        const buttonsToUpdate = allMatchingButtons.length > 0 ? Array.from(allMatchingButtons) : (btnElement ? [btnElement] : []);

        buttonsToUpdate.forEach(btn => {
            const isLargeBtn = btn.classList.contains('w-full') || btn.classList.contains('py-2.5');
            const baseClasses = isLargeBtn 
                ? "interest-btn w-full py-2.5 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-2xs "
                : "interest-btn px-2.5 py-1 rounded-md text-[9px] font-black transition-all active:scale-95 shadow-2xs ";

            if (finalCount > 0) {
                const countText = finalCount === 1 ? (hasDependents ? '1 Seleccionado' : 'En Portal') : `${finalCount} en Portal`;
                btn.className = baseClasses + "bg-gradient-to-r from-red-600 to-rose-600 text-white border-0";
                btn.innerText = countText;
            } else {
                btn.className = baseClasses + "bg-white text-stone-700 hover:bg-red-50 hover:text-red-700 border border-stone-200";
                btn.innerText = "+ Agregar";
            }
        });
    }

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
        btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-red-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...`;
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
                btn.innerHTML = 'Confirmar Cambios';
                btn.disabled = false;
                return;
            }

            if (!response.ok) throw new Error(await response.text());
            const data = await response.json();

            if (data.error) {
                showToast(data.message, 'error');
                btn.innerHTML = 'Confirmar Cambios';
                btn.disabled = false;
            } else {
                pendingClasses.clear();
                sessionStorage.setItem('cart_auto_open', 'true');
                window.location.reload();
            }
        } catch (error) {
            console.error("Error en bulk:", error);
            showToast("Hubo un error de conexión al procesar tus reservas.", 'error');
            btn.innerHTML = 'Confirmar Cambios';
            btn.disabled = false;
        }
    }

    // 4. Toasts UI
    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-6 right-6 z-[200] flex flex-col gap-3';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        const bgClass = type === 'error' ? 'bg-gradient-to-r from-rose-600 to-red-500' : 'bg-gradient-to-r from-emerald-500 to-teal-500';
        toast.className = `${bgClass} text-white px-5 py-3 rounded-2xl font-bold text-sm transition-all duration-300 transform translate-x-full opacity-0 flex items-center gap-2.5 min-w-[280px] max-w-[420px] shadow-xl`;
        toast.innerHTML = `<span class="flex-1">${message}</span><button onclick="this.parentElement.remove()" class="shrink-0">✕</button>`;
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.remove('translate-x-full', 'opacity-0'));
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300);
        }, 4000);
    }
    // =========================================================
    // EFECTO HOLOGRÁFICO Y AUDIO HOVER (Estilo Explorer)
    // =========================================================
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.holo-white-card');
        
        // Precarga del sonido en memoria
        const hoverSound = new Audio('{{ asset("audio/hover-pop.mp3") }}');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                // Clonamos el nodo para permitir reproducciones rápidas y simultáneas
                let soundClone = hoverSound.cloneNode();
                soundClone.volume = 0.15;
                soundClone.play().catch(error => {
                    // Ignora bloqueos de autoplay del navegador antes de la primera interacción táctil
                });

                if (!this.classList.contains('is-animating')) {
                    this.classList.add('is-animating');
                    setTimeout(() => {
                        this.classList.remove('is-animating');
                    }, 500);
                }
            });
        });
    });
</script>
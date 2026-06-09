@php
    // Cargamos las últimas 15 notificaciones y contamos las no leídas para la campanita
    $notifications = auth()->user()->notifications()->take(15)->get();
    $unreadCount = auth()->user()->unreadNotifications->count();
@endphp

<div class="relative" id="notifications-wrapper">
    {{-- 1. EL BOTÓN DE LA CAMPANA --}}
    <button type="button" onclick="toggleNotifications()" class="relative p-2 text-zinc-500 hover:text-zinc-900 transition-colors focus:outline-none rounded-full hover:bg-zinc-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        {{-- Punto Rojo (Solo si hay no leídas) --}}
        @if($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 flex h-3 w-3" id="notification-badge">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500 border-2 border-white"></span>
            </span>
        @endif
    </button>

    {{-- 2. EL PANEL DROPDOWN (AHORA RESPONSIVO Y CENTRADO EN MÓVILES) --}}
    <div id="notifications-panel" class="hidden 
        fixed top-20 left-1/2 -translate-x-1/2 w-[calc(100vw-2rem)] max-w-sm
        sm:absolute sm:top-full sm:left-auto sm:translate-x-0 sm:right-0 sm:mt-2 sm:w-96 sm:max-w-none
        bg-white rounded-2xl shadow-xl border border-zinc-200 z-50 overflow-hidden 
        transform transition-all duration-200 opacity-0 scale-95 origin-top sm:origin-top-right">
        
        {{-- Header del Panel --}}
        <div class="px-4 py-3 border-b border-zinc-100 flex justify-between items-center bg-zinc-50/50">
            <h3 class="text-sm font-black text-zinc-900 tracking-tight">Notificaciones</h3>
            @if($unreadCount > 0)
                <form action="{{ route('notifications.read.all') }}" method="POST" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-widest">
                        Marcar todo como leído
                    </button>
                </form>
            @endif
        </div>

        {{-- Lista de Notificaciones --}}
        <div class="max-h-[60vh] overflow-y-auto">
            @forelse($notifications as $notification)
                @php
                    $isRead = !is_null($notification->read_at);
                @endphp
                
                {{-- Contenedor de la notificación con estilos condicionales --}}
                <div id="notification-{{ $notification->id }}" 
                     class="px-4 py-4 border-b border-zinc-100 relative group transition-all duration-300 {{ $isRead ? 'bg-zinc-50/80 opacity-80 hover:bg-zinc-100/80' : 'bg-white hover:bg-zinc-50' }}">
                    
                    {{-- Indicador de no leído y botón de acción (solo si es nueva) --}}
                    @if(!$isRead)
                        <div class="unread-dot absolute top-5 right-4 w-2 h-2 bg-indigo-500 rounded-full"></div>
                        
                        <button onclick="markAsRead('{{ $notification->id }}', this)" class="absolute top-4 right-8 text-zinc-300 hover:text-indigo-600 opacity-0 group-hover:opacity-100 transition-all mark-read-btn" title="Marcar como leída">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    @endif
                    @php
                        // Asignamos un icono por defecto ('info') si la notificación no trae la llave 'icon'
                    @endphp
                    <div class="flex gap-3 pr-8">
                        {{-- Icono Dinámico --}}
                        <div class="shrink-0 mt-0.5">
                            @if($notification->data['icon'] === 'error')
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></span>
                            @elseif($notification->data['icon'] === 'success')
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                            @elseif($notification->data['icon'] === 'warning')
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></span>
                            @else
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                            @endif
                        </div>
                        
                        {{-- Contenido --}}
                        <div>
                            <p class="text-sm font-bold text-zinc-900 leading-tight">
                                {{ $notification->data['title'] }}
                            </p>
                            <p class="text-xs text-zinc-500 mt-1 leading-relaxed">
                                {{ $notification->data['message'] }}
                            </p>
                            <p class="text-[10px] font-bold text-zinc-400 mt-2 uppercase tracking-widest">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-100 mb-3">
                        <svg class="w-6 h-6 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <p class="text-sm text-zinc-500 font-medium">No tienes notificaciones recientes.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- 3. JAVASCRIPT --}}
<script>
    function toggleNotifications() {
        const panel = document.getElementById('notifications-panel');
        
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            setTimeout(() => {
                panel.classList.remove('opacity-0', 'scale-95');
                panel.classList.add('opacity-100', 'scale-100');
            }, 10);
        } else {
            panel.classList.remove('opacity-100', 'scale-100');
            panel.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                panel.classList.add('hidden');
            }, 200);
        }
    }

    document.addEventListener('click', function(event) {
        const wrapper = document.getElementById('notifications-wrapper');
        const panel = document.getElementById('notifications-panel');
        if (wrapper && !wrapper.contains(event.target) && !panel.classList.contains('hidden')) {
            toggleNotifications();
        }
    });

    // Marcar como leída vía Fetch API (AJAX) - Transformación visual
    function markAsRead(id, buttonElement) {
        fetch(`/notificaciones/${id}/leer`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(response => {
            if(response.ok) {
                const notifDiv = document.getElementById(`notification-${id}`);
                
                // 1. Transformación visual: Pasar a "grisáceo"
                notifDiv.classList.remove('bg-white', 'hover:bg-zinc-50');
                notifDiv.classList.add('bg-zinc-50/80', 'opacity-80', 'hover:bg-zinc-100/80');

                // 2. Eliminar el botón y el puntito azul de nueva notificación
                buttonElement.remove();
                const unreadDot = notifDiv.querySelector('.unread-dot');
                if (unreadDot) unreadDot.remove();

                // 3. Revisar si quedan puntitos azules para apagar el globo rojo principal
                const panel = document.getElementById('notifications-panel');
                if(panel.querySelectorAll('.unread-dot').length === 0) {
                    const badge = document.getElementById('notification-badge');
                    if(badge) badge.remove();
                }
            }
        });
    }
</script>
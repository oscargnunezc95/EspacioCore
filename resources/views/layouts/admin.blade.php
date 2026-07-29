<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin — {{ $metaTitle ?? config('app.name', 'EstadoPrisma') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-stone-800 antialiased">

    <div class="flex min-h-screen">

        {{-- ============================================
             SIDEBAR — Lateral fijo en escritorio,
             oculto en móvil, se despliega con toggle
             ============================================ --}}
        <aside id="admin-sidebar"
               class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-zinc-900
                      -translate-x-full transition-transform duration-300 ease-in-out
                      lg:static lg:inset-auto lg:translate-x-0"
               aria-label="Navegación de administración">

            {{-- Marca / Logotipo --}}
            <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-zinc-800 px-6">
                {{-- Ícono minimalista --}}
                <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span class="text-base font-semibold tracking-tight text-white">
                    EstadoPrisma <span class="font-normal text-stone-400">Admin</span>
                </span>
            </div>

            {{-- Navegación principal --}}
            <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-1">
                {{-- Gestión de Estudios --}}
                <a href="{{ route('admin.studios.index') }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200
                          {{ request()->is('admin/estudios*') ? 'bg-zinc-800 text-red-400 font-black' : 'text-stone-400 hover:bg-zinc-800/60 hover:text-stone-200' }}"
                   {{ request()->is('admin/estudios*') ? 'aria-current="page"' : '' }}>
                    <svg class="h-5 w-5 shrink-0 {{ request()->is('admin/estudios*') ? 'text-red-400' : 'text-stone-500 group-hover:text-stone-300' }} transition-colors duration-200"
                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3 21v-9l4.5-3M3 12l9-6 9 6M7.5 9v12M16.5 21V9M9 14h2.5M12.5 14H15" />
                    </svg>
                    Gestión de Estudios
                </a>

                {{-- Facturación y Pisos Mínimos --}}
                <a href="{{ route('admin.billing.index') }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200
                          {{ request()->is('admin/billing*') ? 'bg-zinc-800 text-red-400 font-black' : 'text-stone-400 hover:bg-zinc-800/60 hover:text-stone-200' }}"
                   {{ request()->is('admin/billing*') ? 'aria-current="page"' : '' }}>
                    <svg class="h-5 w-5 shrink-0 {{ request()->is('admin/billing*') ? 'text-red-400' : 'text-stone-500 group-hover:text-stone-300' }} transition-colors duration-200"
                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                    Facturación
                </a>

                {{-- Planes de Suscripción --}}
                <a href="{{ route('admin.plans.index') }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200
                          {{ request()->is('admin/planes*') ? 'bg-zinc-800 text-red-400 font-black' : 'text-stone-400 hover:bg-zinc-800/60 hover:text-stone-200' }}"
                   {{ request()->is('admin/planes*') ? 'aria-current="page"' : '' }}>
                    <svg class="h-5 w-5 shrink-0 {{ request()->is('admin/planes*') ? 'text-red-400' : 'text-stone-500 group-hover:text-stone-300' }} transition-colors duration-200"
                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Planes de Suscripción
                </a>
            </nav>

            {{-- Footer del Sidebar — usuario + salir --}}
            <div class="shrink-0 border-t border-zinc-800 px-3 py-4">
                <div class="flex items-center gap-3 rounded-xl px-3 py-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-700 text-xs font-medium text-zinc-300">
                        {{ strtoupper(substr(Auth::user()?->name ?? 'A', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-200">{{ Auth::user()?->name ?? 'Admin' }}</p>
                        <p class="truncate text-xs text-zinc-500">{{ Auth::user()?->email ?? '' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-stone-400
                                   transition-all duration-200 hover:bg-zinc-800/60 hover:text-stone-200">
                        <svg class="h-5 w-5 shrink-0 text-zinc-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        {{-- Overlay para mobile — cubre el contenido al abrir sidebar --}}
        <div id="sidebar-overlay"
             class="fixed inset-0 z-40 bg-zinc-900/50 backdrop-blur-sm opacity-0 pointer-events-none
                    transition-opacity duration-300 lg:hidden"
             aria-hidden="true">
        </div>

        {{-- ============================================
             CONTENIDO PRINCIPAL
             ============================================ --}}
        <div class="flex flex-1 flex-col min-w-0">

            {{-- Header superior --}}
            <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-4 border-b border-stone-200 bg-white px-4 sm:px-6 lg:px-8">
                {{-- Botón hamburguesa (solo visible en móvil) --}}
                <button id="sidebar-toggle"
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl p-2 text-stone-500
                               transition-all duration-200 hover:bg-stone-100 hover:text-stone-700
                               focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2
                               lg:hidden"
                        aria-expanded="false"
                        aria-controls="admin-sidebar"
                        aria-label="Abrir menú de navegación">
                    {{-- Ícono hamburguesa --}}
                    <svg class="h-6 w-6" id="hamburger-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                {{-- Título de página y acciones (heredado del slot header) --}}
                @isset($header)
                    <div class="flex-1 min-w-0">
                        {{ $header }}
                    </div>
                @endisset
            </header>

            {{-- Área de contenido principal --}}
            <main class="flex-1">
                {{ $slot }}
            </main>

        </div>
    </div>

    {{-- ============================================
         JAVASCRIPT VANILLA — Sidebar toggle en móvil
         ============================================ --}}
    <script>
        (function () {
            const sidebar  = document.getElementById('admin-sidebar');
            const toggle   = document.getElementById('sidebar-toggle');
            const overlay  = document.getElementById('sidebar-overlay');

            if (!sidebar || !toggle || !overlay) return;

            let isOpen = false;

            function openSidebar() {
                isOpen = true;
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                overlay.classList.add('opacity-100', 'pointer-events-auto');
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                isOpen = false;
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            toggle.addEventListener('click', function () {
                if (isOpen) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            overlay.addEventListener('click', closeSidebar);

            // Cerrar con tecla Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && isOpen) {
                    closeSidebar();
                    toggle.focus();
                }
            });

            // Cerrar sidebar al navegar (clic en enlace del sidebar)
            sidebar.addEventListener('click', function (e) {
                const link = e.target.closest('a[href]');
                if (link && isOpen) {
                    // Pequeña demora para que se vea el feedback táctil
                    setTimeout(closeSidebar, 150);
                }
            });
        })();
    </script>

</body>
</html>

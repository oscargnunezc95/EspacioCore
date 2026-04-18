<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Movaereo - Gestión de Estudio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .font-kalam { font-family: 'Kalam', cursive; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-blue-600 tracking-tighter italic">MOVAEREO</span>
                </div>
                <div class="flex space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-blue-600 font-bold transition">Inicio</a>
                    <a href="{{ route('students.index') }}" class="text-gray-600 hover:text-blue-600 font-bold transition">Alumnos</a>
                    <a href="{{ route('workshops.index') }}" class="text-gray-600 hover:text-blue-600 font-bold transition">Clases</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow py-10">
        @yield('content')
    </main>

    <footer class="bg-white border-t py-6 text-center text-gray-400 text-sm">
        &copy; {{ date('Y') }} Movaereo Studio - Sistema de Gestión
    </footer>
</body>
</html>
<x-mail::message>
# ¡Bienvenido a EstadoPrisma!

Hola, nos emociona mucho que tu estudio se una a nuestra plataforma. 
A partir de hoy, tienes el control total de tu academia, alumnos y finanzas.

<x-mail::panel>
Tu panel de control ya está activo y listo para configurar tus primeros talleres.
</x-mail::panel>

<x-mail::button :url="route('dashboard')">
Ir a mi Panel de Control
</x-mail::button>

Gracias por confiar en nosotros,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
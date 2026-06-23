<x-mail::message>
# ¡Bienvenido a {{ $studio->name }}!

¡Hola, {{ explode(' ', $userName)[0] }}!

Felicidades!! Ahora eres parte de la comunidad de **{{ $studio->name }}**.

A partir de este momento, el estudio podrá agregarte a sus clases si es que se te olvidara inscribirte en ellas.

<x-mail::button :url="route('login')">
Ir a mi Panel
</x-mail::button>
</x-mail::message>

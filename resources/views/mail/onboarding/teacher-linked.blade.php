<x-mail::message>
# ¡Te han agregado como Profesor/a!

¡Hola, {{ explode(' ', $userName)[0] }}!

El equipo de **{{ $studio->name }}** te ha agregado a su staff como **profesor/a**.

Desde tu **Portal de Profesor** podrás revisar tu calendario de clases, tomar asistencia y ver los alumnos inscritos en tus talleres.

<x-mail::panel>
Usa tus credenciales habituales para ingresar.
</x-mail::panel>

<x-mail::button :url="route('login')">
Ingresar a mi Portal
</x-mail::button>
</x-mail::message>

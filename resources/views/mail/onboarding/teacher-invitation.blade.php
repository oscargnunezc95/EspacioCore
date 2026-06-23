<x-mail::message>
# ¡Hola, {{ $teacher->first_name }}!

El equipo de **{{ $studio->name }}** te ha agregado como profesor/a en su plataforma de gestión deportiva.

A partir de ahora, podrás ingresar a tu **Portal de Profesor** para revisar tu calendario de clases, gestionar asistencias y visualizar a los alumnos inscritos en tus talleres.

@if ($temporaryPassword)
<x-mail::panel>
**Tus Credenciales de Acceso**

- **Email:** {{ $teacher->email }}
- **Contraseña temporal:** {{ $temporaryPassword }}
</x-mail::panel>

<x-mail::button :url="route('login')">
Ingresar a mi Portal
</x-mail::button>

*Te recomendamos cambiar esta contraseña temporal desde la configuración de tu perfil una vez que inicies sesión.*
@else
<x-mail::panel>
Para acceder a tu portal necesitas crear una cuenta gratuita.
</x-mail::panel>

<x-mail::button :url="route('register', ['email' => $teacher->email, 'national_id' => $teacher->national_id, 'country_id' => $teacher->country_id])">
Crear mi Cuenta Gratuita
</x-mail::button>

*Solo toma un minuto. Una vez registrada/o, tus fichas se vincularán automáticamente.*
@endif
</x-mail::message>

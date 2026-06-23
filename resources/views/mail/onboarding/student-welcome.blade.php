<x-mail::message>
# ¡Bienvenida a {{ $studio->name }}!

¡Hola, {{ explode(' ', $student->name)[0] }}!

¡Te damos la bienvenida oficial a **{{ $studio->name }}**!

La administración del estudio ha creado tu ficha de alumna. Ahora cuentas con un **Portal de Alumna** privado donde podrás ver tus próximas clases, revisar tu historial de pagos y explorar nuevos talleres disponibles.

<x-mail::panel>
**Tus Credenciales de Acceso**

- **Email:** {{ $student->email }}
- **Contraseña temporal:** {{ $temporaryPassword }}
</x-mail::panel>

<x-mail::button :url="route('login')">
Ingresar a mi Portal
</x-mail::button>

*Por seguridad, te pedimos que cambies esta contraseña en la sección de configuración de tu perfil la primera vez que ingreses.*
</x-mail::message>

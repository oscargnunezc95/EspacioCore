<x-mail::message>
# ¡Ca-ching! Nueva Venta 💰

Felicidades, el estudio **{{ $studio->name }}** acaba de pagar exitosamente su suscripción y subió al **Plan {{ $planName }}**.

<x-mail::panel>
**Datos del Cliente:**
- **Estudio:** {{ $studio->name }}
- **Administrador:** {{ $studio->user->name }}
- **Correo:** {{ $studio->user->email }}
</x-mail::panel>

¡Sigue así, el SaaS está creciendo!

<x-mail::button :url="route('studios.index')">
Ver Estudios en la Base de Datos
</x-mail::button>
</x-mail::message>
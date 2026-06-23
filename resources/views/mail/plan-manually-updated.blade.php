<x-mail::message>
# Actualización de Plan

Hola **{{ $studio->user->name }}**,

Te escribimos para confirmarte que hemos actualizado la configuración de tu cuenta. A partir de este momento, tu estudio **{{ $studio->name }}** cuenta oficialmente con los beneficios del plan **{{ $planName }}**.

<x-mail::panel>
**Nota administrativa:** Este cambio fue aplicado directamente por nuestro equipo para ajustar las características de tu espacio. **No se realizarán cobros inesperados por esta acción.**
</x-mail::panel>

Puedes revisar los detalles actuales de tu suscripción y las nuevas herramientas habilitadas directamente en tu panel de control.

<x-mail::button :url="route('dashboard', ['subdomain' => $studio->subdomain])">
Ir a mi Panel de Control
</x-mail::button>

Si crees que esto es un error o tienes dudas sobre la configuración de tus comisiones, simplemente responde a este correo y te ayudaremos de inmediato.
</x-mail::message>

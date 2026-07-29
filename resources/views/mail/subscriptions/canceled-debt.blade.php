<x-mail::message>
# ❌ Tu suscripción ha sido cancelada

Hola **{{ $studio->user->name }}**,

Lamentamos informarte que, tras **5 días** sin poder procesar el cobro de tu suscripción en **{{ $studio->name }}**, tu cuenta ha sido movida automáticamente al **Plan Gratuito**.

<x-mail::panel>
**¿Qué pasó?**
- El cobro automático falló repetidamente durante el período de gracia.
- Tu suscripción de pago ha sido cancelada definitivamente.
- Tu estudio ahora opera bajo el Plan Gratuito.
</x-mail::panel>

### ¿Qué NO has perdido?

Tranquilo, **no has perdido ningún dato**. Todos tus alumnos, talleres, configuraciones y horarios siguen intactos. Lo único que cambia es que ahora tienes acceso a las funcionalidades del Plan Gratuito.

### ¿Qué puedes hacer ahora?

1. **Seguir usando EstadoPrisma gratuitamente** con las herramientas esenciales.
2. **Reactivar tu plan** cuando quieras desde tu panel de control — solo necesitarás un método de pago válido.
3. Si crees que esto fue un error, responde a este correo y lo revisaremos juntos.

<x-mail::button :url="route('dashboard', ['subdomain' => $studio->subdomain])">
Ir a mi Panel de Control
</x-mail::button>

Gracias por confiar en EstadoPrisma.
— El equipo de EstadoPrisma
</x-mail::message>

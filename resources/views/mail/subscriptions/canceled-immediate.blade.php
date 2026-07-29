<x-mail::message>
# 🔄 Cancelación procesada y reembolso emitido

Hola **{{ $studio->user->name }}**,

Tu solicitud de cancelación para el estudio **{{ $studio->name }}** ha sido procesada. Como te encontrabas dentro del **período de gracia de 7 días**, tu plan ha vuelto a **Gratuito de inmediato** y hemos emitido el **reembolso total** a tu tarjeta.

<x-mail::panel>
**Resumen de la cancelación:**
- ✅ Tu suscripción de pago ha sido cancelada.
- 💰 Se ha emitido el reembolso del 100% a tu método de pago original.
- 🆓 Tu estudio ahora opera bajo el Plan Gratuito.
- 🗂️ Todos tus datos (alumnos, talleres, horarios) siguen intactos.
</x-mail::panel>

### ¿Cuándo veré el reembolso?

El tiempo de procesamiento depende de tu banco o entidad emisora. Generalmente se refleja entre **5 y 15 días hábiles** en tu estado de cuenta.

### ¿Qué sigue?

Puedes seguir usando EstadoPrisma con las funcionalidades del Plan Gratuito. Si en el futuro deseas volver a un plan de pago, puedes hacerlo desde tu panel de control en cualquier momento.

<x-mail::button :url="route('dashboard', ['subdomain' => $studio->subdomain])">
Ir a mi Panel de Control
</x-mail::button>

Gracias por haber confiado en nosotros. Esperamos verte de vuelta cuando estés listo.
— El equipo de EstadoPrisma
</x-mail::message>

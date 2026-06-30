<x-mail::message>
# ⚠️ Tu suscripción requiere atención

Hola **{{ $studio->user->name }}**,

El pago de tu suscripción en **{{ $studio->name }}** no se ha podido procesar. Tu estudio ha entrado en **período de gracia** y dispones de **5 días** para regularizar tu situación antes de que tu cuenta pase automáticamente al plan gratuito.

<x-mail::panel>
**¿Qué significa esto?**
- Tu estudio sigue funcionando con todas las funciones de tu plan actual durante {{ 5 - now()->diffInDays($studio->subscription_expires_at) }} días más.
- Si no se recibe el pago en ese plazo, tu cuenta se moverá al **plan Free** automáticamente.
- No perderás ningún dato: tus alumnos, talleres y configuraciones seguirán intactos.
</x-mail::panel>

### ¿Qué puedes hacer?

1. **Revisar tu método de pago** en Mercado Pago y asegurarte de que tenga fondos suficientes.
2. Mercado Pago reintentará el cobro automáticamente en los próximos días.
3. Si necesitas cambiar de plan o tienes dudas, responde a este correo y te ayudaremos.

<x-mail::button :url="route('dashboard', ['subdomain' => $studio->subdomain])">
Ir a mi Panel de Control
</x-mail::button>

Gracias por confiar en EstadoPrisma.
— El equipo de EstadoPrisma
</x-mail::message>

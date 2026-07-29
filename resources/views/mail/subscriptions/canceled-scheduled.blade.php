<x-mail::message>
# 📅 Cancelación programada con éxito

Hola **{{ $studio->user->name }}**,

Hemos registrado tu solicitud de cancelación para el estudio **{{ $studio->name }}**. Queremos contarte exactamente qué significa esto para que no haya sorpresas.

<x-mail::panel>
**¿Qué cambia desde ahora?**
- 🛑 **No se harán más cobros automáticos.** Tu método de pago no será cargado nuevamente.
- 💎 **Mantienes todos tus beneficios Premium** hasta el {{ \Carbon\Carbon::parse($studio->subscription_expires_at)->translatedFormat('d \d\e F \d\e Y') }}.
- 🆓 Al finalizar tu ciclo actual, tu cuenta pasará automáticamente al **Plan Gratuito**.
- 🗂️ No perderás ningún dato: tus alumnos, talleres y configuraciones permanecen intactos.
</x-mail::panel>

### Disfruta hasta el último día

Tu decisión es respetada, y queremos que aproveches al máximo cada herramienta Premium hasta el último minuto de tu ciclo de facturación. Si cambias de opinión, siempre puedes reactivar tu suscripción desde tu panel de control antes de que finalice el período actual.

<x-mail::button :url="route('dashboard', ['subdomain' => $studio->subdomain])">
Ir a mi Panel de Control
</x-mail::button>

Gracias por haber formado parte de EstadoPrisma. Estaremos aquí si decides volver.
— El equipo de EstadoPrisma
</x-mail::message>

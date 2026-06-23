<x-mail::message>
# ¡Pago procesado con éxito!

Hola, hemos recibido tu pago correctamente. Tu estudio **{{ $studio->name }}** ahora disfruta de todos los beneficios del **Plan {{ $planName }}**.

<x-mail::panel>
**Detalle de la transacción:**
- **Estudio:** {{ $studio->name }}
- **Plan:** {{ $planName }}
- **Total pagado:** ${{ number_format($amount, 0, ',', '.') }} CLP
</x-mail::panel>

Ya puedes acceder a tu panel y utilizar todas las herramientas avanzadas para hacer crecer tu academia.

<x-mail::button :url="route('dashboard', ['subdomain' => $studio->subdomain])">
Ir a mi Panel de Control
</x-mail::button>

</x-mail::message>
<x-mail::message>

@if ($paymentType === 'promocion')
# 🌟 ¡Promoción Especial — {{ $studio->name }}!
@elseif ($paymentType === 'pack')
# 📦 Pack de {{ $classCount }} Clases — {{ $studio->name }}
@else
# Link de Pago — {{ $studio->name }}
@endif

Hola, **{{ $student->name }}**. Tu estudio **{{ $studio->name }}** te ha enviado un link para realizar tu pago.

<x-mail::panel>
### 🧾 Detalle del Cobro

| Clase | Monto |
|-------|-------|
@foreach ($breakdown as $item)
@if (!($item['is_discount'] ?? false))
| {{ $item['name'] }}
@if (!empty($item['badges']))
  <br><small style="color:#6b7280">{{ implode(' · ', $item['badges']) }}</small>
@endif
| ${{ number_format($item['subtotal'], 0, ',', '.') }} |
@endif
@endforeach

@php $discounts = array_filter($breakdown, fn($b) => $b['is_discount'] ?? false); @endphp
@if (!empty($discounts))
| | |
@foreach ($discounts as $discount)
| 🏷️ {{ $discount['name'] }} | <span style="color:#16a34a">−${{ number_format(abs($discount['subtotal']), 0, ',', '.') }}</span> |
@endforeach
@endif

---

| **💵 Total** | **${{ number_format($totalAmount, 0, ',', '.') }}** |
</x-mail::panel>

@if ($paymentType === 'promocion')
<x-mail::panel>
🌟 **¡Se aplicó una promoción a tu compra!** Aprovecha este descuento por tiempo limitado.
</x-mail::panel>
@elseif ($paymentType === 'pack')
<x-mail::panel>
📦 Estás adquiriendo un **pack de {{ $classCount }} clases**. ¡Mejor precio que pagar clase por clase!
</x-mail::panel>
@endif

Haz clic en el botón de abajo para ir a la pasarela de pago segura de MercadoPago y completar tu pago.

<x-mail::button :url="$paymentLink" color="success">
💳 Ir a Pagar
</x-mail::button>

Si tienes problemas con el botón, puedes copiar y pegar este link en tu navegador:

[{{ $paymentLink }}]({{ $paymentLink }})

Gracias,<br>
Equipo de **{{ $studio->name }}**
</x-mail::message>

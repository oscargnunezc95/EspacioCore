<x-mail::message>
# Reembolso Procesado

Hola {{ $student->first_name }},

Tu pago de **${{ number_format($refundedAmount, 0, ',', '.') }}** por la clase **{{ $session->workshop->name }}** en **{{ $studio->name }}** fue recibido exitosamente. Sin embargo, los cupos se agotaron apenas unos instantes antes de que pudiéramos confirmar tu lugar.

<x-mail::panel>
**Tu dinero está a salvo:** El monto total ha sido devuelto a tu medio de pago a través de Mercado Pago. El reembolso puede tardar unos días hábiles en reflejarse según tu banco o tarjeta.
</x-mail::panel>

Te pedimos disculpas por este contratiempo. Te invitamos a explorar otras sesiones disponibles de {{ $studio->name }} o clases similares en [EstadoPrisma]({{ config('app.url') }}).

### Detalles de la clase

- **Taller:** {{ $session->workshop->name }}
- **Fecha:** {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l d \d\e F') }}
- **Hora:** {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} hrs
- **Estudio:** {{ $studio->name }}
- **Monto reembolsado:** ${{ number_format($refundedAmount, 0, ',', '.') }}
</x-mail::message>

<x-mail::message>
# ¡Tu pago ha sido registrado!

Hola, **{{ $studentName }}**. Te confirmamos que hemos recibido correctamente tu pago en **{{ $studio->name }}**.

<x-mail::panel>
### Detalle del Comprobante:
- **Concepto:** {{ $payment->description ?? 'Mensualidad / Taller' }}
- **Monto:** ${{ number_format($payment->amount, 0, ',', '.') }} CLP
- **Fecha:** {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}
</x-mail::panel>

Para revisar tus clases vigentes, asistencias o reservar nuevas sesiones, puedes ingresar directamente a tu portal de alumno.

<x-mail::button :url="route('global.classes.student')">
Ir a mi Portal de Alumno
</x-mail::button>

</x-mail::message>
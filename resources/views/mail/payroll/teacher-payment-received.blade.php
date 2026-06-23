<x-mail::message>
# ¡Liquidación recibida! 💰

Hola, **{{ $payment->teacher->first_name }}**. El estudio **{{ $studio->name }}** ha procesado el pago de tu liquidación mensual.

<x-mail::panel>
### Detalle del Pago:
- **Período:** {{ \Carbon\Carbon::parse($payment->month_year . '-01')->translatedFormat('F Y') }}
- **Monto:** ${{ number_format($payment->amount, 0, ',', '.') }} CLP
- **Método:** {{ $payment->payment_method === 'manual' ? 'Manual (comprobante)' : 'Mercado Pago' }}
- **Fecha de pago:** {{ \Carbon\Carbon::parse($payment->updated_at)->format('d/m/Y') }}
</x-mail::panel>

Puedes revisar tu historial de liquidaciones y tus próximos talleres desde tu portal de profesor.

<x-mail::button :url="route('global.classes.teacher')">
Ir a mi Portal
</x-mail::button>

</x-mail::message>

<x-mail::message>
# Nuevo ingreso registrado 💰

Se ha registrado un nuevo pago en tu plataforma de gestión para **{{ $studio->name }}**.

<x-mail::panel>
### Datos de la Transacción:
- **Alumno:** {{ $studentName }}
- **Concepto:** {{ $payment->description ?? 'Pago General' }}
- **Monto:** ${{ number_format($payment->amount, 0, ',', '.') }} CLP
- **Método de Registro:** Sistema interno
</x-mail::panel>

El saldo y los reportes financieros del mes ya se encuentran actualizados en tu panel administrativo.

<x-mail::button :url="route('dashboard', ['subdomain' => $studio->subdomain])">
Ver Panel de Gestión
</x-mail::button>

{{ config('app.name') }} - Automatizando el crecimiento de tu negocio.
</x-mail::message>
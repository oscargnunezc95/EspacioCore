@component('mail::message')
# ¡Hola, {{ $student->user->name }}!

Te escribimos para avisarte que el centro deportivo **{{ $studio->name }}** te ha ingresado en su directorio interno de alumnos utilizando tus datos de identificación oficial.

Hemos detectado la coincidencia de forma automática y **hemos unificado tus perfiles de forma segura**.

### ¿Qué significa esto para ti?
* A partir de ahora, cualquier clase o reserva que la administración agende a tu nombre aparecerá inmediatamente en tu aplicación.
* Puedes revisar tus cobros pendientes, packs vigentes e historial de asistencias de este estudio de forma unificada.

@component('mail::button', ['url' => route('global.payments.index')])
Ir a mi Portal de Pagos
@endcomponent

Si no reconoces tu vinculación con este espacio de entrenamiento, por favor ponte en contacto con nuestro equipo de soporte respondiendo a este correo.

Saludos,<br>
El equipo de {{ config('app.name') }}
@endcomponent
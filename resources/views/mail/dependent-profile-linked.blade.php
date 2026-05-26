@component('mail::message')
# Control Familiar Activo

Hola, te notificamos una actualización en tu cuenta de tutor / apoderado.

La administración de **{{ $studio->name }}** ha registrado una ficha local para tu familiar **{{ $student->first_name }} {{ $student->last_name }}**.

Debido a las reglas de vinculación familiar de nuestra plataforma, **esta ficha ha sido enlazada a tu cuenta global como Responsable Financiero**.

### Implicancias de Cobro:
* Las reservas asociadas a tu familiar que se encuentren pendientes de pago se derivarán directamente a tu carrito personal.
* Tienes el control total en tu Portal de Pagos para decidir qué clases confirmar de manera granular e individual.

@component('mail::button', ['url' => route('global.payments.index')])
Revisar Estado de Cuentas
@endcomponent

Esta automatización asegura que la asistencia de tus familiares esté siempre garantizada en el estudio sin perder el control centralizado de tu presupuesto.

Gracias por confiar en nosotros,<br>
El equipo de {{ config('app.name') }}
@endcomponent
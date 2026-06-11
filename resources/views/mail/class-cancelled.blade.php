@component('mail::message')
# Clase Cancelada

Hola,

Te escribimos de parte de **{{ $studio->name }}** para avisarte de un cambio importante en la agenda.

La clase de **{{ $session->workshop->name }}** programada para el **{{ \Carbon\Carbon::parse($session->date)->format('d-m-Y') }}** a las **{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}** ha sido **cancelada** por la administración.

> **Importante:** Si ya habías pagado o reservado tu cupo para esta sesión, el sistema interno ya lo tiene registrado. Por favor, comunícate directamente con la administración del estudio para reagendar tu clase o revisar la devolución de tu cupo.

Lamentamos los inconvenientes que esto pueda causarte.

Saludos,<br>
La administración de {{ $studio->name }}
@endcomponent
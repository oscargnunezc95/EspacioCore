<x-mail::message>
# ✅ ¡Tu demo está agendada, {{ explode(' ', $name)[0] }}!

Gracias por tu interés en **EstadoPrisma**. Tu videollamada de demostración ha sido agendada con éxito.

<x-mail::panel>
- **📅 Fecha:** {{ \Carbon\Carbon::parse($date)->translatedFormat('l j \d\e F, Y') }}
- **🕐 Hora:** {{ $time }} hrs (Chile)
- **🔗 Sala de videollamada:** {{ $jitsiLink }}
</x-mail::panel>

El día y hora indicados, solo haz clic en el botón de abajo para unirte a la videollamada. No necesitas instalar nada — funciona directamente en tu navegador.

<x-mail::button :url="$jitsiLink">
🎥 Unirse a la videollamada
</x-mail::button>

*Si tienes dudas antes de la reunión, responde este correo y te ayudaremos.*
</x-mail::message>

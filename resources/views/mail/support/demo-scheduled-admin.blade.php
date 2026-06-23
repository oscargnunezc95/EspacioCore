<x-mail::message>
# 📹 Nueva demo agendada

**Prospecto:** {{ $name }}

**Email:** {{ $email }}

**Fecha:** {{ \Carbon\Carbon::parse($date)->translatedFormat('l j \d\e F, Y') }}

**Hora:** {{ $time }} hrs (Chile)

**Sala Jitsi:** [{{ $jitsiLink }}]({{ $jitsiLink }})

@if($body)
<x-mail::panel>
**Mensaje del prospecto:**

{{ $body }}
</x-mail::panel>
@endif
</x-mail::message>

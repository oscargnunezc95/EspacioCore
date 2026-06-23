<x-mail::message>
# 📩 Nueva consulta de soporte

**Nombre:** {{ $name }}

**Email:** {{ $email }}

@if($body)
<x-mail::panel>
**Mensaje:**

{{ $body }}
</x-mail::panel>
@endif
</x-mail::message>

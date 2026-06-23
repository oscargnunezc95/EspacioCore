<x-mail::message>
# 🔄 Solicitud de transferencia de dependiente

**{{ $requester->name }}** quiere agregar a **{{ $dependent->first_name }} {{ $dependent->last_name }}** a su grupo familiar, pero actualmente figura como tu dependiente.

Esto puede ocurrir si compartes la tuición legal o si la persona se registró de forma independiente y tus clases quedarán duplicadas.

<x-mail::panel>
**Detalles del dependiente:**

- Nombre: {{ $dependent->first_name }} {{ $dependent->last_name }}
- Documento: {{ $dependent->national_id }}
- Parentesco registrado: {{ $dependent->relationship ?? 'No especificado' }}
</x-mail::panel>

Para resolverlo, ingresa a la sección **Mi Familia** en tu perfil de EstadoPrisma y elimina el dependiente si corresponde.

<x-mail::button :url="route('profile.family.index')">
Ir a Mi Familia
</x-mail::button>
</x-mail::message>

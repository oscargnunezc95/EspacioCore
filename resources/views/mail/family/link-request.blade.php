<x-mail::message>
# 📋 Solicitud de Vínculo Familiar

Hola {{ $targetUser->name }},

**{{ $requester->name }}** solicita tu autorización para administrar tus clases en EstadoPrisma como **{{ $dependent->relationship ?? 'familiar' }}**.

Si aceptas, {{ $requester->name }} podrá:

- Inscribirte en clases y talleres
- Gestionar tus reservas y horarios
- Realizar pagos en tu nombre en los estudios donde tengas ficha

Tus clases actuales y tu cuenta personal se mantienen sin cambios. Siempre podrás revocar este vínculo desde la sección **Mi Familia** de tu perfil.

<x-mail::panel>
**Datos del registro:**

- Nombre: {{ $dependent->first_name }} {{ $dependent->last_name }}
- Documento: {{ $dependent->national_id }}
- Parentesco: {{ $dependent->relationship ?? 'No especificado' }}
</x-mail::panel>

<x-mail::panel>
**⚠️ Esta solicitud expira en 7 días.** Si no respondes, el vínculo no se activará y {{ $requester->name }} no podrá gestionar tus clases.
</x-mail::panel>

<x-mail::button :url="$acceptUrl">
✅ Aceptar Solicitud
</x-mail::button>

<x-mail::button :url="$rejectUrl">
❌ Rechazar
</x-mail::button>

*Si no deseas que {{ $requester->name }} administre tus clases, simplemente haz clic en **Rechazar** y el registro será eliminado.*
</x-mail::message>

<x-mail::message>
# ⚠️ Mercado Pago Desvinculado

¡Hola, {{ $studio->user->name ?? 'Estudio' }}!

Te informamos que tu cuenta de Mercado Pago **ha sido desvinculada** de tu portal en EstadoPrisma.

<x-mail::panel>
A partir de este momento, tus alumnas ya no podrán realizar pagos online mediante la plataforma. Cualquier deuda en el carrito deberá ser saldada de forma presencial o por transferencia directa.
</x-mail::panel>

Si deseas volver a activar los pagos automáticos, simplemente dirígete a la configuración de tu estudio y vuelve a vincular tu cuenta.

Si tú no solicitaste esta desvinculación, por favor ponte en contacto con nuestro equipo de soporte técnico a la brevedad.
</x-mail::message>

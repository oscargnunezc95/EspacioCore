<x-mail::message>
# ¡Hola, {{ $student->first_name }}!

**{{ $studio->name }}** te ha agregado a su lista de estudiantes en **EstadoPrisma**.

Para ver tus clases, gestionar tus reservas y realizar tus pagos, crea tu cuenta gratuita haciendo clic en el botón:

<x-mail::button :url="route('register', ['email' => $student->email, 'national_id' => $student->national_id, 'country_id' => $student->country_id])">
Crear mi cuenta gratuita
</x-mail::button>

*Es gratuito y solo toma un minuto. Una vez creada tu cuenta, todas tus fichas de alumna se vincularán automáticamente.*
</x-mail::message>

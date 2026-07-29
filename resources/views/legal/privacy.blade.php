<x-guest-layout>
@section('metaTitle', 'Política de Privacidad — EstadoPrisma')

<div class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
    <div class="prose prose-stone max-w-none">
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-stone-900">Política de Privacidad</h1>
        <p class="text-sm text-stone-500 mt-2">Última actualización: 20 de junio de 2026</p>

        <hr class="my-8 border-stone-200">

        <h2 class="text-xl font-semibold text-stone-800">1. Responsable del Tratamiento</h2>
        <p>
            EstadoPrisma, en adelante "la Plataforma", es el responsable del tratamiento de los datos personales
            que nos proporciones. Puedes contactarnos en cualquier momento a través de nuestro formulario de soporte.
        </p>

        <h2 class="text-xl font-semibold text-stone-800">2. Datos que Recopilamos</h2>
        <p>Recopilamos las siguientes categorías de datos personales:</p>
        <ul>
            <li><strong>Datos de identificación:</strong> nombre completo, correo electrónico, documento nacional de identidad y país.</li>
            <li><strong>Datos de cuenta:</strong> contraseña (hash), preferencias de perfil.</li>
            <li><strong>Datos de uso:</strong> interacciones con la plataforma, clases reservadas, pagos realizados.</li>
            <li><strong>Datos de conexión:</strong> tokens OAuth de Mercado Pago y Google (cuando se vinculan).</li>
        </ul>

        <h2 class="text-xl font-semibold text-stone-800">3. Finalidad del Tratamiento</h2>
        <p>Tus datos son tratados exclusivamente para:</p>
        <ul>
            <li>Crear y administrar tu cuenta de usuario.</li>
            <li>Vincular tus perfiles de alumna/o y/o profesora/or.</li>
            <li>Procesar reservas, pagos y liquidaciones.</li>
            <li>Enviar comunicaciones esenciales del servicio.</li>
            <li>Cumplir con obligaciones legales aplicables.</li>
        </ul>

        <h2 class="text-xl font-semibold text-stone-800">4. Base Legal del Tratamiento</h2>
        <p>
            El tratamiento de tus datos se fundamenta en:
        </p>
        <ul>
            <li><strong>Consentimiento explícito:</strong> otorgado al aceptar esta Política de Privacidad durante el registro.</li>
            <li><strong>Ejecución del contrato:</strong> necesario para prestarte los servicios contratados.</li>
            <li><strong>Obligación legal:</strong> requerido por la legislación chilena aplicable.</li>
        </ul>

        <h2 class="text-xl font-semibold text-stone-800">5. Derechos del Titular (ARCO)</h2>
        <p>De acuerdo con la Ley 21.719, tienes los siguientes derechos sobre tus datos personales:</p>
        <ul>
            <li><strong>Acceso:</strong> solicitar qué datos tuyos almacenamos.</li>
            <li><strong>Rectificación:</strong> corregir datos inexactos o incompletos.</li>
            <li><strong>Cancelación:</strong> solicitar la eliminación de tus datos cuando ya no sean necesarios.</li>
            <li><strong>Oposición:</strong> oponerte al tratamiento de tus datos para fines específicos.</li>
            <li><strong>Portabilidad:</strong> recibir tus datos en un formato estructurado.</li>
        </ul>

        <h2 class="text-xl font-semibold text-stone-800">6. Conservación de los Datos</h2>
        <p>
            Conservamos tus datos personales mientras mantengas una cuenta activa en la Plataforma.
            Una vez eliminada tu cuenta, tus datos serán suprimidos en un plazo máximo de 90 días,
            salvo aquellos que deban conservarse por obligación legal.
        </p>

        <h2 class="text-xl font-semibold text-stone-800">7. Medidas de Seguridad</h2>
        <p>
            Aplicamos medidas técnicas y organizativas para proteger tus datos: cifrado en tránsito (TLS),
            contraseñas hasheadas, acceso restringido al personal autorizado, y monitoreo continuo de accesos.
        </p>

        <h2 class="text-xl font-semibold text-stone-800">8. Transferencias Internacionales</h2>
        <p>
            Utilizamos servicios de terceros (Mercado Pago, Google) que pueden procesar datos fuera de Chile.
            Exigimos a estos proveedores cumplir con estándares equivalentes de protección.
        </p>

        <h2 class="text-xl font-semibold text-stone-800">9. Consentimiento Informado</h2>
        <p>
            Al marcar la casilla de aceptación de esta Política de Privacidad durante el registro,
            declaras haber leído y comprendido el tratamiento que daremos a tus datos personales,
            otorgando tu consentimiento libre, específico e informado.
        </p>

        <h2 class="text-xl font-semibold text-stone-800">10. Contacto</h2>
        <p>
            Para ejercer tus derechos o resolver dudas sobre esta política, contáctanos a través de
            nuestro <a href="{{ route('support.create') }}" class="text-red-600 hover:text-red-700 transition-colors duration-200 underline decoration-red-200 underline-offset-4">formulario de soporte</a>.
        </p>
    </div>
</div>
</x-guest-layout>

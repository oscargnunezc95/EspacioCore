<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .btn { display: inline-block; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 8px 4px; text-align: center; }
        .btn-accept { background-color: #059669; color: #ffffff; }
        .btn-reject { background-color: #dc2626; color: #ffffff; }
        .warning { background-color: #fffbeb; border: 1px solid #f59e0b; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .highlight { color: #4f46e5; font-weight: bold; }
        .muted { font-size: 13px; color: #52525b; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 Solicitud de Vínculo Familiar</h2>

        <p>Hola {{ $targetUser->name }},</p>

        <p><strong class="highlight">{{ $requester->name }}</strong> solicita tu autorización para administrar tus clases en EstadoPrisma como <strong>{{ $dependent->relationship ?? 'familiar' }}</strong>.</p>

        <p>Si aceptas, {{ $requester->name }} podrá:</p>
        <ul>
            <li>Inscribirte en clases y talleres</li>
            <li>Gestionar tus reservas y horarios</li>
            <li>Realizar pagos en tu nombre en los estudios donde tengas ficha</li>
        </ul>

        <p>Tus clases actuales y tu cuenta personal se mantienen sin cambios. Siempre podrás revocar este vínculo desde la sección <strong>Mi Familia</strong> de tu perfil.</p>

        <p><strong>Datos del registro:</strong></p>
        <ul>
            <li>Nombre: {{ $dependent->first_name }} {{ $dependent->last_name }}</li>
            <li>Documento: {{ $dependent->national_id }}</li>
            <li>Parentesco: {{ $dependent->relationship ?? 'No especificado' }}</li>
        </ul>

        <div class="warning">
            <strong>⚠️ Esta solicitud expira en 7 días.</strong> Si no respondes, el vínculo no se activará y {{ $requester->name }} no podrá gestionar tus clases.
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $acceptUrl }}" class="btn btn-accept">✅ Aceptar Solicitud</a>
            <br>
            <a href="{{ $rejectUrl }}" class="btn btn-reject">❌ Rechazar</a>
        </div>

        <p class="muted">Si no deseas que {{ $requester->name }} administre tus clases, simplemente haz clic en <strong>Rechazar</strong> y el registro será eliminado.</p>

        <p class="muted">EstadoPrisma — gestión deportiva multi-estudio</p>
    </div>
</body>
</html>

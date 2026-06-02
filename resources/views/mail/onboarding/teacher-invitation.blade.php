<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .highlight { color: #4f46e5; font-weight: bold; }
        .credentials-box { background-color: #f8fafc; border: 1px dashed #e2e8f0; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .btn { display: inline-block; background-color: #18181b; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 10px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Hola, {{ $teacher->first_name }}!</h2>
        </div>
        
        <p>El equipo de <strong class="highlight">{{ $studio->name }}</strong> te ha agregado como profesor/a en su plataforma de gestión deportiva.</p>
        
        <p>A partir de ahora, podrás ingresar a tu <strong>Portal de Profesor</strong> para revisar tu calendario de clases, gestionar asistencias y visualizar a los alumnos inscritos en tus talleres.</p>

        @if ($temporaryPassword)
        <div class="credentials-box">
            <p style="margin-top: 0; color: #64748b; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Tus Credenciales de Acceso</p>
            <p><strong>Email:</strong> {{ $teacher->email }}</p>
            <p><strong>Contraseña temporal:</strong> {{ $temporaryPassword }}</p>
            
            <a href="{{ route('login') }}" class="btn">Ingresar a mi Portal</a>
        </div>

        <p style="font-size: 13px; color: #52525b;">* Te recomendamos cambiar esta contraseña temporal desde la configuración de tu perfil una vez que inicies sesión.</p>
        @else
        <div class="credentials-box" style="text-align: center;">
            <p style="margin-top: 0; color: #64748b; font-size: 14px;">Para acceder a tu portal necesitas crear una cuenta gratuita.</p>
            
            <a href="{{ route('register', ['email' => $teacher->email, 'national_id' => $teacher->national_id, 'country_id' => $teacher->country_id]) }}" class="btn">
                Crear mi Cuenta Gratuita
            </a>
        </div>

        <p style="font-size: 13px; color: #52525b;">* Solo toma un minuto. Una vez registrada/o, tus fichas se vincularán automáticamente.</p>
        @endif
        
        <br>
        <p>Saludos cordiales,<br>El equipo de EstadoPrisma</p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .highlight { color: #4f46e5; font-weight: bold; }
        .btn { display: inline-block; background-color: #18181b; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 10px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Hola, {{ $student->first_name }}!</h2>
        </div>
        
        <p><strong class="highlight">{{ $studio->name }}</strong> te ha agregado a su lista de estudiantes en <strong>EstadoPrisma</strong>.</p>

        <p>Para ver tus clases, gestionar tus reservas y realizar tus pagos, crea tu cuenta gratuita haciendo clic en el botón:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('register', ['email' => $student->email, 'national_id' => $student->national_id, 'country_id' => $student->country_id]) }}" class="btn">Crear mi cuenta gratuita</a>
        </div>

        <p style="font-size: 13px; color: #52525b;">* Es gratuito y solo toma un minuto. Una vez creada tu cuenta, todas tus fichas de alumna se vincularán automáticamente.</p>
        
        <br>
        <p>Saludos cordiales,<br>El equipo de <strong>{{ config('app.name') }}</strong></p>
    </div>
</body>
</html>

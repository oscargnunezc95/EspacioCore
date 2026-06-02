<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .highlight { color: #4f46e5; font-weight: bold; }
        .info-box { background-color: #eef2ff; border: 1px dashed #c7d2fe; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .btn { display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Hola, {{ explode(' ', $userName)[0] }}!</h2>
        </div>

        <p>El equipo de <strong class="highlight">{{ $studio->name }}</strong> te ha agregado a su staff como <strong>profesor/a</strong>.</p>

        <p>Desde tu <strong>Portal de Profesor</strong> podrás revisar tu calendario de clases, tomar asistencia y ver los alumnos inscritos en tus talleres.</p>

        <div class="info-box">
            <p style="margin: 0; font-size: 15px;">Usa tus credenciales habituales para ingresar.</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('login') }}" class="btn">Ingresar a mi Portal</a>
        </div>

        <br>
        <p>Saludos cordiales,<br>El equipo de {{ $studio->name }}</p>
    </div>
</body>
</html>

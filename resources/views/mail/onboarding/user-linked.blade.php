<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .highlight { color: #4f46e5; font-weight: bold; }
        .btn { display: inline-block; background-color: #18181b; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Hola, {{ explode(' ', $userName)[0] }}!</h2>
        </div>
        
        <p>Felicidades!! Ahora eres parte de la comunidad de <strong class="highlight">{{ $studio->name }}</strong>.</p>
        
        <p>A partir de este momento, el estudio podrá agregarte a sus clases si es que se te olvidara incribirte en ellas.</p>

        <div style="text-align: center;">
            <a href="{{ route('login') }}" class="btn">Ir a mi Panel</a>
        </div>
        
        <br>
        <p>Saludos del creador de EstadoPrisma,<br>Oscar Núñez</p>
    </div>
</body>
</html>
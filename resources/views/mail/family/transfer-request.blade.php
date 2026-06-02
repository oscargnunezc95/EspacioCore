<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .btn { display: inline-block; background-color: #18181b; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 15px; }
        .highlight { color: #4f46e5; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔄 Solicitud de transferencia de dependiente</h2>

        <p><strong class="highlight">{{ $requester->name }}</strong> quiere agregar a 
           <strong>{{ $dependent->first_name }} {{ $dependent->last_name }}</strong> 
           a su grupo familiar, pero actualmente figura como tu dependiente.</p>

        <p>Esto puede ocurrir si compartes la tuición legal o si la persona se registró 
           de forma independiente y tus clases quedarán duplicadas.</p>

        <p><strong>Detalles del dependiente:</strong></p>
        <ul>
            <li>Nombre: {{ $dependent->first_name }} {{ $dependent->last_name }}</li>
            <li>Documento: {{ $dependent->national_id }}</li>
            <li>Parentesco registrado: {{ $dependent->relationship ?? 'No especificado' }}</li>
        </ul>

        <p>Para resolverlo, ingresa a la sección <strong>Mi Familia</strong> en tu perfil de EstadoPrisma 
           y elimina el dependiente si corresponde.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('profile.family.index') }}" class="btn">Ir a Mi Familia</a>
        </div>

        <p style="font-size: 13px; color: #52525b;">EstadoPrisma — gestión deportiva multi-estudio</p>
    </div>
</body>
</html>

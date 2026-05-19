<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-w-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .success-text { color: #10b981; font-weight: bold; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Hola, {{ $studio->user->name ?? 'Estudio' }}!</h2>
        </div>
        
        <p>Te escribimos para confirmarte que tu cuenta de Mercado Pago ha sido vinculada exitosamente con tu portal en EstadoPrisma.</p>
        
        <p class="success-text">✔️ Ya estás listo para recibir pagos online de tus alumnas.</p>
        
        <p>A partir de este momento, cuando una alumna realice una reserva y pague a través de tu catálogo web, el dinero irá directamente a tu cuenta de Mercado Pago y la clase se marcará como pagada automáticamente en tu sistema.</p>

        <p>Si tú no realizaste esta acción, por favor ingresa a tu panel de administración y cambia tu contraseña de inmediato.</p>

        <br>
        <p>El equipo de EstadoPrisma</p>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
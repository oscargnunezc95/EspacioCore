<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #18181b; padding: 20px; }
        .container { max-w-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e4e4e7; padding-bottom: 20px; margin-bottom: 20px; }
        .warning-text { color: #f43f5e; font-weight: bold; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>¡Hola, {{ $studio->user->name ?? 'Estudio' }}!</h2>
        </div>
        
        <p>Te informamos que tu cuenta de Mercado Pago <strong class="warning-text">ha sido desvinculada</strong> de tu portal en EstadoPrisma.</p>
        
        <p>A partir de este momento, tus alumnas ya no podrán realizar pagos online mediante la plataforma. Cualquier deuda en el carrito deberá ser saldada de forma presencial o por transferencia directa.</p>

        <p>Si deseas volver a activar los pagos automáticos, simplemente dirígete a la configuración de tu estudio y vuelve a vincular tu cuenta.</p>

        <p>Si tú no solicitaste esta desvinculación, por favor ponte en contacto con nuestro equipo de soporte técnico a la brevedad.</p>

        <br>
        <p>El equipo de EstadoPrisma</p>

        <div class="footer">
            <p>Por motivos de seguridad, te enviamos esta alerta cada vez que se modifican tus integraciones financieras.</p>
        </div>
    </div>
</body>
</html>
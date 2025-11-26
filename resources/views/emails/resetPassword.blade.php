<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablece tu contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { width: 90%; margin: auto; padding: 20px; }
        .button {
            display: inline-block;
            padding: 12px 25px;
            margin: 20px 0;
            background-color: #0460D9; /* Tu color --azul */
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Hey, {{ $usuario->nombre_comp }}!</h1>
        <p>
            Recibiste este correo electrónico porque hemos recibido una solicitud de
            restablecimiento de contraseña para tu cuenta
        </p>
        <p>
            Por favor, haz clic en el siguiente botón para establecer tu nueva contraseña:
        </p>
        <a href="{{ $link }}" class="button">Restablecer contraseña</a>
    </div>
</body>
</html>
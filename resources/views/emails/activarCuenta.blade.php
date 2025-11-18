<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activa tu cuenta</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { width: 90%; margin: auto; padding: 20px; }
        .button {
            display: inline-block;
            padding: 12px 25px;
            margin: 20px 0;
            background-color: #0460D9; /* Tu color --azul */
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Bienvenido, {{ $usuario->nombre_comp }}!</h1>
        <p>
            Gracias por registrarte en la aplicación. Solo falta un paso más
            para activar tu cuenta.
        </p>
        <p>
            Por favor, haz clic en el siguiente botón para establecer tu contraseña
            y activar tu acceso:
        </p>
        <a href="{{ $urlActivacion }}" class="button">Activar mi Cuenta</a>
        <p>
            Si no te registraste, por favor ignora este correo.
        </p>
    </div>
</body>
</html>
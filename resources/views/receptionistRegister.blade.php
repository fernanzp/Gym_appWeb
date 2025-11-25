<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar Recepcionista</title>
  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Istok+Web:wght@400;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  
  <style>
        :root {
            --azul: #0460D9;
            --azul-oscuro: #0248D2;
            --gris-oscuro: #727272;
            --gris-medio: #A3A3A3;
            --gris-bajito: #F1F1F1;
        }
        .istok-web-regular { font-family: "Istok Web", sans-serif; font-weight: 400; font-style: normal; }
        .istok-web-bold { font-family: "Istok Web", sans-serif; font-weight: 700; font-style: normal; }

        :root { --azul: #0460D9; --gris-bajito: #F1F1F1; --gris-oscuro: #727272; }
        .istok-web-regular { font-family: "Istok Web", sans-serif; font-weight: 400; }
        .istok-web-bold { font-family: "Istok Web", sans-serif; font-weight: 700; }
    </style>
</head>
<body class="flex flex-col items-center min-h-screen bg-white text-black relative">

    <div class="absolute top-[-15px] left-1">
        <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30">
    </div>

    <div class="w-full max-w-[50%] mt-[5vh] mb-10">
        <h1 class="text-4xl font-bold text-center mb-8 istok-web-bold">Registrar nueva recepcionista</h1>

        <form id="" class="space-y-4">
            @csrf

            <!---->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Nombre completo</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="nombre_comp" required type="text" class="flex-1 bg-transparent outline-none istok-web-regular" placeholder="Nombre Apellido">
                </div>
            </div>
            <!---->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Correo electrónico</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="email" required type="email" class="flex-1 bg-transparent outline-none istok-web-regular" placeholder="ejemplo@correo.com">
                </div>
            </div>
            <!---->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Teléfono</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="telefono" type="tel" class="flex-1 bg-transparent outline-none istok-web-regular" placeholder="10 dígitos">
                </div>
            </div>
            <!---->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Fecha de Nacimiento</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="fecha_nac" type="date" class="flex-1 bg-transparent outline-none istok-web-regular">
                </div>
            </div>
            <!---->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Contraseña</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input id="pass1" name="contrasena" type="password" placeholder="Contraseña" class="flex-1 bg-transparent outline-none istok-web-regular">
                </div>
            </div>
            <!---->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Confirmar contraseña</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input id="pass2" name="contrasena_confirmation" type="password" placeholder="Confirmar Contraseña" class="flex-1 bg-transparent outline-none istok-web-regular">
                </div>
            </div>


            <div class="flex gap-4 pt-4">
                <a href="{{ route('dashboard') }}" class="w-full text-center border-2 border-[var(--gris-oscuro)] text-[var(--gris-oscuro)] py-3 rounded-full font-bold hover:bg-[var(--gris-oscuro)] hover:text-white transition">Cancelar</a>
                <button type="submit" class="w-full bg-[var(--azul)] text-white py-3 rounded-full font-bold hover:bg-[var(--azul-oscuro)] transition shadow-md">
                    Registrar
                </button>
            </div>
        </form>
    </div>
</body>
</html>
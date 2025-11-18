<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cuenta activada</title>

  <!-- Favicon / logo -->
  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">

  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Istok+Web:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root{
      --azul: #0460D9;
      --azul-oscuro: #0248D2;
      --gris-oscuro: #727272;
      --gris-bajito: #F1F1F1;
    }

    .istok-web-regular {
      font-family: "Istok Web", sans-serif;
      font-weight: 400;
      font-style: normal;
    }

    .istok-web-bold {
      font-family: "Istok Web", sans-serif;
      font-weight: 700;
      font-style: normal;
    }
  </style>
</head>
<body class="min-h-screen bg-white text-black istok-web-regular">

  <!-- Logo top-left -->
  <div class="absolute top-[-15px] left-1">
    <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30" />
  </div>

  <main class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-[900px] px-6 md:px-12 lg:px-20 py-16">

      <div class="bg-white shadow-sm rounded-xl p-8 text-center">
        <!-- Icono de éxito (puedes usar un SVG o imagen) -->
        <div class="mx-auto w-25 h-25 flex items-center justify-center rounded-full bg-[#ddf0dc] mb-6">
          <!-- check icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="w-15 h-15 text-[#00b52e]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>
          </svg>
        </div>

        <h1 class="text-3xl md:text-4xl istok-web-bold mb-4">¡Cuenta activada!</h1>

        <p class="text-[17px] text-black/80 max-w-[760px] mx-auto mb-6">
          Tu cuenta se ha activado correctamente. Ya puedes iniciar sesión en la app móvil y empezar a usar los servicios del gimnasio.
        </p>

        <p class="mt-10 text-[14px] text-[var(--gris-oscuro)]">Si no puedes iniciar sesión, revisa tu correo o contacta a recepción.</p>
      </div>
    </div>
  </main>

</body>
</html>
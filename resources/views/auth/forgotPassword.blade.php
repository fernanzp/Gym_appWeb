<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recuperar contraseña</title>

  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alumni+Sans+Pinstripe&family=Istok+Web:ital,wght@0,400;0,700;1,400;1,700&family=Poiret+One&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
        :root {
            --azul: #0460D9;
            --azul-oscuro: #0248D2;
            --gris-oscuro: #727272;
            --gris-bajito: #F1F1F1;
        }
        .istok-web-regular { font-family: "Istok Web", sans-serif; font-weight: 400; }
        .istok-web-bold { font-family: "Istok Web", sans-serif; font-weight: 700; }
    </style>
</head>
<body class="flex flex-col items-center justify-between min-h-screen bg-white text-black">

    <div class="absolute top-[-15px] left-1">
        <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30">
    </div>

    <div class="w-full max-w-[50%] mt-[15vh]">
        <h1 class="text-4xl font-bold text-center mb-4 istok-web-bold">Recuperar contraseña</h1>
        <p class="text-center mb-8 text-[var(--gris-oscuro)]">Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-center">
                {{ session('status') }}
            </div>
        @endif

        <form id="forgot-form" action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Correo electrónico</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input type="email" name="email" placeholder="ejemplo@gmail.com" class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular" value="{{ old('email') }}" required>
                </div>
                @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button id="btn-submit" type="submit" 
                    class="w-full mt-2 bg-[var(--azul)] text-white istok-web-regular py-3 rounded-full hover:bg-[var(--azul-oscuro)] transition disabled:opacity-50 disabled:cursor-not-allowed">
                Enviar enlace
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-[var(--azul)] istok-web-regular">Volver al Login</a>
        </div>
    </div>

    <p class="text-center istok-web-regular text-[var(--gris-oscuro)] mb-4">
        No compartas tu contraseña con nadie.
    </p>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btn-submit');
            const form = document.getElementById('forgot-form');
            const COOLDOWN_TIME = 30000; // 30 segundos en milisegundos
            const STORAGE_KEY = 'reset_email_cooldown';

            // Función para iniciar el contador visual
            function startCountdown(endTime) {
                btn.disabled = true;
                
                const interval = setInterval(() => {
                    const now = Date.now();
                    const remaining = Math.ceil((endTime - now) / 1000);

                    if (remaining <= 0) {
                        clearInterval(interval);
                        btn.disabled = false;
                        btn.innerText = 'Enviar enlace';
                        localStorage.removeItem(STORAGE_KEY);
                    } else {
                        btn.innerText = `Enviar enlace (${remaining}s)`;
                    }
                }, 1000);
            }

            // 1. Verificar al cargar la página si hay un bloqueo activo
            const storedEndTime = localStorage.getItem(STORAGE_KEY);
            if (storedEndTime) {
                const now = Date.now();
                if (now < storedEndTime) {
                    // Si el tiempo guardado es futuro, iniciamos el conteo
                    startCountdown(storedEndTime);
                } else {
                    // Si el tiempo ya pasó, limpiamos el storage
                    localStorage.removeItem(STORAGE_KEY);
                }
            }

            // 2. Escuchar el envío del formulario
            form.addEventListener('submit', function(e) {
                // Calculamos cuándo debe terminar el bloqueo (ahora + 30s)
                const endTime = Date.now() + COOLDOWN_TIME;
                
                // Guardamos en localStorage para que persista tras la recarga
                localStorage.setItem(STORAGE_KEY, endTime);
                
                // Iniciamos el efecto visual inmediatamente (aunque la página se recargará pronto)
                btn.innerText = 'Enviando...';
                btn.disabled = true;
            });
        });
    </script>
</body>
</html>
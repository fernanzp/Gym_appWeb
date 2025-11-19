<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Usuario</title>
  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">

  <link href="https://fonts.googleapis.com/css2?family=Istok+Web:wght@400;700&display=swap" rel="stylesheet">
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

        /* --- ANIMACIÓN LOADER TIPO WINDOWS --- */
        .windows-loader {
            width: 48px;
            height: 48px;
            border: 5px solid #F1F1F1;
            border-top-color: var(--azul);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* --- ANIMACIÓN PALOMITA (CHECKMARK) --- */
        .checkmark-circle {
            width: 56px; height: 56px; border-radius: 50%; display: block;
            stroke-width: 2; stroke: #fff; stroke-miterlimit: 10;
            margin: 0 auto; box-shadow: inset 0px 0px 0px #7ac142;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }
        .checkmark-circle circle {
            stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2;
            stroke-miterlimit: 10; stroke: #7ac142; fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .checkmark-check {
            transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        @keyframes stroke { 100% { stroke-dashoffset: 0; } }
        @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
        @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 30px #7ac142; } }
    </style>
</head>
<body class="flex flex-col items-center min-h-screen bg-white text-black relative">

    <div class="absolute top-[-15px] left-1">
        <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30">
    </div>

    <div class="w-full max-w-[50%] mt-[5vh] mb-10 z-10">
        <h1 class="text-4xl font-bold text-center mb-8 istok-web-bold">
            Editar Usuario: {{ $usuario->nombre_comp ?? 'Cargando...' }}
        </h1>

        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            @if (session('success'))
                <div class="p-3 rounded-md bg-green-100 text-green-800 border border-green-200 text-center font-bold">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-3 rounded-md bg-red-100 text-red-800 border border-red-200 text-center font-bold">
                    {{ session('error') }}
                </div>
            @endif

            <div>
                <label class="block font-bold mb-1 istok-web-bold">Nombre completo</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3 ring-1 ring-transparent focus-within:ring-[var(--azul)]">
                    <input name="nombre_comp" type="text" value="{{ old('nombre_comp', $usuario->nombre_comp ?? '') }}" class="flex-1 bg-transparent outline-none istok-web-regular">
                </div>
            </div>
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Correo electrónico</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3 ring-1 ring-transparent focus-within:ring-[var(--azul)]">
                    <input name="email" type="email" value="{{ old('email', $usuario->email ?? '') }}" class="flex-1 bg-transparent outline-none istok-web-regular">
                </div>
            </div>
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Teléfono</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3 ring-1 ring-transparent focus-within:ring-[var(--azul)]">
                    <input name="telefono" type="tel" value="{{ old('telefono', $usuario->telefono ?? '') }}" class="flex-1 bg-transparent outline-none istok-web-regular">
                </div>
            </div>
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Estatus</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3 relative">
                    @php $currentStatus = old('estatus', $usuario->estatus ?? 'activo'); @endphp
                    <select name="estatus" class="flex-1 bg-transparent outline-none istok-web-regular appearance-none cursor-pointer z-10">
                        <option value="activo" @selected($currentStatus == 'activo')>Activo</option>
                        <option value="inactivo" @selected($currentStatus == 'inactivo')>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <a href="{{ route('usuarios') }}" class="w-full text-center border-2 border-[var(--gris-oscuro)] text-[var(--gris-oscuro)] py-3 rounded-full font-bold hover:bg-[var(--gris-oscuro)] hover:text-white transition">Cancelar</a>
                <button type="submit" class="w-full bg-[var(--azul)] text-white py-3 rounded-full font-bold hover:bg-[var(--azul-oscuro)] transition shadow-md">Guardar cambios</button>
            </div>
        </form>
        
        <div class="mt-12 pt-6 border-t-2 border-[var(--gris-bajito)]">
            <h2 class="text-2xl font-bold text-center mb-6 istok-web-bold text-[var(--azul)]">Gestión Biométrica</h2>
            <div class="bg-[var(--gris-bajito)] rounded-xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if($usuario->fingerprint_id)
                        <div class="bg-green-100 p-3 rounded-full text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <p class="istok-web-bold text-lg text-green-700">Huella Registrada</p>
                            <p class="text-sm text-gray-500 istok-web-regular">ID Sensor: {{ $usuario->fingerprint_id }}</p>
                        </div>
                    @else
                        <div class="bg-yellow-100 p-3 rounded-full text-yellow-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div>
                            <p class="istok-web-bold text-lg text-yellow-700">Sin Huella</p>
                            <p class="text-sm text-gray-500 istok-web-regular">No tiene acceso biométrico.</p>
                        </div>
                    @endif
                </div>

                <form action="{{ route('usuario.resetFingerprint', $usuario->id) }}" method="POST" class="w-full sm:w-auto" onsubmit="activarLoader()">
                    @csrf
                    @if($usuario->fingerprint_id)
                        <button type="submit" onclick="return confirm('¿Borrar huella actual y registrar nueva?');" class="w-full sm:w-auto px-6 py-3 bg-white border-2 border-[var(--azul)] text-[var(--azul)] istok-web-bold rounded-full hover:bg-[var(--azul)] hover:text-white transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Actualizar Huella
                        </button>
                    @else
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-[var(--azul)] text-white istok-web-bold rounded-full hover:bg-[var(--azul-oscuro)] transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Registrar Huella
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div id="modalOverlay" class="fixed inset-0 bg-black/70 z-50 hidden flex items-center justify-center backdrop-blur-sm">
        
        <div id="estadoCargando" class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl hidden">
            <div class="flex justify-center mb-6">
                <div class="windows-loader"></div>
            </div>
            <h3 class="text-2xl istok-web-bold text-[var(--azul)] mb-2">Sensor Activado</h3>
            <p class="text-gray-600 mb-4 text-sm">Pida al usuario seguir las instrucciones:</p>
            
            <div class="text-left bg-gray-50 p-4 rounded-lg space-y-3 border border-gray-100">
                <p class="flex items-center gap-2 text-sm text-gray-700">
                    <span class="font-bold text-[var(--azul)]">1.</span> Colocar dedo en el sensor.
                </p>
                <p class="flex items-center gap-2 text-sm text-gray-700">
                    <span class="font-bold text-[var(--azul)]">2.</span> Retirar y colocar nuevamente.
                </p>
            </div>
            <p class="mt-4 text-xs text-gray-400 animate-pulse">Esperando respuesta del dispositivo...</p>
        </div>

        <div id="estadoExito" class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl hidden">
            <div class="flex justify-center mb-4">
                <svg class="checkmark-circle" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle-back" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <h3 class="text-2xl istok-web-bold text-green-600 mb-2">¡Huella Actualizada!</h3>
            <p class="text-gray-600 text-sm">El registro se completó correctamente.</p>
        </div>
    </div>

    <script>
        // 1. Función que activa el loader (se llama en el onsubmit del form)
        function activarLoader() {
            const overlay = document.getElementById('modalOverlay');
            const cargando = document.getElementById('estadoCargando');
            
            overlay.classList.remove('hidden');
            cargando.classList.remove('hidden'); // Muestra el spinner
        }

        // 2. Verificar si venimos de un éxito (Laravel Session)
        // Si la sesión tiene 'success' Y contiene texto relacionado a huella, mostramos la palomita.
        document.addEventListener("DOMContentLoaded", function() {
            // Obtenemos el mensaje de éxito desde PHP (si existe)
            const successMsg = "{{ session('success') }}";
            
            // Detectamos si el mensaje es sobre la huella (para no activarlo al editar nombre/email)
            if (successMsg && (successMsg.includes('huella') || successMsg.includes('Huella') || successMsg.includes('Biométrica'))) {
                const overlay = document.getElementById('modalOverlay');
                const exito = document.getElementById('estadoExito');
                
                overlay.classList.remove('hidden');
                exito.classList.remove('hidden'); // Muestra la palomita
                
                // Ocultar automáticamente después de 2.5 segundos
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 2500);
            }
        });
    </script>
</body>
</html>
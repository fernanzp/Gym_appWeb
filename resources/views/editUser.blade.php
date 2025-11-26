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
        :root { --azul: #0460D9; --azul-oscuro: #0248D2; --gris-oscuro: #727272; --gris-bajito: #F1F1F1; }
        .istok-web-regular { font-family: "Istok Web", sans-serif; font-weight: 400; }
        .istok-web-bold { font-family: "Istok Web", sans-serif; font-weight: 700; }

        /* --- ESTILOS DEL MODAL --- */
        .windows-loader { width: 50px; height: 50px; margin: 0 auto; border: 5px solid #f3f3f3; border-top: 5px solid var(--azul); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* PALOMITA (Success) */
        .checkmark-circle { width: 60px; height: 60px; position: relative; display: inline-block; vertical-align: top; margin: 0 auto; }
        .checkmark-circle .background { width: 60px; height: 60px; border-radius: 50%; background: #2EB150; position: absolute; }
        .checkmark-circle .checkmark { border-radius: 5px; }
        .checkmark-circle .checkmark.draw:after { animation: checkmark 800ms ease forwards; transform: scaleX(-1) rotate(135deg); }
        .checkmark-circle .checkmark:after { opacity: 1; height: 30px; width: 15px; transform-origin: left top; border-right: 4px solid white; border-top: 4px solid white; content: ''; left: 15px; top: 30px; position: absolute; }
        @keyframes checkmark { 0% { height: 0; width: 0; opacity: 1; } 20% { height: 0; width: 15px; opacity: 1; } 40% { height: 30px; width: 15px; opacity: 1; } 100% { height: 30px; width: 15px; opacity: 1; } }
        
        /* TACHE (Error) */
        .cross-circle { width: 60px; height: 60px; position: relative; display: inline-block; vertical-align: top; margin: 0 auto; }
        .cross-circle .background { width: 60px; height: 60px; border-radius: 50%; background: #e74c3c; position: absolute; }
        .cross-line { position: absolute; background-color: white; height: 4px; width: 30px; border-radius: 2px; top: 28px; left: 15px; }
        .cross-line.one { transform: rotate(45deg); }
        .cross-line.two { transform: rotate(-45deg); }
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

        @if ($usuario->estatus == 8)
            <div class="mb-6 p-4 border-l-4 border-red-500 bg-red-50 text-red-700 rounded-r shadow-sm flex items-center justify-between">
                <div>
                    <strong class="block font-bold text-lg">🛑 Error de Huella</strong>
                    <span class="text-sm">La huella no coincidió en el sensor o hubo un error de lectura.</span>
                </div>
                <form action="{{ route('usuario.resetFingerprint', $usuario->id) }}" method="POST" onsubmit="activarLoader()">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded font-bold hover:bg-red-700 transition text-sm">
                        Reintentar
                    </button>
                </form>
            </div>
        @elseif ($usuario->estatus == 9)
            <div class="mb-6 p-4 border-l-4 border-yellow-500 bg-yellow-50 text-yellow-700 rounded-r shadow-sm flex items-center justify-between">
                <div>
                    <strong class="block font-bold text-lg">⏲️ Tiempo Agotado</strong>
                    <span class="text-sm">Se acabó el tiempo para registrar la huella.</span>
                </div>
                <form action="{{ route('usuario.resetFingerprint', $usuario->id) }}" method="POST" onsubmit="activarLoader()">
                    @csrf
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded font-bold hover:bg-yellow-700 transition text-sm">
                        Reintentar
                    </button>
                </form>
            </div>
        @endif

        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            
            @if (session('success') && $usuario->estatus != 8 && $usuario->estatus != 9) 
                <div class="p-3 rounded-md bg-green-100 text-green-800 border border-green-200 text-center font-bold">{{ session('success') }}</div> 
            @endif
            @if (session('error')) <div class="p-3 rounded-md bg-red-100 text-red-800 border border-red-200 text-center font-bold">{{ session('error') }}</div> @endif
            @error('general') <div class="p-3 rounded-md bg-red-100 text-red-800">{{ $message }}</div> @enderror

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
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <select name="estatus" class="flex-1 bg-transparent outline-none istok-web-regular appearance-none cursor-pointer">
                        <option value="1" @selected(old('estatus', $usuario->estatus) == 1)>Activo</option>
                        <option value="0" @selected(old('estatus', $usuario->estatus) != 1)>Inactivo</option>
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
                    @elseif($usuario->estatus == 8)
                         <div class="bg-red-100 p-3 rounded-full text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <p class="istok-web-bold text-lg text-red-700">Error de Registro</p>
                            <p class="text-sm text-gray-500 istok-web-regular">Intente nuevamente.</p>
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
                        <button type="submit" onclick="return confirm('¿Actualizar huella?');" class="w-full sm:w-auto px-6 py-3 bg-white border-2 border-[var(--azul)] text-[var(--azul)] istok-web-bold rounded-full hover:bg-[var(--azul)] hover:text-white transition-all flex items-center justify-center gap-2">
                            Actualizar Huella
                        </button>
                    @else
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-[var(--azul)] text-white istok-web-bold rounded-full hover:bg-[var(--azul-oscuro)] transition-all flex items-center justify-center gap-2">
                            {{ $usuario->estatus == 8 ? 'Reintentar Registro' : 'Registrar Huella' }}
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div id="modalOverlay" class="fixed inset-0 bg-black/70 z-[9999] hidden flex items-center justify-center backdrop-blur-sm">
        
        <div id="estadoCargando" class="bg-white rounded-2xl p-8 max-w-md w-full text-center shadow-2xl relative hidden">
            <button onclick="cerrarModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>

            <div class="flex justify-center mb-6"><div class="windows-loader"></div></div>
            
            <h3 class="text-2xl istok-web-bold text-[var(--azul)] mb-2">Procesando...</h3>
            <p class="text-gray-600 mb-6 istok-web-regular text-lg">
                Si el sensor se activa, siga las instrucciones:
            </p>

            <div class="bg-gray-50 rounded-xl p-4 text-left space-y-3 text-sm text-gray-700 border border-gray-200">
                <div class="flex items-center gap-3">
                    <span class="bg-[var(--azul)] text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    <span>Colocar dedo en el sensor.</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-[var(--azul)] text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    <span>Retirar dedo cuando se indique.</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-[var(--azul)] text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    <span>Colocar el <strong>mismo dedo</strong> otra vez.</span>
                </div>
            </div>
            
            <p class="mt-6 text-xs text-gray-400 animate-pulse">Esperando confirmación del dispositivo...</p>
        </div>

        <div id="estadoExito" class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl hidden">
            <div class="flex justify-center mb-4">
                <div class="checkmark-circle"><div class="background"></div><div class="checkmark draw"></div></div>
            </div>
            <h3 class="text-2xl istok-web-bold text-green-600 mb-2">¡Operación Exitosa!</h3>
            <p class="text-gray-600 text-sm">Los cambios se guardaron correctamente.</p>
        </div>

        <div id="estadoError" class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl hidden">
            <div class="flex justify-center mb-4">
                <div class="cross-circle"><div class="background"></div><div class="cross-line one"></div><div class="cross-line two"></div></div>
            </div>
            <h3 class="text-2xl istok-web-bold text-red-600 mb-2">¡Error!</h3>
            <p id="msgError" class="text-gray-600 text-sm mb-6">Ocurrió un problema con la huella o la conexión.</p>
            
            <button onclick="activarLoader(); document.getElementById('formRetryModal').submit();" class="bg-red-600 text-white px-6 py-2 rounded-full font-bold hover:bg-red-700 w-full">
                Intentar de Nuevo
            </button>
            <form id="formRetryModal" action="{{ route('usuario.resetFingerprint', $usuario->id) }}" method="POST" class="hidden">@csrf</form>
            
            <button onclick="cerrarModal()" class="mt-3 text-gray-500 text-sm hover:underline">
                Cerrar
            </button>
        </div>
    </div>

    <script>
    let pollingInterval;
    
    // --- VARIABLES SEGURAS (Usando json_encode estándar) ---
    // Esto evita el error de "unexpected token"
    const userId = {{ json_encode($usuario->id) }};
    const successMsg = {{ json_encode(session('success')) }};
    const errorMsg = {{ json_encode(session('error')) }}; 
    const triggerEnroll = {{ json_encode(session('trigger_enroll')) }}; 

    console.log("Debug Sesión -> Success:", successMsg, "| Error:", errorMsg, "| Enroll:", triggerEnroll);

    // --- FUNCIÓN PARA MOSTRAR LOADER ---
    function activarLoader() {
        const overlay = document.getElementById('modalOverlay');
        const cargando = document.getElementById('estadoCargando');
        const errorModal = document.getElementById('estadoError');
        const exitoModal = document.getElementById('estadoExito');
        
        // Ocultar todo primero
        cargando.classList.add('hidden');
        errorModal.classList.add('hidden');
        exitoModal.classList.add('hidden');
        
        // Mostrar loader
        overlay.classList.remove('hidden');
        cargando.classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalOverlay').classList.add('hidden');
        if(pollingInterval) clearInterval(pollingInterval);
    }

    // --- FUNCIÓN DE POLLING ---
    function iniciarPolling() {
        if(pollingInterval) clearInterval(pollingInterval);
        let intentos = 0;

        pollingInterval = setInterval(async () => {
            intentos++;
            try {
                // Fetch a la API
                const res = await fetch(`/api/user-status/${userId}`);
                const data = await res.json();

                const cargando = document.getElementById('estadoCargando');
                const exito = document.getElementById('estadoExito');
                const error = document.getElementById('estadoError');

                // 1. CASO ERROR
                if (data.estatus == 8 || data.estatus == 9) {
                    clearInterval(pollingInterval);
                    cargando.classList.add('hidden');
                    error.classList.remove('hidden');

                    document.getElementById('msgError').innerText = (data.estatus == 9) 
                        ? "Se acabó el tiempo de espera del sensor." 
                        : "Error: Las huellas no coincidieron.";
                    return;
                }

                // 2. CASO ÉXITO
                if (data.fingerprint_id != null) {
                    clearInterval(pollingInterval);
                    cargando.classList.add('hidden');
                    exito.classList.remove('hidden');
                    setTimeout(() => location.reload(), 2000);
                    return;
                }

                // Timeout del navegador (60 segundos)
                if (intentos > 60) {
                    clearInterval(pollingInterval);
                    cargando.classList.add('hidden');
                    error.classList.remove('hidden');
                    document.getElementById('msgError').innerText = "El navegador dejó de recibir respuesta.";
                }

            } catch (e) { console.error("Error polling:", e); }
        }, 1000);
    }

    // --- LÓGICA DE INICIO ---
    document.addEventListener("DOMContentLoaded", function() {

        // 1. SI HAY ERROR -> MOSTRAR Y PARAR
        if (errorMsg) {
            const overlay = document.getElementById('modalOverlay');
            const errorModal = document.getElementById('estadoError');
            const txtError = document.getElementById('msgError');

            txtError.innerText = errorMsg;
            overlay.classList.remove('hidden');
            errorModal.classList.remove('hidden');
            return; 
        }

        // 2. MODO ENROLAMIENTO -> LOADER + POLLING
        if (triggerEnroll) {
            const overlay = document.getElementById('modalOverlay');
            const cargando = document.getElementById('estadoCargando');
            
            overlay.classList.remove('hidden');
            cargando.classList.remove('hidden');
            
            iniciarPolling();
            return;
        }

        // 3. ÉXITO GENÉRICO
        if (successMsg) {
            const overlay = document.getElementById('modalOverlay');
            const exito = document.getElementById('estadoExito');

            overlay.classList.remove('hidden');
            exito.classList.remove('hidden');

            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 3000);
        }
    });
</script>

</body>
</html>
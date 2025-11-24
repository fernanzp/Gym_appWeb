<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar Cliente</title>
  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Istok+Web:wght@400;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  
  <style>
        :root { --azul: #0460D9; --gris-bajito: #F1F1F1; --gris-oscuro: #727272; }
        .istok-web-regular { font-family: "Istok Web", sans-serif; font-weight: 400; }
        .istok-web-bold { font-family: "Istok Web", sans-serif; font-weight: 700; }
        
        /* --- ANIMACIONES --- */
        .windows-loader { width: 50px; height: 50px; margin: 0 auto; border: 5px solid #f3f3f3; border-top: 5px solid var(--azul); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .checkmark-circle { width: 60px; height: 60px; position: relative; display: inline-block; vertical-align: top; margin: 0 auto; }
        .checkmark-circle .background { width: 60px; height: 60px; border-radius: 50%; background: #2EB150; position: absolute; }
        .checkmark-circle .checkmark { border-radius: 5px; }
        .checkmark-circle .checkmark.draw:after { animation: checkmark 800ms ease forwards; transform: scaleX(-1) rotate(135deg); }
        .checkmark-circle .checkmark:after { opacity: 1; height: 30px; width: 15px; transform-origin: left top; border-right: 4px solid white; border-top: 4px solid white; content: ''; left: 15px; top: 30px; position: absolute; }
        @keyframes checkmark { 0% { height: 0; width: 0; opacity: 1; } 20% { height: 0; width: 15px; opacity: 1; } 40% { height: 30px; width: 15px; opacity: 1; } 100% { height: 30px; width: 15px; opacity: 1; } }
        
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

    <div class="w-full max-w-[50%] mt-[5vh] mb-10">
        <h1 class="text-4xl font-bold text-center mb-8 istok-web-bold">Registrar nuevo cliente</h1>

        <!-- 
             🔥 FORMULARIO AJAX
             El ID 'registroForm' es clave para el script de abajo.
        -->
        <form id="registroForm" class="space-y-4">
            @csrf
            
            <!-- Contenedor de errores JS -->
            <div id="errorContainer" class="p-3 rounded-md bg-red-100 text-red-800 border border-red-200 hidden"></div>

            <div><label class="block font-bold mb-1 istok-web-bold">Nombre completo</label><div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3"><input name="nombre_comp" required type="text" class="flex-1 bg-transparent outline-none istok-web-regular" placeholder="Nombre Apellido"></div></div>
            <div><label class="block font-bold mb-1 istok-web-bold">Correo electrónico</label><div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3"><input name="email" required type="email" class="flex-1 bg-transparent outline-none istok-web-regular" placeholder="ejemplo@correo.com"></div></div>
            <div><label class="block font-bold mb-1 istok-web-bold">Teléfono</label><div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3"><input name="telefono" type="tel" class="flex-1 bg-transparent outline-none istok-web-regular" placeholder="10 dígitos"></div></div>
            <div><label class="block font-bold mb-1 istok-web-bold">Fecha de Nacimiento</label><div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3"><input name="fecha_nac" type="date" class="flex-1 bg-transparent outline-none istok-web-regular"></div></div>

            <div>
                <label class="block font-bold mb-1 istok-web-bold">Tipo de membresía</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <select name="plan_id" required class="flex-1 bg-transparent outline-none istok-web-regular cursor-pointer">
                        <option value="" disabled selected>Selecciona un plan</option>
                        @foreach($planes as $p)
                            <option value="{{ $p->id }}">{{ $p->nombre }} ({{ $p->duracion_dias }} días) – ${{ number_format($p->precio, 0) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <a href="{{ route('dashboard') }}" class="w-full text-center border-2 border-[var(--gris-oscuro)] text-[var(--gris-oscuro)] py-3 rounded-full font-bold hover:bg-[var(--gris-oscuro)] hover:text-white transition">Cancelar</a>
                <button type="submit" class="w-full bg-[var(--azul)] text-white py-3 rounded-full font-bold hover:bg-[var(--azul-oscuro)] transition shadow-md">
                    Registrar y Enrolar
                </button>
            </div>
        </form>
    </div>

    <!-- 🛠 MODAL OVERLAY -->
    <div id="modalOverlay" class="fixed inset-0 bg-black/70 z-[9999] hidden flex items-center justify-center backdrop-blur-sm">
        
        <!-- 1. CARGANDO -->
        <div id="estadoCargando" class="bg-white rounded-2xl p-8 max-w-md w-full text-center shadow-2xl relative hidden">
            <!-- X para cancelar -->
            <button type="button" onclick="cerrarModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>

            <div class="flex justify-center mb-6"><div class="windows-loader"></div></div>
            <h3 class="text-2xl istok-web-bold text-[var(--azul)] mb-2">Creando Registro...</h3>
            <p class="text-gray-600 mb-6 istok-web-regular text-lg">Usuario creado. Iniciando sensor...</p>
            
            <div class="bg-gray-50 rounded-xl p-4 text-left space-y-3 text-sm text-gray-700 border border-gray-200">
                <div class="flex items-center gap-3"><span class="bg-[var(--azul)] text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</span><span>Colocar dedo.</span></div>
                <div class="flex items-center gap-3"><span class="bg-[var(--azul)] text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</span><span>Retirar dedo.</span></div>
                <div class="flex items-center gap-3"><span class="bg-[var(--azul)] text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</span><span>Colocar nuevamente.</span></div>
            </div>
            <p class="mt-6 text-xs text-gray-400 animate-pulse">Conectando con dispositivo IoT...</p>
        </div>

        <!-- 2. ÉXITO -->
        <div id="estadoExito" class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl hidden">
            <div class="flex justify-center mb-4"><div class="checkmark-circle"><div class="background"></div><div class="checkmark draw"></div></div></div>
            <h3 class="text-2xl istok-web-bold text-green-600 mb-2">¡Registro Exitoso!</h3>
            <p class="text-gray-600 text-sm mb-4">El cliente y su huella han sido guardados.</p>
            <button onclick="location.href='{{ route('dashboard') }}'" class="bg-green-600 text-white px-6 py-2 rounded-full font-bold hover:bg-green-700 w-full">Ir al Dashboard</button>
        </div>

        <!-- 3. ERROR -->
        <div id="estadoError" class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl hidden">
            <div class="flex justify-center mb-4">
                <div class="cross-circle">
                    <div class="background"></div>
                    <div class="cross-line one"></div>
                    <div class="cross-line two"></div>
                </div>
            </div>
            <h3 class="text-2xl istok-web-bold text-red-600 mb-2">Atención</h3>
            
            <p class="text-gray-600 text-sm mb-4">
                El usuario se creó correctamente, pero <strong>no se pudo registrar la huella</strong>.
                <br><br>
                Puedes continuar al pago y registrar la huella después en la sección de Usuarios.
            </p>
            
            <div class="flex flex-col gap-2 justify-center">
                <button id="btnContinuarPagoError" type="button" class="bg-[var(--azul)] text-white px-4 py-2 rounded-full font-bold hover:bg-[var(--azul-oscuro)] text-sm w-full">
                    Continuar al Pago
                </button>
            </div>
        </div>
    </div>

    <script>
        let pollingInterval;
        let currentUserId = null;

        // Manejo del Submit con AJAX
        document.getElementById('registroForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Limpiar errores previos
            const errorDiv = document.getElementById('errorContainer');
            errorDiv.classList.add('hidden');
            errorDiv.innerText = '';

            // Mostrar modal cargando
            mostrarModal('cargando');

            const formData = new FormData(this);

            try {
                // NOTA: Asegúrate de que la ruta en web.php sea 'cliente.store' o 'clientes.store'
                // Aquí uso 'cliente.store' basado en tus códigos anteriores.
                const response = await fetch("{{ route('clientes.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                let urlPago = "";

                if (response.ok && data.success) {
                    currentUserId = data.user_id;
                    
                    // Generamos la ruta de pago reemplazando el ID
                    // Asegúrate de que 'clientes.pagoInicial' exista en tus rutas, usa un placeholder temporal
                    urlPago = "{{ route('clientes.pagoInicial', ':id') }}".replace(':id', currentUserId);
                    
                    // Configurar botón del Modal de Éxito
                    const btnExito = document.querySelector('#estadoExito button');
                    btnExito.innerText = "Ir a Pagar Membresía";
                    btnExito.onclick = function() { window.location.href = urlPago; };

                    // Configurar botón del Modal de Error
                    const btnError = document.getElementById('btnContinuarPagoError');
                    btnError.onclick = function() { window.location.href = urlPago; };

                    iniciarPolling(currentUserId);
                }
            } catch (error) {
                console.error(error);
                cerrarModal();
                errorDiv.classList.remove('hidden');
                errorDiv.innerText = 'Error de conexión con el servidor.';
            }
        });

        function mostrarModal(tipo) {
            const overlay = document.getElementById('modalOverlay');
            const cargando = document.getElementById('estadoCargando');
            const exito = document.getElementById('estadoExito');
            const error = document.getElementById('estadoError');

            overlay.classList.remove('hidden');
            cargando.classList.add('hidden');
            exito.classList.add('hidden');
            error.classList.add('hidden');

            if(tipo === 'cargando') cargando.classList.remove('hidden');
            if(tipo === 'exito') exito.classList.remove('hidden');
            if(tipo === 'error') error.classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('modalOverlay').classList.add('hidden');
            if(pollingInterval) clearInterval(pollingInterval);
        }

        function iniciarPolling(id) {
            if(pollingInterval) clearInterval(pollingInterval);
            let intentos = 0;
            
            pollingInterval = setInterval(async () => {
                intentos++;
                try {
                    const res = await fetch(`/api/user-status/${id}`);
                    const data = await res.json();

                    // Si hay error (8) o timeout (9)
                    if (data.estatus == 8 || data.estatus == 9) {
                        clearInterval(pollingInterval);
                        mostrarModal('error');
                    } 
                    // Si hay éxito (huella asignada)
                    else if (data.fingerprint_id != null) {
                        clearInterval(pollingInterval);
                        mostrarModal('exito');
                        // Limpiamos el formulario para el siguiente
                        document.getElementById('registroForm').reset();
                    }

                    if (intentos > 60) { // 60 segundos de espera máx
                        clearInterval(pollingInterval);
                        mostrarModal('error');
                        document.getElementById('msgError').innerText = "El servidor tardó demasiado en responder.";
                    }
                } catch (e) { console.error(e); }
            }, 1000);
        }
    </script>
</body>
</html>
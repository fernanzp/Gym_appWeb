<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>

  <!--Logo-->
  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">

  <!--Fuente-->
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
        
        /* --- LOADER ESTILO WINDOWS (Agregado) --- */
        .windows-loader {
            width: 50px; height: 50px; margin: 0 auto;
            border: 5px solid #f3f3f3; border-top: 5px solid var(--azul); border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="flex flex-col items-center justify-between min-h-screen bg-white text-black relative">

    <!-- Logo -->
    <div class="absolute top-[-15px] left-1">
        <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30">
    </div>

    <!-- Contenedor principal -->
    <div class="w-full max-w-[50%] mt-[5vh]">
        <h1 class="text-4xl font-bold text-center mb-8 istok-web-bold">Registrar cliente</h1>

        <!-- 
             🔥 FORMULARIO CON TRIGGER DEL MODAL 
             Se agrega onsubmit="activarLoader()"
        -->
        <form action="{{ route('clientes.store') }}" method="POST" class="space-y-4" onsubmit="activarLoader()">
            @csrf

            @if (session('success'))
                <div class="p-3 rounded-md bg-green-100 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @error('general')
                <div class="p-3 rounded-md bg-red-100 text-red-800">{{ $message }}</div>
            @enderror

            <!-- Nota -->
            <div>
                <span>Por favor, llena todos los campos del formulario con los datos proporcionados por el cliente.</span>
            </div>

            <!-- Nombre completo -->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Nombre completo</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="nombre_comp" type="text" placeholder="Nombre completo"
                        value="{{ old('nombre_comp') }}"
                        class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular">
                </div>
                @error('nombre_comp') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Correo -->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">
                    Correo electrónico
                    <span class="text-xs istok-web-regular text-[var(--gris-oscuro)]">
                        Se enviará un correo con el enlace para activar su cuenta.
                    </span>
                </label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="email" type="email" placeholder="ejemplo@gmail.com"
                        value="{{ old('email') }}"
                        class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular">
                </div>
                @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Teléfono -->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Teléfono</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="telefono" type="tel" placeholder="Introduce 10 dígitos"
                        value="{{ old('telefono') }}"
                        class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular">
                </div>
                @error('telefono') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Fecha de nacimiento -->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Fecha de nacimiento</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="fecha_nac" type="date" value="{{ old('fecha_nac') }}"
                        class="flex-1 bg-transparent outline-none istok-web-regular">
                </div>
                @error('fecha_nac') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Tipo de membresía -->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Tipo de membresía</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <select id="plan_id" name="plan_id" class="flex-1 bg-transparent outline-none istok-web-regular">
                        @foreach($planes as $p)
                            <option value="{{ $p->id }}" @selected(old('plan_id') == $p->id)>
                                {{ $p->nombre }} ({{ $p->duracion_dias }} días) – ${{ number_format($p->precio, 0) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('plan_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-4">
                <!-- Botón cancelar sin submit -->
                <a href="{{ url()->previous() }}"
                class="w-full mt-2 text-center bg-transparent text-[var(--gris-oscuro)] border-2 border-[var(--gris-oscuro)] istok-web-regular py-3 rounded-full hover:bg-[var(--gris-oscuro)] hover:text-white transition-colors">
                    Cancelar
                </a>

                <!-- Botón registrar -->
                <button type="submit"
                        class="w-full mt-2 bg-[var(--azul)] text-white istok-web-regular py-3 rounded-full hover:bg-[var(--azul-oscuro)] transition">
                    Registrar cliente y huella
                </button>
            </div>
        </form>

    </div>

    <!-- Nota -->
    <p class="text-center istok-web-regular text-[var(--gris-oscuro)] my-4">
        Al registrar al cliente se generará el cálculo automático de pago y promociones.
    </p>

    <!-- 
        🛠 MODAL OVERLAY DE CARGA (Nuevo)
        Tiene posición fixed para flotar sobre todo y botón X para cerrar.
    -->
    <div id="modalOverlay" class="fixed inset-0 bg-black/70 z-[9999] hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full text-center shadow-2xl relative">
             
             <!-- Botón X para cancelar si se arrepiente o se traba -->
             <button type="button" onclick="cerrarModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 font-bold text-xl transition-colors">&times;</button>

            <div class="flex justify-center mb-6"><div class="windows-loader"></div></div>
            
            <h3 class="text-2xl istok-web-bold text-[var(--azul)] mb-2">Creando Registro...</h3>
            <p class="text-gray-600 mb-4 text-sm">Estamos guardando los datos e iniciando el sensor.</p>
            
            <div class="text-left bg-gray-50 p-4 rounded-lg border border-gray-100 mb-4">
                <p class="text-sm text-gray-700 mb-2"><strong>Siguientes pasos:</strong></p>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-2">
                    <li>El usuario se creará en la base de datos.</li>
                    <li>El sensor biométrico se activará.</li>
                    <li>Serás redirigido para confirmar la huella.</li>
                </ul>
            </div>
            <p class="text-xs text-gray-400 animate-pulse">Conectando con dispositivo IoT...</p>
        </div>
    </div>

    <script>
        function activarLoader() {
            document.getElementById('modalOverlay').classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('modalOverlay').classList.add('hidden');
        }
    </script>

</body>
</html>
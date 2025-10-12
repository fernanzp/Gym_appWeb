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
    </style>
</head>
<body class="flex flex-col items-center justify-between min-h-screen bg-white text-black">

    <!-- Logo -->
    <div class="absolute top-[-15px] left-1">
        <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30">
    </div>

    <!-- Contenedor principal -->
    <div class="w-full max-w-[50%] mt-[5vh]">
        <h1 class="text-4xl font-bold text-center mb-8 istok-web-bold">Registrar cliente</h1>

        <form action="{{ route('clientes.store') }}" method="POST" class="space-y-4">
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

            <!-- Contraseña -->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Contraseña</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="contrasena" type="password" placeholder="Contraseña"
                        class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular">
                </div>
                @error('contrasena') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Confirmación de contraseña -->
            <div>
                <label class="block font-bold mb-1 istok-web-bold">Confirmar contraseña</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3">
                    <input name="contrasena_confirmation" type="password" placeholder="Repite la contraseña"
                        class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular">
                </div>
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
                    Registrar cliente
                </button>
            </div>
        </form>

    </div>

    <!-- Nota -->
    <p class="text-center istok-web-regular text-[var(--gris-oscuro)] my-4">
        Al registrar al cliente se generará el cálculo automático de pago y promociones.
    </p>
</body>
</html>
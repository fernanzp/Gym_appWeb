<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Usuario</title>

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
<body class="flex flex-col items-center min-h-screen bg-white text-black">

    <div class="absolute top-[-15px] left-1">
        <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30">
    </div>

    <div class="w-full max-w-[50%] mt-[5vh] mb-10">
        <h1 class="text-4xl font-bold text-center mb-8 istok-web-bold">
            Editar Usuario: {{ $usuario->nombre_comp ?? 'Cargando...' }}
        </h1>

        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

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

            @error('general')
                <div class="p-3 rounded-md bg-red-100 text-red-800">{{ $message }}</div>
            @enderror

            <div>
                <label class="block font-bold mb-1 istok-web-bold">Nombre completo</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3 ring-1 ring-transparent focus-within:ring-[var(--azul)] transition-all">
                    <input name="nombre_comp" type="text" placeholder="Nombre completo"
                        value="{{ old('nombre_comp', $usuario->nombre_comp ?? '') }}"
                        class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular text-gray-800">
                </div>
                @error('nombre_comp') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-bold mb-1 istok-web-bold">Correo electrónico</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3 ring-1 ring-transparent focus-within:ring-[var(--azul)] transition-all">
                    <input name="email" type="email" placeholder="ejemplo@gmail.com"
                        value="{{ old('email', $usuario->email ?? '') }}"
                        class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular text-gray-800">
                </div>
                @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-bold mb-1 istok-web-bold">Teléfono</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3 ring-1 ring-transparent focus-within:ring-[var(--azul)] transition-all">
                    <input name="telefono" type="tel" placeholder="Introduce 10 dígitos"
                        value="{{ old('telefono', $usuario->telefono ?? '') }}"
                        class="flex-1 bg-transparent outline-none placeholder-[var(--gris-oscuro)] istok-web-regular text-gray-800">
                </div>
                @error('telefono') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-bold mb-1 istok-web-bold">Estatus</label>
                <div class="flex items-center bg-[var(--gris-bajito)] rounded-md px-4 py-3 relative">
                    @php 
                        $currentStatus = old('estatus', $usuario->estatus ?? 'activo'); 
                    @endphp
                    <select name="estatus" class="flex-1 bg-transparent outline-none istok-web-regular text-gray-800 appearance-none cursor-pointer z-10">
                        <option value="activo" @selected($currentStatus == 'activo')>Activo</option>
                        <option value="inactivo" @selected($currentStatus == 'inactivo')>Inactivo</option>
                    </select>
                    <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none text-[var(--gris-oscuro)]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                @error('estatus') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-4 pt-4">
                <a href="{{ route('usuarios') }}"
                   class="w-full text-center bg-transparent text-[var(--gris-oscuro)] border-2 border-[var(--gris-oscuro)] istok-web-regular py-3 rounded-full hover:bg-[var(--gris-oscuro)] hover:text-white transition-colors font-bold">
                    Cancelar
                </a>

                <button type="submit"
                        class="w-full bg-[var(--azul)] text-white istok-web-regular py-3 rounded-full hover:bg-[var(--azul-oscuro)] transition font-bold shadow-md hover:shadow-lg">
                    Guardar cambios
                </button>
            </div>
        </form>
        
        <div class="mt-12 pt-6 border-t-2 border-[var(--gris-bajito)]">
            <h2 class="text-2xl font-bold text-center mb-6 istok-web-bold text-[var(--azul)]">
                Gestión Biométrica
            </h2>

            <div class="bg-[var(--gris-bajito)] rounded-xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4 ring-1 ring-black/5">
                
                <div class="flex items-center gap-3">
                    @if($usuario->fingerprint_id)
                        <div class="bg-green-100 p-3 rounded-full text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="istok-web-bold text-lg text-green-700">Huella Registrada</p>
                            <p class="text-sm text-gray-500 istok-web-regular">ID Sensor: {{ $usuario->fingerprint_id }}</p>
                        </div>
                    @else
                        <div class="bg-yellow-100 p-3 rounded-full text-yellow-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="istok-web-bold text-lg text-yellow-700">Sin Huella</p>
                            <p class="text-sm text-gray-500 istok-web-regular">El usuario no tiene acceso biométrico.</p>
                        </div>
                    @endif
                </div>

                <form action="{{ route('usuario.resetFingerprint', $usuario->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    @if($usuario->fingerprint_id)
                        <button type="submit" 
                                onclick="return confirm('¿Estás seguro? Se borrará la huella actual del sensor y tendrás que registrar una nueva inmediatamente.');"
                                class="w-full sm:w-auto px-6 py-3 bg-white border-2 border-[var(--azul)] text-[var(--azul)] istok-web-bold rounded-full hover:bg-[var(--azul)] hover:text-white transition-all shadow-sm flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Actualizar Huella
                        </button>
                    @else
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-[var(--azul)] text-white istok-web-bold rounded-full hover:bg-[var(--azul-oscuro)] transition-all shadow-md flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Registrar Huella Ahora
                        </button>
                    @endif
                </form>
            </div>
        </div>
        </div>

</body>
</html>
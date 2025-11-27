<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel de control</title>

  <!--Logo-->
  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">

  <!--Fuentes-->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Alumni+Sans+Pinstripe&family=Istok+Web:ital,wght@0,400;0,700;1,400;1,700&family=Poiret+One&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

  <!-- Alpine.js para interacciones UI (Dropdowns) -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
        :root {
            --azul: #0460D9;
            --azul-oscuro: #0248D2;
            --gris-oscuro: #727272;
            --gris-medio: #A3A3A3;
            --gris-medio-bajito: #E5E5E5;
            --gris-bajito: #F1F1F1;
        }
        .istok-web-regular { font-family: "Istok Web", sans-serif; font-weight: 400; font-style: normal; }
        .istok-web-bold { font-family: "Istok Web", sans-serif; font-weight: 700; font-style: normal; }
    </style>
</head>
<body class="antialiased istok-web-regular" x-data="{ aforoModalOpen: false, editPlanModalOpen: false, currentPlan: { nombre: '', precio: 0, duracion: 0 }, isEditing: false }">
  <div class="min-h-screen p-6">
    <!-- GRID PRINCIPAL: [Sidebar | Área principal] -->
    <div class="grid grid-cols-[84px_minmax(0,1fr)] gap-6 h-[calc(100vh-3rem)]">
      
      <!-- SIDEBAR -->
      <aside class="h-full flex flex-col items-center justify-center"> <!--bg-[#D9D9D9] rounded-2xlx-->
        <nav class="flex flex-col items-center gap-6" role="navigation" aria-label="Sidebar">
          
          <!-- Home (activo) -->
          <a href="#" class="p-2 rounded-xl text-[var(--azul)] hover:opacity-85" aria-current="page" title="Inicio"> <!--ring-2 ring-[var(--azul)]-->
            <svg class="w-8 h-8" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M277.8 8.6c-12.3-11.4-31.3-11.4-43.5 0l-224 208c-9.6 9-12.8 22.9-8 35.1S18.8 272 32 272l16 0 0 176c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-176 16 0c13.2 0 25-8.1 29.8-20.3s1.6-26.2-8-35.1l-224-208zM240 320l32 0c26.5 0 48 21.5 48 48l0 96-128 0 0-96c0-26.5 21.5-48 48-48z"/>
            </svg>
            <span class="sr-only">Inicio</span>
          </a>

          <!-- Users -->
          <a href="{{ route('usuarios') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Usuarios"> <!--ring-2 ring-[var(--gris-medio)] hover:ring-[var(--gris-oscuro)]-->
            <svg class="w-8 h-8" viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M320 16a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM96 88a72 72 0 1 1 0 144 72 72 0 1 1 0-144zM0 416c0-70.7 57.3-128 128-128 12.8 0 25.2 1.9 36.9 5.4-32.9 36.8-52.9 85.4-52.9 138.6l0 16c0 11.4 2.4 22.2 6.7 32L32 480c-17.7 0-32-14.3-32-32l0-32zm521.3 64c4.3-9.8 6.7-20.6 6.7-32l0-16c0-53.2-20-101.8-52.9-138.6 11.7-3.5 24.1-5.4 36.9-5.4 70.7 0 128 57.3 128 128l0 32c0 17.7-14.3 32-32 32l-86.7 0zM472 160a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zM160 432c0-88.4 71.6-160 160-160s160 71.6 160 160l0 16c0 17.7-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32l0-16z"/>
            </svg>
            <span class="sr-only">Usuarios</span>
          </a>

          <!-- Membresías -->
          <a href="{{ route('membresias') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Membresías"> <!--ring-2 ring-[var(--gris-medio)] hover:ring-[var(--gris-oscuro)]-->
            <svg class="w-8 h-8" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32zm80 256l64 0c44.2 0 80 35.8 80 80 0 8.8-7.2 16-16 16L80 384c-8.8 0-16-7.2-16-16 0-44.2 35.8-80 80-80zm-24-96a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm240-48l112 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-112 0c-13.3 0-24-10.7-24-24s10.7-24 24-24zm0 96l112 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-112 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/>
            </svg>
            <span class="sr-only">Membresías</span>
          </a>

          <!-- Entradas y salidas 
          <a href="{{ route('entradas-salidas') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Entradas y salidas">
            <svg class="w-8 h-8" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M48 256c0-114.9 93.1-208 208-208 63.1 0 119.6 28.1 157.8 72.5 8.6 10.1 23.8 11.2 33.8 2.6s11.2-23.8 2.6-33.8C403.3 34.6 333.7 0 256 0 114.6 0 0 114.6 0 256l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40zm458.5-52.9c-2.7-13-15.5-21.3-28.4-18.5s-21.3 15.5-18.5 28.4c2.9 13.9 4.5 28.3 4.5 43.1l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40c0-18.1-1.9-35.8-5.5-52.9zM256 80c-19 0-37.4 3-54.5 8.6-15.2 5-18.7 23.7-8.3 35.9 7.1 8.3 18.8 10.8 29.4 7.9 10.6-2.9 21.8-4.4 33.4-4.4 70.7 0 128 57.3 128 128l0 24.9c0 25.2-1.5 50.3-4.4 75.3-1.7 14.6 9.4 27.8 24.2 27.8 11.8 0 21.9-8.6 23.3-20.3 3.3-27.4 5-55 5-82.7l0-24.9c0-97.2-78.8-176-176-176zM150.7 148.7c-9.1-10.6-25.3-11.4-33.9-.4-23.1 29.8-36.8 67.1-36.8 107.7l0 24.9c0 24.2-2.6 48.4-7.8 71.9-3.4 15.6 7.9 31.1 23.9 31.1 10.5 0 19.9-7 22.2-17.3 6.4-28.1 9.7-56.8 9.7-85.8l0-24.9c0-27.2 8.5-52.4 22.9-73.1 7.2-10.4 8-24.6-.2-34.2zM256 160c-53 0-96 43-96 96l0 24.9c0 35.9-4.6 71.5-13.8 106.1-3.8 14.3 6.7 29 21.5 29 9.5 0 17.9-6.2 20.4-15.4 10.5-39 15.9-79.2 15.9-119.7l0-24.9c0-28.7 23.3-52 52-52s52 23.3 52 52l0 24.9c0 36.3-3.5 72.4-10.4 107.9-2.7 13.9 7.7 27.2 21.8 27.2 10.2 0 19-7 21-17 7.7-38.8 11.6-78.3 11.6-118.1l0-24.9c0-53-43-96-96-96zm24 96c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 24.9c0 59.9-11 119.3-32.5 175.2l-5.9 15.3c-4.8 12.4 1.4 26.3 13.8 31s26.3-1.4 31-13.8l5.9-15.3C267.9 411.9 280 346.7 280 280.9l0-24.9z"/>
            </svg>
            <span class="sr-only">Entradas y salidas</span>
          </a>-->

          <!-- Análisis y reportes -->
          <a href="{{ route('analytics') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Análisis y reportes"> <!--ring-2 ring-[var(--gris-medio)] hover:ring-[var(--gris-oscuro)]-->
            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
              <path fill="currentColor" d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64L0 400c0 44.2 35.8 80 80 80l400 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 416c-8.8 0-16-7.2-16-16L64 64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7 262.6 153.4c-12.5-12.5-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l73.4-73.4 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z"/>
            </svg>
            <span class="sr-only">Análisis y reportes</span>
          </a>
        </nav>
      </aside>

      <!-- ÁREA PRINCIPAL -->
      <main class="h-full min-h-0 flex flex-col overflow-hidden">
        @if (session('success'))
            <div x-data="{ show: true }" 
                x-init="setTimeout(() => show = false, 4000)" 
                x-show="show" 
                x-transition.duration.500ms
                class="mb-3 p-3 rounded-md bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        <!-- Cabecera -->
        <header class="h-16 flex items-center justify-between"> <!--bg-[#D9D9D9] rounded-2xl-->
          @php
            // Usa tu zona horaria real:
            $hoy = now('America/Mexico_City');

            $dias  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
            $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

            $fechaCorta = $dias[$hoy->dayOfWeek] . ', ' . $hoy->format('j') . ' ' . $meses[$hoy->month - 1];
          @endphp
          <h1 class="text-3xl istok-web-bold">Panel de control</h1>
          <div class="flex items-center gap-3">
            <div class="text-right leading-tight">
              <p class="istok-web-bold">
                Hola, {{ auth()->user()->nombre_comp ?? auth()->user()->name ?? 'Usuario' }}
              </p>
              <p class="text-xs">{{ $fechaCorta }}</p>
            </div>
            <!-- Dropdown de Usuario -->
            <div class="relative" x-data="{ open: false }">
                <!-- CAMBIO: Agregado 'mr-1' para que el anillo azul no se corte a la derecha -->
                <button @click="open = !open" class="focus:outline-none block transition-transform active:scale-95 mr-1" title="Menú de usuario">
                    <img
                        src="{{ asset('images/avatar-default.jpg') }}"
                        alt="Foto de perfil"
                        class="w-10 h-10 rounded-full object-cover ring-1 ring-black/10 hover:ring-2 hover:ring-[var(--azul)] transition-all"
                    />
                </button>

                <!-- Menú Desplegable -->
                <div 
                    x-show="open" 
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-[var(--gris-bajito)] py-1 z-50 origin-top-right"
                    style="display: none;"
                >
                    <!-- Info del usuario -->
                    <div class="px-4 py-3 border-b border-[var(--gris-bajito)] mb-1">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->nombre_comp ?? 'Usuario' }}</p>
                        <p class="text-xs text-[var(--gris-oscuro)] truncate">{{ auth()->user()->email ?? 'correo@ejemplo.com' }}</p>
                    </div>
                    
                    <!-- Botón de Cerrar Sesión -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium transition-colors">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
          </div>
        </header>

        @foreach($usuariosPendientes as $usuario)
            @php
                $isError = $usuario->estatus == 8;
                $isTimeout = $usuario->estatus == 9;
                $alertClass = $isError ? 'bg-red-100 border-red-500 text-red-700' : 'bg-yellow-100 border-yellow-500 text-yellow-700';
                $title = $isError ? '🛑 Error de Huella' : '⏲️ Proceso de Huella Pendiente';
                $message = $isError ? 
                          'La huella para <strong>' . $usuario->nombre_comp . '</strong> no coincidió. Por favor, asegúrese de que el sensor esté listo.' :
                          'El registro de huella para <strong>' . $usuario->nombre_comp . '</strong> ha expirado (timeout). Debe reintentarlo.' ;
                $buttonColor = $isError ? 'bg-red-600 hover:bg-red-700' : 'bg-yellow-600 hover:bg-yellow-700';
            @endphp

            <div class="mt-6 p-4 border-l-4 rounded-lg {{ $alertClass }} flex items-center justify-between shadow-sm">
                <div>
                    <h4 class="text-xl istok-web-bold mb-1">{{ $title }}</h4>
                    <p class="text-sm">{!! $message !!}</p>
                </div>
                
                <form method="POST" action="{{ route('cliente.retry', $usuario->id) }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="istok-web-bold text-white py-2 px-4 rounded-lg {{ $buttonColor }}">
                        Volver a Intentar Registro
                    </button>
                </form>
            </div>

        @endforeach
          <section class="mt-6 flex-1 min-h-0 overflow-auto no-scrollbar">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">

                <!-- Card 1: Registrar Nuevo Cliente (Acción Principal) -->
                <div class="h-[180px] flex flex-col gap-4">
                    <!-- Botón 1.1: Registrar Nuevo Cliente -->
                    <article class="flex-1 bg-white rounded-xl border-2 border-dashed border-blue-200 hover:border-[var(--azul)] hover:shadow-md transition-all group">
                      <!-- He ajustado el layout a horizontal (flex-row) para que se vea bien en la mitad de altura -->
                      <a href="{{ route('clientRegister') }}" class="w-full h-full flex flex-row items-center justify-center gap-3 px-2">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-[var(--azul)] group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 640 512" fill="currentColor">
                                <path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312V248H440c-13.3 0-24-10.7-24-24s10.7-24 24-24h64V136c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--azul)] group-hover:text-[var(--azul-oscuro)] group-hover:scale-105 transition-transform">Nuevo Cliente</h3>
                      </a>
                    </article>

                    <!-- Botón 1.2: Nueva Recepcionista (NUEVO) -->
                    <!-- Mismo estilo que Nuevo Cliente, ruta 'register' (ajústala si es distinta) -->
                    <article class="flex-1 bg-white rounded-xl border-2 border-dashed border-blue-200 hover:border-[var(--azul)] hover:shadow-md transition-all group">
                      <a href="{{ route('receptionist.create') }}" class="w-full h-full flex flex-row items-center justify-center gap-3 px-2">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-[var(--azul)] group-hover:scale-110 transition-transform">
                            <!-- Icono: Usuario con gafete/corbata -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 640 512" fill="currentColor">
                                <path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312V248H440c-13.3 0-24-10.7-24-24s10.7-24 24-24h64V136c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--azul)] group-hover:text-[var(--azul-oscuro)] group-hover:scale-105 transition-transform">Nueva Recepcionista</h3>
                      </a>
                    </article>
                </div>

                <!-- Card 2: Control de Acceso (Entrada/Salida) -->
                <div class="h-[180px] flex flex-col gap-4">
                    
                    <!-- Botón Abrir Entrada (Estilo Verde/Clean) -->
                    <form action="{{ route('access.visita') }}" method="POST" class="flex-1 h-full">
                        @csrf
                        <input type="hidden" name="direction" value="entry">
                        
                        <button type="submit" class="w-full h-full rounded-xl bg-blue-50 border border-blue-100 text-[var(--azul)] hover:bg-blue-100 hover:shadow-sm flex items-center justify-center gap-3 transition-all group">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 512 512">
                            <path d="M352 96l64 0c17.7 0 32 14.3 32 32l0 256c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0c53 0 96-43 96-96l0-256c0-53-43-96-96-96l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm-9.4 182.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L242.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128z"/>
                          </svg>
                          <span class="font-bold group-hover:scale-105 transition-transform block">Abrir Entrada</span>
                        </button>
                    </form>

                    <!-- Botón Abrir Salida (Estilo Rojo/Clean) -->
                    <form action="{{ route('access.visita') }}" method="POST" class="flex-1 h-full">
                        @csrf
                        <input type="hidden" name="direction" value="exit">
                        
                        <button type="submit" class="w-full h-full rounded-xl bg-blue-50 border border-blue-100 text-[var(--azul)] hover:bg-blue-100 hover:shadow-sm flex items-center justify-center gap-3 transition-all group">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                          </svg>
                          <span class="font-bold group-hover:scale-105 transition-transform block">Abrir Salida</span>
                        </button>
                    </form>
                </div>

                <!-- Card 3: Ocupación Actual (Aforo) - MODIFICADA -->
                <!-- Importante: Verifica que en tu <body x-data="{ aforoModalOpen: false }"> esté declarado el x-data -->
                <article class="h-[180px] bg-white p-4 rounded-2xl border border-[var(--gris-medio-bajito)] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between group">
                    
                    <div class="flex justify-between items-start">
                        <!-- Icono Izquierdo -->
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 640 512">
                                <path d="M320 16a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM96 88a72 72 0 1 1 0 144 72 72 0 1 1 0-144zM0 416c0-70.7 57.3-128 128-128 12.8 0 25.2 1.9 36.9 5.4-32.9 36.8-52.9 85.4-52.9 138.6l0 16c0 11.4 2.4 22.2 6.7 32L32 480c-17.7 0-32-14.3-32-32l0-32zm521.3 64c4.3-9.8 6.7-20.6 6.7-32l0-16c0-53.2-20-101.8-52.9-138.6 11.7-3.5 24.1-5.4 36.9-5.4 70.7 0 128 57.3 128 128l0 32c0 17.7-14.3 32-32 32l-86.7 0zM472 160a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zM160 432c0-88.4 71.6-160 160-160s160 71.6 160 160l0 16c0 17.7-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32l0-16z"/>
                            </svg>
                        </div>

                        <!-- Botón Configuración (Engranaje) -->
                        <button 
                            @click="aforoModalOpen = true" 
                            class="text-[var(--gris-medio)] hover:text-[var(--azul)] hover:bg-blue-50 p-1.5 rounded-full transition-all"
                            title="Configurar Aforo Máximo"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>

                    <div>
                        <p class="text-[var(--gris-oscuro)] text-sm font-medium">Ocupación Actual</p>
                        <div class="flex items-baseline gap-1 mt-1">
                            <h3 id="txt-aforo" class="text-4xl istok-web-bold text-gray-900">-</h3>
                            <!-- Puedes poner dinámico este / 100 después -->
                            <span class="text-sm text-gray-400 font-medium">/ 100</span> 
                        </div>
                    </div>
                </article>

              <!-- Card 4: Membresías por Vencer -->
              <article class="h-[180px] bg-white p-4 rounded-2xl border border-[var(--gris-medio-bajito)] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                  <div class="flex justify-between items-start">
                      <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600">
                          <!-- Icono Alerta/Reloj -->
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 512 512">
                              <path d="M256 0a256 256 0 1 1 0 512 256 256 0 1 1 0-512zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
                          </svg>
                      </div>
                  </div>
                  <div>
                      <p class="text-[var(--gris-oscuro)] text-sm font-medium">Membresías por vencer esta semana</p>
                      <h3 class="text-4xl istok-web-bold text-gray-900 mt-1">{{ $porVencerCount }}</h3>
                  </div>
              </article>

              <!-- Card 5: Nuevos Usuarios -->
              <article class="h-[180px] bg-white p-4 rounded-2xl border border-[var(--gris-medio-bajito)] shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                  <div class="flex justify-between items-start">
                      <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center text-sky-600">
                          <!-- Icono Usuario/Estrella -->
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 640 512">
                              <path d="M286 304c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7L78 512c-16.4 0-29.7-13.3-29.7-29.7 0-98.5 79.8-178.3 178.3-178.3l59.4 0zM585.7 105.9c7.8-10.7 22.8-13.1 33.5-5.3s13.1 22.8 5.3 33.5L522.1 274.9c-4.2 5.7-10.7 9.4-17.7 9.8s-14-2.2-18.9-7.3l-46.4-48c-9.2-9.5-9-24.7 .6-33.9 9.5-9.2 24.7-8.9 33.9 .6l26.5 27.4 85.6-117.7zM256.3 248a120 120 0 1 1 0-240 120 120 0 1 1 0 240z"/>
                          </svg>
                      </div>
                  </div>
                  <div>
                      <p class="text-[var(--gris-oscuro)] text-sm font-medium">Nuevos clientes en el último mes</p>
                      <h3 class="text-4xl istok-web-bold text-gray-900 mt-1">{{ $nuevosUsuariosCount }}</h3>
                  </div>
              </article>

          </div>

          <!-- Sección: Gestión de Planes y Membresías -->
          <div class="mt-8">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl istok-web-bold">Planes y Membresías</h2>
                    <p class="text-sm text-[var(--gris-oscuro)]">Administra los precios y duracion de tus servicios.</p>
                </div>
                
              <!--Botón: Crear nuevo plan-->
              <button type="button"
                  @click="
                      currentPlan = { nombre: '', precio: '', duracion: '' }; 
                      isEditing = false; 
                      editPlanModalOpen = true"
                  class="inline-flex items-center justify-center gap-2 bg-[var(--azul)] hover:bg-[var(--azul-oscuro)] text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-md shadow-blue-500/20 group">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <span>Crear Nuevo Plan</span>
              </button>
            </div>

            <!-- Datos de Prueba (Frontend Only) -->
            @php
                $planesDummy = [
                    (object)['id' => 1, 'nombre' => 'Plan Mensual', 'precio' => 500.00, 'duracion_dias' => 30, 'usuarios_activos' => 142, 'estatus' => 'activo'],
                    (object)['id' => 2, 'nombre' => 'Plan Trimestral', 'precio' => 1350.00, 'duracion_dias' => 90, 'usuarios_activos' => 56, 'estatus' => 'activo'],
                    (object)['id' => 3, 'nombre' => 'Plan Anual', 'precio' => 4800.00, 'duracion_dias' => 365, 'usuarios_activos' => 24, 'estatus' => 'inactivo'],
                ];
            @endphp

            <div class="overflow-hidden rounded-2xl bg-white border border-[var(--gris-medio-bajito)] shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-[#F8F8F8] border-b border-[var(--gris-medio-bajito)] istok-web-bold">
                            <tr>
                                <th class="px-6 py-4 text-left text-gray-600">Nombre del Plan</th>
                                <th class="px-6 py-4 text-left text-gray-600">Precio</th>
                                <th class="px-6 py-4 text-left text-gray-600">Duración</th>
                                <th class="px-6 py-4 text-left text-gray-600">Usuarios Activos</th>
                                <th class="px-6 py-4 text-left text-gray-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--gris-medio-bajito)] istok-web-regular">
                            @forelse($planesDummy as $plan)
                                <tr class="hover:bg-[#FAFAFA] transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-[var(--azul)]">
                                                <!-- Icono Plan -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 text-lg">{{ $plan->nombre }}</p>
                                                <p class="text-xs text-gray-500">ID: {{ $plan->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-800 font-semibold">
                                        ${{ number_format($plan->precio, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $plan->duracion_dias }} días
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $plan->usuarios_activos }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2 transition-all transform translate-x-2 group-hover:translate-x-0">
                                            <!-- Botón Editar -->
                                            <button 
                                                type="button"
                                                @click="
                                                    currentPlan = { 
                                                        nombre: '{{ $plan->nombre }}', 
                                                        precio: {{ $plan->precio }}, 
                                                        duracion: {{ $plan->duracion_dias }} 
                                                    }; 
                                                    isEditing = true; 
                                                    editPlanModalOpen = true"
                                                class="p-2 rounded-lg text-[var(--gris-medio)] hover:text-[var(--azul)] hover:bg-blue-50 transition-colors" 
                                                title="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <!-- Botón Eliminar -->
                                            <button class="p-2 rounded-lg text-[var(--gris-medio)] hover:text-red-600 hover:bg-red-50 transition-colors" title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No hay planes registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- MODAL DE CONFIGURACIÓN DE AFORO (Alpine.js) -->
  <!-- Este bloque va al final de tu archivo dashboard.blade.php, antes del </body> -->
  <div 
      x-show="aforoModalOpen" 
      style="display: none;"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
  >
      <!-- Contenedor del Modal -->
      <div 
          @click.away="aforoModalOpen = false"
          class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden transform transition-all"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0 translate-y-4 scale-95"
          x-transition:enter-end="opacity-100 translate-y-0 scale-100"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100 translate-y-0 scale-100"
          x-transition:leave-end="opacity-0 translate-y-4 scale-95"
      >
          <!-- Header -->
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
              <h3 class="text-lg font-bold text-gray-800 istok-web-bold">Configurar Capacidad</h3>
              <button @click="aforoModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </button>
          </div>

          <!-- Body -->
          <div class="p-6">
              <!-- Formulario sin acción real por ahora -->
              <form action="#" method="POST"> 
                  @csrf
                  <div class="mb-4">
                      <label for="aforo_maximo" class="block text-sm font-medium text-gray-700 mb-2">
                          Aforo Máximo Permitido
                      </label>
                      <div class="relative">
                          <input 
                              type="number" 
                              name="aforo_maximo" 
                              id="aforo_maximo" 
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-100 focus:border-[var(--azul)] outline-none transition-all text-lg font-semibold text-gray-900 placeholder-gray-400"
                              placeholder="Ej. 150"
                              value="100" 
                          >
                          <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                              <span class="text-gray-400 text-sm">personas</span>
                          </div>
                      </div>
                      <p class="mt-2 text-xs text-gray-500">
                          Esto ajustará los cálculos de porcentaje de ocupación en el dashboard y la app.
                      </p>
                  </div>

                  <!-- Botones de Acción -->
                  <div class="flex gap-3 mt-6">
                      <button 
                          type="button" 
                          @click="aforoModalOpen = false"
                          class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors"
                      >
                          Cancelar
                      </button>
                      <button 
                          type="button"
                          @click="aforoModalOpen = false" 
                          class="flex-1 px-4 py-2.5 rounded-xl bg-[var(--azul)] text-white font-bold hover:bg-[var(--azul-oscuro)] shadow-md shadow-blue-500/20 transition-all"
                      >
                          Guardar
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>

  <!-- MODAL DINÁMICO (CREAR / EDITAR PLAN) -->
  <div 
      x-show="editPlanModalOpen" 
      style="display: none;"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
  >
      <div 
          @click.away="editPlanModalOpen = false"
          class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden transform transition-all"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0 translate-y-4 scale-95"
          x-transition:enter-end="opacity-100 translate-y-0 scale-100"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100 translate-y-0 scale-100"
          x-transition:leave-end="opacity-0 translate-y-4 scale-95"
      >
          <!-- Header -->
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
              <!-- Título Dinámico -->
              <h3 class="text-lg font-bold text-gray-800 istok-web-bold" x-text="isEditing ? 'Editar Plan' : 'Crear Nuevo Plan'"></h3>
              
              <button @click="editPlanModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </button>
          </div>

          <!-- Body -->
          <div class="p-6">
              <form action="#" method="POST">
                  @csrf
                  
                  <!-- Solo incluimos el método PUT si estamos editando -->
                  <template x-if="isEditing">
                      <input type="hidden" name="_method" value="PUT">
                  </template>
                  
                  <div class="space-y-4">
                      <!-- Campo: Nombre -->
                      <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Plan</label>
                          <input 
                              type="text" 
                              x-model="currentPlan.nombre"
                              class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-100 focus:border-[var(--azul)] outline-none transition-all text-gray-900 font-medium"
                              placeholder="Ej. Plan Mensual"
                          >
                      </div>

                      <div class="grid grid-cols-2 gap-4">
                          <!-- Campo: Precio -->
                          <div>
                              <label class="block text-sm font-medium text-gray-700 mb-1">Precio (MXN)</label>
                              <div class="relative">
                                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">$</span>
                                  <input 
                                      type="number" 
                                      step="0.01"
                                      x-model="currentPlan.precio"
                                      class="w-full pl-7 pr-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-100 focus:border-[var(--azul)] outline-none transition-all text-gray-900 font-medium"
                                      placeholder="0.00"
                                  >
                              </div>
                          </div>

                          <!-- Campo: Duración -->
                          <div>
                              <label class="block text-sm font-medium text-gray-700 mb-1">Duración</label>
                              <div class="relative">
                                  <input 
                                      type="number" 
                                      x-model="currentPlan.duracion"
                                      class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-100 focus:border-[var(--azul)] outline-none transition-all text-gray-900 font-medium"
                                      placeholder="30"
                                  >
                                  <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 text-sm pointer-events-none">días</span>
                              </div>
                          </div>
                      </div>
                  </div>

                  <!-- Footer Botones -->
                  <div class="flex gap-3 mt-8">
                      <button 
                          type="button" 
                          @click="editPlanModalOpen = false"
                          class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors"
                      >
                          Cancelar
                      </button>
                      <button 
                          type="submit"
                          class="flex-1 px-4 py-2.5 rounded-xl bg-[var(--azul)] text-white font-bold hover:bg-[var(--azul-oscuro)] shadow-md shadow-blue-500/20 transition-all"
                          x-text="isEditing ? 'Guardar Cambios' : 'Crear Plan'"
                      >
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>

  <!-- SCRIPT DE AFORO AUTOMÁTICO -->
  <script>
    async function actualizarAforo() {
        try {
            // Usamos 'url()' para generar la dirección completa y evitar errores
            const res = await fetch("{{ url('/api/aforo-live') }}");
            const data = await res.json();
            
            const elemento = document.getElementById('txt-aforo');
            if(elemento) {
                // Solo ponemos el número, sin el %
                elemento.innerText = data.total; 
            }
        } catch (e) {
            console.error("Error aforo:", e);
        }
    }

    // Iniciar al cargar y repetir cada 5 segundos
    document.addEventListener("DOMContentLoaded", function() {
        actualizarAforo();
        setInterval(actualizarAforo, 5000);
    });
  </script>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Membresías</title>

  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">

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
<body class="antialiased istok-web-regular">
  <div class="min-h-screen p-6">
    <div class="grid grid-cols-[84px_minmax(0,1fr)] gap-6 h-[calc(100vh-3rem)]">
      
      <aside class="h-full flex flex-col items-center justify-center">
        <nav class="flex flex-col items-center gap-6" role="navigation" aria-label="Sidebar">
          
          <a href="{{ route('dashboard') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Inicio">
            <svg class="w-8 h-8" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M277.8 8.6c-12.3-11.4-31.3-11.4-43.5 0l-224 208c-9.6 9-12.8 22.9-8 35.1S18.8 272 32 272l16 0 0 176c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-176 16 0c13.2 0 25-8.1 29.8-20.3s1.6-26.2-8-35.1l-224-208zM240 320l32 0c26.5 0 48 21.5 48 48l0 96-128 0 0-96c0-26.5 21.5-48 48-48z"/>
            </svg>
            <span class="sr-only">Inicio</span>
          </a>

          <a href="{{ route('usuarios') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Usuarios">
            <svg class="w-8 h-8" viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M320 16a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM96 88a72 72 0 1 1 0 144 72 72 0 1 1 0-144zM0 416c0-70.7 57.3-128 128-128 12.8 0 25.2 1.9 36.9 5.4-32.9 36.8-52.9 85.4-52.9 138.6l0 16c0 11.4 2.4 22.2 6.7 32L32 480c-17.7 0-32-14.3-32-32l0-32zm521.3 64c4.3-9.8 6.7-20.6 6.7-32l0-16c0-53.2-20-101.8-52.9-138.6 11.7-3.5 24.1-5.4 36.9-5.4 70.7 0 128 57.3 128 128l0 32c0 17.7-14.3 32-32 32l-86.7 0zM472 160a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zM160 432c0-88.4 71.6-160 160-160s160 71.6 160 160l0 16c0 17.7-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32l0-16z"/>
            </svg>
            <span class="sr-only">Usuarios</span>
          </a>

          <a href="{{ route('membresias') }}" class="p-2 rounded-xl text-[var(--azul)]" aria-current="page" title="Membresías">
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
          <a href="analisis-reportes" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Análisis y reportes"> <!--ring-2 ring-[var(--gris-medio)] hover:ring-[var(--gris-oscuro)]-->
            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
              <path fill="currentColor" d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64L0 400c0 44.2 35.8 80 80 80l400 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 416c-8.8 0-16-7.2-16-16L64 64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7 262.6 153.4c-12.5-12.5-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l73.4-73.4 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z"/>
            </svg>
            <span class="sr-only">Análisis y reportes</span>
          </a>
        </nav>
      </aside>

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
        <header class="h-16 flex items-center justify-between">
          @php
            $hoy = now('America/Mexico_City');
            $dias  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
            $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            $fechaCorta = $dias[$hoy->dayOfWeek] . ', ' . $hoy->format('j') . ' ' . $meses[$hoy->month - 1];
          @endphp
          <h1 class="text-3xl istok-web-bold">Gestión de Membresías</h1>
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

        <section class="mt-6 flex-1 min-h-0 overflow-auto no-scrollbar">
          
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Card 1: Membresías Activas (Estilo Green/Vigente) -->
            <article class="bg-white p-5 rounded-2xl border border-[var(--gris-medio-bajito)] shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                        <!-- Icono Check -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 448 512">
                          <path d="M434.8 70.1c14.3 10.4 17.5 30.4 7.1 44.7l-256 352c-5.5 7.6-14 12.3-23.4 13.1s-18.5-2.7-25.1-9.3l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l101.5 101.5 234-321.7c10.4-14.3 30.4-17.5 44.7-7.1z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-[var(--gris-oscuro)] text-sm">Membresías Activas</p>
                    <h3 class="text-3xl istok-web-bold text-black mt-1">{{ $totalActivas }}</h3>
                </div>
            </article>

            <!-- Card 2: Próximas a Vencer (Estilo Orange/Alerta) -->
            <article class="bg-white p-5 rounded-2xl border border-[var(--gris-medio-bajito)] shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                        <!-- Icono Reloj -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 512 512">
                          <path d="M256 0a256 256 0 1 1 0 512 256 256 0 1 1 0-512zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-[var(--gris-oscuro)] text-sm">Próximas a Vencer</p>
                    <h3 class="text-3xl istok-web-bold text-black mt-1">{{ $totalPorVencer }}</h3>
                </div>
            </article>

            <!-- Card 3: Vencidas (Estilo Red/Error) -->
            <article class="bg-white p-5 rounded-2xl border border-[var(--gris-medio-bajito)] shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                        <!-- Icono X -->
                        <!--<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 512 512">
                          <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z"/>
                        </svg>-->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 512 512">
                          <path d="M367.2 412.5L99.5 144.8c-22.4 31.4-35.5 69.8-35.5 111.2 0 106 86 192 192 192 41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3c22.4-31.4 35.5-69.8 35.5-111.2 0-106-86-192-192-192-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0 256 256 0 1 1 -512 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-[var(--gris-oscuro)] text-sm">Vencidas</p>
                    <h3 class="text-3xl istok-web-bold text-black mt-1">{{ $totalVencidas }}</h3>
                </div>
            </article>

            <!-- Card 4: Congeladas (Estilo Blue/Info) -->
            <article class="bg-white p-5 rounded-2xl border border-[var(--gris-medio-bajito)] shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <!-- Icono Nieve -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 512 512">
                          <path d="M288.2 0c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 62.1-15-15c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l49 49 0 70.6-61.2-35.3-17.9-66.9c-3.4-12.8-16.6-20.4-29.4-17S95.3 98 98.7 110.8l5.5 20.5-53.7-31C35.2 91.5 15.6 96.7 6.8 112s-3.6 34.9 11.7 43.7l53.7 31-20.5 5.5c-12.8 3.4-20.4 16.6-17 29.4s16.6 20.4 29.4 17l66.9-17.9 61.2 35.3-61.2 35.3-66.9-17.9c-12.8-3.4-26 4.2-29.4 17s4.2 26 17 29.4l20.5 5.5-53.7 31C3.2 365.1-2 384.7 6.8 400s28.4 20.6 43.7 11.7l53.7-31-5.5 20.5c-3.4 12.8 4.2 26 17 29.4s26-4.2 29.4-17l17.9-66.9 61.2-35.3 0 70.6-49 49c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l15-15 0 62.1c0 17.7 14.3 32 32 32s32-14.3 32-32l0-62.1 15 15c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-49-49 0-70.6 61.2 35.3 17.9 66.9c3.4 12.8 16.6 20.4 29.4 17s20.4-16.6 17-29.4l-5.5-20.5 53.7 31c15.3 8.8 34.9 3.6 43.7-11.7s3.6-34.9-11.7-43.7l-53.7-31 20.5-5.5c12.8-3.4 20.4-16.6 17-29.4s-16.6-20.4-29.4-17l-66.9 17.9-61.2-35.3 61.2-35.3 66.9 17.9c12.8 3.4 26-4.2 29.4-17s-4.2-26-17-29.4l-20.5-5.5 53.7-31c15.3-8.8 20.6-28.4 11.7-43.7s-28.4-20.5-43.7-11.7l-53.7 31 5.5-20.5c3.4-12.8-4.2-26-17-29.4s-26 4.2-29.4 17l-17.9 66.9-61.2 35.3 0-70.6 49-49c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-15 15 0-62.1z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-[var(--gris-oscuro)] text-sm">Congeladas</p>
                    <h3 class="text-3xl istok-web-bold text-black mt-1">{{ $totalCongeladas }}</h3>
                </div>
            </article>
          </div>

          <div class="mt-6">
            <div class="flex flex-col xl:flex-row items-center justify-between gap-4 mb-4">
              <!-- Lado Izquierdo: Título y Filtros -->
              <div class="flex-grow w-full xl:w-auto flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-6">
                  <h2 class="text-2xl istok-web-bold text-gray-900 whitespace-nowrap">Listado de Membresías</h2>
                  
                  <!-- Separador vertical (visible solo en pantallas medianas hacia arriba) -->
                  <div class="hidden md:block w-px h-8 bg-gray-200"></div>
                  
                  <div class="flex flex-wrap gap-2">
                      @php
                          // Actualizamos las clases para que parezcan "píldoras" modernas
                          // Transition-all suaviza el cambio de color al pasar el mouse
                          $baseClass = "px-4 py-2 text-sm font-medium rounded-xl border transition-all duration-200";
                          
                          // Estilo Activo: Azul sólido, sombra suave, sin borde visible para resaltar
                          $activeClass = "bg-[var(--azul)] border-transparent text-white shadow-md shadow-blue-500/20";
                          
                          // Estilo Inactivo: Fondo gris muy claro, texto gris, borde sutil hoverable
                          $inactiveClass = "bg-gray-50 border-[var(--gris-medio-bajito)] text-gray-600 hover:bg-white hover:border-gray-300 hover:shadow-sm";
                      @endphp

                      <a href="{{ route('membresias', ['filter' => 'todas', 'search' => request('search')]) }}" 
                        class="{{ $baseClass }} {{ $filtro == 'todas' ? $activeClass : $inactiveClass }}">Todas</a>
                      
                      <a href="{{ route('membresias', ['filter' => 'vigentes', 'search' => request('search')]) }}" 
                        class="{{ $baseClass }} {{ $filtro == 'vigentes' ? $activeClass : $inactiveClass }}">Vigentes</a>
                      
                      <a href="{{ route('membresias', ['filter' => 'por_vencer', 'search' => request('search')]) }}" 
                        class="{{ $baseClass }} {{ $filtro == 'por_vencer' ? $activeClass : $inactiveClass }}">Por Vencer</a>

                      <a href="{{ route('membresias', ['filter' => 'vencidas', 'search' => request('search')]) }}" 
                        class="{{ $baseClass }} {{ $filtro == 'vencidas' ? $activeClass : $inactiveClass }}">Vencidas</a>

                      <a href="{{ route('membresias', ['filter' => 'congeladas', 'search' => request('search')]) }}" 
                        class="{{ $baseClass }} {{ $filtro == 'congeladas' ? $activeClass : $inactiveClass }}">Congeladas</a>
                  </div>
              </div>
              
              <!-- Lado Derecho: Buscador Mejorado -->
              <form action="{{ route('membresias') }}" method="GET" class="flex gap-2 w-full xl:w-auto min-w-[320px]">
                  {{-- Mantenemos el input hidden para no perder el filtro al buscar --}}
                  <input type="hidden" name="filter" value="{{ $filtro }}">
                  
                  <div class="relative w-full">
                      {{-- Icono de lupa posicionado dentro del input --}}
                      <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                      </span>
                      <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Buscar usuario..." 
                            class="w-full pl-10 pr-4 py-2 rounded-xl border border-[var(--gris-medio-bajito)] bg-gray-50 text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-[var(--azul)] transition-all">
                  </div>

                  <button type="submit" class="bg-[var(--azul)] hover:bg-[#0350b5] text-white px-5 py-2 rounded-xl shadow-md shadow-blue-500/20 transition-colors font-medium">
                      Buscar
                  </button>
              </form>
          </div>

            <div class="overflow-hidden rounded-2xl bg-white border border-[var(--gris-medio-bajito)] shadow-sm">
              <div class="overflow-x-auto">
                <table class="min-w-full">
                  <thead class="bg-gray-50 border-b border-[var(--gris-medio-bajito)] istok-web-bold">
                      <tr class="">
                          <th class="px-4 py-3 text-left text-gray-600">Usuario</th>
                          <th class="px-4 py-3 text-left text-gray-600">Plan</th>
                          <th class="px-4 py-3 text-left text-gray-600">Fecha Inicio</th>
                          <th class="px-4 py-3 text-left text-gray-600">Fecha Fin</th>
                          <th class="px-4 py-3 text-left text-gray-600">Días Restantes</th>
                          <th class="px-4 py-3 text-left text-gray-600">Estatus</th>
                          <th class="px-4 py-3 text-left text-gray-600">Acciones</th>
                      </tr>
                  </thead>
                  <tbody class="divide-y divide-[var(--gris-medio-bajito)] istok-web-regular">
                      @forelse($membresias as $m)
                          @php
                          if ($m->fecha_fin < now()){
                                $diasRestantes = 0;
                          }else {
                            $diasRestantes = now()->diffInDays($m->fecha_fin);
                          }
                              
                              $estatusTexto = '';
                              $estatusClase = '';
                              $diasClase = 'font-semibold';

                              if ($m->estatus === 'vigente') {
                                  $estatusTexto = 'Vigente';
                                  $estatusClase = 'bg-green-100 text-green-700';
                                  
                                  if ($diasRestantes < 0) {
                                      // Caso raro: Estatus dice activo pero fecha ya pasó
                                      $diasClase = 'text-red-600'; 
                                  } elseif ($diasRestantes <= 5) {
                                      $diasClase = 'text-orange-600';
                                  } else {
                                      $diasClase = 'text-green-600';
                                  }
                              } elseif ($m->estatus === 'congelada') {
                                  $estatusTexto = 'Congelada';
                                  $estatusClase = 'bg-blue-100 text-blue-700';
                                  $diasClase = 'text-gray-500';
                              } else {
                                  $estatusTexto = 'Vencida';
                                  $estatusClase = 'bg-red-100 text-red-700';
                                  $diasClase = 'text-red-600';
                              }
                          @endphp

                          <tr class="hover:bg-gray-50 transition-colors">
                              <td class="px-4 py-3">
                                  <p class="font-semibold">{{ $m->usuario->nombre_comp ?? 'Usuario Eliminado' }}</p>
                                  <p class="text-sm text-gray-600">{{ $m->usuario->email ?? '' }}</p>
                              </td>
                              <td class="px-4 py-3 text-gray-800">{{ $m->plan->nombre ?? 'Sin Plan' }}</td>
                              <td class="px-4 py-3 text-gray-800">{{ $m->fecha_ini ? $m->fecha_ini->format('d/m/Y') : '-' }}</td>
                              <td class="px-4 py-3 text-gray-800">{{ $m->fecha_fin ? $m->fecha_fin->format('d/m/Y') : '-' }}</td>
                              
                              <td class="px-4 py-3 {{ $diasClase }}">
                                  {{ round($diasRestantes) }} días
                              </td>
                              
                              <td class="px-4 py-3">
                                  <span class="px-3 py-0.5 rounded-full text-xs font-semibold {{ $estatusClase }}">
                                      {{ $estatusTexto }}
                                  </span>
                              </td>
                              
                              <td class="px-4 py-3">
                                  <div class="flex gap-1">
                                    @if($m->estatus === 'vencida')
                                      <button type="button" onclick="abrirModalRenovacion({{ $m->id }}, '{{ $m->usuario->nombre_comp }}', {{ $m->plan_id }})" class="p-1.5 rounded-lg text-[var(--gris-oscuro)] font-semibold hover:bg-gray-200/60" title="Renovar">
                                        Renovar
                                      </button>
                                      @elseif($m->estatus === 'vigente')
                                          <button type="button" onclick="abrirModal({{ $m->id }}, '{{ $m->usuario->nombre_comp }}', 'congelar')" class="p-1.5 rounded-lg text-[var(--gris-oscuro)] font-semibold hover:bg-gray-200/60" title="Congelar">
                                            Congelar
                                          </button>
                                      @elseif($m->estatus === 'congelada')
                                          <button type="button" onclick="abrirModal({{ $m->id }}, '{{ $m->usuario->nombre_comp }}', 'reactivar')" class="p-1.5 rounded-lg text-[var(--gris-oscuro)] font-semibold hover:bg-gray-200/60" title="Reactivar">
                                            Reactivar
                                          </button>
                                      @endif
                                  </div>
                              </td>
                          </tr>
                      @empty
                        <tr>
                          <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                              <div class="flex flex-col items-center justify-center">
                                  <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                  </svg>
                                  <p>No se encontraron membresías con los criterios seleccionados.</p>
                              </div>
                          </td>
                        </tr>
                      @endforelse
                  </tbody>
                </table>
              </div>
                
              <div class="p-4">
                  {{ $membresias->appends(['search' => request('search'), 'filter' => $filtro])->links() }}
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>
  </div>
<div id="statusModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="cerrarModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-xl font-semibold leading-6 text-gray-900 istok-web-bold" id="modal-title">
                                Confirmar acción
                            </h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 istok-web-regular">
                                    ¿Estás seguro de que deseas <span id="modalActionText" class="font-bold"></span> la membresía de:
                                </p>
                                <p class="text-lg text-[var(--azul)] font-bold mt-2" id="modalUserName">
                                    </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                    <form id="statusForm" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <button type="submit" id="confirmButton" class="inline-flex w-full justify-center rounded-lg bg-[var(--azul)] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 sm:ml-3 sm:w-auto">
                            Confirmar
                        </button>
                    </form>
                    
                    <button type="button" onclick="cerrarModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE RENOVACIÓN -->
<div id="renewModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="cerrarModalRenovacion()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                
                <form action="{{ route('membresias.prepararRenovacion') }}" method="POST">
                    @csrf
                    <input type="hidden" name="membresia_id" id="renewMembresiaId">

                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-[var(--azul)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-xl font-semibold leading-6 text-gray-900 istok-web-bold">Renovar Membresía</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">Selecciona el plan para renovar a <span id="renewUserName" class="font-bold text-[var(--azul)]"></span>.</p>
                                    
                                    <label for="planSelect" class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                                    <select name="plan_id" id="planSelect" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-[var(--azul)] sm:text-sm sm:leading-6">
                                        @foreach($planes as $plan)
                                            <option value="{{ $plan->id }}" data-precio="{{ $plan->precio }}">
                                                {{ $plan->nombre }} - ${{ number_format($plan->precio, 0) }} ({{ $plan->duracion_dias }} días)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-[var(--azul)] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 sm:ml-3 sm:w-auto">
                            Continuar al Pago
                        </button>
                        <button type="button" onclick="cerrarModalRenovacion()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModal(id, nombre, accion) {
        const modal = document.getElementById('statusModal');
        const form = document.getElementById('statusForm');
        const userNameText = document.getElementById('modalUserName');
        const actionText = document.getElementById('modalActionText');
        const confirmBtn = document.getElementById('confirmButton');

        // 1. Actualizar la URL del formulario
        // Usamos una ruta base y reemplazamos el ID placeholder
        let url = "{{ route('membresias.toggleStatus', ':id') }}";
        url = url.replace(':id', id);
        form.action = url;

        // 2. Actualizar textos
        userNameText.textContent = nombre;
        actionText.textContent = accion.toUpperCase();

        // 3. Cambiar color del botón según acción (Opcional, visualmente útil)
        if(accion === 'congelar') {
            confirmBtn.classList.remove('bg-green-600', 'hover:bg-green-500');
            confirmBtn.classList.add('bg-blue-600', 'hover:bg-blue-500'); // Azul para congelar
            confirmBtn.textContent = "Sí, Congelar";
        } else {
            confirmBtn.classList.remove('bg-blue-600', 'hover:bg-blue-500');
            confirmBtn.classList.add('bg-green-600', 'hover:bg-green-500'); // Verde para reactivar
            confirmBtn.textContent = "Sí, Reactivar";
        }

        // 4. Mostrar modal
        modal.classList.remove('hidden');
    }

    function cerrarModal() {
        const modal = document.getElementById('statusModal');
        modal.classList.add('hidden');
    }

    function abrirModalRenovacion(id, nombre, planIdActual) {
        document.getElementById('renewMembresiaId').value = id;
        document.getElementById('renewUserName').textContent = nombre;
        
        // Seleccionar por defecto el plan que ya tenía
        const select = document.getElementById('planSelect');
        select.value = planIdActual;

        document.getElementById('renewModal').classList.remove('hidden');
    }

    function cerrarModalRenovacion() {
        document.getElementById('renewModal').classList.add('hidden');
    }
</script>
</body>
</html>
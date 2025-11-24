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

  <!-- Chart.js para las gráficas -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
        :root {
            --azul: #0460D9;
            --azul-oscuro: #0248D2;
            --gris-oscuro: #727272;
            --gris-medio: #A3A3A3;
            --gris-bajito: #F1F1F1;
        }
        .istok-web-regular { font-family: "Istok Web", sans-serif; font-weight: 400; font-style: normal; }
        .istok-web-bold { font-family: "Istok Web", sans-serif; font-weight: 700; font-style: normal; }
        
        /* Ocultar scrollbar pero permitir scroll */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="antialiased istok-web-regular">
    <div class="min-h-screen p-6">
        <!-- GRID PRINCIPAL: [Sidebar | Área principal] -->
        <div class="grid grid-cols-[84px_minmax(0,1fr)] gap-6 h-[calc(100vh-3rem)]">
            <!-- SIDEBAR -->
            <aside class="h-full flex flex-col items-center justify-center">
                <nav class="flex flex-col items-center gap-6" role="navigation" aria-label="Sidebar">
                
                <!-- Home -->
                <a href="{{ route('dashboard') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Inicio">
                    <svg class="w-8 h-8" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                        <path fill="currentColor" d="M277.8 8.6c-12.3-11.4-31.3-11.4-43.5 0l-224 208c-9.6 9-12.8 22.9-8 35.1S18.8 272 32 272l16 0 0 176c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-176 16 0c13.2 0 25-8.1 29.8-20.3s1.6-26.2-8-35.1l-224-208zM240 320l32 0c26.5 0 48 21.5 48 48l0 96-128 0 0-96c0-26.5 21.5-48 48-48z"/>
                    </svg>
                    <span class="sr-only">Inicio</span>
                </a>

                <!-- Users -->
                <a href="{{ route('usuarios') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Usuarios">
                    <svg class="w-8 h-8" viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg">
                        <path fill="currentColor" d="M320 16a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM96 88a72 72 0 1 1 0 144 72 72 0 1 1 0-144zM0 416c0-70.7 57.3-128 128-128 12.8 0 25.2 1.9 36.9 5.4-32.9 36.8-52.9 85.4-52.9 138.6l0 16c0 11.4 2.4 22.2 6.7 32L32 480c-17.7 0-32-14.3-32-32l0-32zm521.3 64c4.3-9.8 6.7-20.6 6.7-32l0-16c0-53.2-20-101.8-52.9-138.6 11.7-3.5 24.1-5.4 36.9-5.4 70.7 0 128 57.3 128 128l0 32c0 17.7-14.3 32-32 32l-86.7 0zM472 160a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zM160 432c0-88.4 71.6-160 160-160s160 71.6 160 160l0 16c0 17.7-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32l0-16z"/>
                    </svg>
                    <span class="sr-only">Usuarios</span>
                </a>

                <!-- Membresías -->
                <a href="{{ route('membresias') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Membresías">
                    <svg class="w-8 h-8" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg">
                        <path fill="currentColor" d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32zm80 256l64 0c44.2 0 80 35.8 80 80 0 8.8-7.2 16-16 16L80 384c-8.8 0-16-7.2-16-16 0-44.2 35.8-80 80-80zm-24-96a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm240-48l112 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-112 0c-13.3 0-24-10.7-24-24s10.7-24 24-24zm0 96l112 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-112 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/>
                    </svg>
                    <span class="sr-only">Membresías</span>
                </a>

                <!-- Entradas y salidas -->
                <a href="{{ route('entradas-salidas') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Entradas y salidas">
                    <svg class="w-8 h-8" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                        <path fill="currentColor" d="M48 256c0-114.9 93.1-208 208-208 63.1 0 119.6 28.1 157.8 72.5 8.6 10.1 23.8 11.2 33.8 2.6s11.2-23.8 2.6-33.8C403.3 34.6 333.7 0 256 0 114.6 0 0 114.6 0 256l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40zm458.5-52.9c-2.7-13-15.5-21.3-28.4-18.5s-21.3 15.5-18.5 28.4c2.9 13.9 4.5 28.3 4.5 43.1l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40c0-18.1-1.9-35.8-5.5-52.9zM256 80c-19 0-37.4 3-54.5 8.6-15.2 5-18.7 23.7-8.3 35.9 7.1 8.3 18.8 10.8 29.4 7.9 10.6-2.9 21.8-4.4 33.4-4.4 70.7 0 128 57.3 128 128l0 24.9c0 25.2-1.5 50.3-4.4 75.3-1.7 14.6 9.4 27.8 24.2 27.8 11.8 0 21.9-8.6 23.3-20.3 3.3-27.4 5-55 5-82.7l0-24.9c0-97.2-78.8-176-176-176zM150.7 148.7c-9.1-10.6-25.3-11.4-33.9-.4-23.1 29.8-36.8 67.1-36.8 107.7l0 24.9c0 24.2-2.6 48.4-7.8 71.9-3.4 15.6 7.9 31.1 23.9 31.1 10.5 0 19.9-7 22.2-17.3 6.4-28.1 9.7-56.8 9.7-85.8l0-24.9c0-27.2 8.5-52.4 22.9-73.1 7.2-10.4 8-24.6-.2-34.2zM256 160c-53 0-96 43-96 96l0 24.9c0 35.9-4.6 71.5-13.8 106.1-3.8 14.3 6.7 29 21.5 29 9.5 0 17.9-6.2 20.4-15.4 10.5-39 15.9-79.2 15.9-119.7l0-24.9c0-28.7 23.3-52 52-52s52 23.3 52 52l0 24.9c0 36.3-3.5 72.4-10.4 107.9-2.7 13.9 7.7 27.2 21.8 27.2 10.2 0 19-7 21-17 7.7-38.8 11.6-78.3 11.6-118.1l0-24.9c0-53-43-96-96-96zm24 96c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 24.9c0 59.9-11 119.3-32.5 175.2l-5.9 15.3c-4.8 12.4 1.4 26.3 13.8 31s26.3-1.4 31-13.8l5.9-15.3C267.9 411.9 280 346.7 280 280.9l0-24.9z"/>
                    </svg>
                    <span class="sr-only">Entradas y salidas</span>
                </a>

                <!-- Análisis y reportes (activo) -->
                <a href="#" class="p-2 rounded-xl text-[var(--azul)] hover:opacity-85" aria-current="page" title="Análisis y reportes">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="currentColor" d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64L0 400c0 44.2 35.8 80 80 80l400 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 416c-8.8 0-16-7.2-16-16L64 64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7 262.6 153.4c-12.5-12.5-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l73.4-73.4 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z"/>
                    </svg>
                    <span class="sr-only">Análisis y reportes</span>
                </a>
                </nav>
            </aside>

            <!-- ÁREA PRINCIPAL -->
            <main class="h-full min-h-0 flex flex-col overflow-hidden">
                <!-- Cabecera -->
                <header class="h-16 flex items-center justify-between">
                    @php
                        $hoy = now('America/Mexico_City');
                        $dias  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
                        $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                        $fechaCorta = $dias[$hoy->dayOfWeek] . ', ' . $hoy->format('j') . ' ' . $meses[$hoy->month - 1];
                    @endphp
                    <h1 class="text-3xl istok-web-bold">Análisis y reportes</h1>
                    <div class="flex items-center gap-3">
                        <div class="text-right leading-tight">
                        <p class="istok-web-bold">
                            Hola, {{ auth()->user()->nombre_comp ?? auth()->user()->name ?? 'Usuario' }}
                        </p>
                        <p class="text-xs">{{ $fechaCorta }}</p>
                        </div>
                        <img
                        src="{{ asset('images/avatar-default.jpg') }}"
                        alt="Foto de perfil"
                        class="w-10 h-10 rounded-full object-cover ring-1 ring-black/10"
                        />
                    </div>
                </header>
                
                <section class="mt-6 flex-1 min-h-0 overflow-auto no-scrollbar pb-6">
                    <!-- 1. GRID DE CARDS SUPERIORES -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <!-- Card 1: Ingresos -->
                        <div class="bg-white p-5 rounded-2xl border border-[var(--gris-bajito)] shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div class="w-10 h-10 rounded-lg bg-[rgba(4,96,217,0.1)] flex items-center justify-center text-[var(--azul)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 320 512">
                                        <path d="M136 24c0-13.3 10.7-24 24-24s24 10.7 24 24l0 40 56 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-114.9 0c-24.9 0-45.1 20.2-45.1 45.1 0 22.5 16.5 41.5 38.7 44.7l91.6 13.1c53.8 7.7 93.7 53.7 93.7 108 0 60.3-48.9 109.1-109.1 109.1l-10.9 0 0 40c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-40-72 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l130.9 0c24.9 0 45.1-20.2 45.1-45.1 0-22.5-16.5-41.5-38.7-44.7l-91.6-13.1C55.9 273.5 16 227.4 16 173.1 16 112.9 64.9 64 125.1 64l10.9 0 0-40z"/>
                                    </svg>
                                </div>
                                <div class="flex items-center text-emerald-500 text-sm font-bold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span>+8.1%</span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-[var(--gris-oscuro)] text-sm">Ingresos Mensuales</p>
                                <h3 class="text-3xl istok-web-bold text-black mt-1">$94,250</h3>
                            </div>
                        </div>

                        <!-- Card 2: Miembros Activos -->
                        <div class="bg-white p-5 rounded-2xl border border-[var(--gris-bajito)] shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div class="w-10 h-10 rounded-lg bg-[rgba(4,96,217,0.1)] flex items-center justify-center text-[var(--azul)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 640 512">
                                        <path d="M320 16a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM96 88a72 72 0 1 1 0 144 72 72 0 1 1 0-144zM0 416c0-70.7 57.3-128 128-128 12.8 0 25.2 1.9 36.9 5.4-32.9 36.8-52.9 85.4-52.9 138.6l0 16c0 11.4 2.4 22.2 6.7 32L32 480c-17.7 0-32-14.3-32-32l0-32zm521.3 64c4.3-9.8 6.7-20.6 6.7-32l0-16c0-53.2-20-101.8-52.9-138.6 11.7-3.5 24.1-5.4 36.9-5.4 70.7 0 128 57.3 128 128l0 32c0 17.7-14.3 32-32 32l-86.7 0zM472 160a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zM160 432c0-88.4 71.6-160 160-160s160 71.6 160 160l0 16c0 17.7-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32l0-16z"/>
                                    </svg>
                                </div>
                                <div class="flex items-center text-emerald-500 text-sm font-bold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span>+12.3%</span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-[var(--gris-oscuro)] text-sm">Miembros Activos</p>
                                <h3 class="text-3xl istok-web-bold text-black mt-1">447</h3>
                            </div>
                        </div>

                        <!-- Card 3: Asistencia -->
                        <div class="bg-white p-5 rounded-2xl border border-[var(--gris-bajito)] shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div class="w-10 h-10 rounded-lg bg-[rgba(4,96,217,0.1)] flex items-center justify-center text-[var(--azul)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 640 512">
                                        <path d="M286 304c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7L78 512c-16.4 0-29.7-13.3-29.7-29.7 0-98.5 79.8-178.3 178.3-178.3l59.4 0zM585.7 105.9c7.8-10.7 22.8-13.1 33.5-5.3s13.1 22.8 5.3 33.5L522.1 274.9c-4.2 5.7-10.7 9.4-17.7 9.8s-14-2.2-18.9-7.3l-46.4-48c-9.2-9.5-9-24.7 .6-33.9 9.5-9.2 24.7-8.9 33.9 .6l26.5 27.4 85.6-117.7zM256.3 248a120 120 0 1 1 0-240 120 120 0 1 1 0 240z"/>
                                    </svg>
                                </div>
                                <div class="flex items-center text-emerald-500 text-sm font-bold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span>+5.7%</span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-[var(--gris-oscuro)] text-sm">Asistencia Promedio</p>
                                <h3 class="text-3xl istok-web-bold text-black mt-1">218/día</h3>
                            </div>
                        </div>

                        <!-- Card 4: Retención -->
                        <div class="bg-white p-5 rounded-2xl border border-[var(--gris-bajito)] shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div class="w-10 h-10 rounded-lg bg-[rgba(4,96,217,0.1)] flex items-center justify-center text-[var(--azul)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 576 512">
                                        <path d="M384 160c-17.7 0-32-14.3-32-32s14.3-32 32-32l160 0c17.7 0 32 14.3 32 32l0 160c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-82.7-169.4 169.4c-12.5 12.5-32.8 12.5-45.3 0L192 269.3 54.6 406.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l160-160c12.5-12.5 32.8-12.5 45.3 0L320 306.7 466.7 160 384 160z"/>
                                    </svg>
                                </div>
                                <div class="flex items-center text-emerald-500 text-sm font-bold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span>+2.1%</span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-[var(--gris-oscuro)] text-sm">Tasa de Retención</p>
                                <h3 class="text-3xl istok-web-bold text-black mt-1">94.2%</h3>
                            </div>
                        </div>
                    </div>

                    <!-- 2. GRID DE GRÁFICAS -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Columna Izquierda (Ancha): Gráfico de Ingresos -->
                        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-[var(--gris-bajito)] shadow-sm">
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
                                <h3 class="text-3xl istok-web-bold text-black">Ingresos</h3>
                                <!-- Selectores -->
                                <div class="flex bg-[var(--gris-bajito)] p-1 rounded-lg mt-3 sm:mt-0">
                                    <button class="px-3 py-1 text-sm rounded-md hover:bg-white hover:shadow-sm transition-all text-[var(--gris-oscuro)]">Semana</button>
                                    <button class="px-3 py-1 text-sm rounded-md bg-white shadow-sm text-black font-semibold">Mes</button>
                                    <button class="px-3 py-1 text-sm rounded-md hover:bg-white hover:shadow-sm transition-all text-[var(--gris-oscuro)]">Año</button>
                                </div>
                            </div>
                            <div class="relative w-full h-[300px]">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>

                        <!-- Columna Derecha (Angosta): Gráfico "En este momento" (Estilo Azul) -->
                        <!-- CAMBIO: P-4 para compactar márgenes internos y h-fit -->
                        <div class="lg:col-span-1 bg-[var(--azul)] p-4 rounded-2xl shadow-sm flex flex-col text-white relative overflow-hidden h-fit">
                            <!-- Header -->
                            <!-- CAMBIO: Margin bottom reducido (mb-1) -->
                            <h3 class="text-2xl istok-web-bold mb-1">En este momento</h3>
                            <p class="text-blue-100 text-base mb-1">Actividad en vivo</p>

                            <!-- Chart Container -->
                            <!-- CAMBIO: Altura fija explícita h-[110px] para forzar el tamaño -->
                            <div class="relative flex-1 flex items-center justify-center h-[110px]">
                                <canvas id="liveActivityChart"></canvas>
                                <!-- Inner Text -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-1 text-white" viewBox="0 0 576 512">
                                        <path fill="currentColor" d="M64 128a112 112 0 1 1 224 0 112 112 0 1 1 -224 0zM0 464c0-97.2 78.8-176 176-176s176 78.8 176 176l0 6c0 23.2-18.8 42-42 42L42 512c-23.2 0-42-18.8-42-42l0-6zM432 64a96 96 0 1 1 0 192 96 96 0 1 1 0-192zm0 240c79.5 0 144 64.5 144 144l0 22.4c0 23-18.6 41.6-41.6 41.6l-144.8 0c6.6-12.5 10.4-26.8 10.4-42l0-6c0-51.5-17.4-98.9-46.5-136.7 22.6-14.7 49.6-23.3 78.5-23.3z"/>
                                    </svg>
                                    <span class="text-2xl font-bold">42</span>
                                    <span class="text-base text-blue-100">Miembros activos</span>
                                </div>
                            </div>

                            <!-- Divider -->
                            <!-- CAMBIO: Margin vertical reducido (my-2) -->
                            <div class="border-t border-white/20 my-2"></div>

                            <!-- Footer Stats -->
                            <div class="flex justify-between items-end">
                                <div>
                                    <div class="flex items-center gap-2 text-blue-100 text-sm mb-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Pico de hoy</span>
                                    </div>
                                    <p class="text-2xl font-bold">61</p>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center gap-2 text-blue-100 text-sm mb-1 justify-end">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                        <span>vs Ayer</span>
                                    </div>
                                    <p class="text-2xl font-bold">+14%</p>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="h-6"></div> <!-- Espaciado final -->
                </section>
            </main>
        </div>
    </div>

    <!-- Script para renderizar las gráficas con Chart.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Gráfica de Ingresos (Line Chart)
            const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
            const gradientRevenue = ctxRevenue.createLinearGradient(0, 0, 0, 300);
            gradientRevenue.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
            gradientRevenue.addColorStop(1, 'rgba(37, 99, 235, 0)');

            new Chart(ctxRevenue, {
                type: 'line',
                data: {
                    labels: ['Nov 1', 'Nov 5', 'Nov 9', 'Nov 13', 'Nov 17', 'Nov 21', 'Nov 24'],
                    datasets: [{
                        label: 'Ingresos',
                        data: [2400, 3100, 2900, 4100, 3600, 4800, 5200],
                        borderColor: '#2563EB',
                        backgroundColor: gradientRevenue,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2563EB',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1F2937',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) { return '$' + context.parsed.y; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 6000,
                            border: { display: false },
                            grid: {
                                color: '#F3F4F6',
                                drawBorder: false,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: { size: 11 },
                                stepSize: 1500,
                                callback: function(value) { return value === 0 ? '$0k' : '$' + (value / 1000) + 'k'; }
                            }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: '#9CA3AF', font: { size: 11 } }
                        }
                    },
                    interaction: { intersect: false, mode: 'index' },
                }
            });

            // 2. Gráfica "En este momento" (Circular Progress Bar style)
            const ctxLive = document.getElementById('liveActivityChart').getContext('2d');
            new Chart(ctxLive, {
                type: 'doughnut',
                data: {
                    labels: ['Activos', 'Restante'],
                    datasets: [{
                        data: [250, 150], // Simulando 250 de 400 capacidad
                        backgroundColor: ['#ffffff', 'rgba(255, 255, 255, 0.2)'], // Blanco y Transparente
                        borderWidth: 0,
                        borderRadius: 0, // Bordes planos
                        hoverOffset: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '85%', // Grosor fino
                    layout: {
                        // CAMBIO: Padding reducido a 5 para aprovechar el contenedor pequeño
                        padding: 5 
                    },
                    rotation: -90, // Empezar desde arriba
                    circumference: 360,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false } // Desactivar tooltip para mantener limpio
                    }
                }
            });
        });
    </script>
</body>
</html>
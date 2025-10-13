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
    </style>
</head>
<body class="antialiased istok-web-regular">
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

          <!-- Accesos -->
          <a href="{{ route('accesos') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Accesos"> <!--ring-2 ring-[var(--gris-medio)] hover:ring-[var(--gris-oscuro)]-->
            <svg class="w-8 h-8" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M48 256c0-114.9 93.1-208 208-208 63.1 0 119.6 28.1 157.8 72.5 8.6 10.1 23.8 11.2 33.8 2.6s11.2-23.8 2.6-33.8C403.3 34.6 333.7 0 256 0 114.6 0 0 114.6 0 256l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40zm458.5-52.9c-2.7-13-15.5-21.3-28.4-18.5s-21.3 15.5-18.5 28.4c2.9 13.9 4.5 28.3 4.5 43.1l0 40c0 13.3 10.7 24 24 24s24-10.7 24-24l0-40c0-18.1-1.9-35.8-5.5-52.9zM256 80c-19 0-37.4 3-54.5 8.6-15.2 5-18.7 23.7-8.3 35.9 7.1 8.3 18.8 10.8 29.4 7.9 10.6-2.9 21.8-4.4 33.4-4.4 70.7 0 128 57.3 128 128l0 24.9c0 25.2-1.5 50.3-4.4 75.3-1.7 14.6 9.4 27.8 24.2 27.8 11.8 0 21.9-8.6 23.3-20.3 3.3-27.4 5-55 5-82.7l0-24.9c0-97.2-78.8-176-176-176zM150.7 148.7c-9.1-10.6-25.3-11.4-33.9-.4-23.1 29.8-36.8 67.1-36.8 107.7l0 24.9c0 24.2-2.6 48.4-7.8 71.9-3.4 15.6 7.9 31.1 23.9 31.1 10.5 0 19.9-7 22.2-17.3 6.4-28.1 9.7-56.8 9.7-85.8l0-24.9c0-27.2 8.5-52.4 22.9-73.1 7.2-10.4 8-24.6-.2-34.2zM256 160c-53 0-96 43-96 96l0 24.9c0 35.9-4.6 71.5-13.8 106.1-3.8 14.3 6.7 29 21.5 29 9.5 0 17.9-6.2 20.4-15.4 10.5-39 15.9-79.2 15.9-119.7l0-24.9c0-28.7 23.3-52 52-52s52 23.3 52 52l0 24.9c0 36.3-3.5 72.4-10.4 107.9-2.7 13.9 7.7 27.2 21.8 27.2 10.2 0 19-7 21-17 7.7-38.8 11.6-78.3 11.6-118.1l0-24.9c0-53-43-96-96-96zm24 96c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 24.9c0 59.9-11 119.3-32.5 175.2l-5.9 15.3c-4.8 12.4 1.4 26.3 13.8 31s26.3-1.4 31-13.8l5.9-15.3C267.9 411.9 280 346.7 280 280.9l0-24.9z"/>
            </svg>
            <span class="sr-only">Accesos</span>
          </a>
        </nav>
      </aside>

      <!-- ÁREA PRINCIPAL -->
      <main class="h-full min-h-0 flex flex-col overflow-hidden">
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
            <img
              src="{{ asset('images/avatar-default.jpg') }}"
              alt="Foto de perfil"
              class="w-10 h-10 rounded-full object-cover ring-1 ring-black/10"
            />
          </div>
        </header>

        <!-- Contenido grande -->
        <section class="mt-6 flex-1 min-h-0 overflow-auto no-scrollbar">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1 -->
            <article class="h-[180px] rounded-2xl bg-gradient-to-br from-[var(--azul)] to-[#023373] text-white shadow-sm flex flex-col">
              <a href="{{ route('clientRegister') }}" class="p-4">
                <h3 class="text-xl font-bold text-center">Registrar nuevo cliente</h3>
                <div class="w-full h-full flex items-center justify-center">
                  <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                    <path fill="#ffffff" d="M136 128a120 120 0 1 1 240 0 120 120 0 1 1 -240 0zM48 482.3C48 383.8 127.8 304 226.3 304l59.4 0c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7L77.7 512C61.3 512 48 498.7 48 482.3zM544 96c13.3 0 24 10.7 24 24l0 48 48 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-48 0 0 48c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-48-48 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l48 0 0-48c0-13.3 10.7-24 24-24z"/>
                  </svg>
                </div>
              </a>
            </article>

            <!-- Card 2 -->
            <article class="h-[180px] rounded-2xl bg-[var(--gris-bajito)] p-4 shadow-sm flex flex-col justify-between">
              <h3 class="text-xl font-bold text-center">Ocupación actual</h3>
              <div class="w-full h-full flex items-center justify-center">
                <p class="text-6xl istok-web-bold">-%</p>
              </div>
              <!--<p class="text-sm text-black/80">Proximamente</p>-->
            </article>

            <!-- Card 3 -->
            <article class="h-[180px] rounded-2xl bg-[var(--gris-bajito)] p-4 shadow-sm flex flex-col justify-between">
              <h3 class="text-xl font-bold text-center">Membresías por vencer esta semana</h3>
              <div class="w-full h-full flex items-center justify-center">
                <p class="text-6xl istok-web-bold">-</p>
              </div>
              <!--<p class="text-sm text-white/80">Contenido</p>-->
            </article>

            <!-- Card 4 -->
            <article class="h-[180px] rounded-2xl bg-[var(--gris-bajito)] p-4 shadow-sm flex flex-col justify-between">
              <h3 class="text-xl font-bold text-center">Nuevos usuarios en el último mes</h3>
              <div class="w-full h-full flex items-center justify-center">
                <p class="text-6xl istok-web-bold">-</p>
              </div>
              <!--<p class="text-sm text-white/80">Contenido</p>-->
            </article>
          </div>

          <!--Tabla usuarios-->
          <div class="mt-6">
            <h1 class="text-3xl istok-web-bold mb-1">Nuevos usuarios en el último mes</h1>
            <div class="overflow-x-auto rounded-2xl bg-[var(--gris-bajito)] ring-1 ring-black/10">
                <table class="min-w-full">
                <thead class="bg-[var(--gris-bajito)] text-xl istok-web-bold">
                    <tr class="border-b border-[var(--gris-medio)]">
                    <th class="px-4 py-3 text-left">Nombre completo</th>
                    <th class="px-4 py-3 text-left ">Teléfono</th>
                    <th class="px-4 py-3 text-left ">Membresía</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--gris-medio)] istok-web-regular">
                    <!-- Fila 1 -->
                    <tr class="hover:bg-[#FAFAFA]">
                    <td class="px-4 py-3">
                        <p class="text-[#0460D9]">Juan Pérez Rodríguez</p>
                    </td>
                    <td class="px-4 py-3 text-gray-800">3141254879</td>
                    <td class="px-4 py-3"><span class="text-green-600">Vigente</span></td>
                    </tr>

                    <!-- Fila 2 -->
                    <tr class="hover:bg-[#FAFAFA]">
                    <td class="px-4 py-3"><p class="text-[#0460D9]">Juan Pérez Rodríguez</p></td>
                    <td class="px-4 py-3 text-gray-800">3141254879</td>
                    <td class="px-4 py-3"><span class="text-red-600">Vencida</span></td>
                    </tr>

                    <!-- Fila 3 -->
                    <tr class="hover:bg-[#FAFAFA]">
                    <td class="px-4 py-3"><p class="text-[#0460D9]">Juan Pérez Rodríguez</p></td>
                    <td class="px-4 py-3 text-gray-800">3141254879</td>
                    <td class="px-4 py-3"><span class="text-[var(--gris-medio)]">Congelada</span></td>
                    </tr>

                    <!-- Más filas dummy… copia/pega según necesites -->
                    <tr class="hover:bg-[#FAFAFA]">
                    <td class="px-4 py-3"><p class="text-[#0460D9]">Juan Pérez Rodríguez</p></td>
                    <td class="px-4 py-3 text-gray-800">3141254879</td>
                    <td class="px-4 py-3"><span class="text-green-600">Vigente</span></td>
                    </tr>
                </tbody>
                </table>
            </div>
          </div>
        </section>
      </main>

    </div>
  </div>
</body>
</html>
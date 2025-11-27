<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Usuarios</title>

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
<body class="antialiased istok-web-regular" 
      x-data="{ 
          modalDeleteOpen: false, 
          deleteAction: '', 
          userNameToDelete: '' 
      }">
  <div class="min-h-screen p-6">
    <!-- GRID PRINCIPAL: [Sidebar | Área principal] -->
    <div class="grid grid-cols-[84px_minmax(0,1fr)] gap-6 h-[calc(100vh-3rem)]">
      
      <!-- SIDEBAR -->
      <aside class="h-full flex flex-col items-center justify-center"> <!--bg-[#D9D9D9] rounded-2xlx-->
        <nav class="flex flex-col items-center gap-6" role="navigation" aria-label="Sidebar">
          
          <!-- Home (activo) -->
          <a href="{{ route('dashboard') }}" class="p-2 rounded-xl text-[var(--gris-medio)] hover:text-[var(--gris-oscuro)]" title="Inicio"> <!--ring-2 ring-[var(--azul)]-->
            <svg class="w-8 h-8" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor" d="M277.8 8.6c-12.3-11.4-31.3-11.4-43.5 0l-224 208c-9.6 9-12.8 22.9-8 35.1S18.8 272 32 272l16 0 0 176c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-176 16 0c13.2 0 25-8.1 29.8-20.3s1.6-26.2-8-35.1l-224-208zM240 320l32 0c26.5 0 48 21.5 48 48l0 96-128 0 0-96c0-26.5 21.5-48 48-48z"/>
            </svg>
            <span class="sr-only">Inicio</span>
          </a>

          <!-- Users -->
          <a href="{{ route('usuarios') }}" class="p-2 rounded-xl text-[var(--azul)] hover:opacity-85" aria-current="page" title="Usuarios"> <!--ring-2 ring-[var(--gris-medio)] hover:ring-[var(--gris-oscuro)]-->
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
        @if ($errors->has('general'))
          <div class="mb-3 p-3 rounded-md bg-red-100 text-red-800">
            {{ $errors->first('general') }}
          </div>
        @endif

        @if (session('success'))
          <div class="mb-3 p-3 rounded-md bg-green-100 text-green-800">
            {{ session('success') }}
          </div>
        @endif

        <!-- Cabecera -->
        <header class="h-16 flex items-center justify-between">
          <h1 class="text-3xl istok-web-bold">Usuarios</h1>

            @php
            // Usa tu zona horaria real:
            $hoy = now('America/Mexico_City');

            $dias  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
            $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

            $fechaCorta = $dias[$hoy->dayOfWeek] . ', ' . $hoy->format('j') . ' ' . $meses[$hoy->month - 1];
          @endphp

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
          <form method="GET" action="{{ route('usuarios') }}" class="rounded-2xl border border-[var(--gris-medio-bajito)] shadow-sm h-12 mb-4 flex items-center gap-3 px-4">
            <!-- Icono de Búsqueda -->
            <svg class="w-5 h-5 text-[var(--gris-medio)] transition-colors group-focus-within:text-[var(--azul)]" 
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
              <path d="M416 208c0 45.9-14.9 88.3-40 122.7L500 455.7c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 377c-34.4 25.2-76.8 40-122.7 40C93.1 417 0 323.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
            </svg>
            <!-- Input de búsqueda -->
            <input name="q" value="{{ $q ?? '' }}" 
              placeholder="Buscar por nombre o teléfono..."
              class="flex-1 bg-transparent h-full focus:outline-none text-gray-800 placeholder:text-[var(--gris-medio)] istok-web-regular" />
          </form>

          <div class="overflow-hidden rounded-2xl bg-white border border-[var(--gris-medio-bajito)] shadow-sm">
            <div class="overflow-x-auto">
              <table class="min-w-full">
                <thead class="bg-[#F8F8F8] border-b border-[var(--gris-medio-bajito)] istok-web-bold">
                  <tr class="">
                    <th class="px-4 py-3 text-left text-gray-600">Nombre completo</th>
                    <th class="px-4 py-3 text-left text-gray-600">Teléfono</th>
                    <th class="px-4 py-3 text-left text-gray-600">Membresía</th>
                    <th class="px-4 py-3 text-right text-gray-600 w-28">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[var(--gris-medio-bajito)] istok-web-regular">
                  @forelse($usuarios as $u)
                    @php
                      $estatus = $u->membresia_estatus ?? null;
                        
                      $badgeData = match ($estatus) {
                        'vigente'   => ['label' => 'Vigente',   'class' => 'bg-green-100 text-green-700'],
                        'vencida'   => ['label' => 'Vencida',   'class' => 'bg-red-100 text-red-700'],
                        'congelada' => ['label' => 'Congelada', 'class' => 'bg-blue-100 text-blue-700'],
                        default     => [
                          'label' => $estatus ? ucfirst($estatus) : '—', 
                          'class' => 'bg-gray-100 text-gray-500'
                        ],
                      };
                    @endphp

                    <tr class="hover:bg-[#FAFAFA] transition-colors group">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $u->nombre_comp }}</p>
                            <p class="text-sm text-gray-600">{{ $u->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $u->telefono ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeData['class'] }}">
                                {{ $badgeData['label'] }}
                            </span>
                        </td>
                        
                        <td class="px-2 py-2">
                          <div class="flex items-center justify-end gap-3 pr-2">
                            <a href="{{ route('usuarios.edit', $u->id) }}" class="p-1.5 rounded-lg text-[var(--gris-medio)] hover:text-[var(--azul)] hover:bg-blue-50 transition-colors" title="Editar" aria-label="Editar">
                              <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L368 46.1 465.9 144 490.3 119.6c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L432 177.9 334.1 80 172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z"/>
                              </svg>
                            </a>
                            <button 
                                type="button"
                                @click="modalDeleteOpen = true; deleteAction = '{{ route('usuarios.destroy', $u->id) }}'; userNameToDelete = '{{ $u->nombre_comp }}'"
                                class="p-1.5 rounded-lg hover:bg-red-50 text-[var(--gris-medio)] hover:text-red-600 transition-colors" 
                                title="Eliminar" 
                                aria-label="Eliminar"
                            >
                                <svg class="w-5 h-5" viewBox="0 0 448 512" fill="currentColor">
                                    <path d="M136.7 5.9L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-8.7-26.1C306.9-7.2 294.7-16 280.9-16L167.1-16c-13.8 0-26 8.8-30.4 21.9zM416 144L32 144 53.1 467.1C54.7 492.4 75.7 512 101 512L347 512c25.3 0 46.3-19.6 47.9-44.9L416 144z"/>
                                </svg>
                            </button>
                          </div>
                        </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="px-4 py-6 text-center text-gray-600">
                        @if (!empty($q))
                          No se encontraron resultados para: <span class="font-semibold text-gray-700">"{{ $q }}"</span>
                        @else
                          Aún no hay usuarios registrados.
                        @endif
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-4">
            {{ $usuarios->links() }} {{-- paginación Tailwind --}}
          </div>
        </section>

      </main>

    </div>
  </div>

<div 
    x-show="modalDeleteOpen" 
    style="display: none;"
    class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div 
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 mx-4 transform transition-all"
        @click.away="modalDeleteOpen = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
    >
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h3 class="text-xl font-bold text-gray-900 istok-web-bold mb-2">¿Eliminar usuario?</h3>
            
            <p class="text-gray-500 mb-6">
                Estás a punto de eliminar a <span x-text="userNameToDelete" class="font-bold text-gray-800"></span>. <br>
                Esta acción no se puede deshacer y se borrarán todos sus datos asociados.
            </p>

            <div class="flex gap-3 justify-center">
                <button 
                    @click="modalDeleteOpen = false" 
                    class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition"
                >
                    Cancelar
                </button>

                <form method="POST" :action="deleteAction">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 shadow-lg shadow-red-600/30 transition"
                    >
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
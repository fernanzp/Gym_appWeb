<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalles de Pago</title>
  <link rel="icon" href="{{ asset('images/logo_blue.png') }}" type="image/png">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  
  <style>
      :root { --azul: #0460D9; --azul-oscuro: #0248D2; --gris-oscuro: #727272; --gris-bajito: #F1F1F1; }
      .istok-web-regular { font-family: "Istok Web", sans-serif; font-weight: 400; }
      .istok-web-bold { font-family: "Istok Web", sans-serif; font-weight: 700; }
  </style>
</head>
<body class="flex flex-col items-center min-h-screen bg-white text-black relative">

    <div class="absolute top-[-15px] left-1">
        <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30">
    </div>

    <div class="w-full max-w-[50%] mt-[5vh]">
        
        <h1 class="text-4xl font-bold text-center mb-10 istok-web-bold">
            {{ isset($contexto) && $contexto == 'registro_nuevo' ? 'Primer Pago de Membresía' : 'Renovación de Membresía' }}
        </h1>

        <div class="istok-web-regular text-lg space-y-3">
            <p class="text-xl mb-4 font-bold">
                Usuario: <span class="text-[var(--azul)]">{{ $membresia->usuario->nombre_comp }}</span>
            </p>
            <p class="text-lg">Plan seleccionado: <strong>{{ $plan->nombre }}</strong></p>

            <div class="flex justify-between">
                <span>Fecha de inicio:</span>
                <span>{{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Fecha de vencimiento:</span>
                <span>{{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</span>
            </div>

            <div class="border-b border-gray-300 my-6 pt-2"></div>

            <div class="flex justify-between items-center">
                <span>Costo del plan:</span>
                <span>${{ number_format($costo_base, 2) }} MXN</span>
            </div>

            <div class="flex justify-between items-center mt-6 text-2xl istok-web-bold">
                <span>Total a Pagar:</span>
                <span>${{ number_format($total, 2) }} MXN</span>
            </div>
        </div>

        <p class="mt-8 text-[var(--gris-oscuro)] istok-web-regular text-sm">
            {{ isset($contexto) && $contexto == 'registro_nuevo' 
                ? 'Confirma el pago para activar completamente al usuario.' 
                : 'Al confirmar, la vigencia se actualizará automáticamente.' }}
        </p>

        <div class="flex gap-4 mt-4">
            
            @if(isset($contexto) && $contexto == 'registro_nuevo')
                <button type="button" onclick="abrirModalCancelar()"
                   class="w-full text-center border-2 border-red-500 text-red-500 istok-web-bold py-3 rounded-full hover:bg-red-50 transition-colors text-lg cursor-pointer">
                    Cancelar Registro
                </button>

                <a href="{{ route('clientes.finalizarRegistro', $membresia->usuario_id) }}"
                   class="w-full text-center bg-[var(--azul)] text-white istok-web-bold py-3 rounded-full hover:bg-[var(--azul-oscuro)] transition shadow-md text-lg flex items-center justify-center">
                    Pago en efectivo recibido
                </a>

            @else
                <a href="{{ route('membresias') }}" 
                   class="w-full text-center border-2 border-[var(--gris-oscuro)] text-[var(--gris-oscuro)] istok-web-bold py-3 rounded-full hover:bg-gray-100 transition-colors text-lg flex items-center justify-center">
                    Cancelar
                </a>

                <form action="{{ route('membresias.procesarRenovacion') }}" method="POST" class="w-full">
                    @csrf
                    <input type="hidden" name="membresia_id" value="{{ $membresia->id }}">
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <input type="hidden" name="fecha_ini" value="{{ $fecha_inicio }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fecha_fin }}">
                    <button type="submit" class="w-full bg-[var(--azul)] text-white istok-web-bold py-3 rounded-full hover:bg-[var(--azul-oscuro)] transition shadow-md text-lg">
                        Pago en efectivo recibido
                    </button>
                </form>
            @endif

        </div>
    </div>

    @if(isset($contexto) && $contexto == 'registro_nuevo')
    <div id="cancelModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="cerrarModalCancelar()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 istok-web-bold">¿Cancelar registro?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Si cancelas ahora, <strong>el usuario y su membresía serán eliminados</strong> de la base de datos. Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <form action="{{ route('clientes.cancelarRegistro', $membresia->usuario_id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto">
                                Sí, Eliminar Registro
                            </button>
                        </form>
                        <button type="button" onclick="cerrarModalCancelar()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            No, regresar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirModalCancelar() {
            document.getElementById('cancelModal').classList.remove('hidden');
        }
        function cerrarModalCancelar() {
            document.getElementById('cancelModal').classList.add('hidden');
        }
    </script>
    @endif

</body>
</html>
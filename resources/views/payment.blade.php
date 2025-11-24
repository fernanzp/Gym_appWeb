<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pago de Membresía</title>

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

    <!-- Logo (Posicionamiento absoluto igual que en registro) -->
    <div class="absolute top-[-15px] left-1">
        <img src="{{ asset('images/logo_blue.png') }}" alt="Logo" class="w-30 h-30">
    </div>

    <!-- Contenedor principal -->
    <!-- Usamos max-w-2xl para darle el ancho adecuado similar al mockup -->
    <div class="w-full max-w-[50%] mt-[5vh]">
        
        <!-- Título -->
        <h1 class="text-4xl font-bold text-center mb-10 istok-web-bold">Pago de la membresía</h1>

        <!-- Sección de Información -->
        <div class="istok-web-regular text-lg space-y-3">
            
            <p class="text-xl mb-4">Detalles de la membresía:</p>

            <!-- Fechas -->
            <div class="flex justify-between">
                <span>Fecha de inicio:</span>
                <span>{{ $fecha_inicio ?? '30/08/2025' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Fecha de vencimiento:</span>
                <span>{{ $fecha_fin ?? '30/09/2025' }}</span>
            </div>

            <!-- Divisor -->
            <div class="border-b border-gray-300 my-6 pt-2"></div>

            <!-- Costos -->
            <div class="flex justify-between items-center">
                <span>Costo base:</span>
                <span>${{ number_format($costo_base ?? 700, 2) }} MXN</span>
            </div>
            
            <!-- Promoción (Mostrada solo si existe) -->
            <div class="flex justify-between items-center">
                <span>Promoción aplicada:</span>
                {{-- Asumimos un valor negativo visualmente --}}
                <span>-${{ number_format($descuento ?? 100, 2) }} MXN</span>
            </div>

            <!-- Total -->
            <div class="flex justify-between items-center mt-6 text-2xl istok-web-bold">
                <span>Total:</span>
                <span>${{ number_format($total ?? 600, 2) }} MXN</span>
            </div>

        </div>

        <!-- Nota al pie -->
        <p class="mt-8 text-[var(--gris-oscuro)] istok-web-regular text-sm">
            Revisa los detalles de la membresía antes de registrar el pago.
        </p>

        <!-- Botones de Acción -->
        <div class="flex gap-4 mt-4">
            
            <!-- Opción 1: Pagar después (Redirección o Submit diferente) -->
            <!-- Usamos <a> si solo redirige, o <button> si envía form -->
            <a href="{{ route('dashboard') }}" 
               class="w-full text-center border-2 border-[var(--gris-oscuro)] text-[var(--gris-oscuro)] istok-web-bold py-3 rounded-full hover:bg-gray-100 transition-colors text-lg cursor-pointer flex items-center justify-center">
                Cancelar 
            </a>

            <!-- Opción 2: Confirmar Pago -->
            <form action="{{-- route('pagos.store') --}}" method="POST" class="w-full">
                @csrf
                <!-- Aquí irían inputs hidden con los datos de la transacción si es necesario -->
                <button type="submit"
                        class="w-full bg-[var(--azul)] text-white istok-web-bold py-3 rounded-full hover:bg-[var(--azul-oscuro)] transition shadow-md hover:shadow-lg text-lg">
                    Pago en efectivo recibido
                </button>
            </form>

        </div>

    </div>

</body>
</html>
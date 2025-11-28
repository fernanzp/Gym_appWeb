<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero Gym Flow</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0460D9;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #0460D9;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #777;
        }
        .kpi-container {
            width: 100%;
            margin-bottom: 30px;
        }
        .kpi-box {
            float: left;
            width: 30%;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            margin-right: 3%;
        }
        .kpi-box:last-child {
            margin-right: 0;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #0460D9;
            display: block;
            margin-bottom: 5px;
        }
        .kpi-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #555;
        }
        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #0460D9;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        td {
            border-bottom: 1px solid #eee;
            padding: 8px;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .clear { clear: both; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Gym Flow</h1>
        <p>Reporte Mensual de Operaciones</p>
        <p>Generado el: {{ $now->format('d/m/Y H:i A') }}</p>
    </div>

    <!-- KPIs Rápidos -->
    <div class="kpi-container">
        <div class="kpi-box">
            <span class="kpi-value">${{ number_format($ingresosMes, 2) }}</span>
            <span class="kpi-label">Ingresos del Mes</span>
        </div>
        <div class="kpi-box">
            <span class="kpi-value">{{ $miembrosActivos }}</span>
            <span class="kpi-label">Miembros Activos</span>
        </div>
        <div class="kpi-box">
            <span class="kpi-value">{{ number_format($tasaRetencion, 1) }}%</span>
            <span class="kpi-label">Tasa Retención</span>
        </div>
        <div class="clear"></div>
    </div>

    <h3>Bitácora de Últimos Pagos</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Socio</th>
                <th>Plan Adquirido</th>
                <th>Monto</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ultimosPagos as $pago)
            <tr>
                <td>{{ $pago->created_at->format('d/m/Y') }}</td>
                <td>{{ $pago->usuario->nombre_comp ?? 'Usuario Eliminado' }}</td>
                <td>{{ $pago->plan->nombre ?? 'N/A' }}</td>
                <td>${{ number_format($pago->precio_pagado, 2) }}</td>
                <td>{{ ucfirst($pago->estatus) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Gym Flow Systems - Documento generado automáticamente para uso administrativo.</p>
    </div>

</body>
</html>
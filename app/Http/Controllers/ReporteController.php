<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Membresia;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function descargarAnalytics()
    {
        // Aseguramos la misma zona horaria que en el Dashboard
        $now = Carbon::now('America/Mexico_City');
        
        $inicioMes = $now->copy()->startOfMonth();
        $finMes = $now->copy()->endOfMonth();

        // --- 1. INGRESOS DEL MES (Lógica idéntica a AnalyticsController) ---
        // Usamos whereBetween con fechas exactas para mayor precisión
        $ingresosMes = Membresia::whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('precio_pagado');

        // --- 2. MIEMBROS ACTIVOS (Lógica idéntica a AnalyticsController) ---
        $miembrosActivos = Membresia::where('estatus', 'vigente')->count();

        // --- 3. TASA DE RETENCIÓN (Lógica AVANZADA idéntica a AnalyticsController) ---
        // Fórmula: (Usuarios que han comprado > 1 vez) / (Total de usuarios que han comprado al menos 1 vez)
        
        // A. Total de clientes que han comprado alguna vez
        $totalClientesUnicos = Membresia::distinct('usuario_id')->count('usuario_id');
        
        // B. Clientes que han vuelto a comprar (tienen más de 1 registro en la tabla membresías)
        $clientesRecurrentes = Membresia::select('usuario_id')
            ->groupBy('usuario_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        // C. Cálculo del porcentaje
        $tasaRetencion = ($totalClientesUnicos > 0) 
            ? ($clientesRecurrentes / $totalClientesUnicos) * 100 
            : 0;

        // --- 4. DATOS EXTRA PARA LA TABLA DEL PDF ---
        // Listado de los últimos 20 pagos/movimientos para llenar la hoja
        $ultimosPagos = Membresia::with(['usuario', 'plan'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // Generar el PDF
        // 'pdf.analytics' es la vista que ya creamos
        $pdf = Pdf::loadView('pdf.analytics', compact(
            'ingresosMes', 
            'miembrosActivos', 
            'tasaRetencion', 
            'ultimosPagos',
            'now'
        ));

        // Descargar (stream para ver en navegador)
        return $pdf->stream('Reporte_GymFlow_' . $now->format('Y-m-d') . '.pdf');
    }
}

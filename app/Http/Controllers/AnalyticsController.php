<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membresia;
use App\Models\RegistroAcceso; // Asegúrate de tener este modelo creado
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Configuracion;

class AnalyticsController extends Controller
{
    public function index()
    {
        $hoy = Carbon::now();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();
        
        // --- 1. INGRESOS MENSUALES ---
        // Sumamos 'precio_pagado' de las membresías creadas este mes
        $ingresosMes = Membresia::whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('precio_pagado');

        // Cálculo para el porcentaje (Comparado con el mes anterior)
        $inicioMesPasado = $hoy->copy()->subMonth()->startOfMonth();
        $finMesPasado = $hoy->copy()->subMonth()->endOfMonth();
        $ingresosMesPasado = Membresia::whereBetween('created_at', [$inicioMesPasado, $finMesPasado])
            ->sum('precio_pagado');
            
        // Evitamos división por cero
        if ($ingresosMesPasado > 0) {
            $porcentajeIngresos = (($ingresosMes - $ingresosMesPasado) / $ingresosMesPasado) * 100;
        } else {
            $porcentajeIngresos = 100; // Si antes era 0, creció 100% (o infinito técnicamente)
        }


        // --- 2. MIEMBROS ACTIVOS ---
        // Simplemente contamos las vigentes
        $miembrosActivos = Membresia::where('estatus', 'vigente')->count();

        // Para el porcentaje (Comparación simple con mes pasado aproximada)
        // Nota: Esto es un estimado, ya que no tenemos tabla histórica de estados.
        // Asumiremos un crecimiento base comparando membresías creadas vs vencidas este mes.
        $nuevosEsteMes = Membresia::whereBetween('fecha_ini', [$inicioMes, $finMes])->count();
        $vencidosEsteMes = Membresia::whereBetween('fecha_fin', [$inicioMes, $finMes])->count();
        $crecimientoNeto = $nuevosEsteMes - $vencidosEsteMes;
        
        // Calcular porcentaje de crecimiento respecto al total
        $totalAnterior = $miembrosActivos - $crecimientoNeto;
        $porcentajeMiembros = ($totalAnterior > 0) ? ($crecimientoNeto / $totalAnterior) * 100 : 0;


        // --- 3. ASISTENCIA PROMEDIO DIARIA (Últimos 30 días) ---
        // Filtramos: Dirección 1 (Entrada), últimos 30 días
        $asistencias = RegistroAcceso::where('direccion', 1)
            ->where('fecha', '>=', $hoy->copy()->subDays(30))
            ->select(DB::raw('DATE(fecha) as dia'), DB::raw('count(*) as total'))
            ->groupBy('dia')
            ->get();

        // Sacamos el promedio de la colección
        $promedioAsistencia = $asistencias->avg('total') ?? 0;
        
        // Comparación con el periodo anterior (30 días previos a los últimos 30)
        $asistenciasAnteriores = RegistroAcceso::where('direccion', 1)
            ->whereBetween('fecha', [$hoy->copy()->subDays(60), $hoy->copy()->subDays(30)])
            ->count();
        $promedioAnterior = $asistenciasAnteriores / 30; // Promedio simple
        
        $porcentajeAsistencia = ($promedioAnterior > 0) 
            ? (($promedioAsistencia - $promedioAnterior) / $promedioAnterior) * 100 
            : 0;


        // --- 4. TASA DE RETENCIÓN ---
        // Lógica: (Usuarios que han comprado > 1 vez) / (Total de usuarios que han comprado al menos 1 vez)
        // Esto nos dice qué porcentaje de clientes "vuelven".
        
        $totalClientesUnicos = Membresia::distinct('usuario_id')->count('usuario_id');
        
        $clientesRecurrentes = Membresia::select('usuario_id')
            ->groupBy('usuario_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $tasaRetencion = ($totalClientesUnicos > 0) 
            ? ($clientesRecurrentes / $totalClientesUnicos) * 100 
            : 0;

        // Retornamos a la vista con todas las variables
        return view('analytics', compact(
            'ingresosMes', 'porcentajeIngresos',
            'miembrosActivos', 'porcentajeMiembros',
            'promedioAsistencia', 'porcentajeAsistencia',
            'tasaRetencion'
        ));
    }

    public function revenueData(Request $request)
    {
        $filter = $request->input('filter', 'month'); // Default: mes
        $hoy = Carbon::now();
        
        $labels = [];
        $data = [];
        
        // Configuración según el filtro
        if ($filter === 'week') {
                // Mapa de traducción manual para asegurar acentos y mayúsculas
                $diasTraduccion = [
                    'Mon' => 'Lun',
                    'Tue' => 'Mar',
                    'Wed' => 'Mié',
                    'Thu' => 'Jue',
                    'Fri' => 'Vie',
                    'Sat' => 'Sáb',
                    'Sun' => 'Dom'
                ];

                // Últimos 7 días
                for ($i = 6; $i >= 0; $i--) {
                    $date = $hoy->copy()->subDays($i);
                    
                    // 1. Obtenemos el día en inglés (ej: Mon)
                    $diaIngles = $date->format('D'); 
                    
                    // 2. Lo traducimos usando nuestro array
                    $labels[] = $diasTraduccion[$diaIngles]; 
                    
                    $fechaBusqueda = $date->format('Y-m-d');
                    
                    // Consulta: Sumar ventas de ese día específico
                    $total = Membresia::whereDate('created_at', $fechaBusqueda)->sum('precio_pagado');
                    $data[] = $total;
                }
            }
        elseif ($filter === 'month') {
            // Días del mes actual
            $daysInMonth = $hoy->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = $i; // Día 1, 2, 3...
                
                // Consulta: Sumar ventas del día X del mes actual
                $total = Membresia::whereYear('created_at', $hoy->year)
                            ->whereMonth('created_at', $hoy->month)
                            ->whereDay('created_at', $i)
                            ->sum('precio_pagado');
                $data[] = $total;
            }
        } 
        elseif ($filter === 'year') {
            // 12 Meses
            $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            
            foreach ($meses as $index => $nombreMes) {
                $mesNumero = $index + 1;
                $labels[] = $nombreMes;
                
                // Consulta: Sumar ventas del mes X
                $total = Membresia::whereYear('created_at', $hoy->year)
                            ->whereMonth('created_at', $mesNumero)
                            ->sum('precio_pagado');
                $data[] = $total;
            }
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    public function liveActivityData()
    {
        $capacidadMaxima = Configuracion::where('clave', 'aforo_maximo')->value('valor');
        if (!$capacidadMaxima) {
            $capacidadMaxima = 100; // Valor default
        }
        $hoy = Carbon::now();
        $ayer = Carbon::yesterday();

        // 1. Obtener registros de HOY ordenados cronológicamente
        $registrosHoy = RegistroAcceso::whereDate('fecha', $hoy->toDateString())
            ->orderBy('fecha', 'asc')
            ->get();

        // 2. Calcular gente actual y el pico del día
        $personasDentro = 0;
        $picoHoy = 0;

        foreach ($registrosHoy as $registro) {
            if ($registro->direccion == 1) {
                $personasDentro++;
            } elseif ($registro->direccion == 0) {
                // Evitamos negativos por si hubo errores en registros manuales
                $personasDentro = max(0, $personasDentro - 1); 
            }
            
            // Actualizamos el pico si el número actual supera al máximo registrado
            if ($personasDentro > $picoHoy) {
                $picoHoy = $personasDentro;
            }
        }

        // 3. Comparativa de Afluencia vs Ayer (Total de entradas hasta la misma hora)
        // Contamos solo las ENTRADAS (direccion = 1)
        $entradasHoy = $registrosHoy->where('direccion', 1)->count();
        
        $entradasAyer = RegistroAcceso::whereDate('fecha', $ayer->toDateString())
            ->whereTime('fecha', '<=', $hoy->toTimeString()) // Hasta la hora actual
            ->where('direccion', 1)
            ->count();

        $porcentajeCambio = 0;
        if ($entradasAyer > 0) {
            $porcentajeCambio = (($entradasHoy - $entradasAyer) / $entradasAyer) * 100;
        } elseif ($entradasHoy > 0) {
            $porcentajeCambio = 100; // Si ayer fue 0 y hoy hay gente
        }

        return response()->json([
            'active' => $personasDentro,
            'remaining' => max(0, $capacidadMaxima - $personasDentro),
            'capacity' => $capacidadMaxima,
            'peak_today' => $picoHoy,
            'percentage_change' => round($porcentajeCambio, 1)
        ]);
    }
}
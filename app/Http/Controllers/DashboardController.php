<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Usuario; 
use App\Models\RegistroAcceso; // 🔥 Importante para el historial de visitas
use App\Models\Plan;
use App\Models\Configuracion;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tz  = 'America/Mexico_City';
        $now = Carbon::now($tz);

        // ---- Nuevos usuarios en el último mes (conteo)
        $haceUnMes = (clone $now)->subMonth();
        $nuevosUsuariosCount = DB::table('usuarios')
            ->where('created_at', '>=', $haceUnMes->startOfDay()->toDateTimeString())
            ->count();

        // ---- Tabla: últimos usuarios creados en el último mes
        $latestMembership = DB::table('membresias as m1')
            ->select('m1.usuario_id', 'm1.estatus', 'm1.fecha_ini')
            ->join(DB::raw('(SELECT usuario_id, MAX(fecha_ini) AS max_ini FROM membresias GROUP BY usuario_id) as mm'),
                function ($join) {
                    $join->on('m1.usuario_id', '=', 'mm.usuario_id')
                          ->on('m1.fecha_ini', '=', 'mm.max_ini');
                });

        $nuevosUsuarios = DB::table('usuarios as u')
            ->leftJoinSub($latestMembership, 'lm', function ($join) {
                $join->on('u.id', '=', 'lm.usuario_id');
            })
            ->where('u.created_at', '>=', $haceUnMes->startOfDay()->toDateTimeString())
            ->orderByDesc('u.created_at')
            ->limit(10)
            ->get([
                'u.id', 'u.nombre_comp', 'u.telefono',
                DB::raw('COALESCE(lm.estatus, NULL) as membresia_estatus')
            ]);

        // ---- Membresías por vencer esta semana
        $finDeSemana = (clone $now)->endOfWeek(); 
        $porVencerCount = DB::table('membresias')
            ->where('estatus', 'vigente')
            ->whereDate('fecha_fin', '>=', $now->toDateString())
            ->whereDate('fecha_fin', '<=', $finDeSemana->toDateString())
            ->count();

        // ⭐️ AÑADIR CONSULTA PARA UX Y REINTENTO ⭐️
        $usuariosPendientes = Usuario::whereIn('estatus', [8, 9])
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        $planes = Plan::withCount(['membresias as usuarios_activos' => function ($query) {
                $query->where('estatus', 'vigente');
            }])
            // AGREGAMOS ESTE FILTRO:
            ->where(function($query) {
                // Traer planes que NO sean 'desactivado' O que sean NULL (por si acaso)
                $query->where('descripcion', '!=', 'desactivado')
                    ->orWhereNull('descripcion');
            })
            ->get();

        //OBTENER AFORO MÁXIMO (Si no existe, usamos 100 por defecto)
        $aforoMaximo = Configuracion::where('clave', 'aforo_maximo')->value('valor');
        if (!$aforoMaximo) {
            $aforoMaximo = 100; // Valor default
        }

        return view('dashboard', [
            'nuevosUsuariosCount' => $nuevosUsuariosCount,
            'porVencerCount'      => $porVencerCount,
            'nuevosUsuarios'      => $nuevosUsuarios,
            'todayHuman'          => $now->isoFormat('ddd, D MMM'),
            'usuariosPendientes'  => $usuariosPendientes,
            'aforoMaximo'         => $aforoMaximo,
            'planes'              => $planes
        ]);
    }

    public function updateAforo(Request $request)
    {
        $request->validate([
            'aforo_maximo' => 'required|integer|min:2|max:120'
        ]);

        // Usamos updateOrCreate: si existe la clave la actualiza, si no, la crea
        Configuracion::updateOrCreate(
            ['clave' => 'aforo_maximo'],
            ['valor' => $request->aforo_maximo]
        );

        return redirect()->back()->with('success', 'Capacidad máxima actualizada correctamente.');
    }

    // 🔥 NUEVA API PARA AFORO EN VIVO (Socios + Visitas) 🔥
    // Esta función calcula el aforo recorriendo cronológicamente las entradas y salidas
    // para evitar números negativos si se marca salida errónea.
    public function getAforoEnVivo()
    {
        // A. Contar SOCIOS reales adentro (los que tienen is_inside = 1)
        // Excluimos al usuario visita (99999) por seguridad
        $sociosAdentro = Usuario::where('is_inside', 1)
                                ->where('id', '!=', 99999) 
                                ->count();

        // B. Calcular VISITAS adentro (Recorrido Cronológico)
        $visitaId = 99999;
        
        // 1. Obtenemos TODOS los movimientos de visita de HOY en orden
        $movimientos = RegistroAcceso::where('usuario_id', $visitaId)
            ->where('acceso', 1)
            ->whereDate('fecha', Carbon::today())
            ->orderBy('fecha', 'asc') // Importante: Orden cronológico
            ->get(['direccion']); // Solo necesitamos saber si entró o salió

        // 2. Simulamos el día paso a paso
        $visitasAdentro = 0;

        foreach ($movimientos as $mov) {
            if ($mov->direccion == 1) {
                // Entrada: Sumamos 1
                $visitasAdentro++;
            } else {
                // Salida: Restamos 1, pero NUNCA bajamos de 0
                // Esto ignora las salidas erróneas si el local ya estaba "vacío"
                if ($visitasAdentro > 0) {
                    $visitasAdentro--;
                }
            }
        }

        // C. Total Final
        $totalGente = $sociosAdentro + $visitasAdentro;

        return response()->json([
            'total' => $totalGente,
            'desglose' => [
                'socios' => $sociosAdentro,
                'visitas' => $visitasAdentro
            ]
        ]);
    }
}
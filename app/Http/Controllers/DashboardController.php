<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Usuario; 
use App\Models\RegistroAcceso; // 🔥 Iñimportante ko'ápe

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

        return view('dashboard', [
            'nuevosUsuariosCount' => $nuevosUsuariosCount,
            'porVencerCount'      => $porVencerCount,
            'nuevosUsuarios'      => $nuevosUsuarios,
            'todayHuman'          => $now->isoFormat('ddd, D MMM'),
            'usuariosPendientes'  => $usuariosPendientes, 
        ]);
    }

    // 🔥 NUEVA API PARA AFORO EN VIVO (Socios + Visitas) 🔥
    public function getAforoEnVivo()
    {
        // A. Contar SOCIOS reales adentro (los que tienen is_inside = 1)
        // Excluimos al usuario visita (99999) por seguridad
        $sociosAdentro = Usuario::where('is_inside', 1)
                                ->where('id', '!=', 99999) 
                                ->count();

        // B. Calcular VISITAS adentro (Basado en logs de HOY)
        $visitaId = 99999;
        
        // Contamos cuántas veces entró el usuario 99999 hoy
        $entradasVisita = RegistroAcceso::where('usuario_id', $visitaId)
            ->where('acceso', 1) // Solo accesos exitosos
            ->where('direccion', 1) // Entradas
            ->whereDate('fecha', Carbon::today())
            ->count();

        // Contamos cuántas veces salió el usuario 99999 hoy
        $salidasVisita = RegistroAcceso::where('usuario_id', $visitaId)
            ->where('acceso', 1)
            ->where('direccion', 0) // Salidas
            ->whereDate('fecha', Carbon::today())
            ->count();

        // El aforo de visitas es la resta. Usamos max(0) para no tener negativos.
        $visitasAdentro = max(0, $entradasVisita - $salidasVisita);

        // C. Total
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
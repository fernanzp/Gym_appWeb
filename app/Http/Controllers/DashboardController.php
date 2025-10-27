<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        // Subconsulta: membresía más reciente por usuario (max fecha_ini)
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

        // ---- Membresías por vencer esta semana (vigentes cuyo fin cae dentro de esta semana)
        // Consideramos "esta semana" como hoy .. fin de semana local
        $finDeSemana = (clone $now)->endOfWeek(); // domingo
        $porVencerCount = DB::table('membresias')
            ->where('estatus', 'vigente')
            ->whereDate('fecha_fin', '>=', $now->toDateString())
            ->whereDate('fecha_fin', '<=', $finDeSemana->toDateString())
            ->count();

        return view('dashboard', [
            'nuevosUsuariosCount' => $nuevosUsuariosCount,
            'porVencerCount'      => $porVencerCount,
            'nuevosUsuarios'      => $nuevosUsuarios,
            'todayHuman'          => $now->isoFormat('ddd, D MMM'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MembresiaController extends Controller
{
    public function index(Request $request)
    {
        // Ejecutamos una actualización rápida antes de mostrar (truco si no tienes cron jobs)
        Membresia::where('estatus', 'vigente')
            ->where('fecha_fin', '<', now()->format('Y-m-d'))
            ->update(['estatus' => 'vencida']);

        // 1. STATS (Usando strings del ENUM)
        $totalActivas = Membresia::where('estatus', 'vigente')->count();
        $totalVencidas = Membresia::where('estatus', 'vencida')->count();
        $totalCongeladas = Membresia::where('estatus', 'congelada')->count();

        $fechaLimite = Carbon::now()->addDays(15);
        $totalPorVencer = Membresia::where('estatus', 'vigente')
            ->whereBetween('fecha_fin', [Carbon::now(), $fechaLimite])
            ->count();

        // 2. FILTRO
        $query = Membresia::with(['usuario', 'plan']);
        $filtro = $request->get('filter', 'todas');

        switch ($filtro) {
            case 'vigentes':
                $query->where('estatus', 'vigente');
                break;
            case 'vencidas':
                $query->where('estatus', 'vencida');
                break;
            case 'congeladas':
                $query->where('estatus', 'congelada');
                break;
            case 'por_vencer':
                $query->where('estatus', 'vigente')
                    ->whereBetween('fecha_fin', [Carbon::now(), $fechaLimite]);
                break;
        }

        if ($search = $request->get('search')) {
            $query->whereHas('usuario', function($q) use ($search) {
                $q->where('nombre_comp', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $membresias = $query->orderBy('fecha_fin', 'asc')->paginate(10);

        return view('membresias', compact('membresias', 'totalActivas', 'totalVencidas', 'totalCongeladas', 'totalPorVencer', 'filtro'));
    }

    public function toggleStatus($id)
    {
        $membresia = Membresia::findOrFail($id);

        if ($membresia->estatus === 'vigente') {
            $membresia->estatus = 'congelada';
            $mensaje = 'Membresía congelada correctamente.';
        } elseif ($membresia->estatus === 'congelada') {
            $membresia->estatus = 'vigente';
            $mensaje = 'Membresía reactivada correctamente.';
        } else {
            return back()->with('error', 'No se puede cambiar el estatus de una membresía vencida.');
        }

        $membresia->save();

        return back()->with('success', $mensaje);
    }
}
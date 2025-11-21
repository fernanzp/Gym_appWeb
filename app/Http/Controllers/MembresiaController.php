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
        $hoy = Carbon::now(); // Fecha actual

        // LOGICA PARA CONGELAR
        if ($membresia->estatus === 'vigente') {
            
            // 1. Calcular días restantes (Fecha Fin - Hoy)
            // Usamos diffInDays(..., false) para obtener negativos si ya pasó, 
            // pero validaremos que sea positivo.
            $fechaFin = Carbon::parse($membresia->fecha_fin);
            
            // Si la fecha fin es hoy o futura, calculamos la diferencia
            if ($fechaFin->greaterThanOrEqualTo($hoy)) {
                $diasRestantes = $hoy->diffInDays($fechaFin);
                
                // Guardamos esos días en la columna temporal
                $membresia->dias_congelados = $diasRestantes;
                $membresia->estatus = 'congelada';
                
                $mensaje = "Membresía congelada. Se guardaron $diasRestantes días restantes.";
            } else {
                return back()->with('error', 'No se puede congelar una membresía que ya venció.');
            }

        // LOGICA PARA REACTIVAR
        } elseif ($membresia->estatus === 'congelada') {
            
            // Verificamos que tenga días guardados
            if ($membresia->dias_congelados !== null) {
                
                // 1. Nueva Fecha Fin = Hoy + Días Guardados
                $nuevaFechaFin = $hoy->copy()->addDays($membresia->dias_congelados);
                
                // 2. Actualizamos la fecha fin y limpiamos los días guardados
                $membresia->fecha_fin = $nuevaFechaFin;
                $membresia->dias_congelados = null;
                $membresia->estatus = 'vigente';

                $mensaje = "Membresía reactivada. Nueva fecha de vencimiento: " . $nuevaFechaFin->format('d/m/Y');
            } else {
                // Caso de error (datos corruptos o manuales)
                $membresia->estatus = 'vigente';
                $mensaje = 'Membresía reactivada (No había días guardados registrados).';
            }

        } else {
            return back()->with('error', 'No se puede modificar una membresía vencida.');
        }

        $membresia->save();

        return back()->with('success', $mensaje);
    }
}
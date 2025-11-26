<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Membresia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MembresiaController extends Controller
{
    public function index(Request $request)
    {
        // 0. ACTUALIZACIÓN AUTOMÁTICA DE VENCIMIENTOS
        Membresia::where('estatus', 'vigente')
            ->where('fecha_fin', '<', now()->format('Y-m-d'))
            ->update(['estatus' => 'vencida']);

        // --- CORE: SUBCONSULTA PARA OBTENER SOLO LA ÚLTIMA MEMBRESÍA DE CADA USUARIO ---
        // Esto es lo que "limpia" la basura histórica.
        // Seleccionamos el ID más alto agrupado por usuario.
        $subqueryUltimas = Membresia::selectRaw('MAX(id)')->groupBy('usuario_id');


        // 1. STATS (Ahora aplicando el filtro de últimas membresías)
        // Usamos whereIn('id', $subqueryUltimas) en cada contador para asegurar precisión real.

        $totalActivas = Membresia::whereIn('id', $subqueryUltimas)
                            ->where('estatus', 'vigente')
                            ->count();

        $totalVencidas = Membresia::whereIn('id', $subqueryUltimas)
                            ->where('estatus', 'vencida')
                            ->count();

        $totalCongeladas = Membresia::whereIn('id', $subqueryUltimas)
                            ->where('estatus', 'congelada')
                            ->count();

        $fechaLimite = Carbon::now()->addDays(15);
        $totalPorVencer = Membresia::whereIn('id', $subqueryUltimas)
                            ->where('estatus', 'vigente')
                            ->whereBetween('fecha_fin', [Carbon::now(), $fechaLimite])
                            ->count();


        // 2. TABLA PRINCIPAL (También filtrada)
        $query = Membresia::with(['usuario', 'plan'])
                    ->whereIn('id', $subqueryUltimas); // <--- Aplicamos el filtro base aquí

        // Filtros de la interfaz
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

        // Buscador
        if ($search = $request->get('search')) {
            $query->whereHas('usuario', function($q) use ($search) {
                $q->where('nombre_comp', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $membresias = $query->orderBy('fecha_fin', 'asc')->paginate(10);
        $planes = Plan::all();

        return view('membresias', compact('membresias', 'totalActivas', 'totalVencidas', 'totalCongeladas', 'totalPorVencer', 'filtro', 'planes'));
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

    public function prepararRenovacion(Request $request)
    {
        $request->validate([
            'membresia_id' => 'required|exists:membresias,id',
            'plan_id' => 'required|exists:planes,id',
        ]);

        $membresia = Membresia::findOrFail($request->membresia_id);
        $planNuevo = Plan::findOrFail($request->plan_id);
        $hoy = Carbon::now();

        // Lógica de fechas:
        // Si está vigente, la renovación empieza el día después de que termine la actual.
        // Si está vencida o congelada, empieza hoy.
        if ($membresia->estatus === 'vigente' && $membresia->fecha_fin > $hoy) {
            $fechaInicio = Carbon::parse($membresia->fecha_fin)->addDay();
        } else {
            $fechaInicio = $hoy;
        }

        // Calculamos nueva fecha fin basada en la duración del plan seleccionado
        $fechaFin = $fechaInicio->copy()->addDays($planNuevo->duracion_dias);

        // Datos para la vista de pago
        return view('payment', [
            'membresia' => $membresia,
            'plan' => $planNuevo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'costo_base' => $planNuevo->precio,
            'total' => $planNuevo->precio, // Aquí podrías sumar impuestos si quisieras
        ]);
    }

    public function procesarRenovacion(Request $request)
    {
        // 1. Validamos los datos entrantes
        $request->validate([
            'membresia_id' => 'required|exists:membresias,id', // ID de la membresía ANTERIOR
            'plan_id' => 'required|exists:planes,id',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        // 2. Recuperamos la información necesaria
        $membresiaAnterior = Membresia::findOrFail($request->membresia_id);
        $planNuevo = Plan::findOrFail($request->plan_id); // Buscamos el plan para obtener el precio actual
        
        // 3. CREAMOS el nuevo registro (Aquí ocurre la magia de la retención)
        Membresia::create([
            'usuario_id'    => $membresiaAnterior->usuario_id, // Usamos el mismo usuario
            'plan_id'       => $planNuevo->id,
            'precio_pagado' => $planNuevo->precio, // <--- IMPORTANTE: Guardamos el precio histórico
            'fecha_ini'     => $request->fecha_ini,
            'fecha_fin'     => $request->fecha_fin,
            'estatus'       => 'vigente',
            'dias_congelados' => null,
            'created_at'    => now(), // Aseguramos la fecha de creación para reportes
            'updated_at'    => now(),
        ]);

        // NOTA SOBRE LA MEMBRESÍA ANTERIOR:
        // No necesitamos cambiarle el estatus a la anterior manualmente.
        // Tu función index() ya se encarga de poner en 'vencida' 
        // cualquier membresía cuya fecha_fin haya pasado. 
        // Así permites que termine sus días restantes si renovó por adelantado.

        return redirect()->route('membresias')
            ->with('success', 'Membresía renovada exitosamente. Se ha generado un nuevo historial.');
    }
}
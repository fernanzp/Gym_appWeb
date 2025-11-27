<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;

class PlanController extends Controller
{
    // Método para guardar un nuevo plan
    public function store(Request $request)
    {
        // 1. Validar datos
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'precio'   => 'required|numeric|min:20',
            'duracion' => 'required|integer|min:1', // En la vista se llama 'duracion'
        ], [
            'nombre.required'   => 'Ingresa el nombre del plan para continuar.',
            'precio.required'   => 'Ingresa el precio del plan para continuar.',
            'precio.min'   => 'El plan debe de tener un precio mínimo de $20 MXN.',
            'duracion.required'   => 'Ingresa la duración del plan para continuar.',
            'duracion.min'   => 'El plan debe durar al menos 1 día.'
        ]);

        // 2. Crear el plan
        Plan::create([
            'nombre'        => $request->nombre,
            'precio'        => $request->precio,
            'duracion_dias' => $request->duracion, // Mapeamos 'duracion' a 'duracion_dias'
            'descripcion'   => null, // Como pediste, enviamos null
        ]);

        // 3. Redireccionar con éxito
        return redirect()->back()->with('success', 'Plan creado correctamente.');
    }

    // Método para actualizar un plan existente
    public function update(Request $request, $id)
    {
        // 1. Buscar el plan (o fallar si no existe)
        $plan = Plan::findOrFail($id);

        // 2. Validar
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'precio'   => 'required|numeric|min:20',
            'duracion' => 'required|integer|min:1',
        ], [
            'nombre.required'   => 'Ingresa el nombre del plan para continuar.',
            'precio.required'   => 'Ingresa el precio del plan para continuar.',
            'precio.min'   => 'El plan debe de tener un precio mínimo de $20 MXN.',
            'duracion.required'   => 'Ingresa la duración del plan para continuar.',
            'duracion.min'   => 'El plan debe durar al menos 1 día.'
        ]);

        // 3. Actualizar
        $plan->update([
            'nombre'        => $request->nombre,
            'precio'        => $request->precio,
            'duracion_dias' => $request->duracion, // Mapeo del input 'duracion' a la columna 'duracion_dias'
        ]);

        // 4. Retornar
        return redirect()->back()->with('success', 'Plan actualizado correctamente.');
    }

    // Método para "eliminar" (desactivar) un plan
    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);

        // En lugar de borrar, marcamos la descripción como 'desactivado'
        // Esto oculta el plan del dashboard pero mantiene el historial
        $plan->update([
            'descripcion' => 'desactivado'
        ]);

        return redirect()->back()->with('success', 'Plan eliminado correctamente.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Plan;
use App\Models\Membresia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClienteController extends Controller
{
        public function create()
    {
        // Pobla el select de planes
        $planes = Plan::orderBy('precio')->get(['id','nombre','duracion_dias','precio']);
        return view('clientRegister', compact('planes'));
    }

    public function store(Request $request)
    {
        // Nota: email en tu esquema puede ser NULL; si quieres forzarlo, cambia 'nullable' por 'required'
        $request->validate([
            'nombre_comp'   => ['required','string','max:160'],
            'email'         => ['nullable','email','max:160','unique:usuarios,email'],
            'telefono'      => ['nullable','regex:/^\d{10}$/'],
            'fecha_nac'     => ['nullable','date'],
            'plan_id'       => ['required','exists:planes,id'],
            'contrasena'    => ['required','confirmed','min:8'], // requiere contrasena_confirmation
        ],[
            'telefono.regex' => 'El teléfono debe tener exactamente 10 dígitos.',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        DB::beginTransaction();
        try {
            // 1) Crear usuario (NO Hash::make, tu modelo ya hashea en creating)
            $usuario = Usuario::create([
                'nombre_comp' => $request->nombre_comp,
                'email'       => $request->email,
                'telefono'    => $request->telefono,
                'fecha_nac'   => $request->fecha_nac,
                'contrasena'  => $request->contrasena,
                'estatus'     => 1, // activo
            ]);

            // 2) Crear membresía a partir del plan elegido
            $inicio = Carbon::today();
            $fin    = (clone $inicio)->addDays($plan->duracion_dias);

            Membresia::create([
                'usuario_id' => $usuario->id,
                'plan_id'    => $plan->id,
                'fecha_ini'  => $inicio->toDateString(),
                'fecha_fin'  => $fin->toDateString(),
                'estatus'    => 'vigente',
                // 'creada' y 'actualizada' se autollenan por DEFAULT CURRENT_TIMESTAMP en el .sql
            ]);

            // 3) Asignar rol "member" mediante la relación muchos-a-muchos que ya tienes
            $rolMember = Rol::firstOrCreate(['rol' => 'member']);
            $usuario->roles()->syncWithoutDetaching([$rolMember->id]);

            DB::commit();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Cliente registrado y membresía creada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            if (app()->environment('local')) {
                throw $e; // SOLO en local para ver el error completo en el navegador
            }

            return back()
                ->withInput()
                ->withErrors(['general' => 'Ocurrió un error al registrar al cliente. Intenta nuevamente.']);
        }
    }
}

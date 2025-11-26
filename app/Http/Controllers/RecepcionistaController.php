<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;

class RecepcionistaController extends Controller
{
    // Muestra el formulario
    public function create()
    {
        return view('receptionistRegister');
    }

    // Procesa el guardado
    public function store(Request $request)
    {
        // 1. Validar los datos
        $validated = $request->validate([
            'nombre_comp' => 'required|string|max:160',
            'email'       => 'required|email|max:160|unique:usuarios,email',
            'telefono'    => 'nullable|string|max:15',
            'fecha_nac'   => 'nullable|date',
            'contrasena'  => 'required|string|min:6|confirmed', // 'confirmed' busca contrasena_confirmation
        ], [
            // Mensajes personalizados (opcional)
            'email.unique' => 'Este correo ya está registrado.',
            'contrasena.confirmed' => 'Las contraseñas no coinciden.'
        ]);

        try {
            DB::beginTransaction();

            // 2. Crear el Usuario
            // Nota: El modelo Usuario.php ya hashea la contraseña automáticamente en setContrasenaAttribute
            $usuario = Usuario::create([
                'nombre_comp' => $validated['nombre_comp'],
                'email'       => $validated['email'],
                'telefono'    => $validated['telefono'],
                'fecha_nac'   => $validated['fecha_nac'],
                'contrasena'  => $validated['contrasena'],
                'estatus'     => 1, // Activo por defecto
                'is_inside'   => 0
            ]);

            // 3. Asignar el Rol de Staff/Recepcionista
            // Según tu SQL, el ID 2 corresponde a 'staff'
            $rolStaff = 2; 
            $usuario->roles()->attach($rolStaff);

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Recepcionista registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hubo un error al registrar: ' . $e->getMessage())->withInput();
        }
    }
}
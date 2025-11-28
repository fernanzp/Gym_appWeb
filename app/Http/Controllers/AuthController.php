<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class AuthController extends Controller
{
    // Mostrar el formulario de login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Procesar el login
    public function login(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Intentar autenticar al usuario
        $usuario = Usuario::where('email', $request->email)->first();

        // Si el usuario no existe
        if (!$usuario) {
            return back()->withErrors([
                'email' => 'El usuario no existe.',
            ]);
        }

        // Verificar la contraseña
        if (Hash::check($request->password, $usuario->contrasena)) {
            //Verificación del rol
            $tienePermiso = $usuario->roles()
                ->whereIn('rol', ['admin', 'staff'])
                ->exists();

            if (!$tienePermiso) {
                // Si la contraseña es correcta pero no es personal del gym:
                return back()->withErrors([
                    'email' => 'Tu cuenta no tiene permisos para acceder al panel administrativo.',
                ]);
            }

            // Autenticación exitosa
            Auth::login($usuario);

            // Si la autenticación es exitosa, redirigir al dashboard
            if (Auth::check()) {
                return redirect()->route('dashboard'); // Redirige a la ruta del dashboard
            } else {
                return back()->withErrors([
                    'email' => 'Hubo un problema al autenticar al usuario.',
                ]);
            }
        } else {
            // Error de autenticación
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidar la sesión actual
        $request->session()->invalidate();

        // Regenerar el token CSRF por seguridad
        $request->session()->regenerateToken();

        // Redirigir al login
        return redirect()->route('login');
    }
}
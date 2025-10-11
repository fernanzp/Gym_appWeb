<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    // Definir el nombre de la tabla
    protected $table = 'usuarios';

    // Los campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre_comp', 'email', 'telefono', 'contrasena', 'fecha_nac', 'estatus', 'remember_token'
    ];

    // Asegurarse de que la contraseña se encripte antes de guardarla
    public static function boot()
    {
        parent::boot();

        static::creating(function ($usuario) {
            if ($usuario->contrasena) {
                $usuario->contrasena = Hash::make($usuario->contrasena);
            }
        });
    }

    // Relación con los roles (muchos a muchos)
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'roles_usuarios', 'usuario_id', 'rol_id');
    }
}

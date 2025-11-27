<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';
    
    // 🔥 ACTUALIZACIÓN AQUÍ: Agregué 'fingerprint_id' e 'is_inside'
    protected $fillable = [
        'nombre_comp',
        'email',
        'telefono',
        'contrasena',
        'fecha_nac',
        'estatus',
        'fingerprint_id', 
        'is_inside'
    ];

    protected $hidden  = ['contrasena','remember_token'];

    // 🔥 OPCIONAL PERO RECOMENDADO: Esto hace que is_inside se trate como true/false
    protected $casts = [
        'is_inside' => 'boolean',
    ];

    // Indica a Laravel dónde está el password
    public function getAuthPassword(){ return $this->contrasena; }

    // Hashea al asignar
    public function setContrasenaAttribute($value)
    {
        if (!$value) return;
        if (is_string($value) && strlen($value) === 60 && str_starts_with($value, '$2y$')) {
            $this->attributes['contrasena'] = $value; // ya viene hasheado
        } else {
            $this->attributes['contrasena'] = Hash::make($value);
        }
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'roles_usuarios', 'usuario_id', 'rol_id');
    }
}
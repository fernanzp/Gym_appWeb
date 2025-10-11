<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    // Definir el nombre de la tabla
    protected $table = 'roles';

    // Los campos que se pueden asignar masivamente
    protected $fillable = ['rol'];

    // Relación con los usuarios (muchos a muchos)
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'roles_usuarios', 'rol_id', 'usuario_id');
    }
}

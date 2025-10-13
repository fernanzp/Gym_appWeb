<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RegistroAcceso extends Model
{
    protected $table = 'registros_accesos';
    
    public $timestamps = false; // tu tabla usa 'fecha'
    protected $fillable = ['fecha','usuario_id','acceso','observaciones','direccion'];
    
    protected $casts = ['fecha'=>'datetime','acceso'=>'boolean']; 
    //pendiente de un uso futuro para el acceso registrado
}

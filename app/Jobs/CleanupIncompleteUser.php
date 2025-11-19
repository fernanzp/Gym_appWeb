<?php

// app/Jobs/CleanupIncompleteUser.php

namespace App\Jobs;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupIncompleteUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    /**
     * Crea una nueva instancia del Job.
     * @param int $userId El ID del usuario que estamos monitoreando.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Ejecuta el trabajo.
     * Esta función se ejecuta después del tiempo de retraso.
     */
    public function handle(): void
    {
        $usuario = Usuario::find($this->userId);
        
        // Si el usuario existe, el fingerprint_id es NULL, Y el estatus es el inicial (0), 
        // significa que no hubo éxito ni fallo de huella (fue un timeout).
        if ($usuario && is_null($usuario->fingerprint_id) && $usuario->estatus == 0) {
            
            // ⚠️ ACCIÓN: Marcar como Timeout, NO ELIMINAR.
            $usuario->estatus = 9; // Nuevo estatus para "Timeout / Pendiente"
            $usuario->save();
            
            Log::warning("Usuario #{$this->userId} marcado como TIMEOUT (9).");
        }
        // Nota: Si el estatus es 8, fue fallo de huella. Si ya no existe, ya fue eliminado.
    }
}
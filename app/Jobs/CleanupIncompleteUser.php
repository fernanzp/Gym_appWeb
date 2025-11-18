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
        // 1. Buscar al usuario
        $usuario = Usuario::find($this->userId);

        // 2. Comprobar si existe Y si el campo de huella sigue VACÍO
        // Si el usuario ya fue completado por el webhook, fingerprint_id no será null.
        // Si el usuario fue eliminado por el webhook de error, $usuario será null.
        if ($usuario && is_null($usuario->fingerprint_id)) {
            
            // 3. Ejecutar ROLLBACK: Eliminar registro (Bug 2 solucionado por timeout)
            $usuario->delete();
            
            Log::warning("Usuario #{$this->userId} eliminado por TIMEOUT del registro de huella. No se recibió confirmación del Photon.");
        }
    }
}
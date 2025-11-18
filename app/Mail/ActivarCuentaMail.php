<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivarCuentaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $urlActivacion;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Usuario $usuario, $urlActivacion)
    {
        $this->usuario = $usuario;
        $this->urlActivacion = $urlActivacion;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Activa tu cuenta')
                    ->view('emails.activarCuenta'); // Esta vista la creamos a continuación
    }
}
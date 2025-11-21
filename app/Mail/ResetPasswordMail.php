<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $link;
    public $usuario;

    public function __construct(Usuario $usuario, $link)
    {
        $this->usuario = $usuario;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Restablecer contraseña')->view('emails.resetPassword');
    }
}
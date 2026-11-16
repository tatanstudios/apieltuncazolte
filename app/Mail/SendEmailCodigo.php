<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmailCodigo extends Mailable
{
    use Queueable, SerializesModels;

    public $codigo;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * compilador
     *
     * @return $this
     */
    public function build()
    {

        // Gmail solo permite enviar desde la cuenta autenticada (MAIL_FROM_ADDRESS en el .env)
        $address = config('mail.from.address');
        $subject = 'Recuperación de contraseña';
        $name = 'El Tuncazo';

        return $this->from($address, $name)
            ->subject($subject)
            ->view('backend.correos.vistacorreocodigo')
            ->with([
                'codigo' => $this->codigo
            ]);
    }
}

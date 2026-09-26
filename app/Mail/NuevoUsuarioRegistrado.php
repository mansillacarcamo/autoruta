<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NuevoUsuarioRegistrado extends Mailable
{
    public function __construct(public User $usuario)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nuevo registro en AutoRuta: ' . $this->usuario->name);
    }

    public function content(): Content
    {
        return new Content(view: 'correos.nuevo-usuario');
    }
}

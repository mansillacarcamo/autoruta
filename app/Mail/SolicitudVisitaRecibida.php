<?php

namespace App\Mail;

use App\Models\SolicitudVisita;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SolicitudVisitaRecibida extends Mailable
{
    public function __construct(public SolicitudVisita $solicitud)
    {
    }

    public function envelope(): Envelope
    {
        $v = $this->solicitud->vehiculo;

        return new Envelope(subject: "Solicitud de visita para tu {$v->marca} {$v->modelo}: {$this->solicitud->fechaTexto()} a las {$this->solicitud->hora} hrs");
    }

    public function content(): Content
    {
        return new Content(view: 'correos.solicitud-visita');
    }
}

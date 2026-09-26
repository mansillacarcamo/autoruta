<?php

namespace App\Listeners;

use App\Mail\NuevoUsuarioRegistrado;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Avisa por correo al administrador cada vez que alguien se registra. Si el correo falla
// (servidor de correo caído o mal configurado) el registro del cliente sigue normal.
class NotificarNuevoRegistro
{
    public function handle(Registered $evento): void
    {
        $destino = config('autoruta.correo_notificaciones');
        if (! $destino) {
            return;
        }

        try {
            Mail::to($destino)->send(new NuevoUsuarioRegistrado($evento->user));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el aviso de nuevo registro', ['usuario' => $evento->user->email, 'error' => $e->getMessage()]);
        }
    }
}

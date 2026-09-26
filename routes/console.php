<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('autoruta:admin {email}', function (string $email) {
    $usuario = \App\Models\User::where('email', $email)->first();
    if (! $usuario) {
        $this->error("No existe un usuario con el correo {$email}. Regístrate primero en el sitio.");
        return 1;
    }
    $usuario->update(['rol' => 'admin']);
    $this->info("{$email} ahora es administrador.");
})->purpose('Convierte una cuenta registrada en administrador');

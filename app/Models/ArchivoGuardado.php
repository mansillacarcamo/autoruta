<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoGuardado extends Model
{
    protected $table = 'archivos_guardados';

    protected $fillable = ['ruta', 'mime', 'contenido'];
}

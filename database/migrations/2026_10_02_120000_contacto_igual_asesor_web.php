<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// El teléfono y WhatsApp de contacto pasan a ser los mismos del botón "Asesor web".
return new class extends Migration
{
    public function up(): void
    {
        DB::table('configuracion_sitio')->where('clave', 'autoruta.contacto_whatsapp')->update(['valor' => '+56993393409']);
        DB::table('configuracion_sitio')->where('clave', 'autoruta.contacto_telefono')->update(['valor' => '+56 9 9339 3409']);
    }

    public function down(): void
    {
    }
};

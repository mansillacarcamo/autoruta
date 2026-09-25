<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuracion_sitio', function (Blueprint $table) {
            $table->string('clave')->primary();
            $table->string('valor');
        });

        \DB::table('configuracion_sitio')->insert([
            'clave' => 'precio_publicidad_negocio',
            'valor' => '15000',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sitio');
    }
};

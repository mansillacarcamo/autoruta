<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anunciantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_negocio');
            $table->string('rubro'); // financiera | taller | otro
            $table->string('logo')->nullable();
            $table->text('descripcion');
            $table->string('telefono_whatsapp')->nullable();
            $table->string('sitio_web')->nullable();
            $table->string('estado')->default('pausado'); // activo | pausado | vencido
            $table->timestamp('publicado_en')->nullable();
            $table->timestamp('vence_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anunciantes');
    }
};

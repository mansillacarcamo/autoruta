<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Segundo archivo opcional del banner (imagen o video) que se alterna con el principal cada 3 segundos.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->string('archivo_alterno')->nullable()->after('logo');
            $table->string('tipo_alterno', 10)->nullable()->after('archivo_alterno');
        });
    }

    public function down(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->dropColumn(['archivo_alterno', 'tipo_alterno']);
        });
    }
};

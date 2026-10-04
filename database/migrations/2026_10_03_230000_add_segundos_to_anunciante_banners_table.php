<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Segundos que se muestra cada archivo cuando el banner alterna entre el principal y el segundo.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->unsignedTinyInteger('segundos_principal')->default(3)->after('tipo_alterno');
            $table->unsignedTinyInteger('segundos_alterno')->default(3)->after('segundos_principal');
        });
    }

    public function down(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->dropColumn(['segundos_principal', 'segundos_alterno']);
        });
    }
};

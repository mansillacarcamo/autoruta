<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Banner de video con capa: imagen PNG/WEBP transparente (logo, teléfono, texto) que queda fija sobre el video.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->string('archivo_capa')->nullable()->after('archivo');
        });
    }

    public function down(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->dropColumn('archivo_capa');
        });
    }
};

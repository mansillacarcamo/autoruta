<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// El link de la publicidad pasa a ser opcional: hay banners que solo se muestran, sin redirigir a ningún lado.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->string('link_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->string('link_url')->nullable(false)->default('')->change();
        });
    }
};

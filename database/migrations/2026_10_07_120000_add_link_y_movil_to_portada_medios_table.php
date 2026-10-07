<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Slider del banner principal: cada banner puede llevar un link al tocarlo y una imagen para celular (opcionales).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portada_medios', function (Blueprint $table) {
            $table->string('link_url')->nullable()->after('archivo');
            $table->string('archivo_movil')->nullable()->after('link_url');
        });
    }

    public function down(): void
    {
        Schema::table('portada_medios', function (Blueprint $table) {
            $table->dropColumn(['link_url', 'archivo_movil']);
        });
    }
};

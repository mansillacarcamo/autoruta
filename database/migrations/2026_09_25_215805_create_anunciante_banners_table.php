<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anunciante_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anunciante_id')->constrained()->cascadeOnDelete();
            $table->string('tipo_medio'); // imagen | video
            $table->string('archivo');
            $table->string('link_url');
            $table->string('posicion'); // inicio | listado | superior | inferior | lateral
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anunciante_banners');
    }
};

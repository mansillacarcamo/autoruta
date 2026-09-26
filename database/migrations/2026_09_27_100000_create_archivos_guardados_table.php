<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Respaldo permanente de fotos y banners cuando no hay Object Storage: el disco de
// Laravel Cloud es temporal y se borra en cada deploy, la base de datos no.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos_guardados', function (Blueprint $table) {
            $table->id();
            $table->string('ruta')->unique();
            $table->string('mime', 100);
            $table->binary('contenido');
            $table->timestamps();
        });

        // En MySQL "binary" crea un BLOB de 64 KB; las fotos y videos necesitan LONGBLOB.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE archivos_guardados MODIFY contenido LONGBLOB NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_guardados');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('estado')->default('activa'); // activa | pausada | vendida | vencida
            $table->string('tipo'); // auto | camioneta | suv | moto | camion | otro
            $table->string('marca');
            $table->string('modelo');
            $table->unsignedSmallInteger('anio');
            $table->unsignedBigInteger('precio');
            $table->unsignedInteger('kilometraje');
            $table->string('region');
            $table->string('comuna');
            $table->text('descripcion')->default('');
            $table->string('version')->nullable();
            $table->string('transmision')->nullable(); // manual | automatica
            $table->string('combustible')->nullable(); // bencina | diesel | hibrido | electrico | gas
            $table->string('cilindrada')->nullable();
            $table->string('color')->nullable();
            $table->unsignedTinyInteger('puertas')->nullable();
            $table->string('traccion')->nullable(); // 4x2 | 4x4 | awd
            $table->unsignedTinyInteger('duenos_anteriores')->nullable();
            $table->text('equipamiento')->nullable();
            $table->unsignedInteger('vistas')->default(0);
            $table->timestamp('publicado_en')->nullable();
            $table->timestamp('vence_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};

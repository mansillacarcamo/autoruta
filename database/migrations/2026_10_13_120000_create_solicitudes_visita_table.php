<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Solicitudes de "Agendar visita" desde la ficha del auto: le llegan al vendedor por correo y en su panel,
// y él responde al comprador por WhatsApp.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_visita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // vendedor
            $table->string('nombre', 60);
            $table->string('telefono', 20);
            $table->date('fecha');
            $table->string('hora', 5);
            $table->string('estado', 15)->default('pendiente'); // pendiente | atendida
            $table->timestamps();
            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_visita');
    }
};

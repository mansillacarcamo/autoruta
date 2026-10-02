<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Premium: el admin lo activa desde Admin > Vehículos; el aviso sale primero en el inicio y el listado.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->boolean('premium')->default(false)->after('estado');
            $table->timestamp('premium_desde')->nullable()->after('premium');
            $table->index(['premium', 'premium_desde']);
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropIndex(['premium', 'premium_desde']);
            $table->dropColumn(['premium', 'premium_desde']);
        });
    }
};

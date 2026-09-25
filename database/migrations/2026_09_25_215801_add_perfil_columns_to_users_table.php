<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono_whatsapp')->nullable()->after('email');
            $table->string('region')->nullable()->after('telefono_whatsapp');
            $table->string('comuna')->nullable()->after('region');
            $table->string('rol')->default('usuario')->after('comuna'); // usuario | negocio | admin
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefono_whatsapp', 'region', 'comuna', 'rol']);
        });
    }
};

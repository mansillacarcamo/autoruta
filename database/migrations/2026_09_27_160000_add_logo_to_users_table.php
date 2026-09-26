<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre_comercial', 80)->nullable()->after('name');
            $table->string('logo')->nullable()->after('nombre_comercial');
            $table->boolean('mostrar_logo')->default(false)->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombre_comercial', 'logo', 'mostrar_logo']);
        });
    }
};

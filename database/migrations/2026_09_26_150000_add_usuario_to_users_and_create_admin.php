<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('usuario', 50)->nullable()->unique()->after('name');
        });

        DB::table('users')->updateOrInsert(
            ['usuario' => 'cesar'],
            [
                'name' => 'Cesar',
                'email' => 'admin@autoruta.cl',
                // Hash bcrypt de la clave; nunca guardar la clave en texto plano.
                'password' => '$2y$12$wwWmHYA6qmU6bzHA5cG7s.nknRlvCVkJEVFDgQHTOVn9k8/qJM86.',
                'rol' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->where('usuario', 'cesar')->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['usuario']);
            $table->dropColumn('usuario');
        });
    }
};

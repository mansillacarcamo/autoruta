<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->tinyInteger('id')->primary(); // siempre 1, una sola fila
            $table->unsignedBigInteger('total')->default(0);
        });

        \DB::table('visitas')->insert(['id' => 1, 'total' => 0]);
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};

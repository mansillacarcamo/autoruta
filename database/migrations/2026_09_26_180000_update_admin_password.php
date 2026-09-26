<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('usuario', 'cesar')->update([
            'password' => '$2y$12$YHHI7BA5sL9e/ZEUjRgsjui8nvlbXP0SIC4noSkk.1MjE/1LXHS/S',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        //
    }
};

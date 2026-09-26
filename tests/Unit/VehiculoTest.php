<?php

namespace Tests\Unit;

use App\Models\Vehiculo;
use PHPUnit\Framework\TestCase;

class VehiculoTest extends TestCase
{
    public function test_precio_con_puntos_de_miles(): void
    {
        $this->assertSame('$15.500.000', (new Vehiculo(['precio' => 15500000]))->precioFormateado());
    }
}

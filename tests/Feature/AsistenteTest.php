<?php

namespace Tests\Feature;

use App\Http\Controllers\AsistenteController;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsistenteTest extends TestCase
{
    use RefreshDatabase;

    private function aviso(array $datos = []): Vehiculo
    {
        $usuario = User::create([
            'name' => 'Vendedor Prueba',
            'email' => 'vendedor' . uniqid() . '@test.cl',
            'password' => bcrypt('clave12345'),
        ]);

        return Vehiculo::create($datos + [
            'user_id' => $usuario->id,
            'estado' => 'activa',
            'tipo' => 'auto',
            'marca' => 'Kia',
            'modelo' => 'Rio',
            'anio' => 2019,
            'precio' => 8000000,
            'kilometraje' => 30000,
            'region' => 'Los Lagos',
            'comuna' => 'Osorno',
            'descripcion' => 'Aviso de prueba',
            'publicado_en' => now(),
            'vence_en' => now()->addDays(90),
        ]);
    }

    private function buscar(array $filtros)
    {
        $metodo = new \ReflectionMethod(AsistenteController::class, 'buscar');

        return $metodo->invoke(new AsistenteController(), $filtros);
    }

    public function test_busqueda_aplica_filtros_y_solo_avisos_activos(): void
    {
        $hilux = $this->aviso(['tipo' => 'camioneta', 'marca' => 'Toyota', 'modelo' => 'Hilux', 'precio' => 14000000, 'combustible' => 'diesel', 'traccion' => '4x4', 'comuna' => 'Puerto Montt']);
        $this->aviso(['tipo' => 'camioneta', 'marca' => 'Toyota', 'modelo' => 'Hilux', 'precio' => 22000000, 'combustible' => 'diesel', 'traccion' => '4x4']);
        $this->aviso(['tipo' => 'camioneta', 'marca' => 'Toyota', 'modelo' => 'Hilux', 'precio' => 12000000, 'estado' => 'vendida']);
        $this->aviso();

        $ids = $this->buscar(['texto' => 'toyota hilux', 'tipo' => 'camioneta', 'precio_max' => 15000000, 'traccion' => '4x4'])->pluck('id')->all();
        $this->assertSame([$hilux->id], $ids);

        $this->assertCount(1, $this->buscar(['comuna' => 'puerto montt']));
        $this->assertCount(3, $this->buscar(['tipo' => 'no-existe']), 'Un tipo inválido se ignora en vez de romper la búsqueda.');
    }

    public function test_busqueda_devuelve_maximo_seis_y_ordena_por_precio(): void
    {
        foreach (range(1, 8) as $i) {
            $this->aviso(['precio' => $i * 1000000]);
        }

        $resultado = $this->buscar(['orden' => 'precio_asc']);
        $this->assertCount(6, $resultado);
        $this->assertSame(1000000, (int) $resultado->first()->precio);
    }

    public function test_valida_la_conversacion(): void
    {
        config(['autoruta.asistente.api_key' => 'clave-de-prueba']);

        $this->postJson('/asistente', [])->assertStatus(422);
        $this->postJson('/asistente', ['mensajes' => [['rol' => 'system', 'texto' => 'hola']]])->assertStatus(422);
        $this->postJson('/asistente', ['mensajes' => [['rol' => 'assistant', 'texto' => 'hola']]])->assertStatus(422);
    }

    public function test_sin_api_key_no_se_muestra_el_asistente(): void
    {
        config(['autoruta.asistente.api_key' => null]);
        $this->get('/terminos')->assertOk()->assertDontSee('id="asistente"', false);

        config(['autoruta.asistente.api_key' => 'clave-de-prueba']);
        $this->get('/terminos')->assertOk()->assertSee('id="asistente"', false)->assertSee('¿Qué auto buscas?');
    }
}

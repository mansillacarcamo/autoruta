<?php

namespace Tests\Feature;

use App\Models\ArchivoGuardado;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AutoRutaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function vendedor(array $datos = []): User
    {
        return User::create($datos + [
            'name' => 'Vendedor Prueba',
            'email' => 'vendedor' . uniqid() . '@test.cl',
            'password' => bcrypt('clave12345'),
            'telefono_whatsapp' => '+56911112222',
            'comuna' => 'Osorno',
        ]);
    }

    private function datosAviso(array $cambios = []): array
    {
        return $cambios + [
            'tipo' => 'camioneta',
            'marca' => 'Toyota',
            'modelo' => 'Hilux',
            'anio' => 2020,
            'kilometraje' => '45.000',
            'precio' => '15.500.000',
            'region' => 'Los Lagos',
            'comuna' => 'Puerto Montt',
            'telefonoWhatsapp' => '+56911112222',
            'descripcion' => 'Camioneta en buen estado',
            'fotos' => [UploadedFile::fake()->image('frente.jpg', 1200, 900), UploadedFile::fake()->image('lado.png', 800, 600)],
        ];
    }

    private function aviso(User $usuario, array $datos = []): Vehiculo
    {
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

    public function test_paginas_publicas_cargan(): void
    {
        $this->aviso($this->vendedor());

        foreach (['/', '/vehiculos', '/vehiculos?tipo=auto', '/como-funciona', '/terminos', '/privacidad', '/login', '/register', '/forgot-password', '/vehiculos/nuevos?desde=0'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_pagina_no_encontrada_en_espanol(): void
    {
        $this->get('/no-existe')->assertNotFound()->assertSee('Página no encontrada');
        $this->get('/vehiculos/999999')->assertNotFound();
    }

    public function test_publicar_requiere_iniciar_sesion(): void
    {
        $this->get('/panel/publicar')->assertRedirect('/login');
    }

    public function test_publicar_aviso_con_fotos(): void
    {
        $usuario = $this->vendedor();

        $this->actingAs($usuario)
            ->postJson('/panel/publicar', $this->datosAviso())
            ->assertOk()
            ->assertJson(['redirect' => route('panel')]);

        $vehiculo = Vehiculo::with('fotos')->firstOrFail();
        $this->assertSame(15500000, (int) $vehiculo->precio);
        $this->assertSame(45000, (int) $vehiculo->kilometraje);
        $this->assertCount(2, $vehiculo->fotos);
        foreach ($vehiculo->fotos as $foto) {
            Storage::disk('public')->assertExists('vehiculos/' . $foto->archivo);
            $this->assertDatabaseHas('archivos_guardados', ['ruta' => 'vehiculos/' . $foto->archivo]);
        }
        $this->get('/vehiculos/' . $vehiculo->id)->assertOk()->assertSee('Hilux')->assertSee('wa.me/56911112222', false);
    }

    public function test_publicar_sin_fotos_o_con_formato_invalido_falla(): void
    {
        $usuario = $this->vendedor();

        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso(['fotos' => []]))
            ->assertStatus(422)->assertJsonValidationErrors('fotos');

        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso(['fotos' => [UploadedFile::fake()->image('animada.gif')]]))
            ->assertStatus(422)->assertJsonValidationErrors('fotos.0');

        $this->assertSame(0, Vehiculo::count());
    }

    public function test_formulario_vacio_muestra_diagnostico(): void
    {
        $this->actingAs($this->vendedor())->postJson('/panel/publicar', [])
            ->assertStatus(422)
            ->assertJsonPath('errors.servidor.0', fn ($m) => str_contains($m, 'no recibió los datos'));
    }

    public function test_limite_de_publicaciones_activas(): void
    {
        $usuario = $this->vendedor();
        foreach (range(1, config('autoruta.max_publicaciones_activas')) as $i) {
            $this->aviso($usuario);
        }

        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso())->assertStatus(422);
    }

    public function test_avisos_vencidos_no_cuentan_ni_se_muestran_y_se_pueden_renovar(): void
    {
        $usuario = $this->vendedor();
        $vencido = $this->aviso($usuario, ['modelo' => 'Vencido', 'vence_en' => now()->subDay()]);
        foreach (range(1, config('autoruta.max_publicaciones_activas') - 1) as $i) {
            $this->aviso($usuario);
        }

        $this->get('/vehiculos')->assertDontSee('Kia Vencido');
        $this->get('/vehiculos/' . $vencido->id)->assertOk()->assertSee('ya no está vigente')->assertDontSee('Contactar por WhatsApp');

        // El vencido no cuenta para el límite: todavía puede publicar uno más.
        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso())->assertOk();

        // Con el límite lleno no puede renovar el vencido.
        $this->actingAs($usuario)->post('/panel/vehiculos/' . $vencido->id . '/renovar')->assertSessionHas('error');

        $usuario->vehiculos()->where('modelo', 'Hilux')->update(['estado' => 'vendida']);
        $this->actingAs($usuario)->post('/panel/vehiculos/' . $vencido->id . '/renovar')->assertSessionHas('ok');
        $this->assertTrue($vencido->fresh()->estaVisible());
        $this->get('/vehiculos')->assertSee('Kia Vencido');
    }

    public function test_vendido_oculta_el_contacto(): void
    {
        $usuario = $this->vendedor();
        $vehiculo = $this->aviso($usuario);

        $this->actingAs($usuario)->post('/panel/vehiculos/' . $vehiculo->id . '/vendido')->assertRedirect();

        $this->get('/vehiculos/' . $vehiculo->id)->assertSee('ya fue vendido')->assertDontSee('Contactar por WhatsApp');
        $this->get('/vehiculos')->assertDontSee('Kia Rio');
    }

    public function test_editar_aviso_propio_y_no_ajeno(): void
    {
        $dueno = $this->vendedor();
        $otro = $this->vendedor();
        $this->actingAs($dueno)->postJson('/panel/publicar', $this->datosAviso())->assertOk();
        $vehiculo = Vehiculo::firstOrFail();

        $this->actingAs($otro)->get('/panel/vehiculos/' . $vehiculo->id . '/editar')->assertForbidden();
        $this->actingAs($otro)->putJson('/panel/vehiculos/' . $vehiculo->id, $this->datosAviso(['fotos' => []]))->assertForbidden();

        $this->actingAs($dueno)->get('/panel/vehiculos/' . $vehiculo->id . '/editar')->assertOk()->assertSee('Guardar cambios');
        $this->actingAs($dueno)
            ->putJson('/panel/vehiculos/' . $vehiculo->id, $this->datosAviso(['precio' => '14.000.000', 'fotos' => [UploadedFile::fake()->image('nueva.jpg')]]))
            ->assertOk();

        $this->assertSame(14000000, (int) $vehiculo->fresh()->precio);
        $this->assertCount(3, $vehiculo->fresh()->fotos);
    }

    public function test_eliminar_fotos_pero_nunca_la_ultima(): void
    {
        $usuario = $this->vendedor();
        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso())->assertOk();
        $vehiculo = Vehiculo::with('fotos')->firstOrFail();
        [$primera, $segunda] = $vehiculo->fotos;

        $this->actingAs($this->vendedor())->deleteJson("/panel/vehiculos/{$vehiculo->id}/fotos/{$primera->id}")->assertForbidden();

        $this->actingAs($usuario)->deleteJson("/panel/vehiculos/{$vehiculo->id}/fotos/{$primera->id}")->assertOk();
        Storage::disk('public')->assertMissing('vehiculos/' . $primera->archivo);
        $this->assertDatabaseMissing('archivos_guardados', ['ruta' => 'vehiculos/' . $primera->archivo]);

        $this->actingAs($usuario)->deleteJson("/panel/vehiculos/{$vehiculo->id}/fotos/{$segunda->id}")->assertStatus(422);
        $this->assertCount(1, $vehiculo->fresh()->fotos);
    }

    public function test_fotos_se_sirven_desde_el_respaldo_si_el_disco_se_borra(): void
    {
        $usuario = $this->vendedor();
        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso())->assertOk();
        $foto = Vehiculo::with('fotos')->firstOrFail()->fotos->first();

        $this->get('/media/vehiculos/' . $foto->archivo)->assertOk();

        Storage::disk('public')->delete('vehiculos/' . $foto->archivo);
        $respuesta = $this->get('/media/vehiculos/' . $foto->archivo);
        $respuesta->assertOk();
        $this->assertStringStartsWith('image/', $respuesta->headers->get('Content-Type'));

        $this->get('/media/vehiculos/no-existe.jpg')->assertNotFound();
        $this->get('/media/otra-carpeta/archivo.jpg')->assertNotFound();
    }

    public function test_home_no_repite_avisos_entre_ultimos_y_destacados(): void
    {
        $usuario = $this->vendedor();
        foreach (range(1, 10) as $i) {
            $this->aviso($usuario, ['modelo' => 'Modelo' . $i, 'publicado_en' => now()->addMinutes($i), 'vistas' => $i]);
        }

        $html = $this->get('/')->assertOk()->getContent();
        preg_match_all('/tarjeta-titulo">([^<]+)/', $html, $titulos);
        $this->assertSame(count($titulos[1]), count(array_unique($titulos[1])));
    }

    public function test_cuenta_cambia_datos_y_contrasena(): void
    {
        $usuario = $this->vendedor();

        $this->actingAs($usuario)->put('/panel/cuenta', [
            'name' => 'Nuevo Nombre', 'email' => 'nuevo@test.cl', 'telefono' => '+56 9 8888 7777', 'ciudad' => 'Puerto Varas',
        ])->assertSessionHas('ok');
        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'name' => 'Nuevo Nombre', 'telefono_whatsapp' => '+56988887777', 'comuna' => 'Puerto Varas']);

        $this->actingAs($usuario)->put('/panel/cuenta/clave', [
            'clave_actual' => 'incorrecta', 'password' => 'nuevaClave123', 'password_confirmation' => 'nuevaClave123',
        ])->assertSessionHasErrorsIn('clave', 'clave_actual');

        $this->actingAs($usuario)->put('/panel/cuenta/clave', [
            'clave_actual' => 'clave12345', 'password' => 'nuevaClave123', 'password_confirmation' => 'nuevaClave123',
        ])->assertSessionHas('ok');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('nuevaClave123', $usuario->fresh()->password));
    }

    public function test_admin_protegido_y_funcional(): void
    {
        $cliente = $this->vendedor();
        $this->actingAs($cliente)->get('/admin')->assertForbidden();

        $admin = User::where('usuario', 'cesar')->firstOrFail();
        foreach (['/admin', '/admin/negocios', '/admin/negocios/nuevo', '/admin/portada', '/admin/usuarios', '/admin/configuracion'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        $this->actingAs($admin)->post('/admin/usuarios/' . $cliente->id . '/clave')->assertSessionHas('clave_temporal');
        $clave = session('clave_temporal')['clave'];
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($clave, $cliente->fresh()->password));
    }

    public function test_login_con_usuario_admin_y_con_correo(): void
    {
        $this->post('/login', ['email' => 'cesar', 'password' => 'cesar1342'])->assertRedirect(route('panel', absolute: false));
        $this->assertAuthenticated();
        $this->post('/logout');

        $cliente = $this->vendedor(['email' => 'cliente@test.cl']);
        $this->post('/login', ['email' => 'cliente@test.cl', 'password' => 'clave12345']);
        $this->assertAuthenticatedAs($cliente);
    }
    public function test_registro_envia_aviso_al_administrador(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->post('/register', [
            'name' => 'Cliente Nuevo', 'email' => 'nuevo@cliente.cl', 'telefono' => '+56 9 5555 4444',
            'ciudad' => 'Osorno', 'password' => 'clave12345', 'password_confirmation' => 'clave12345',
        ])->assertRedirect(route('panel', absolute: false));

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\NuevoUsuarioRegistrado::class, function ($correo) {
            return $correo->hasTo(config('autoruta.correo_notificaciones')) && $correo->usuario->email === 'nuevo@cliente.cl';
        });

        $html = (new \App\Mail\NuevoUsuarioRegistrado(User::where('email', 'nuevo@cliente.cl')->first()))->render();
        $this->assertStringContainsString('Cliente Nuevo', $html);
        $this->assertStringContainsString('+56955554444', $html);
    }

    public function test_registro_funciona_aunque_falle_el_correo(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);

        $this->post('/register', [
            'name' => 'Sin Correo', 'email' => 'sincorreo@cliente.cl', 'telefono' => '+56 9 5555 3333',
            'ciudad' => 'Osorno', 'password' => 'clave12345', 'password_confirmation' => 'clave12345',
        ])->assertRedirect(route('panel', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'sincorreo@cliente.cl']);
    }

    public function test_admin_correo_de_prueba_avisa_si_no_esta_conectado(): void
    {
        $admin = User::where('usuario', 'cesar')->firstOrFail();
        config(['mail.default' => 'log']);
        $this->actingAs($admin)->post('/admin/configuracion/correo-prueba')->assertSessionHas('error');
        $this->actingAs($admin)->get('/admin/configuracion')->assertSee('No conectado');
    }
    public function test_banners_de_publicidad_con_link_y_contador_de_clics(): void
    {
        $admin = User::where('usuario', 'cesar')->firstOrFail();
        $negocio = \App\Models\Anunciante::create(['nombre_negocio' => 'Taller Sur', 'rubro' => 'taller', 'descripcion' => '', 'estado' => 'activo', 'publicado_en' => now()]);

        // Link sin https:// se completa solo; links peligrosos se rechazan.
        $this->actingAs($admin)->post("/admin/negocios/{$negocio->id}/banners", [
            'tipoMedio' => 'imagen', 'posicion' => 'superior', 'linkUrl' => 'javascript:alert(1)', 'archivo' => UploadedFile::fake()->image('b.jpg', 728, 90),
        ])->assertSessionHasErrors('linkUrl');
        $this->actingAs($admin)->post("/admin/negocios/{$negocio->id}/banners", [
            'tipoMedio' => 'imagen', 'posicion' => 'superior', 'linkUrl' => 'www.tallersur.cl', 'archivo' => UploadedFile::fake()->image('b.jpg', 728, 90),
        ])->assertSessionHas('ok');
        $banner = $negocio->banners()->firstOrFail();
        $this->assertSame('https://www.tallersur.cl', $banner->link_url);

        // En la web el banner apunta a la ruta que cuenta el clic.
        auth()->logout();
        $this->get('/')->assertSee(route('publicidad.clic', $banner), false);
        $this->get(route('publicidad.clic', $banner))->assertRedirect('https://www.tallersur.cl');
        $this->get(route('publicidad.clic', $banner))->assertRedirect('https://www.tallersur.cl');
        $this->assertSame(2, $banner->fresh()->clics);

        // El admin puede cambiar el link y sus propios clics no cuentan.
        $this->actingAs($admin)->put("/admin/negocios/{$negocio->id}/banners/{$banner->id}", ['linkUrl' => 'https://wa.me/56912345678', 'posicion' => 'inferior'])->assertSessionHas('ok');
        $this->actingAs($admin)->get(route('publicidad.clic', $banner))->assertRedirect('https://wa.me/56912345678');
        $this->assertSame(2, $banner->fresh()->clics);
        $this->actingAs($admin)->get('/admin/negocios')->assertOk()->assertSee('Taller Sur');
        $this->actingAs($admin)->get("/admin/negocios/{$negocio->id}")->assertOk()->assertSee('2 clics');
    }
}

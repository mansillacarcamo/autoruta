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

    public function test_pie_opcional_se_guarda_y_se_muestra(): void
    {
        $usuario = $this->vendedor();

        // Sin pie: el aviso se publica igual y no muestra la etiqueta.
        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso())->assertOk();
        $sinPie = Vehiculo::latest('id')->first();
        $this->assertNull($sinPie->pie);
        $this->get('/vehiculos/' . $sinPie->id)->assertDontSee('class="tarjeta-pie"', false);

        // Con pie escrito con puntos.
        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso(['pie' => '3.000.000']))->assertOk();
        $conPie = Vehiculo::latest('id')->first();
        $this->assertSame(3000000, (int) $conPie->pie);
        $this->get('/vehiculos/' . $conPie->id)->assertSee('Pie $3.000.000');
        $this->get('/vehiculos')->assertSee('Pie $3.000.000');

        // El pie no puede ser igual o mayor que el precio.
        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso(['pie' => '15.500.000']))
            ->assertStatus(422)->assertJsonValidationErrors('pie');
    }

    public function test_tarjeta_y_ficha_muestran_contador_de_visitas(): void
    {
        $vehiculo = $this->aviso($this->vendedor());
        $vehiculo->update(['vistas' => 1233]);

        // Abrir la ficha suma una visita y la muestra.
        $this->get('/vehiculos/' . $vehiculo->id)->assertOk()->assertSee('1.234 visitas');
        $this->get('/vehiculos')->assertSee('1.234 visitas');
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

    public function test_sin_tope_de_publicaciones_activas(): void
    {
        $usuario = $this->vendedor();
        foreach (range(1, 10) as $i) {
            $this->aviso($usuario);
        }

        $this->actingAs($usuario)->postJson('/panel/publicar', $this->datosAviso())->assertOk();
        $this->assertSame(11, $usuario->vehiculos()->activos()->count());
    }

    public function test_avisos_vencidos_no_cuentan_ni_se_muestran_y_se_pueden_renovar(): void
    {
        $usuario = $this->vendedor();
        $vencido = $this->aviso($usuario, ['modelo' => 'Vencido', 'vence_en' => now()->subDay()]);
        foreach (range(1, 5) as $i) {
            $this->aviso($usuario);
        }

        $this->get('/vehiculos')->assertDontSee('Kia Vencido');
        $this->get('/vehiculos/' . $vencido->id)->assertOk()->assertSee('ya no está vigente')->assertDontSee('Contactar por WhatsApp');

        // Sin tope de publicaciones: puede renovar aunque tenga varias activas.
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

    public function test_admin_edita_sube_y_elimina_autos_de_cualquier_vendedor(): void
    {
        $vendedor = $this->vendedor(['telefono_whatsapp' => '+56933334444']);
        $this->actingAs($vendedor)->get('/admin/vehiculos/nuevo')->assertForbidden();

        $admin = User::where('usuario', 'cesar')->firstOrFail();
        $telefonoAdmin = $admin->telefono_whatsapp;
        $this->actingAs($admin)->get('/admin/vehiculos/nuevo')->assertOk()->assertSee('Publicar a nombre de')->assertSee('Vendedor Prueba');

        // Sube un auto a nombre del vendedor.
        $this->actingAs($admin)
            ->postJson('/admin/vehiculos', $this->datosAviso(['publicarComo' => $vendedor->id, 'telefonoWhatsapp' => '+56955556666']))
            ->assertOk()->assertJson(['redirect' => route('admin.vehiculos.index')]);
        $vehiculo = Vehiculo::firstOrFail();
        $this->assertSame($vendedor->id, (int) $vehiculo->user_id);
        $this->assertSame('+56955556666', $vendedor->fresh()->telefono_whatsapp);

        // Edita el aviso ajeno sin tocar los datos de contacto del admin.
        $this->actingAs($admin)->get('/admin/vehiculos')->assertSee('Editar');
        $this->actingAs($admin)->get("/admin/vehiculos/{$vehiculo->id}/editar")->assertOk()->assertSee('Guardar cambios')->assertSee('+56955556666');
        $this->actingAs($admin)
            ->putJson("/admin/vehiculos/{$vehiculo->id}", $this->datosAviso(['precio' => '9.990.000', 'fotos' => [], 'telefonoWhatsapp' => '+56955556666']))
            ->assertOk();
        $this->assertSame(9990000, (int) $vehiculo->fresh()->precio);
        $this->assertSame($telefonoAdmin, $admin->fresh()->telefono_whatsapp);

        $primera = $vehiculo->fotos()->first();
        $this->actingAs($admin)->deleteJson("/panel/vehiculos/{$vehiculo->id}/fotos/{$primera->id}")->assertOk();

        $this->actingAs($admin)->delete("/admin/vehiculos/{$vehiculo->id}")->assertSessionHas('ok');
        $this->assertNull($vehiculo->fresh());
    }

    public function test_ticket_premium_pone_el_auto_primero_con_etiqueta(): void
    {
        $vendedor = $this->vendedor();
        $antiguo = $this->aviso($vendedor, ['marca' => 'Lada', 'modelo' => 'Niva', 'publicado_en' => now()->subDays(10)]);
        $this->aviso($vendedor, ['marca' => 'Kia', 'modelo' => 'Rio', 'publicado_en' => now()]);

        // Un cliente no puede marcar Premium.
        $this->actingAs($vendedor)->post('/admin/vehiculos/' . $antiguo->id . '/premium', ['premium' => 1])->assertForbidden();
        $this->assertFalse((bool) $antiguo->fresh()->premium);

        $admin = User::where('usuario', 'cesar')->firstOrFail();
        $this->actingAs($admin)->get('/admin/vehiculos')->assertOk()->assertSee('Lada Niva')->assertSee('Kia Rio');

        $this->actingAs($admin)->post('/admin/vehiculos/' . $antiguo->id . '/premium', ['premium' => 1])->assertSessionHas('ok');
        $this->assertTrue($antiguo->fresh()->premium);

        // El Premium (más antiguo) queda primero en el listado y en el inicio, con su etiqueta.
        $this->get('/vehiculos')->assertSeeInOrder(['Lada Niva', 'Kia Rio'])->assertSee('★ Premium');
        $this->get('/')->assertSeeInOrder(['Lada Niva', 'Kia Rio']);
        $this->get('/vehiculos/' . $antiguo->id)->assertSee('★ Premium');
        $this->actingAs($admin)->get('/admin/vehiculos?filtro=premium')->assertSee('Lada Niva')->assertDontSee('Kia Rio');

        // Quitar el ticket lo devuelve a su lugar.
        $this->actingAs($admin)->post('/admin/vehiculos/' . $antiguo->id . '/premium', ['premium' => 0])->assertSessionHas('ok');
        $this->assertFalse($antiguo->fresh()->premium);
        $this->get('/vehiculos')->assertSeeInOrder(['Kia Rio', 'Lada Niva'])->assertDontSee('★ Premium');
    }

    public function test_popup_del_inicio_se_administra_desde_admin(): void
    {
        $this->actingAs($this->vendedor())->get('/admin/popup')->assertForbidden();
        $this->get('/')->assertDontSee('id="popup-inicio"', false);

        $admin = User::where('usuario', 'cesar')->firstOrFail();
        $this->actingAs($admin)->get('/admin/popup')->assertOk()->assertSee('No hay pop-up');

        // Subir un diseño con enlace: aparece en el inicio y su archivo se puede ver.
        $this->actingAs($admin)->post('/admin/popup', [
            'archivo' => UploadedFile::fake()->image('oferta.jpg', 1080, 1080),
            'link_url' => 'https://autoruta.cl/vehiculos',
        ])->assertSessionHas('ok');
        $popup = \App\Models\Popup::firstOrFail();
        $this->assertSame('imagen', $popup->tipo_medio);
        $this->get('/')->assertSee('id="popup-inicio"', false)->assertSee($popup->url(), false)->assertSee('https://autoruta.cl/vehiculos', false);
        $this->get('/media/popup/' . $popup->archivo)->assertOk();

        // Pausarlo lo oculta del inicio.
        $this->actingAs($admin)->put('/admin/popup/' . $popup->id, ['link_url' => ''])->assertSessionHas('ok');
        $this->assertFalse($popup->fresh()->activo);
        $this->get('/')->assertDontSee('id="popup-inicio"', false);

        // Subir uno nuevo reemplaza al anterior (queda uno solo, activo).
        $this->actingAs($admin)->post('/admin/popup', ['archivo' => UploadedFile::fake()->create('promo.mp4', 500, 'video/mp4')])->assertSessionHas('ok');
        $this->assertSame(1, \App\Models\Popup::count());
        $nuevo = \App\Models\Popup::firstOrFail();
        $this->assertSame('video', $nuevo->tipo_medio);
        $this->assertTrue($nuevo->activo);

        $this->actingAs($admin)->delete('/admin/popup/' . $nuevo->id)->assertSessionHas('ok');
        $this->assertSame(0, \App\Models\Popup::count());
    }

    public function test_admin_protegido_y_funcional(): void
    {
        $cliente = $this->vendedor();
        $this->actingAs($cliente)->get('/admin')->assertForbidden();

        $admin = User::where('usuario', 'cesar')->firstOrFail();
        foreach (['/admin', '/admin/negocios', '/admin/negocios/nuevo', '/admin/portada', '/admin/popup', '/admin/vehiculos', '/admin/usuarios', '/admin/configuracion'] as $url) {
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
            'region' => 'Los Lagos', 'ciudad' => 'Osorno', 'password' => 'clave12345', 'password_confirmation' => 'clave12345',
        ])->assertRedirect(route('panel', absolute: false));

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\NuevoUsuarioRegistrado::class, function ($correo) {
            return $correo->hasTo(config('autoruta.correo_notificaciones')) && $correo->usuario->email === 'nuevo@cliente.cl';
        });

        $html = (new \App\Mail\NuevoUsuarioRegistrado(User::where('email', 'nuevo@cliente.cl')->first()))->render();
        $this->assertStringContainsString('Cliente Nuevo', $html);
        $this->assertStringContainsString('+56955554444', $html);
    }

    public function test_registro_pide_region_y_comuna_de_la_lista(): void
    {
        $this->get('/register')->assertOk()->assertSee('Selecciona tu región')->assertSee('Los Lagos');

        // Comuna que no es de la región elegida: se rechaza.
        $this->post('/register', [
            'name' => 'Mal Lugar', 'email' => 'mal@cliente.cl', 'telefono' => '+56 9 5555 1111',
            'region' => 'Los Lagos', 'ciudad' => 'Providencia', 'password' => 'clave12345', 'password_confirmation' => 'clave12345',
        ])->assertSessionHasErrors('ciudad');
        $this->assertGuest();

        $this->post('/register', [
            'name' => 'Buen Lugar', 'email' => 'bien@cliente.cl', 'telefono' => '+56 9 5555 2222',
            'region' => 'Los Lagos', 'ciudad' => 'Puerto Varas', 'password' => 'clave12345', 'password_confirmation' => 'clave12345',
        ])->assertRedirect(route('panel', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'bien@cliente.cl', 'region' => 'Los Lagos', 'comuna' => 'Puerto Varas']);
    }

    public function test_registro_funciona_aunque_falle_el_correo(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);

        $this->post('/register', [
            'name' => 'Sin Correo', 'email' => 'sincorreo@cliente.cl', 'telefono' => '+56 9 5555 3333',
            'region' => 'Los Lagos', 'ciudad' => 'Osorno', 'password' => 'clave12345', 'password_confirmation' => 'clave12345',
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
    public function test_logo_de_automotora_lo_decide_cada_usuario(): void
    {
        $usuario = $this->vendedor();
        $vehiculo = $this->aviso($usuario);

        $this->actingAs($usuario)->post('/panel/cuenta/logo', ['logo' => UploadedFile::fake()->create('logo.gif', 10, 'image/gif')])
            ->assertSessionHasErrorsIn('logo', 'logo');

        $this->actingAs($usuario)->post('/panel/cuenta/logo', [
            'nombre_comercial' => 'Automotora Sur', 'mostrar_logo' => '1', 'logo' => UploadedFile::fake()->image('logo.png', 1200, 1200),
        ])->assertSessionHas('ok');
        $usuario->refresh();
        $this->assertNotNull($usuario->logo);
        Storage::disk('public')->assertExists('logos/' . $usuario->logo);
        [$ancho, $alto] = getimagesizefromstring(Storage::disk('public')->get('logos/' . $usuario->logo));
        $this->assertLessThanOrEqual(400, max($ancho, $alto));

        $this->get('/vehiculos')->assertSee('tarjeta-logo', false)->assertSee('/media/logos/' . $usuario->logo, false);
        $this->get('/vehiculos/' . $vehiculo->id)->assertSee('Automotora Sur')->assertSee('vendedor-logo', false);
        $this->get('/media/logos/' . $usuario->logo)->assertOk();

        // Desmarcar la casilla oculta el logo sin borrarlo.
        $this->actingAs($usuario)->post('/panel/cuenta/logo', ['nombre_comercial' => 'Automotora Sur'])->assertSessionHas('ok');
        $this->get('/vehiculos')->assertDontSee('tarjeta-logo', false);
        $this->assertNotNull($usuario->fresh()->logo);

        // Quitar el logo lo elimina.
        $archivo = $usuario->fresh()->logo;
        $this->actingAs($usuario)->delete('/panel/cuenta/logo')->assertSessionHas('ok');
        $this->assertNull($usuario->fresh()->logo);
        Storage::disk('public')->assertMissing('logos/' . $archivo);
    }
    public function test_banners_laterales_tambien_se_muestran_en_celular(): void
    {
        $negocio = \App\Models\Anunciante::create(['nombre_negocio' => 'Lateral', 'rubro' => 'taller', 'descripcion' => '', 'estado' => 'activo', 'publicado_en' => now()]);
        $banner = $negocio->banners()->create(['tipo_medio' => 'imagen', 'archivo' => 'lat.jpg', 'link_url' => 'https://ejemplo.cl', 'posicion' => 'lateral_derecho_2']);

        $html = $this->get('/')->assertOk()->getContent();

        // Versión celular: 3 filas en el inicio; la fila 2 trae el banner y las vacías muestran la franja "Disponible".
        $this->assertSame(3, substr_count($html, 'class="laterales-movil"'));
        $this->assertSame(1, substr_count($html, 'class="laterales-movil-banner"'));
        $this->assertStringContainsString('Espacios publicitarios laterales 1', $html);
        $this->assertStringContainsString('Espacios publicitarios laterales 3', $html);
        // El mismo banner también está en el costado para pantallas grandes (2 enlaces en total).
        $this->assertSame(2, substr_count($html, route('publicidad.clic', $banner)));

        // En otras páginas se muestra la fila 1.
        $this->assertSame(1, substr_count($this->get('/vehiculos')->getContent(), 'class="laterales-movil"'));
    }

    public function test_banner_lateral_5_se_muestra_en_costado_y_en_fila_5_del_celular(): void
    {
        $negocio = \App\Models\Anunciante::create(['nombre_negocio' => 'Lateral 5', 'rubro' => 'taller', 'descripcion' => '', 'estado' => 'activo', 'publicado_en' => now()]);
        $banner = $negocio->banners()->create(['tipo_medio' => 'imagen', 'archivo' => 'lat5.jpg', 'link_url' => 'https://ejemplo.cl', 'posicion' => 'lateral_izquierdo_5']);

        $html = $this->get('/')->assertOk()->getContent();

        // Costado (pantallas grandes) + fila 5 del celular; la fila 4 vacía no se muestra.
        $this->assertSame(2, substr_count($html, route('publicidad.clic', $banner)));
        $this->assertSame(4, substr_count($html, 'class="laterales-movil"'));
        $this->assertSame(10, substr_count($html, 'class="banner-lateral-tramo"'));
    }
}

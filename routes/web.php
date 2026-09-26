<?php

use App\Http\Controllers\Admin\NegocioController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');
Route::get('/vehiculos/nuevos', [VehiculoController::class, 'nuevos'])->name('vehiculos.nuevos');
Route::get('/vehiculos/{vehiculo}', [VehiculoController::class, 'show'])->name('vehiculos.show');

Route::get('/media/{carpeta}/{archivo}', [\App\Http\Controllers\MediaController::class, 'mostrar'])
    ->where(['carpeta' => 'vehiculos|negocios|portada', 'archivo' => '[A-Za-z0-9_.-]+'])
    ->name('media');

Route::view('/como-funciona', 'como-funciona')->name('como-funciona');
Route::view('/terminos', 'legal.terminos')->name('terminos');
Route::view('/privacidad', 'legal.privacidad')->name('privacidad');

Route::middleware('auth')->group(function () {
    Route::get('/panel', [PanelController::class, 'index'])->name('panel');
    Route::get('/panel/cuenta', [\App\Http\Controllers\CuentaController::class, 'editar'])->name('panel.cuenta');
    Route::put('/panel/cuenta', [\App\Http\Controllers\CuentaController::class, 'actualizarDatos'])->name('panel.cuenta.datos');
    Route::put('/panel/cuenta/clave', [\App\Http\Controllers\CuentaController::class, 'actualizarClave'])->name('panel.cuenta.clave');
    Route::get('/panel/publicar', [PanelController::class, 'crear'])->name('panel.publicar');
    Route::post('/panel/publicar', [PanelController::class, 'guardar'])->name('panel.publicar.guardar');
    Route::get('/panel/vehiculos/{vehiculo}/editar', [PanelController::class, 'editar'])->name('panel.editar');
    Route::put('/panel/vehiculos/{vehiculo}', [PanelController::class, 'actualizar'])->name('panel.actualizar');
    Route::delete('/panel/vehiculos/{vehiculo}/fotos/{foto}', [PanelController::class, 'eliminarFoto'])->name('panel.fotos.eliminar');
    Route::post('/panel/vehiculos/{vehiculo}/renovar', [PanelController::class, 'renovar'])->name('panel.renovar');
    Route::post('/panel/vehiculos/{vehiculo}/vendido', [PanelController::class, 'marcarVendido'])->name('panel.vendido');
    Route::delete('/panel/vehiculos/{vehiculo}', [PanelController::class, 'eliminar'])->name('panel.eliminar');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\ResumenController::class, 'index'])->name('inicio');
    Route::get('/negocios', [NegocioController::class, 'index'])->name('negocios.index');
    Route::get('/configuracion', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'editar'])->name('configuracion');
    Route::put('/configuracion', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'actualizar'])->name('configuracion.actualizar');
    Route::post('/precio', [NegocioController::class, 'actualizarPrecio'])->name('precio.actualizar');
    Route::get('/negocios/nuevo', [NegocioController::class, 'crear'])->name('negocios.crear');
    Route::post('/negocios', [NegocioController::class, 'guardar'])->name('negocios.guardar');
    Route::get('/negocios/{negocio}', [NegocioController::class, 'editar'])->name('negocios.editar');
    Route::put('/negocios/{negocio}', [NegocioController::class, 'actualizar'])->name('negocios.actualizar');
    Route::post('/negocios/{negocio}/banners', [NegocioController::class, 'subirBanner'])->name('negocios.banners.subir');
    Route::delete('/negocios/{negocio}/banners/{banner}', [NegocioController::class, 'eliminarBanner'])->name('negocios.banners.eliminar');
    Route::get('/portada', [\App\Http\Controllers\Admin\PortadaController::class, 'index'])->name('portada.index');
    Route::post('/portada', [\App\Http\Controllers\Admin\PortadaController::class, 'subir'])->name('portada.subir');
    Route::delete('/portada/{medio}', [\App\Http\Controllers\Admin\PortadaController::class, 'eliminar'])->name('portada.eliminar');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/{usuario}/clave', [UsuarioController::class, 'restablecerClave'])->name('usuarios.clave');
});

require __DIR__.'/auth.php';

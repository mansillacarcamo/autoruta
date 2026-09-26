<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // PHP descarta todo el formulario cuando los archivos superan el límite del servidor.
        // Ocurre antes de iniciar la sesión, así que el aviso viaja en la URL y no como flash.
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Las fotos pesan demasiado para enviarlas juntas. Sube menos fotos o fotos más livianas.'], 413);
            }

            $ruta = parse_url((string) $request->headers->get('referer'), PHP_URL_PATH) ?: '/panel/publicar';

            return redirect(url($ruta) . '?aviso=archivos-pesados');
        });
    })->create();

<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\TrackInactivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'inactivity' => TrackInactivity::class,
            'cors' => \App\Http\Middleware\AllowCors::class,
        ]);

        $middleware->appendToGroup('api', [
            \App\Http\Middleware\AllowCors::class,
            \App\Http\Middleware\SecureApiHeaders::class,
        ]);

        // Enforce 3-minute inactivity auto-logout across the authenticated web UI.
        $middleware->web(append: [
            TrackInactivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

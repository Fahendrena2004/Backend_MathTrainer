<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ensure API routes normalize 404s to a consistent JSON payload
        $middleware->prependToGroup('api', [\App\Http\Middleware\NormalizeApiExceptions::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

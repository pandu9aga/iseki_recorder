<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureMember;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Env;

if (php_sapi_name() === 'apache2handler' && ($_SERVER['WINDIR'] ?? false)) {
    Env::disablePutenv();
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'member' => EnsureMember::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

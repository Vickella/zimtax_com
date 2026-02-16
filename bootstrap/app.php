<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ✅ aliases
        $middleware->alias([
            'company'   => \App\Http\Middleware\CompanyMiddleware::class,
            'coa.ready' => \App\Http\Middleware\CoaReady::class,
        ]);

        // ✅ IMPORTANT: force order (Company MUST run early)
        $middleware->priority([
            \App\Http\Middleware\CompanyMiddleware::class,
            \App\Http\Middleware\CoaReady::class,

            // keep Laravel core order after
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
            \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

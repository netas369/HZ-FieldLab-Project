<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));  // Add this
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);

        // ✅ FIX: Add the import routes here
        $middleware->validateCsrfTokens(except: [
            'user/*',
            'api/data-import/chunked/*', // <--- ADD THIS LINE
            'api/data-import/*'          // <--- AND THIS ONE FOR SAFETY
        ]);

        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        // Clean up: You don't need to manually prepend EnsureFrontendRequestsAreStateful
        // if you are calling ->statefulApi() below. It handles it for you.
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

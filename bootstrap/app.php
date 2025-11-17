<?php

use App\Component\Transaction\Domain\Exception\InsufficientBalanceException;
use App\Component\Transaction\Domain\Exception\UserNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/auth.php'));

            Route::middleware('api')
                ->prefix('api/v1')
                ->name('api.v1.')
                ->group(base_path('app/Component/Transaction/Resource/routes.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(fn (InsufficientBalanceException $e, Request $request) =>
            $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], 422)
                : null
        );

        $exceptions->renderable(fn (UserNotFoundException $e, Request $request) =>
            $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], 404)
                : null
        );
    })->create();

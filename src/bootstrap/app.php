<?php

use App\Application\Customer\CreateCustomer\CustomerAlreadyExists;
use App\Application\Customer\GetCustomer\CustomerNotFound;
use Illuminate\Http\JsonResponse;
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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(
        fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
    );

    $exceptions->render(
        function (CustomerAlreadyExists $exception): JsonResponse {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 409);
        }
    );

    $exceptions->render(
        function (CustomerNotFound $exception): JsonResponse {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    );
})
->create();

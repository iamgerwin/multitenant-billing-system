<?php

use App\Http\Middleware\EnsureTenantContext;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => EnsureTenantContext::class,
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Token-based API authentication - CSRF not required for Bearer token auth
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log all exceptions
        $exceptions->report(function (\Throwable $e) {
            // Custom reporting logic if needed
        });

        // Render exceptions - don't show debug info in production
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $statusCode = 500;
                $message = 'An error occurred processing your request.';

                // Get actual status code if available
                if ($e instanceof HttpException) {
                    $statusCode = $e->getStatusCode();
                } elseif (method_exists($e, 'getStatusCode')) {
                    $statusCode = $e->getStatusCode();
                }

                // In production, don't expose detailed error info
                if (app()->isProduction()) {
                    return response()->json([
                        'message' => $message,
                        'errors' => $statusCode === 422 ? $e->errors() ?? [] : [],
                    ], $statusCode);
                }

                // In development, include more details
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $statusCode === 422 ? $e->errors() ?? [] : [],
                    'debug' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ],
                ], $statusCode);
            }
        });
    })->create();

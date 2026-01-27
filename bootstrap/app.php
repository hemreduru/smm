<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, \Illuminate\Http\Request $request) {
            // Let Laravel handle authorization/validation exceptions normally
            if ($e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Illuminate\Validation\ValidationException ||
                $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return null;
            }

            $userId = auth()->id() ?? 'guest';
            \Illuminate\Support\Facades\Log::error("Global Exception: User: {$userId} - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // In debug mode, let the developer see the error
            if (config('app.debug')) {
               return null;
            }

            return (new \App\Core\Results\ServerErrorResult(
                __('messages.server_error')
            ))->toResponse($request);
        });
    })->create();

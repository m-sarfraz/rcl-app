<?php

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
            'permission'  => \App\Http\Middleware\CheckPermission::class,
            'scoring.key' => \App\Http\Middleware\VerifyScoringToken::class,
        ]);

        /* The mobile app is a different origin — CORS must run before anything else. */
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         | Everything under /api/* answers in the ApiResponse envelope, whatever
         | blew up. Without this Laravel renders HTML for anything that is not a
         | ValidationException, and the mobile client sees an unparseable body.
         */
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return match (true) {
                $e instanceof ValidationException => ApiResponse::error(
                    'The given data was invalid.', 422, $e->errors(), 'validation_failed'
                ),

                $e instanceof AuthenticationException => ApiResponse::error(
                    'Unauthenticated.', 401, [], 'unauthenticated'
                ),

                $e instanceof AuthorizationException => ApiResponse::error(
                    $e->getMessage() ?: 'This action is unauthorized.', 403, [], 'forbidden'
                ),

                $e instanceof ModelNotFoundException => ApiResponse::error(
                    'Resource not found.', 404, [], 'not_found'
                ),

                $e instanceof NotFoundHttpException => ApiResponse::error(
                    'Endpoint not found.', 404, [], 'not_found'
                ),

                $e instanceof TooManyRequestsHttpException => ApiResponse::error(
                    'Too many attempts. Please slow down.', 429, [], 'rate_limited'
                ),

                $e instanceof HttpExceptionInterface => ApiResponse::error(
                    $e->getMessage() ?: 'Request failed.', $e->getStatusCode(), [], 'http_error'
                ),

                default => ApiResponse::error(
                    config('app.debug') ? $e->getMessage() : 'Something went wrong on our end.',
                    500,
                    config('app.debug')
                        ? ['exception' => $e::class, 'file' => $e->getFile(), 'line' => $e->getLine()]
                        : [],
                    'server_error'
                ),
            };
        });
    })->create();

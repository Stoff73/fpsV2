<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Helpers\JsonResponseHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle API exceptions with JSON responses
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions with consistent JSON responses.
     */
    protected function handleApiException(Throwable $exception, Request $request): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return JsonResponseHelper::validationError(
                $exception->errors(),
                'The given data was invalid.'
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            return JsonResponseHelper::notFound('Resource not found');
        }

        if ($exception instanceof NotFoundHttpException) {
            return JsonResponseHelper::notFound('Endpoint not found');
        }

        if ($exception instanceof AuthenticationException) {
            return JsonResponseHelper::unauthorized('Unauthenticated');
        }

        // Default error response
        $statusCode = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : 500;

        $message = $exception->getMessage() ?: 'An error occurred';

        // Don't expose internal errors in production
        if ($statusCode === 500 && ! config('app.debug')) {
            $message = 'Internal server error';
        }

        return JsonResponseHelper::error($message, $statusCode);
    }
}

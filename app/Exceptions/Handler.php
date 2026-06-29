<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use DomainException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        // Normalize common exceptions for API routes to avoid leaking debug details
        $this->renderable(function (Throwable $e, $request) {
            $isApi = $request->expectsJson() || $request->is('api/*') || str_starts_with($request->path(), 'api');
            if (! $isApi) {
                return null;
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException || ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 404)) {
                return response()->json(['message' => 'Resource not found'], 404);
            }

            if ($e instanceof DomainException) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return null;
        });
    }

    /**
     * Convert certain exceptions into more generic HTTP exceptions
     * so that API responses do not leak debug details.
     */
    protected function prepareException(Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return new NotFoundHttpException('Resource not found', $e);
        }

        // If a NotFoundHttpException was created from a ModelNotFoundException earlier,
        // normalize its message so API responses don't leak model internals.
        if ($e instanceof NotFoundHttpException && $e->getPrevious() instanceof ModelNotFoundException) {
            return new NotFoundHttpException('Resource not found', $e->getPrevious());
        }

        return parent::prepareException($e);
    }

    public function render($request, Throwable $e)
    {
        // If request expects JSON or is an API route, return structured JSON responses
        if ($request->expectsJson() || $request->is('api/*') || str_starts_with($request->path(), 'api')) {
            // Validation errors
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json(['message' => $e->getMessage() ?: 'Forbidden'], 403);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json(['message' => 'Resource not found'], 404);
            }

            if ($e instanceof DomainException) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            if ($e instanceof HttpExceptionInterface) {
                $message = $e->getStatusCode() === 404
                    ? 'Resource not found'
                    : ($e->getMessage() ?: 'HTTP Error');
                return response()->json(['message' => $message], $e->getStatusCode());
            }

            // Fallback - don't leak exception details in production
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            return response()->json(['message' => $e->getMessage() ?: 'Server Error'], $status);
        }

        return parent::render($request, $e);
    }
}

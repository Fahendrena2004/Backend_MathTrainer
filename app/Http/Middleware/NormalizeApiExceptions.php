<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class NormalizeApiExceptions
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            $isApi = $request->expectsJson() || $request->is('api/*') || str_starts_with($request->path(), 'api');
            if (! $isApi) {
                throw $e;
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json(['message' => 'Resource not found'], 404);
            }

            // Re-throw other exceptions so the global handler still processes them
            throw $e;
        }
    }
}

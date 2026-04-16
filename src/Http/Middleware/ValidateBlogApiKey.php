<?php

namespace Noeticit\AdminBlog\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateBlogApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configKey = config('blog.api.key');

        if (empty($configKey)) {
            return $this->unauthorized('Blog API key is not configured.');
        }

        $requestKey = $request->header('X-Blog-Api-Key')
            ?? $request->header('X-API-Key')
            ?? $request->query('api_key');

        if (! $requestKey || ! hash_equals($configKey, $requestKey)) {
            return $this->unauthorized('Invalid API key.');
        }

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json(['error' => $message], 401);
    }
}

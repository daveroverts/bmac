<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds RFC 8594 deprecation headers to legacy unversioned API responses.
 * Consumers should migrate to /api/v1/*.
 */
class DeprecatedApiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Deprecation', 'true');
        $response->headers->set('Sunset', 'Wed, 31 Dec 2026 23:59:59 GMT');
        $response->headers->set('Link', url('/api/v1') . '; rel="successor-version"');

        return $response;
    }
}

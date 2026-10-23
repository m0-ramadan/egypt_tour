<?php

namespace App\Http\Middleware;

use App\Support\LocaleNormalizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Supports locale-prefixed URLs such as /de/tour-packages/42 or /fr/cruise/27.
 *
 * The prefix is removed from the incoming path *before* routing, so the normal
 * (unprefixed) routes handle the request and the response is served in the
 * requested language at the requested URL — no redirect is issued.
 */
class StripLocalePrefix
{
    public function __construct(private readonly LocaleNormalizer $localeNormalizer) {}

    public function handle(Request $request, Closure $next): Response
    {
        $path = ltrim((string) $request->getPathInfo(), '/');

        if ($path === '') {
            return $next($request);
        }

        $segments = explode('/', $path);
        $candidate = strtolower(rawurldecode($segments[0]));

        if (! in_array($candidate, $this->localeNormalizer->supported(), true)) {
            return $next($request);
        }

        array_shift($segments);

        $request->attributes->set('locale_prefix', $candidate);
        $this->replacePath($request, '/' . implode('/', $segments));

        return $next($request);
    }

    /**
     * Rewrite the request URI and clear Symfony's cached path information so
     * the router sees the unprefixed URL.
     */
    private function replacePath(Request $request, string $path): void
    {
        $queryString = (string) $request->server->get('QUERY_STRING', '');
        $request->server->set('REQUEST_URI', $path . ($queryString !== '' ? '?' . $queryString : ''));

        foreach (['requestUri', 'pathInfo', 'baseUrl'] as $property) {
            try {
                $reflection = new \ReflectionProperty(SymfonyRequest::class, $property);
                $reflection->setAccessible(true);
                $reflection->setValue($request, null);
            } catch (\Throwable) {
                // Property layout differs between Symfony versions; the URI
                // in the server bag is still correct for the router.
            }
        }
    }
}

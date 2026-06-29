<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HomepageCache
{
    private const CSRF_PLACEHOLDER = 'SANISO_CSRF_TOKEN_PLACEHOLDER';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldUseCache($request)) {
            return $next($request);
        }

        $cacheKey = $this->cacheKey($request);

        if ($cached = Cache::get($cacheKey)) {
            return response($this->hydrateCsrfToken($cached), 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Saniso-Page-Cache' => 'HIT',
            ]);
        }

        $response = $next($request);

        if ($this->shouldStoreResponse($response)) {
            Cache::put(
                $cacheKey,
                $this->dehydrateCsrfToken((string) $response->getContent()),
                (int) config('saniso-performance.homepage_cache.ttl', 300)
            );

            $response->headers->set('X-Saniso-Page-Cache', 'MISS');
        }

        return $response;
    }

    private function shouldUseCache(Request $request): bool
    {
        if (! config('saniso-performance.homepage_cache.enabled', true)) {
            return false;
        }

        if (
            ! $request->isMethodCacheable()
            || $request->expectsJson()
            || (! $request->acceptsAnyContentType() && ! $request->accepts('text/html'))
            || $request->ajax()
        ) {
            return false;
        }

        if ($request->query->count() > 0 || ! in_array(trim($request->path(), '/'), ['', '/'], true)) {
            return false;
        }

        if (auth()->check()) {
            return false;
        }

        return ! $this->hasPersonalizedSessionState($request);
    }

    private function hasPersonalizedSessionState(Request $request): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        $session = $request->session();

        foreach (['cart.cart', 'cart.wishlist', 'cart.compare', 'cart.recently_viewed', 'currency'] as $key) {
            if ($session->has($key)) {
                return true;
            }
        }

        return false;
    }

    private function shouldStoreResponse(Response $response): bool
    {
        return $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }

    private function cacheKey(Request $request): string
    {
        return implode(':', [
            'saniso_homepage_html',
            app()->getLocale(),
            md5($request->getSchemeAndHttpHost() . '/'),
        ]);
    }

    private function dehydrateCsrfToken(string $content): string
    {
        return str_replace(csrf_token(), self::CSRF_PLACEHOLDER, $content);
    }

    private function hydrateCsrfToken(string $content): string
    {
        return str_replace(self::CSRF_PLACEHOLDER, csrf_token(), $content);
    }
}

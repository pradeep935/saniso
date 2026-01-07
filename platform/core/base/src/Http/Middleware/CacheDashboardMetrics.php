<?php

namespace Botble\Base\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheDashboardMetrics extends \Illuminate\Http\Middleware
{
    /**
     * Cache dashboard API endpoints to reduce database load.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only cache GET requests to dashboard endpoints
        if ($request->method() !== 'GET' || !$this->isDashboardRoute($request)) {
            return $next($request);
        }

        $cacheKey = 'dashboard_response_' . md5($request->getRequestUri());
        
        // Check cache first (5 minute TTL for dashboard)
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $next($request);
        
        // Only cache successful responses
        if ($response->getStatusCode() === 200) {
            Cache::put($cacheKey, $response, 300);
        }

        return $response;
    }

    private function isDashboardRoute(Request $request): bool
    {
        return $request->is('admin*') && (
            $request->is('*/dashboard*') ||
            $request->is('*/quote-notifications/latest')
        );
    }
}

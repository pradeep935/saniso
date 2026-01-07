<?php

namespace Botble\AffiliatePro\Http\Middleware;

use Botble\AffiliatePro\Services\AffiliateTrackingService;
use Closure;
use Illuminate\Http\Request;

class AffiliateTrackingMiddleware
{
    public function __construct(protected AffiliateTrackingService $trackingService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $affiliateCode = $request->query('aff');

        if ($affiliateCode) {
            $this->trackingService->handleAffiliateTracking($request, $affiliateCode);
        }

        return $next($request);
    }
}

<?php

namespace Botble\AffiliatePro\Http\Middleware;

use Botble\AffiliatePro\Models\Affiliate;
use Closure;
use Illuminate\Http\Request;

class CheckAffiliateBanned
{
    public function handle(Request $request, Closure $next)
    {
        $customer = auth('customer')->user();

        if ($customer) {
            $affiliate = Affiliate::query()->where('customer_id', $customer->id)->first();

            if ($affiliate && $affiliate->isBanned()) {
                if ($request->routeIs('affiliate-pro.banned')) {
                    return $next($request);
                }

                return redirect()->route('affiliate-pro.banned');
            }
        }

        return $next($request);
    }
}

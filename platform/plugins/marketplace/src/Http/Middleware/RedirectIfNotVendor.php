<?php

namespace Botble\Marketplace\Http\Middleware;

use Botble\Marketplace\Facades\MarketplaceHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotVendor
{
    public function handle(Request $request, Closure $next, string $guard = 'customer')
    {
        if (! Auth::guard($guard)->check() || ! Auth::guard($guard)->user()->is_vendor) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }

            // Redirect to customer login instead of admin login
            return redirect()->guest(route('customer.login'));
        }

        $customer = Auth::guard($guard)->user();
        
        if (MarketplaceHelper::getSetting('verify_vendor', true) &&
            ! $customer->vendor_verified_at) {
            if ($request->ajax() || $request->wantsJson()) {
                return response(__('Vendor account is not verified.'), 403);
            }

            return redirect()->guest(route('marketplace.vendor.become-vendor'));
        }

        // Ensure the customer session is maintained across language changes
        if ($request->isMethod('PUT') && $request->route()->getName() === 'marketplace.vendor.language-settings.update') {
            // Refresh the authentication session
            Auth::guard($guard)->login($customer, true);
        }

        return $next($request);
    }
}

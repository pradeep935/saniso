<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Facades\AdminHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FallbackRouteController
{
    public function __invoke(Request $request, ?string $route = null): RedirectResponse
    {
        $prefix = (string) $request->route('fallback_prefix');
        $uri = $prefix . ($route ? '/' . $route : '');

        return redirect()->to(
            str_replace($uri, AdminHelper::getAdminPrefix() . '/' . $uri, $request->fullUrl())
        );
    }
}

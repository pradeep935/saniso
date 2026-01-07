<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Services\DashboardCacheService;

class InvalidateDashboardCache
{
    public function handleOrderChange(): void
    {
        \Illuminate\Support\Facades\Cache::forget(DashboardCacheService::CACHE_KEY_ORDERS);
    }

    public function handleProductChange(): void
    {
        \Illuminate\Support\Facades\Cache::forget(DashboardCacheService::CACHE_KEY_PRODUCTS);
    }

    public function handleCustomerChange(): void
    {
        \Illuminate\Support\Facades\Cache::forget(DashboardCacheService::CACHE_KEY_CUSTOMERS);
    }

    public function handleReviewChange(): void
    {
        \Illuminate\Support\Facades\Cache::forget(DashboardCacheService::CACHE_KEY_REVIEWS);
    }

    public function handleQuoteChange(): void
    {
        \Illuminate\Support\Facades\Cache::forget(DashboardCacheService::CACHE_KEY_QUOTES);
    }

    public function handleProjectChange(): void
    {
        \Illuminate\Support\Facades\Cache::forget(DashboardCacheService::CACHE_KEY_PROJECTS);
    }
}

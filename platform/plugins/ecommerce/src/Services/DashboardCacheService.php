<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProjectRequest;
use Botble\Ecommerce\Models\QuoteRequest;
use Botble\Ecommerce\Models\Review;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    const CACHE_KEY_ORDERS = 'dashboard_widget_orders_count';
    const CACHE_KEY_PRODUCTS = 'dashboard_widget_products_count';
    const CACHE_KEY_CUSTOMERS = 'dashboard_widget_customers_count';
    const CACHE_KEY_REVIEWS = 'dashboard_widget_reviews_count';
    const CACHE_KEY_QUOTES = 'dashboard_widget_quotes_count';
    const CACHE_KEY_PROJECTS = 'dashboard_widget_projects_count';
    const CACHE_TTL = 3600; // 1 hour

    public static function getOrderCount(): int
    {
        return Cache::remember(
            self::CACHE_KEY_ORDERS,
            self::CACHE_TTL,
            fn () => Order::query()->where('is_finished', 1)->count()
        );
    }

    public static function getProductCount(): int
    {
        return Cache::remember(
            self::CACHE_KEY_PRODUCTS,
            self::CACHE_TTL,
            fn () => Product::query()
                ->where('is_variation', false)
                ->wherePublished()
                ->count()
        );
    }

    public static function getCustomerCount(): int
    {
        return Cache::remember(
            self::CACHE_KEY_CUSTOMERS,
            self::CACHE_TTL,
            fn () => Customer::query()->count()
        );
    }

    public static function getReviewCount(): int
    {
        return Cache::remember(
            self::CACHE_KEY_REVIEWS,
            self::CACHE_TTL,
            fn () => Review::query()->wherePublished()->count()
        );
    }

    public static function getQuoteCount(): int
    {
        return Cache::remember(
            self::CACHE_KEY_QUOTES,
            self::CACHE_TTL,
            fn () => QuoteRequest::query()->count()
        );
    }

    public static function getProjectCount(): int
    {
        return Cache::remember(
            self::CACHE_KEY_PROJECTS,
            self::CACHE_TTL,
            fn () => ProjectRequest::query()->count()
        );
    }

    public static function clearAllCache(): void
    {
        Cache::forget(self::CACHE_KEY_ORDERS);
        Cache::forget(self::CACHE_KEY_PRODUCTS);
        Cache::forget(self::CACHE_KEY_CUSTOMERS);
        Cache::forget(self::CACHE_KEY_REVIEWS);
        Cache::forget(self::CACHE_KEY_QUOTES);
        Cache::forget(self::CACHE_KEY_PROJECTS);
    }
}

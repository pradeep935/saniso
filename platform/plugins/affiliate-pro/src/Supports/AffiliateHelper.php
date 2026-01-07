<?php

namespace Botble\AffiliatePro\Supports;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Media\Facades\RvMedia;
use Botble\Theme\Facades\Theme;

class AffiliateHelper
{
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return setting('affiliate_' . $key, $default);
    }

    public function isCommissionCategoryFeeBasedEnabled(): bool
    {
        return (bool) $this->getSetting('enable_commission_for_each_category', false);
    }

    public function getCommissionEachCategory(): array
    {
        $commissions = [];

        if (! $this->isCommissionCategoryFeeBasedEnabled()) {
            return $commissions;
        }

        $categoryCommission = $this->getSetting('commission_by_category');

        if (! $categoryCommission) {
            return $commissions;
        }

        $categoryCommission = json_decode($categoryCommission, true);

        if (empty($categoryCommission)) {
            return $commissions;
        }

        foreach ($categoryCommission as $commission) {
            if (empty($commission['categories'])) {
                continue;
            }

            $categories = json_decode($commission['categories'], true);

            if (empty($categories)) {
                continue;
            }

            $categoryData = [];
            foreach ($categories as $category) {
                // Handle different possible formats of category data
                $categoryId = null;

                if (is_array($category) && isset($category['id'])) {
                    // If it's an array with an 'id' key
                    $categoryId = $category['id'];
                } elseif (is_numeric($category)) {
                    // If it's already a numeric ID
                    $categoryId = $category;
                }

                if ($categoryId) {
                    $categoryInfo = ProductCategory::query()->find($categoryId);
                    if ($categoryInfo) {
                        $categoryData[] = [
                            'id' => $categoryInfo->id,
                            'value' => $categoryInfo->name,
                        ];
                    }
                }
            }

            $commissions[$commission['commission_percentage']] = [
                'commission_percentage' => $commission['commission_percentage'],
                'categories' => $categoryData,
            ];
        }

        return $commissions;
    }

    protected function getCategoriesFromIds(array $categoryIds): array
    {
        $categories = [];

        // Ensure we have a flat array of category IDs
        $flatCategoryIds = [];
        foreach ($categoryIds as $categoryId) {
            if (is_array($categoryId)) {
                // If it's an array, extract the ID value
                if (isset($categoryId['value'])) {
                    $flatCategoryIds[] = $categoryId['value'];
                }
            } else {
                // If it's already a scalar value, use it directly
                $flatCategoryIds[] = $categoryId;
            }
        }

        $flatCategoryIds = array_filter($flatCategoryIds);

        if (empty($flatCategoryIds)) {
            return $categories;
        }

        return ProductCategory::query()
            ->whereIn('id', $flatCategoryIds)
            ->pluck('name', 'id')
            ->all();
    }

    public function getCommissionPercentage(?int $productId = null, ?Affiliate $affiliate = null): float
    {
        // If affiliate has a custom commission rate, use it as the base rate
        if ($affiliate && $affiliate->commission_rate !== null && $affiliate->commission_rate > 0) {
            $defaultCommissionPercentage = (float) $affiliate->commission_rate;
        } else {
            // Otherwise use the default commission percentage from settings
            $defaultCommissionPercentage = (float) $this->getSetting('commission_percentage', 10);
        }

        if (! $productId) {
            return $defaultCommissionPercentage;
        }

        $product = Product::query()->find($productId);

        if (! $product) {
            return $defaultCommissionPercentage;
        }

        // Check if affiliate is enabled for this product
        if (isset($product->is_affiliate_enabled) && ! $product->is_affiliate_enabled) {
            return 0;
        }

        // Check for product-specific commission percentage
        if ($product->affiliate_commission_percentage > 0) {
            return (float) $product->affiliate_commission_percentage;
        }

        // If category-based commission is not enabled, return default
        if (! $this->isCommissionCategoryFeeBasedEnabled()) {
            return $defaultCommissionPercentage;
        }

        $categoryCommission = $this->getSetting('commission_by_category');

        if (! $categoryCommission) {
            return $defaultCommissionPercentage;
        }

        $categoryCommission = json_decode($categoryCommission, true);

        if (empty($categoryCommission)) {
            return $defaultCommissionPercentage;
        }

        $productCategoryIds = $product->categories()->pluck('id')->all();

        if (empty($productCategoryIds)) {
            return $defaultCommissionPercentage;
        }

        $commissionPercentage = $defaultCommissionPercentage;

        foreach ($categoryCommission as $commission) {
            if (empty($commission['categories'])) {
                continue;
            }

            $categoryIds = json_decode($commission['categories'], true);

            if (empty($categoryIds)) {
                continue;
            }

            // Ensure we have a flat array of category IDs
            $flatCategoryIds = [];
            foreach ($categoryIds as $categoryId) {
                if (is_array($categoryId)) {
                    // If it's an array, extract the ID value
                    if (isset($categoryId['value'])) {
                        $flatCategoryIds[] = $categoryId['value'];
                    }
                } else {
                    // If it's already a scalar value, use it directly
                    $flatCategoryIds[] = $categoryId;
                }
            }

            foreach ($flatCategoryIds as $categoryId) {
                if (in_array($categoryId, $productCategoryIds)) {
                    $commissionPercentage = (float) $commission['commission_percentage'];

                    break 2;
                }
            }
        }

        return $commissionPercentage;
    }

    public function getMinimumWithdrawalAmount(): float
    {
        return (float) $this->getSetting('minimum_withdrawal_amount', 50);
    }

    public function getCookieLifetime(): int
    {
        return (int) $this->getSetting('cookie_lifetime', 30);
    }

    public function isRegistrationEnabled(): bool
    {
        return (bool) $this->getSetting('enable_registration', true);
    }

    public function isAutoApproveAffiliatesEnabled(): bool
    {
        return (bool) $this->getSetting('auto_approve_affiliates', false);
    }

    public function isAutoApproveCommissionsEnabled(): bool
    {
        return (bool) $this->getSetting('auto_approve_commissions', false);
    }

    public function getAffiliateByCustomerId(int $customerId): ?Affiliate
    {
        return Affiliate::query()
            ->where('customer_id', $customerId)
            ->first();
    }

    public function getActiveAffiliateByCustomerId(int $customerId): ?Affiliate
    {
        return Affiliate::query()
            ->where('customer_id', $customerId)
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->first();
    }

    public function getPromotionalBanners(Affiliate $affiliate): array
    {
        $banners = [];

        $banner1Name = $this->getSetting('banner_1_name', 'Banner 1 (468x60)');
        $banner1Image = $this->getSetting('banner_1_image');
        if ($banner1Image) {
            $banners[] = [
                'name' => $banner1Name,
                'image' => $banner1Image,
                'html' => '<a href="' . url('?aff=' . $affiliate->affiliate_code) . '"><img src="' . RvMedia::getImageUrl($banner1Image) . '" alt="' . $banner1Name . '" /></a>',
            ];
        }

        $banner2Name = $this->getSetting('banner_2_name', 'Banner 2 (300x250)');
        $banner2Image = $this->getSetting('banner_2_image');
        if ($banner2Image) {
            $banners[] = [
                'name' => $banner2Name,
                'image' => $banner2Image,
                'html' => '<a href="' . url('?aff=' . $affiliate->affiliate_code) . '"><img src="' . RvMedia::getImageUrl($banner2Image) . '" alt="' . $banner2Name . '" /></a>',
            ];
        }

        $banner3Name = $this->getSetting('banner_3_name', 'Banner 3 (728x90)');
        $banner3Image = $this->getSetting('banner_3_image');
        if ($banner3Image) {
            $banners[] = [
                'name' => $banner3Name,
                'image' => $banner3Image,
                'html' => '<a href="' . url('?aff=' . $affiliate->affiliate_code) . '"><img src="' . RvMedia::getImageUrl($banner3Image) . '" alt="' . $banner3Name . '" /></a>',
            ];
        }

        return $banners;
    }

    public function registerAssets(): void
    {
        $version = '1.1.2';

        Theme::asset()
            ->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: $version);
        Theme::asset()
            ->add('front-ecommerce-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce.css', version: $version);

        Theme::asset()->add('select2-css', 'vendor/core/core/base/libraries/select2/css/select2.min.css', version: $version);

        Theme::asset()->add('affiliate-css', 'vendor/core/plugins/affiliate-pro/css/front-affiliate.css', version: $version);
        Theme::asset()->add('affiliate-short-links-css', 'vendor/core/plugins/affiliate-pro/css/short-links.css', version: $version);

        Theme::asset()
            ->container('footer')
            ->usePath(false)
            ->add('select2-js', 'vendor/core/core/base/libraries/select2/js/select2.min.js', ['jquery'], version: $version);

        // Add affiliate JavaScript files
        Theme::asset()
            ->container('footer')
            ->usePath(false)
            ->add('front-affiliate-js', 'vendor/core/plugins/affiliate-pro/js/front-affiliate.js', ['jquery', 'select2-js'], version: $version);
        Theme::asset()
            ->container('footer')
            ->usePath(false)
            ->add('short-links-management-js', 'vendor/core/plugins/affiliate-pro/js/short-links-management.js', ['jquery', 'select2-js'], version: $version);
    }

    public function viewPath(string $view): string
    {
        $themeView = Theme::getThemeNamespace() . '::views.affiliate.' . $view;

        if (view()->exists($themeView)) {
            return $themeView;
        }

        return 'plugins/affiliate-pro::themes.' . $view;
    }
}

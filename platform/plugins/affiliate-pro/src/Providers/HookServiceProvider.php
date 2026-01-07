<?php

namespace Botble\AffiliatePro\Providers;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Enums\WithdrawalStatusEnum;
use Botble\AffiliatePro\Facades\AffiliateHelper;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Models\Product;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_action(BASE_ACTION_META_BOXES, function ($context, $object) {
            if (
                ! $object
                || $context != 'advanced'
                || ! is_in_admin()
                || ! $object instanceof Product
                || ! in_array(Route::currentRouteName(), [
                    'products.create',
                    'products.edit',
                ])
            ) {
                return false;
            }

            MetaBox::addMetaBox(
                'affiliate_settings_wrap',
                trans('plugins/affiliate-pro::affiliate.affiliate_settings'),
                [$this, 'addAffiliateSettingsFields'],
                get_class($object),
                $context
            );

            return true;
        }, 24, 2);

        add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'saveAffiliateFields'], 230, 3);
        add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'saveAffiliateFields'], 231, 3);

        add_filter('ecommerce_after_product_description', [$this, 'showAffiliateCommissionInfo'], 10, 2);

        add_filter(BASE_FILTER_APPEND_MENU_NAME, [$this, 'getPendingRequestsCount'], 130, 2);
        add_filter(BASE_FILTER_MENU_ITEMS_COUNT, [$this, 'getMenuItemCount'], 121);
    }

    public function addAffiliateSettingsFields(Product $product): string
    {
        $isAffiliateEnabled = $product->is_affiliate_enabled ?? true;
        $commissionPercentage = $product->affiliate_commission_percentage ?? 0;

        return view('plugins/affiliate-pro::product-affiliate-fields', compact('product', 'isAffiliateEnabled', 'commissionPercentage'))->render();
    }

    public function saveAffiliateFields(string $screen, Request $request, $object): void
    {
        if (! $object instanceof Product) {
            return;
        }

        $object->is_affiliate_enabled = (bool) $request->input('is_affiliate_enabled');
        $object->affiliate_commission_percentage = $request->input('affiliate_commission_percentage');
        $object->save();
    }

    public function showAffiliateCommissionInfo(?string $html, $product): string
    {
        // Check if customer is logged in
        if (! Auth::guard('customer')->check()) {
            return $html ?? '';
        }

        $customer = Auth::guard('customer')->user();

        $affiliate = AffiliateHelper::getActiveAffiliateByCustomerId($customer->id);

        if (! $affiliate) {
            return $html ?? '';
        }

        if (isset($product->is_affiliate_enabled) && ! $product->is_affiliate_enabled) {
            return $html ?? '';
        }

        $commissionPercentage = AffiliateHelper::getCommissionPercentage($product->id);

        if ($commissionPercentage <= 0) {
            return $html ?? '';
        }

        $productPrice = $product->price()->getPrice();
        $commissionAmount = $productPrice * ($commissionPercentage / 100);

        $currentUrl = request()->url();
        $affiliateLinkForProduct = $currentUrl . '?aff=' . $affiliate->affiliate_code;

        $version = '1.1.0';

        Theme::asset()->add('affiliate-commission-info-css', 'vendor/core/plugins/affiliate-pro/css/affiliate-commission-info.css', version: $version);

        Theme::asset()
            ->container('footer')
            ->usePath(false)
            ->add('affiliate-commission-info-js', 'vendor/core/plugins/affiliate-pro/js/affiliate-commission-info.js', ['jquery'], version: $version);

        $affiliateHtml = view('plugins/affiliate-pro::themes.affiliate-commission-info', [
            'affiliate' => $affiliate,
            'product' => $product,
            'commissionPercentage' => $commissionPercentage,
            'commissionAmount' => $commissionAmount,
            'affiliateLink' => $affiliateLinkForProduct,
            'productPrice' => $productPrice,
        ])->render();

        return ($html ?? '') . $affiliateHtml;
    }

    public function getPendingRequestsCount(string|int|null $number, string $menuId): int|string|null
    {
        switch ($menuId) {
            case 'cms-plugins-affiliate-pro-pending':
                if (! Auth::user()->hasPermission('affiliate-pro.edit')) {
                    return $number;
                }

                return view('core/base::partials.navbar.badge-count', ['class' => 'pending-affiliate-requests'])->render();

            case 'cms-plugins-affiliate-pro-withdrawals':
                if (! Auth::user()->hasPermission('affiliate.withdrawals.index')) {
                    return $number;
                }

                return view('core/base::partials.navbar.badge-count', ['class' => 'pending-affiliate-withdrawals'])->render();

            case 'cms-plugins-affiliate-pro':
                if (
                    ! Auth::user()->hasAnyPermission([
                        'affiliate-pro.edit',
                        'affiliate.withdrawals.index',
                    ])
                ) {
                    return $number;
                }

                return view('core/base::partials.navbar.badge-count', ['class' => 'affiliate-pro-notifications-count'])->render();
        }

        return $number;
    }

    public function getMenuItemCount(array $data = []): array
    {
        if (! Auth::check()) {
            return $data;
        }

        $countPendingRequests = 0;

        if (Auth::user()->hasPermission('affiliate-pro.edit')) {
            $countPendingRequests = Affiliate::query()
                ->where('status', AffiliateStatusEnum::PENDING)
                ->count();

            $data[] = [
                'key' => 'pending-affiliate-requests',
                'value' => $countPendingRequests,
            ];
        }

        $countPendingWithdrawals = 0;

        if (Auth::user()->hasPermission('affiliate.withdrawals.index')) {
            $countPendingWithdrawals = Withdrawal::query()
                ->whereIn('status', [WithdrawalStatusEnum::PENDING, WithdrawalStatusEnum::PROCESSING])
                ->count();

            $data[] = [
                'key' => 'pending-affiliate-withdrawals',
                'value' => $countPendingWithdrawals,
            ];
        }

        if (Auth::user()->hasAnyPermission(['affiliate-pro.edit', 'affiliate.withdrawals.index'])) {
            $data[] = [
                'key' => 'affiliate-pro-notifications-count',
                'value' => $countPendingRequests + $countPendingWithdrawals,
            ];
        }

        return $data;
    }
}

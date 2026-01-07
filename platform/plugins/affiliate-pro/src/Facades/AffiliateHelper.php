<?php

namespace Botble\AffiliatePro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed getSetting(string $key, mixed $default = null)
 * @method static bool isCommissionCategoryFeeBasedEnabled()
 * @method static array getCommissionEachCategory()
 * @method static float getCommissionPercentage(int $productId = null)
 * @method static float getMinimumWithdrawalAmount()
 * @method static int getCookieLifetime()
 * @method static bool isRegistrationEnabled()
 * @method static bool isAutoApproveAffiliatesEnabled()
 * @method static bool isAutoApproveCommissionsEnabled()
 * @method static array getPromotionalBanners(\Botble\AffiliatePro\Models\Affiliate $affiliate)
 * @method static \Botble\AffiliatePro\Models\Affiliate getActiveAffiliateByCustomerId(int $customerId)
 *
 * @see \Botble\AffiliatePro\Supports\AffiliateHelper
 */
class AffiliateHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'affiliate-helper';
    }
}

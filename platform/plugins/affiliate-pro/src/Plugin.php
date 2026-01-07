<?php

namespace Botble\AffiliatePro;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('affiliate_transactions');
        Schema::dropIfExists('affiliate_withdrawals');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_clicks');
        Schema::dropIfExists('affiliate_coupons');
        Schema::dropIfExists('affiliate_short_links');
        Schema::dropIfExists('affiliates');

        if (Schema::hasTable('ec_products')) {
            Schema::table('ec_products', function ($table) {
                if (Schema::hasColumn('ec_products', 'affiliate_commission_percentage')) {
                    $table->dropColumn('affiliate_commission_percentage');
                }

                if (Schema::hasColumn('ec_products', 'is_affiliate_enabled')) {
                    $table->dropColumn('is_affiliate_enabled');
                }
            });
        }

        $settingKeys = [
            'affiliate_commission_percentage',
            'affiliate_cookie_lifetime',
            'affiliate_minimum_withdrawal_amount',
            'affiliate_enable_registration',
            'affiliate_auto_approve_affiliates',
            'affiliate_auto_approve_commissions',
            'affiliate_enable_commission_for_each_category',
            'affiliate_commission_by_category',
            'affiliate_enable_short_links',
        ];

        foreach ($settingKeys as $key) {
            DB::table('settings')->where('key', $key)->delete();
        }

        cache()->flush();
    }
}

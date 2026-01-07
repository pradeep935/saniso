<?php

use Botble\Setting\Facades\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        if (! Setting::has('affiliate_rules_content')) {
            $defaultRulesContent = '
<div class="affiliate-rules">
    <h5>' . trans('plugins/affiliate-pro::affiliate.rules') . '</h5>
    <p>' . trans('plugins/affiliate-pro::affiliate.rules_agreement_intro') . '</p>
    <ul>
        <li>' . trans('plugins/affiliate-pro::affiliate.no_spam_rule') . '</li>
        <li>' . trans('plugins/affiliate-pro::affiliate.no_misleading_ads_rule') . '</li>
        <li>' . trans('plugins/affiliate-pro::affiliate.no_brand_bidding_rule') . '</li>
        <li>' . trans('plugins/affiliate-pro::affiliate.commission_confirmed_orders_rule') . '</li>
        <li>' . trans('plugins/affiliate-pro::affiliate.rejection_termination_rule') . '</li>
    </ul>
</div>';

            Setting::set('affiliate_rules_content', $defaultRulesContent);

            Setting::save();
        }
    }

    public function down(): void
    {
        Setting::delete('affiliate_rules_content');
    }
};

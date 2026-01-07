<div class="mb-3">
    <x-core::form-group>
        <x-core::form.label
            for="is_affiliate_enabled"
            :label="trans('plugins/affiliate-pro::affiliate.enable_affiliate')"
        />
        <div class="position-relative form-check-group">
            <x-core::form.radio
                name="is_affiliate_enabled"
                value="1"
                :checked="$isAffiliateEnabled"
                id="is_affiliate_enabled_1"
                data-bb-toggle="collapse"
                data-bb-target="#affiliate_commission_percentage_wrap"
            >
                {{ trans('core/base::base.yes') }}
            </x-core::form.radio>

            <x-core::form.radio
                name="is_affiliate_enabled"
                value="0"
                :checked="!$isAffiliateEnabled"
                id="is_affiliate_enabled_0"
                data-bb-toggle="collapse"
                data-bb-target="#affiliate_commission_percentage_wrap"
                data-bb-reverse
            >
                {{ trans('core/base::base.no') }}
            </x-core::form.radio>
        </div>
        <x-core::form.helper-text>
            {{ trans('plugins/affiliate-pro::product.enable_affiliate_helper') }}
        </x-core::form.helper-text>
    </x-core::form-group>

    <div id="affiliate_commission_percentage_wrap" class="mb-3" @if (!$isAffiliateEnabled) style="display: none;" @endif data-bb-value="1">
        <x-core::form.text-input
            type="number"
            name="affiliate_commission_percentage"
            :label="trans('plugins/affiliate-pro::affiliate.commission_percentage')"
            :value="$commissionPercentage"
            min="0"
            max="100"
            step="0.01"
            :helper-text="trans('plugins/affiliate-pro::affiliate.commission_percentage_helper')"
        >
            <x-slot:append>
                <span class="input-group-text">%</span>
            </x-slot:append>
        </x-core::form.text-input>
    </div>
</div>

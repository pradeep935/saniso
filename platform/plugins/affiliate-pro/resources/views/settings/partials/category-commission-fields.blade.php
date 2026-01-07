<x-core::form.fieldset
    class="category-commission-settings"
    @style(['display: none' => ! old('enable_commission_for_each_category', \Botble\AffiliatePro\Facades\AffiliateHelper::isCommissionCategoryFeeBasedEnabled())])
>
    <div class="commission-setting-item-wrapper">
        @if (!empty($commissionEachCategory))
            @foreach ($commissionEachCategory as $percentage => $commission)
                <div
                    class="row commission-setting-item"
                    id="commission-setting-item-{{ $loop->index }}"
                >
                    <div class="col-3">
                        <x-core::form.text-input
                            :label="trans('plugins/affiliate-pro::affiliate.commission_percentage')"
                            name="commission_by_category[{{ $loop->index }}][commission_percentage]"
                            type="number"
                            value="{{ $percentage }}"
                            min="0"
                            max="100"
                        />
                    </div>

                    <div class="col-9">
                        <x-core::form.label for="commission_percentage_for_each_category">
                            {{ trans('plugins/affiliate-pro::settings.categories') }}
                        </x-core::form.label>
                        <div class="row">
                            <div class="col-10">
                                <x-core::form.textarea
                                    class="tagify-commission-setting categories"
                                    name="commission_by_category[{{ $loop->index }}][categories]"
                                    rows="3"
                                    :value="$commission['categories'] ? json_encode($commission['categories']) : null"
                                    placeholder="{{ trans('plugins/affiliate-pro::settings.select_categories') }}"
                                >
                                    {{ Js::from($commission['categories'], true) }}
                                </x-core::form.textarea>
                            </div>
                            <div class="col-2">
                                @if ($loop->index > 0)
                                    <x-core::button
                                        data-bb-toggle="commission-remove"
                                        data-index="{{ $loop->index }}"
                                        icon="ti ti-trash"
                                    />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div
                class="row commission-setting-item"
                id="commission-setting-item-0"
            >
                <div class="col-3">
                    <x-core::form.text-input
                        :label="trans('plugins/affiliate-pro::affiliate.commission_percentage')"
                        name="commission_by_category[0][commission_percentage]"
                        type="number"
                        min="0"
                        max="100"
                    />
                </div>
                <div class="col-9">
                    <x-core::form.label
                        class="form-label"
                        for="commission_percentage_for_each_category"
                        :label="trans('plugins/affiliate-pro::settings.categories')"
                    />
                    <div class="row">
                        <div class="col-10">
                            <x-core::form.textarea
                                class="tagify-commission-setting"
                                name="commission_by_category[0][categories]"
                                rows="3"
                                :value="setting('affiliate_commission_by_category')"
                                placeholder="{{ trans('plugins/affiliate-pro::settings.select_categories') }}"
                            />
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <x-core::button color="primary" data-bb-toggle="commission-category-add">
        {{ trans('plugins/affiliate-pro::settings.add_new') }}
    </x-core::button>
</x-core::form.fieldset>

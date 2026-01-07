<div class="affiliate-commission-info mt-4 mb-4 p-4 border rounded-3 bg-light">
    <div class="d-flex align-items-center mb-3">
        <x-core::icon name="ti ti-percentage" class="text-success me-2" style="width: 24px; height: 24px;" />
        <h5 class="mb-0 text-success fw-bold">{{ trans('plugins/affiliate-pro::affiliate.affiliate_commission_info') }}</h5>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="commission-details">
                <p class="mb-2 text-muted small">{{ trans('plugins/affiliate-pro::affiliate.your_commission_rate') }}</p>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success fs-6 px-3 py-2">{{ number_format($commissionPercentage, 1) }}%</span>
                    <span class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.of_product_price') }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="commission-amount">
                <p class="mb-2 text-muted small">{{ trans('plugins/affiliate-pro::affiliate.commission_per_sale') }}</p>
                <div class="fw-bold text-success fs-5">
                    {{ format_price($commissionAmount) }}
                </div>
            </div>
        </div>
    </div>

    <div class="affiliate-actions mt-3 pt-3 border-top">
        <div class="row g-3 align-items-center">
            <div class="col-md-12">
                <label class="form-label small text-muted mb-1">{{ trans('plugins/affiliate-pro::affiliate.your_affiliate_link') }}</label>
                <div class="input-group">
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        value="{{ $affiliateLink }}"
                        readonly
                        id="affiliate-link-{{ $product->id }}"
                    >
                    <button
                        class="btn btn-success btn-sm"
                        type="button"
                        data-affiliate-copy-link="{{ $product->id }}"
                        title="{{ trans('plugins/affiliate-pro::affiliate.copy_link') }}"
                    >
                        <x-core::icon name="ti ti-copy" style="width: 16px; height: 16px;" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="affiliate-tip mt-3 p-3 bg-white rounded-2 border">
        <div class="d-flex align-items-start gap-2">
            <x-core::icon name="ti ti-info-circle" class="text-info mt-1" style="width: 18px; height: 18px;" />
            <div class="small text-muted">
                <strong>{{ trans('plugins/affiliate-pro::affiliate.tip') }}:</strong>
                {{ trans('plugins/affiliate-pro::affiliate.share_link_tip', ['amount' => format_price($commissionAmount)]) }}
            </div>
        </div>
    </div>
</div>

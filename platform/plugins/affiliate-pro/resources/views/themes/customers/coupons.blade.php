@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::affiliate.affiliate_coupons'))

@section('content')
    <div class="bb-customer-card-list affiliate-coupons-cards">
        {{-- Affiliate Status Card --}}
        @if ($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::PENDING)
            <div class="bb-customer-card affiliate-status-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.affiliate_status') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <span class="badge bg-warning text-dark">{{ trans('plugins/affiliate-pro::affiliate.pending') }}</span>
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="alert alert-warning mb-0">
                        <x-core::icon name="ti ti-clock" class="me-2" />
                        {{ trans('plugins/affiliate-pro::affiliate.pending_approval') }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Coupons Overview Card --}}
        <div class="bb-customer-card coupons-overview-card">
            <div class="bb-customer-card-header">
                <div class="bb-customer-card-title">
                    <span class="value">{{ trans('plugins/affiliate-pro::affiliate.affiliate_coupons') }}</span>
                </div>
                <div class="bb-customer-card-status">
                    <span class="badge bg-primary">{{ count($coupons) }}</span>
                </div>
            </div>
            <div class="bb-customer-card-body">
                <p class="text-muted mb-3">
                    <x-core::icon name="ti ti-info-circle" class="me-2" />
                    {{ trans('plugins/affiliate-pro::affiliate.view_affiliate_coupons') }}
                </p>

                @if ($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED)
                    @if(count($coupons) > 0)
                        <div class="bb-customer-card-list">
                            @foreach($coupons as $coupon)
                                <div class="bb-customer-card-content coupon-item">
                                    <div class="bb-customer-card-image">
                                        <div class="coupon-icon">
                                            <x-core::icon name="ti ti-ticket" class="text-primary" style="font-size: 2rem;" />
                                        </div>
                                    </div>
                                    <div class="bb-customer-card-details">
                                        <div class="bb-customer-card-name">
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-primary me-2">{{ $coupon->code }}</span>
                                                <button
                                                    class="btn btn-outline-primary btn-sm"
                                                    data-copy-coupon-code="{{ $coupon->code }}"
                                                    title="{{ trans('plugins/affiliate-pro::affiliate.copy_code') }}"
                                                >
                                                    <x-core::icon name="ti ti-copy" />
                                                </button>
                                            </div>
                                        </div>

                                        <div class="bb-customer-card-meta">
                                            @if($coupon->description)
                                                <div class="coupon-description mb-2">
                                                    <span class="text-muted">{{ $coupon->description }}</span>
                                                </div>
                                            @endif

                                            <div class="coupon-details">
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <div class="info-item">
                                                            <span class="label">{{ trans('plugins/affiliate-pro::affiliate.discount') }}:</span>
                                                            <span class="value fw-bold text-success">
                                                                @if($coupon->discount_type == 'percentage')
                                                                    {{ $coupon->discount_amount }}%
                                                                @else
                                                                    {{ format_price($coupon->discount_amount) }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="info-item">
                                                            <span class="label">{{ trans('plugins/affiliate-pro::affiliate.expires') }}:</span>
                                                            <span class="value">
                                                                @if($coupon->expires_at)
                                                                    {{ $coupon->expires_at->translatedFormat('M d, Y') }}
                                                                    @if($coupon->expires_at->isPast())
                                                                        <span class="badge bg-danger ms-1">{{ trans('plugins/affiliate-pro::affiliate.expired') }}</span>
                                                                    @else
                                                                        <span class="badge bg-success ms-1">{{ trans('plugins/affiliate-pro::affiliate.active') }}</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.never') }}</span>
                                                                    <span class="badge bg-info ms-1">{{ trans('plugins/affiliate-pro::affiliate.permanent') }}</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="info-item">
                                                            <span class="label">{{ trans('plugins/affiliate-pro::affiliate.created') }}:</span>
                                                            <span class="value">{{ $coupon->created_at->translatedFormat('M d, Y') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if (!$loop->last)
                                    <hr class="my-3">
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center p-4">
                            <div class="empty-state">
                                <x-core::icon name="ti ti-ticket-off" class="text-muted mb-3" style="font-size: 3rem;" />
                                <h5 class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_coupons_available') }}</h5>
                                <p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_coupons_created') }}</p>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- How to Use Card --}}
        @if ($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED && count($coupons) > 0)
            <div class="bb-customer-card how-to-use-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.how_to_share_coupons') }}</span>
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-center">
                                <x-core::icon name="ti ti-copy" class="text-primary mb-2" style="font-size: 2rem;" />
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.copy_code') }}</h6>
                                <p class="text-muted small">{{ trans('plugins/affiliate-pro::affiliate.copy_code_instruction') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <x-core::icon name="ti ti-share" class="text-success mb-2" style="font-size: 2rem;" />
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.share_with_audience') }}</h6>
                                <p class="text-muted small">{{ trans('plugins/affiliate-pro::affiliate.share_audience_instruction') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <x-core::icon name="ti ti-coins" class="text-warning mb-2" style="font-size: 2rem;" />
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.earn_commission') }}</h6>
                                <p class="text-muted small">{{ trans('plugins/affiliate-pro::affiliate.earn_commission_instruction') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- JavaScript translations --}}
    <script>
    window.affiliateTranslations = window.affiliateTranslations || {};
    window.affiliateTranslations = {
        couponCopied: '{{ trans("plugins/affiliate-pro::affiliate.coupon_copied") }}',
        copyFailed: '{{ trans("plugins/affiliate-pro::affiliate.copy_failed") }}'
    };
    </script>



    {{-- CSS Styles --}}
    <style>
    .coupon-item {
        transition: all 0.3s ease;
        border-radius: 8px;
        padding: 1rem;
        background-color: #f8f9fa;
        margin-bottom: 1rem;
    }

    .coupon-item:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .coupon-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        margin-right: 1rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        margin-bottom: 0.5rem;
    }

    .info-item .label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }

    .info-item .value {
        font-size: 0.9rem;
        color: #212529;
        margin-top: 0.25rem;
    }

    .empty-state {
        padding: 2rem;
    }

    .how-to-use-card .bb-customer-card-body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    @media (max-width: 768px) {
        .coupon-details .row {
            flex-direction: column;
        }

        .coupon-details .col-md-4 {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .info-item {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .info-item .value {
            margin-top: 0;
            text-align: right;
        }
    }
    </style>
@endsection

@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::affiliate.dashboard'))

@section('content')
    <div class="affiliate-dashboard">
        <div class="affiliate-card">
            <div class="affiliate-card-header">
                <div class="affiliate-card-title">
                    {{ trans('plugins/affiliate-pro::affiliate.dashboard') }}
                </div>
            </div>
            <div class="affiliate-card-body">
                <div class="mb-4">
                    <p>{{ trans('plugins/affiliate-pro::affiliate.welcome_message') }}</p>
                </div>

                @if ($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::PENDING)
                    {{-- Enhanced Pending Status Section --}}
                    <div class="affiliate-pending-status-section">
                        <div class="pending-status-hero">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <div class="pending-status-content">
                                        <div class="status-badge">
                                            <x-core::icon name="ti ti-clock" class="me-2" />
                                            {{ trans('plugins/affiliate-pro::affiliate.pending_approval') }}
                                        </div>
                                        <h4 class="pending-title">{{ trans('plugins/affiliate-pro::affiliate.application_under_review') }}</h4>
                                        <p class="pending-description">{{ trans('plugins/affiliate-pro::affiliate.pending_review_message') }}</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-lg-center">
                                    <div class="pending-status-icon">
                                        <x-core::icon name="ti ti-hourglass" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Timeline Section --}}
                        <div class="approval-timeline mt-4">
                            <h5 class="timeline-title">
                                <x-core::icon name="ti ti-timeline" class="me-2" />
                                {{ trans('plugins/affiliate-pro::affiliate.approval_process') }}
                            </h5>
                            <div class="timeline-steps">
                                <div class="timeline-step completed">
                                    <div class="step-icon">
                                        <x-core::icon name="ti ti-check" />
                                    </div>
                                    <div class="step-content">
                                        <h6>{{ trans('plugins/affiliate-pro::affiliate.application_submitted') }}</h6>
                                        <p>{{ trans('plugins/affiliate-pro::affiliate.application_submitted_desc') }}</p>
                                    </div>
                                </div>
                                <div class="timeline-step active">
                                    <div class="step-icon">
                                        <x-core::icon name="ti ti-eye" />
                                    </div>
                                    <div class="step-content">
                                        <h6>{{ trans('plugins/affiliate-pro::affiliate.under_review') }}</h6>
                                        <p>{{ trans('plugins/affiliate-pro::affiliate.under_review_desc') }}</p>
                                    </div>
                                </div>
                                <div class="timeline-step pending">
                                    <div class="step-icon">
                                        <x-core::icon name="ti ti-user-check" />
                                    </div>
                                    <div class="step-content">
                                        <h6>{{ trans('plugins/affiliate-pro::affiliate.approval_decision') }}</h6>
                                        <p>{{ trans('plugins/affiliate-pro::affiliate.approval_decision_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- What's Next Section --}}
                        <div class="whats-next-section mt-4">
                            <h5 class="section-title">
                                <x-core::icon name="ti ti-bulb" class="me-2" />
                                {{ trans('plugins/affiliate-pro::affiliate.whats_next') }}
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="next-step-card">
                                        <div class="step-icon">
                                            <x-core::icon name="ti ti-mail" />
                                        </div>
                                        <div class="step-content">
                                            <h6>{{ trans('plugins/affiliate-pro::affiliate.check_email') }}</h6>
                                            <p>{{ trans('plugins/affiliate-pro::affiliate.check_email_desc') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="next-step-card">
                                        <div class="step-icon">
                                            <x-core::icon name="ti ti-book" />
                                        </div>
                                        <div class="step-content">
                                            <h6>{{ trans('plugins/affiliate-pro::affiliate.prepare_materials') }}</h6>
                                            <p>{{ trans('plugins/affiliate-pro::affiliate.prepare_materials_desc') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Estimated Timeline --}}
                        <div class="estimated-timeline mt-4">
                            <div class="timeline-info-card">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="timeline-info">
                                            <h6>{{ trans('plugins/affiliate-pro::affiliate.estimated_review_time') }}</h6>
                                            <p class="mb-0">{{ trans('plugins/affiliate-pro::affiliate.review_time_desc') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="timeline-badge">
                                            <x-core::icon name="ti ti-clock" class="me-1" />
                                            {{ trans('plugins/affiliate-pro::affiliate.review_time_estimate') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED)
                    <div class="stats-card-list">
                        <div class="stats-card stats-commission">
                            <div class="stats-card-title">{{ trans('plugins/affiliate-pro::affiliate.balance') }}</div>
                            <div class="stats-card-value">{{ format_price($affiliate->balance) }}</div>
                            <div class="stats-card-subtitle">{{ trans('plugins/affiliate-pro::affiliate.available_for_withdrawal') }}</div>
                        </div>

                        <div class="stats-card stats-withdrawal">
                            <div class="stats-card-title">{{ trans('plugins/affiliate-pro::affiliate.total_commission') }}</div>
                            <div class="stats-card-value">{{ format_price($affiliate->total_commission) }}</div>
                            <div class="stats-card-subtitle">{{ trans('plugins/affiliate-pro::affiliate.total_earned') }}</div>
                        </div>

                        <div class="stats-card stats-clicks">
                            <div class="stats-card-title">{{ trans('plugins/affiliate-pro::affiliate.total_withdrawn') }}</div>
                            <div class="stats-card-value">{{ format_price($affiliate->total_withdrawn) }}</div>
                            <div class="stats-card-subtitle">{{ trans('plugins/affiliate-pro::affiliate.successfully_paid_out') }}</div>
                        </div>

                        <div class="stats-card stats-conversion">
                            <div class="stats-card-title">{{ trans('plugins/affiliate-pro::affiliate.this_month_commission') }}</div>
                            <div class="stats-card-value">{{ format_price($statistics['this_month_commission']) }}</div>
                            <div class="stats-card-subtitle">{{ trans('plugins/affiliate-pro::affiliate.earned_this_month') }}</div>
                        </div>
                    </div>

                    {{-- Withdrawal Action Card --}}
                    @if($affiliate->balance > 0)
                        <div class="withdrawal-action-card mt-4">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="withdrawal-info">
                                        <h6 class="mb-1">{{ trans('plugins/affiliate-pro::affiliate.ready_to_withdraw') }}</h6>
                                        <p class="text-muted small mb-0">
                                            {{ trans('plugins/affiliate-pro::affiliate.withdrawal_description') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <a href="{{ route('affiliate-pro.withdrawals') }}" class="btn btn-success">
                                        <x-core::icon name="ti ti-wallet" class="me-1" />
                                        {{ trans('plugins/affiliate-pro::affiliate.manage_withdrawals') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="withdrawal-action-card mt-4">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="withdrawal-info">
                                        <h6 class="mb-1">{{ trans('plugins/affiliate-pro::affiliate.withdrawals') }}</h6>
                                        <p class="text-muted small mb-0">
                                            {{ trans('plugins/affiliate-pro::affiliate.earn_commissions_to_withdraw') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <a href="{{ route('affiliate-pro.withdrawals') }}" class="btn btn-outline-secondary">
                                        <x-core::icon name="ti ti-wallet" class="me-1" />
                                        {{ trans('plugins/affiliate-pro::affiliate.view_withdrawals') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                <div class="affiliate-card-list mt-4">
                    <div class="affiliate-card">
                        <div class="affiliate-card-header">
                            <div class="affiliate-card-title">
                                {{ trans('plugins/affiliate-pro::affiliate.statistics') }}
                            </div>
                        </div>
                        <div class="affiliate-card-body">
                            <div class="affiliate-card-info">
                                <div class="info-item">
                                    <span class="label">{{ trans('plugins/affiliate-pro::affiliate.total_clicks') }}:</span>
                                    <span class="value">{{ number_format($statistics['total_clicks']) }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">{{ trans('plugins/affiliate-pro::affiliate.conversion_rate') }}:</span>
                                    <span class="value">{{ $statistics['conversion_rate'] }}%</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">{{ trans('plugins/affiliate-pro::affiliate.this_month_clicks') }}:</span>
                                    <span class="value">{{ number_format($statistics['this_month_clicks']) }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">{{ trans('plugins/affiliate-pro::affiliate.this_month_conversions') }}:</span>
                                    <span class="value">{{ number_format($statistics['this_month_conversions']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="affiliate-card">
                        <div class="affiliate-card-header">
                            <div class="affiliate-card-title">
                                {{ trans('plugins/affiliate-pro::affiliate.detailed_reports') }}
                            </div>
                            <div class="affiliate-card-status">
                                <a href="{{ route('affiliate-pro.reports') }}" class="btn btn-sm btn-primary">
                                    <x-core::icon name="ti ti-chart-line" /> {{ trans('plugins/affiliate-pro::affiliate.view_detailed_reports') }}
                                </a>
                            </div>
                        </div>
                        <div class="affiliate-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="report-preview-card">
                                        <div class="report-preview-icon">
                                            <x-core::icon name="ti ti-chart-bar" class="text-primary" />
                                        </div>
                                        <div class="report-preview-content">
                                            <h6>{{ trans('plugins/affiliate-pro::affiliate.performance_trend') }}</h6>
                                            <p class="text-muted small mb-0">{{ trans('plugins/affiliate-pro::affiliate.track_clicks_conversions') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="report-preview-card">
                                        <div class="report-preview-icon">
                                            <x-core::icon name="ti ti-chart-donut" class="text-success" />
                                        </div>
                                        <div class="report-preview-content">
                                            <h6>{{ trans('plugins/affiliate-pro::affiliate.conversion_analytics') }}</h6>
                                            <p class="text-muted small mb-0">{{ trans('plugins/affiliate-pro::affiliate.analyze_conversion_rates') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="report-preview-card">
                                        <div class="report-preview-icon">
                                            <x-core::icon name="ti ti-world" class="text-info" />
                                        </div>
                                        <div class="report-preview-content">
                                            <h6>{{ trans('plugins/affiliate-pro::affiliate.geographic_analytics') }}</h6>
                                            <p class="text-muted small mb-0">{{ trans('plugins/affiliate-pro::affiliate.see_traffic_source') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="report-preview-card">
                                        <div class="report-preview-icon">
                                            <x-core::icon name="ti ti-history" class="text-warning" />
                                        </div>
                                        <div class="report-preview-content">
                                            <h6>{{ trans('plugins/affiliate-pro::affiliate.click_history') }}</h6>
                                            <p class="text-muted small mb-0">{{ trans('plugins/affiliate-pro::affiliate.detailed_click_history') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="affiliate-card">
                        <div class="affiliate-card-header">
                            <div class="affiliate-card-title">
                                {{ trans('plugins/affiliate-pro::affiliate.your_affiliate_link') }}
                            </div>
                            <div class="affiliate-card-status">
                                <a href="{{ route('affiliate-pro.reports') }}" class="btn btn-sm btn-primary me-2">
                                    <x-core::icon name="ti ti-chart-line" /> {{ trans('plugins/affiliate-pro::affiliate.reports') }}
                                </a>
                                <a href="{{ route('affiliate-pro.materials') }}" class="btn btn-sm btn-primary me-2">
                                    <x-core::icon name="ti ti-share" /> {{ trans('plugins/affiliate-pro::affiliate.promotional_materials') }}
                                </a>
                                <a href="{{ route('affiliate-pro.coupons') }}" class="btn btn-sm btn-primary">
                                    <x-core::icon name="ti ti-ticket" /> {{ trans('plugins/affiliate-pro::affiliate.coupons') }}
                                </a>
                            </div>
                        </div>
                        <div class="affiliate-card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="affiliate-link-box mb-3">
                                        <div class="affiliate-link-title">{{ trans('plugins/affiliate-pro::affiliate.share_link') }}</div>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ url('?aff=' . $affiliate->affiliate_code) }}" id="affiliate-link" readonly>
                                            <button class="btn btn-primary" type="button" data-copy-affiliate-link>
                                                <x-core::icon name="ti ti-copy" /> <span class="d-none d-md-inline">{{ trans('plugins/affiliate-pro::affiliate.copy') }}</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="affiliate-link-box">
                                        <div class="affiliate-link-title">{{ trans('plugins/affiliate-pro::affiliate.your_affiliate_code') }}</div>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $affiliate->affiliate_code }}" id="affiliate-code" readonly>
                                            <button class="btn btn-primary" type="button" data-copy-affiliate-code>
                                                <x-core::icon name="ti ti-copy" /> <span class="d-none d-md-inline">{{ trans('plugins/affiliate-pro::affiliate.copy') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-none d-md-block">
                                    <div class="text-center">
                                        <img src="data:image/svg+xml;base64,{{ app(\Botble\AffiliatePro\Services\QrCodeService::class)->getAffiliateQrCode($affiliate) }}"
                                            alt="QR Code" class="img-fluid" style="max-width: 120px;">
                                        <p class="mt-2 small">{{ trans('plugins/affiliate-pro::affiliate.scan_to_share') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="affiliate-card-list mt-4">
                    <div class="affiliate-card">
                        <div class="affiliate-card-header">
                            <div class="affiliate-card-title">
                                {{ trans('plugins/affiliate-pro::affiliate.create_short_link') }}
                            </div>
                            <div class="affiliate-card-status">
                                <a href="{{ route('affiliate-pro.short-links') }}" class="btn btn-sm btn-primary">
                                    {{ trans('plugins/affiliate-pro::affiliate.manage_links') }}
                                </a>
                            </div>
                        </div>
                        <div class="affiliate-card-body">
                            <div class="short-link-creator">
                                {{-- Create Short Link Form --}}
                                @include('plugins/affiliate-pro::themes.customers.partials.create-short-link-form', [
                                    'formId' => 'dashboard-create-short-link-form',
                                    'showCard' => false,
                                    'showManageLink' => false,
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="affiliate-card">
                        <div class="affiliate-card-header">
                            <div class="affiliate-card-title">
                                <x-core::icon name="ti ti-coins" class="me-2" />
                                {{ trans('plugins/affiliate-pro::affiliate.recent_commissions') }}
                            </div>
                            <div class="affiliate-card-status">
                                <a href="{{ route('affiliate-pro.commissions') }}" class="btn btn-sm btn-outline-primary">
                                    <x-core::icon name="ti ti-eye" class="me-1" />
                                    {{ trans('plugins/affiliate-pro::affiliate.view_all') }}
                                </a>
                            </div>
                        </div>
                        <div class="affiliate-card-body">
                            @if(count($recentCommissions) > 0)
                                <div class="commission-list">
                                    @foreach($recentCommissions as $commission)
                                        <div class="commission-item">
                                            <div class="commission-info">
                                                <div class="commission-amount">
                                                    <span class="amount-value">{{ format_price($commission->amount) }}</span>
                                                    <small class="text-muted">{{ trans('plugins/affiliate-pro::commission.amount') }}</small>
                                                </div>
                                                <div class="commission-details">
                                                    <div class="commission-order">
                                                        <x-core::icon name="ti ti-shopping-cart" class="me-1 text-muted" />
                                                        <span class="fw-medium">
                                                            {{ $commission->order ? $commission->order->code : '-' }}
                                                        </span>
                                                    </div>
                                                    <div class="commission-date">
                                                        <x-core::icon name="ti ti-calendar" class="me-1 text-muted" />
                                                        <small class="text-muted">{{ $commission->created_at->translatedFormat('M d, Y') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="commission-status">
                                                @if($commission->status == 'pending')
                                                    <span class="badge bg-warning text-dark">
                                                        <x-core::icon name="ti ti-clock" class="me-1" />
                                                        {{ trans('plugins/affiliate-pro::commission.statuses.pending') }}
                                                    </span>
                                                @elseif($commission->status == 'approved')
                                                    <span class="badge bg-success text-white">
                                                        <x-core::icon name="ti ti-check" class="me-1" />
                                                        {{ trans('plugins/affiliate-pro::commission.statuses.approved') }}
                                                    </span>
                                                @elseif($commission->status == 'rejected')
                                                    <span class="badge bg-danger text-white">
                                                        <x-core::icon name="ti ti-x" class="me-1" />
                                                        {{ trans('plugins/affiliate-pro::commission.statuses.rejected') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state text-center py-4">
                                    <div class="empty-state-icon mb-3">
                                        <x-core::icon name="ti ti-coins" class="text-muted" style="font-size: 3rem;" />
                                    </div>
                                    <h6 class="text-muted mb-2">{{ trans('plugins/affiliate-pro::affiliate.no_commissions') }}</h6>
                                    <p class="text-muted small mb-0">{{ trans('plugins/affiliate-pro::affiliate.start_promoting_to_earn') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="affiliate-card">
                        <div class="affiliate-card-header">
                            <div class="affiliate-card-title">
                                <x-core::icon name="ti ti-wallet" class="me-2" />
                                {{ trans('plugins/affiliate-pro::affiliate.recent_withdrawals') }}
                            </div>
                            <div class="affiliate-card-status">
                                <a href="{{ route('affiliate-pro.withdrawals') }}" class="btn btn-sm btn-outline-primary">
                                    <x-core::icon name="ti ti-eye" class="me-1" />
                                    {{ trans('plugins/affiliate-pro::affiliate.view_all') }}
                                </a>
                            </div>
                        </div>
                        <div class="affiliate-card-body">
                            @if(count($recentWithdrawals) > 0)
                                <div class="withdrawal-list">
                                    @foreach($recentWithdrawals as $withdrawal)
                                        <div class="withdrawal-item">
                                            <div class="withdrawal-info">
                                                <div class="withdrawal-amount">
                                                    <span class="amount-value">{{ format_price($withdrawal->amount) }}</span>
                                                    <small class="text-muted">{{ trans('plugins/affiliate-pro::withdrawal.amount') }}</small>
                                                </div>
                                                <div class="withdrawal-details">
                                                    <div class="withdrawal-method">
                                                        <x-core::icon name="ti ti-credit-card" class="me-1 text-muted" />
                                                        <span class="fw-medium">{{ ucfirst($withdrawal->payment_method) }}</span>
                                                    </div>
                                                    <div class="withdrawal-date">
                                                        <x-core::icon name="ti ti-calendar" class="me-1 text-muted" />
                                                        <small class="text-muted">{{ $withdrawal->created_at->translatedFormat('M d, Y') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="withdrawal-status">
                                                @if($withdrawal->status == 'pending')
                                                    <span class="badge bg-warning text-dark">
                                                        <x-core::icon name="ti ti-clock" class="me-1" />
                                                        {{ trans('plugins/affiliate-pro::withdrawal.statuses.pending') }}
                                                    </span>
                                                @elseif($withdrawal->status == 'processing')
                                                    <span class="badge bg-info text-white">
                                                        <x-core::icon name="ti ti-loader" class="me-1" />
                                                        {{ trans('plugins/affiliate-pro::withdrawal.statuses.processing') }}
                                                    </span>
                                                @elseif($withdrawal->status == 'approved')
                                                    <span class="badge bg-success text-white">
                                                        <x-core::icon name="ti ti-check" class="me-1" />
                                                        {{ trans('plugins/affiliate-pro::withdrawal.statuses.approved') }}
                                                    </span>
                                                @elseif($withdrawal->status == 'rejected')
                                                    <span class="badge bg-danger text-white">
                                                        <x-core::icon name="ti ti-x" class="me-1" />
                                                        {{ trans('plugins/affiliate-pro::withdrawal.statuses.rejected') }}
                                                    </span>
                                                @elseif($withdrawal->status == 'canceled')
                                                    <span class="badge bg-secondary text-white">
                                                        <x-core::icon name="ti ti-ban" class="me-1" />
                                                        {{ trans('plugins/affiliate-pro::withdrawal.statuses.canceled') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state text-center py-4">
                                    <div class="empty-state-icon mb-3">
                                        <x-core::icon name="ti ti-wallet" class="text-muted" style="font-size: 3rem;" />
                                    </div>
                                    <h6 class="text-muted mb-2">{{ trans('plugins/affiliate-pro::affiliate.no_withdrawals') }}</h6>
                                    <p class="text-muted small mb-0">{{ trans('plugins/affiliate-pro::affiliate.earn_commissions_to_withdraw') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

    {{-- CSS Styles for Report Preview Cards and Withdrawal Action Card --}}
    <style>
    .withdrawal-action-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .withdrawal-action-card:hover {
        border-color: #28a745;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.1);
        transform: translateY(-2px);
    }

    .withdrawal-info h6 {
        color: #333;
        font-weight: 600;
    }

    .withdrawal-action-card .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .withdrawal-action-card .btn-success:hover {
        background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .withdrawal-action-card .btn-outline-secondary {
        border: 2px solid #6c757d;
        color: #6c757d;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .withdrawal-action-card .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }

    @media (max-width: 768px) {
        .withdrawal-action-card {
            padding: 1rem;
            text-align: center;
        }

        .withdrawal-action-card .col-md-4 {
            margin-top: 1rem;
        }
    }
    .report-preview-card {
        display: flex;
        align-items: center;
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background-color: #fff;
        transition: all 0.3s ease;
        height: 100%;
    }

    .report-preview-card:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        transform: translateY(-2px);
    }

    .report-preview-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background-color: #f8f9fa;
        margin-right: 0.75rem;
    }

    .report-preview-icon svg {
        width: 20px;
        height: 20px;
    }

    .report-preview-content h6 {
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #333;
    }

    .report-preview-content p {
        font-size: 0.75rem;
        line-height: 1.4;
    }

    @media (max-width: 768px) {
        .report-preview-card {
            padding: 0.75rem;
        }

        .report-preview-icon {
            width: 32px;
            height: 32px;
            margin-right: 0.5rem;
        }

        .report-preview-icon svg {
            width: 16px;
            height: 16px;
        }

        .report-preview-content h6 {
            font-size: 0.8rem;
        }

        .report-preview-content p {
            font-size: 0.7rem;
        }
    }
    </style>

    {{-- JavaScript translations --}}
    <script>
    window.affiliateTranslations = window.affiliateTranslations || {};
    window.affiliateTranslations = {
        creating: '{{ trans("plugins/affiliate-pro::affiliate.js.creating") }}',
        createShortLink: '{{ trans("plugins/affiliate-pro::affiliate.js.create_short_link") }}',
        errorOccurred: '{{ trans("plugins/affiliate-pro::affiliate.js.error_occurred") }}',
        copiedToClipboard: '{{ trans("plugins/affiliate-pro::affiliate.copied_to_clipboard") }}',
        couponCopied: '{{ trans("plugins/affiliate-pro::affiliate.coupon_copied") }}',
        copyFailed: '{{ trans("plugins/affiliate-pro::affiliate.copy_failed") }}',
        htmlCopied: '{{ trans("plugins/affiliate-pro::affiliate.html_copied") }}',
        copied: '{{ trans("plugins/affiliate-pro::affiliate.copied") }}',
        deleteConfirm: '{{ trans("plugins/affiliate-pro::affiliate.js.delete_short_link_confirm") }}'
    };

    // Backward compatibility
    window.trans = window.trans || {};
    window.trans['plugins/affiliate-pro::affiliate.creating'] = '{{ trans("plugins/affiliate-pro::affiliate.creating") }}';
    window.trans['plugins/affiliate-pro::affiliate.create_short_link'] = '{{ trans("plugins/affiliate-pro::affiliate.create_short_link") }}';
    window.trans['plugins/affiliate-pro::affiliate.error_occurred'] = '{{ trans("plugins/affiliate-pro::affiliate.error_occurred") }}';
    window.trans['plugins/affiliate-pro::affiliate.copied_to_clipboard'] = '{{ trans("plugins/affiliate-pro::affiliate.copied_to_clipboard") }}';
    window.trans['plugins/affiliate-pro::affiliate.coupon_copied'] = '{{ trans("plugins/affiliate-pro::affiliate.coupon_copied") }}';
    window.trans['plugins/affiliate-pro::affiliate.copy_failed'] = '{{ trans("plugins/affiliate-pro::affiliate.copy_failed") }}';
    window.trans['plugins/affiliate-pro::affiliate.html_copied'] = '{{ trans("plugins/affiliate-pro::affiliate.html_copied") }}';
    </script>
@endsection

@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::affiliate.short_links'))

@section('content')
    <div class="bb-customer-card-list affiliate-short-links-cards">
        {{-- Account Status Card (if not approved) --}}
        @if($affiliate->status != \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED)
            <div class="bb-customer-card affiliate-status-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.account_status') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <span class="badge bg-warning text-dark">{{ trans('plugins/affiliate-pro::affiliate.not_approved') }}</span>
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="alert alert-warning mb-0">
                        <x-core::icon name="ti ti-alert-triangle" class="me-2" />
                        {{ trans('plugins/affiliate-pro::affiliate.pending_approval') }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Short Links Overview Card --}}
        <div class="bb-customer-card short-links-overview-card">
            <div class="bb-customer-card-header">
                <div class="bb-customer-card-title">
                    <span class="value">{{ trans('plugins/affiliate-pro::affiliate.short_links_overview') }}</span>
                </div>
                <div class="bb-customer-card-status">
                    <span class="badge bg-primary">{{ count($shortLinks) }}</span>
                </div>
            </div>
            <div class="bb-customer-card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <x-core::icon name="ti ti-link" class="text-primary" />
                            </div>
                            <div class="stats-info">
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.total_links') }}</h6>
                                <p class="text-primary fw-bold">{{ count($shortLinks) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <x-core::icon name="ti ti-click" class="text-success" />
                            </div>
                            <div class="stats-info">
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.total_clicks') }}</h6>
                                <p class="text-success fw-bold">{{ $shortLinks->sum('clicks') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <x-core::icon name="ti ti-target" class="text-warning" />
                            </div>
                            <div class="stats-info">
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.conversions') }}</h6>
                                <p class="text-warning fw-bold">{{ $shortLinks->sum('conversions') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <x-core::icon name="ti ti-percentage" class="text-info" />
                            </div>
                            <div class="stats-info">
                                <h6>{{ trans('plugins/affiliate-pro::affiliate.avg_conv_rate') }}</h6>
                                <p class="text-info fw-bold">
                                    @php
                                        $totalClicks = $shortLinks->sum('clicks');
                                        $totalConversions = $shortLinks->sum('conversions');
                                        $avgRate = $totalClicks > 0 ? number_format(($totalConversions / $totalClicks) * 100, 1) : '0';
                                    @endphp
                                    {{ $avgRate }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Create Short Link Card --}}
        @include('plugins/affiliate-pro::themes.customers.partials.create-short-link-form', [
            'formId' => 'create-short-link-form',
            'showCard' => true,
            'showManageLink' => false,
        ])

        {{-- Your Short Links Card --}}
        <div class="bb-customer-card your-short-links-card">
            <div class="bb-customer-card-header">
                <div class="bb-customer-card-title">
                    <span class="value">{{ trans('plugins/affiliate-pro::affiliate.your_short_links') }}</span>
                </div>
                <div class="bb-customer-card-status">
                    <span class="badge bg-info">{{ count($shortLinks) }}</span>
                </div>
            </div>
            <div class="bb-customer-card-body">
                <div class="short-link-list" id="short-links-container">
                    @if(count($shortLinks) > 0)
                        <div class="row g-4">
                            @foreach($shortLinks as $link)
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm h-100 short-link-item" id="short-link-{{ $link->id }}">
                                        <div class="card-body p-4">
                                            {{-- Header with title and actions --}}
                                            <div class="d-flex align-items-start justify-content-between mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                            <x-core::icon name="ti ti-link" class="text-primary" style="font-size: 1.5rem;" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $link->title ?: trans('plugins/affiliate-pro::affiliate.untitled_link') }}</h6>
                                                        <small class="text-muted">
                                                            <x-core::icon name="ti ti-calendar" class="me-1" style="font-size: 0.875rem;" />
                                                            {{ trans('plugins/affiliate-pro::affiliate.created') }}: {{ $link->created_at->translatedFormat('M d, Y') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <x-core::icon name="ti ti-dots-vertical" />
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <button class="dropdown-item" type="button" data-bb-toggle="clipboard" data-clipboard-text="{{ $link->getShortUrl() }}" data-clipboard-message="{{ trans('plugins/affiliate-pro::affiliate.copied_to_clipboard') }}">
                                                                <x-core::icon name="ti ti-copy" class="me-2" data-clipboard-icon="true" />
                                                                <x-core::icon name="ti ti-check" class="me-2 text-success d-none" data-clipboard-success-icon="true" />
                                                                {{ trans('plugins/affiliate-pro::affiliate.copy_url') }}
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ $link->destination_url }}" target="_blank">
                                                                <x-core::icon name="ti ti-external-link" class="me-2" />
                                                                {{ trans('plugins/affiliate-pro::affiliate.visit_destination') }}
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item text-danger delete-link-btn" data-id="{{ $link->id }}">
                                                                <x-core::icon name="ti ti-trash" class="me-2" />
                                                                {{ trans('plugins/affiliate-pro::affiliate.delete_link') }}
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            {{-- URL Section --}}
                                            <div class="mb-4">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-medium text-muted mb-2">
                                                            <x-core::icon name="ti ti-link" class="me-1" />
                                                            {{ trans('plugins/affiliate-pro::affiliate.short_url') }}
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control bg-light border-0" value="{{ $link->getShortUrl() }}" readonly>
                                                            <button class="btn btn-primary" type="button" data-bb-toggle="clipboard" data-clipboard-text="{{ $link->getShortUrl() }}" data-clipboard-message="{{ trans('plugins/affiliate-pro::affiliate.copied_to_clipboard') }}" title="{{ trans('plugins/affiliate-pro::affiliate.copy_url') }}">
                                                                <x-core::icon name="ti ti-copy" data-clipboard-icon="true" />
                                                                <x-core::icon name="ti ti-check" data-clipboard-success-icon="true" class="text-white d-none" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-medium text-muted mb-2">
                                                            <x-core::icon name="ti ti-world" class="me-1" />
                                                            {{ trans('plugins/affiliate-pro::affiliate.destination') }}
                                                        </label>
                                                        <div class="bg-light border-0 rounded p-2">
                                                            <a href="{{ $link->destination_url }}" target="_blank" class="text-decoration-none d-flex align-items-center">
                                                                <x-core::icon name="ti ti-external-link" class="me-2 text-muted flex-shrink-0" />
                                                                <span class="text-truncate">{{ $link->destination_url }}</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Performance Stats --}}
                                            <div class="border-top pt-3">
                                                <div class="row g-3 text-center">
                                                    <div class="col-6 col-lg-3">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <div class="bg-success bg-opacity-10 rounded-circle p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <x-core::icon name="ti ti-click" class="text-success" />
                                                            </div>
                                                            <div class="fw-bold h5 mb-0">{{ number_format($link->clicks) }}</div>
                                                            <small class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.clicks') }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-lg-3">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <x-core::icon name="ti ti-target" class="text-warning" />
                                                            </div>
                                                            <div class="fw-bold h5 mb-0">{{ number_format($link->conversions) }}</div>
                                                            <small class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.conversions') }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-lg-3">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <div class="bg-info bg-opacity-10 rounded-circle p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <x-core::icon name="ti ti-percentage" class="text-info" />
                                                            </div>
                                                            <div class="fw-bold h5 mb-0">{{ $link->conversions > 0 && $link->clicks > 0 ? number_format(($link->conversions / $link->clicks) * 100, 1) : '0' }}%</div>
                                                            <small class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.conv_rate') }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-lg-3">
                                                        <div class="d-flex flex-column align-items-center">
                                                            @php
                                                                $commissionAmount = $link->conversions * 10; // Example calculation
                                                            @endphp
                                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 mb-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <x-core::icon name="ti ti-coin" class="text-primary" />
                                                            </div>
                                                            <div class="fw-bold h5 mb-0">${{ number_format($commissionAmount, 2) }}</div>
                                                            <small class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.earnings') }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <div class="mb-4">
                                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <x-core::icon name="ti ti-link-off" class="text-muted" style="font-size: 2.5rem;" />
                                    </div>
                                </div>
                                <h5 class="mb-3">{{ trans('plugins/affiliate-pro::affiliate.no_short_links_yet') }}</h5>
                                <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                                    {{ trans('plugins/affiliate-pro::affiliate.no_short_links_created') }}
                                    {{ trans('plugins/affiliate-pro::affiliate.create_links_to_track_performance') }}
                                </p>
                                @if($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED)
                                    <button class="btn btn-primary btn-lg" onclick="document.getElementById('create-short-link-form').scrollIntoView({ behavior: 'smooth' })">
                                        <x-core::icon name="ti ti-plus" class="me-2" />
                                        {{ trans('plugins/affiliate-pro::affiliate.create_your_first_link') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden empty state template for JavaScript --}}
    <div id="short-links-empty-state-template" style="display: none;">
        <div class="text-center py-5">
            <div class="empty-state">
                <div class="mb-4">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <x-core::icon name="ti ti-link-off" class="text-muted" style="font-size: 2.5rem;" />
                    </div>
                </div>
                <h5 class="mb-3">{{ trans('plugins/affiliate-pro::affiliate.js.no_short_links_created_yet') }}</h5>
                <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                    {{ trans('plugins/affiliate-pro::affiliate.js.create_your_first_short_link') }}
                </p>
                <button type="button" class="btn btn-primary btn-lg" onclick="document.getElementById('create-short-link-form').scrollIntoView({ behavior: 'smooth' })">
                    <x-core::icon name="ti ti-plus" class="me-2" />
                    {{ trans('plugins/affiliate-pro::affiliate.js.create_your_first_link') }}
                </button>
            </div>
        </div>
    </div>

    {{-- JavaScript translations --}}
    <script>
    window.affiliateTranslations = window.affiliateTranslations || {};
    window.affiliateTranslations = {
        creating: '{{ trans("plugins/affiliate-pro::affiliate.js.creating") }}',
        createShortLink: '{{ trans("plugins/affiliate-pro::affiliate.js.create_short_link") }}',
        errorOccurred: '{{ trans("plugins/affiliate-pro::affiliate.js.error_occurred") }}',
        deleteConfirm: '{{ trans("plugins/affiliate-pro::affiliate.js.delete_short_link_confirm") }}',
        copied: '{{ trans("plugins/affiliate-pro::affiliate.copied") }}'
    };
    </script>

@endsection

@push('footer')
    {{-- Include the short links management JavaScript --}}
    <script src="{{ asset('vendor/core/plugins/affiliate-pro/js/short-links-management.js') }}"></script>
@endpush

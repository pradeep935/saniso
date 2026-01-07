@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <x-core::icon name="ti ti-link" class="text-primary" style="font-size: 1.75rem;" />
                                </div>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">{{ $shortLink->title ?: trans('plugins/affiliate-pro::short-link.untitled') }}</h4>
                                <div class="d-flex align-items-center text-muted">
                                    <x-core::icon name="ti ti-calendar" class="me-1" style="font-size: 0.875rem;" />
                                    <span class="me-3">{{ trans('plugins/affiliate-pro::short-link.created_at') }}: {{ $shortLink->created_at->format('M d, Y H:i') }}</span>
                                    <x-core::icon name="ti ti-clock" class="me-1" style="font-size: 0.875rem;" />
                                    <span>{{ trans('plugins/affiliate-pro::short-link.updated_at') }}: {{ $shortLink->updated_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('affiliate-pro.short-links.edit', $shortLink->id) }}" class="btn btn-primary">
                                <x-core::icon name="ti ti-pencil" class="me-1" />
                                {{ trans('core/base::forms.edit') }}
                            </a>
                            <a href="{{ route('affiliate-pro.short-links.index') }}" class="btn btn-outline-secondary">
                                <x-core::icon name="ti ti-arrow-left" class="me-1" />
                                {{ trans('plugins/affiliate-pro::short-link.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- URL Information Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <x-core::icon name="ti ti-world" class="me-2 text-primary" />
                        {{ trans('plugins/affiliate-pro::short-link.url_information') }}
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-4">
                        {{-- Short Code --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted mb-2">
                                <x-core::icon name="ti ti-hash" class="me-1" />
                                {{ trans('plugins/affiliate-pro::short-link.short_code') }}
                            </label>
                            <div class="bg-light rounded p-3">
                                <code class="text-primary fs-6">{{ $shortLink->short_code }}</code>
                            </div>
                        </div>

                        {{-- Short URL --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold text-muted mb-2">
                                <x-core::icon name="ti ti-link" class="me-1" />
                                {{ trans('plugins/affiliate-pro::short-link.short_url') }}
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light border-0 font-monospace" value="{{ $shortLink->getShortUrl() }}" readonly id="short-url-input">
                                <button class="btn btn-primary" type="button" data-bb-toggle="clipboard" data-clipboard-target="#short-url-input" data-clipboard-message="{{ trans('plugins/affiliate-pro::short-link.copied_to_clipboard') }}" title="{{ trans('plugins/affiliate-pro::short-link.copy_url') }}">
                                    <x-core::icon name="ti ti-copy" data-clipboard-icon="true" />
                                    <x-core::icon name="ti ti-check" data-clipboard-success-icon="true" class="text-white d-none" />
                                </button>
                                <a href="{{ $shortLink->getShortUrl() }}" target="_blank" class="btn btn-outline-secondary" title="{{ trans('plugins/affiliate-pro::short-link.test_link') }}">
                                    <x-core::icon name="ti ti-external-link" />
                                </a>
                            </div>
                        </div>

                        {{-- Destination URL --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold text-muted mb-2">
                                <x-core::icon name="ti ti-world" class="me-1" />
                                {{ trans('plugins/affiliate-pro::short-link.destination_url') }}
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light border-0 font-monospace" value="{{ $shortLink->destination_url }}" readonly>
                                <a href="{{ $shortLink->destination_url }}" target="_blank" class="btn btn-outline-secondary" title="{{ trans('plugins/affiliate-pro::short-link.visit_destination') }}">
                                    <x-core::icon name="ti ti-external-link" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Affiliate & Product Information Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <x-core::icon name="ti ti-users" class="me-2 text-success" />
                        {{ trans('plugins/affiliate-pro::short-link.affiliate_product_info') }}
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-4">
                        {{-- Affiliate Information --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted mb-2">
                                <x-core::icon name="ti ti-user-star" class="me-1" />
                                {{ trans('plugins/affiliate-pro::short-link.affiliate') }}
                            </label>
                            <div class="bg-light rounded p-3">
                                @if($shortLink->affiliate && $shortLink->affiliate->customer)
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <x-core::icon name="ti ti-user" class="text-success" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                <a href="{{ route('affiliate-pro.edit', $shortLink->affiliate->id) }}" class="text-decoration-none">
                                                    {{ $shortLink->affiliate->customer->name }}
                                                </a>
                                            </div>
                                            <small class="text-muted">
                                                {{ trans('plugins/affiliate-pro::affiliate.code') }}:
                                                <code class="text-primary">{{ $shortLink->affiliate->affiliate_code }}</code>
                                            </small>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-2">
                                        <x-core::icon name="ti ti-user-off" class="text-muted mb-2" style="font-size: 2rem;" />
                                        <div class="text-muted">{{ trans('plugins/affiliate-pro::short-link.affiliate_not_found') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Product Information --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted mb-2">
                                <x-core::icon name="ti ti-package" class="me-1" />
                                {{ trans('plugins/affiliate-pro::short-link.product') }}
                            </label>
                            <div class="bg-light rounded p-3">
                                @if($shortLink->product)
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <x-core::icon name="ti ti-package" class="text-warning" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                <a href="{{ route('products.edit', $shortLink->product->id) }}" target="_blank" class="text-decoration-none">
                                                    {{ $shortLink->product->name }}
                                                </a>
                                            </div>
                                            <small class="text-muted">{{ trans('plugins/affiliate-pro::short-link.product_link') }}</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-2">
                                        <x-core::icon name="ti ti-world" class="text-muted mb-2" style="font-size: 2rem;" />
                                        <div class="text-muted">{{ trans('plugins/affiliate-pro::short-link.general_link') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Sidebar --}}
        <div class="col-lg-4">
            {{-- Performance Statistics Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <x-core::icon name="ti ti-chart-bar" class="me-2 text-info" />
                        {{ trans('plugins/affiliate-pro::short-link.statistics') }}
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3">
                        {{-- Total Clicks --}}
                        <div class="col-12">
                            <div class="bg-primary bg-opacity-10 rounded p-4 text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <x-core::icon name="ti ti-click" class="text-white" style="font-size: 1.5rem;" />
                                    </div>
                                </div>
                                <h2 class="text-primary mb-2 fw-bold">{{ number_format($shortLink->clicks) }}</h2>
                                <div class="text-muted fw-medium">{{ trans('plugins/affiliate-pro::short-link.total_clicks') }}</div>
                            </div>
                        </div>

                        {{-- Total Conversions --}}
                        <div class="col-12">
                            <div class="bg-success bg-opacity-10 rounded p-4 text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <x-core::icon name="ti ti-target" class="text-white" style="font-size: 1.5rem;" />
                                    </div>
                                </div>
                                <h2 class="text-success mb-2 fw-bold">{{ number_format($shortLink->conversions) }}</h2>
                                <div class="text-muted fw-medium">{{ trans('plugins/affiliate-pro::short-link.total_conversions') }}</div>
                            </div>
                        </div>

                        {{-- Conversion Rate --}}
                        <div class="col-12">
                            <div class="bg-info bg-opacity-10 rounded p-4 text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <x-core::icon name="ti ti-percentage" class="text-white" style="font-size: 1.5rem;" />
                                    </div>
                                </div>
                                @php
                                    $conversionRate = $shortLink->clicks > 0 ? round(($shortLink->conversions / $shortLink->clicks) * 100, 2) : 0;
                                @endphp
                                <h2 class="text-info mb-2 fw-bold">{{ $conversionRate }}%</h2>
                                <div class="text-muted fw-medium">{{ trans('plugins/affiliate-pro::short-link.conversion_rate') }}</div>
                            </div>
                        </div>

                        {{-- Performance Indicator --}}
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted">{{ trans('plugins/affiliate-pro::short-link.performance') }}</span>
                                    @if($conversionRate >= 5)
                                        <span class="badge bg-success">{{ trans('plugins/affiliate-pro::short-link.excellent') }}</span>
                                    @elseif($conversionRate >= 2)
                                        <span class="badge bg-warning">{{ trans('plugins/affiliate-pro::short-link.good') }}</span>
                                    @elseif($conversionRate > 0)
                                        <span class="badge bg-info">{{ trans('plugins/affiliate-pro::short-link.average') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ trans('plugins/affiliate-pro::short-link.no_data') }}</span>
                                    @endif
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar
                                        @if($conversionRate >= 5) bg-success
                                        @elseif($conversionRate >= 2) bg-warning
                                        @elseif($conversionRate > 0) bg-info
                                        @else bg-secondary
                                        @endif"
                                        role="progressbar"
                                        style="width: {{ min($conversionRate * 2, 100) }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <x-core::icon name="ti ti-bolt" class="me-2 text-warning" />
                        {{ trans('plugins/affiliate-pro::short-link.quick_actions') }}
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="d-grid gap-3">
                        {{-- Copy Short URL --}}
                        <button class="btn btn-outline-primary d-flex align-items-center justify-content-center"
                                data-bb-toggle="clipboard"
                                data-clipboard-text="{{ $shortLink->getShortUrl() }}"
                                data-clipboard-message="{{ trans('plugins/affiliate-pro::short-link.copied_to_clipboard') }}">
                            <x-core::icon name="ti ti-copy" class="me-2" data-clipboard-icon="true" />
                            <x-core::icon name="ti ti-check" class="me-2 text-success d-none" data-clipboard-success-icon="true" />
                            {{ trans('plugins/affiliate-pro::short-link.copy_short_url') }}
                        </button>

                        {{-- Test Link --}}
                        <a href="{{ $shortLink->getShortUrl() }}" target="_blank" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                            <x-core::icon name="ti ti-external-link" class="me-2" />
                            {{ trans('plugins/affiliate-pro::short-link.test_link') }}
                        </a>

                        {{-- Visit Destination --}}
                        <a href="{{ $shortLink->destination_url }}" target="_blank" class="btn btn-outline-info d-flex align-items-center justify-content-center">
                            <x-core::icon name="ti ti-world" class="me-2" />
                            {{ trans('plugins/affiliate-pro::short-link.visit_destination') }}
                        </a>

                        <hr class="my-2">

                        {{-- Edit Link --}}
                        <a href="{{ route('affiliate-pro.short-links.edit', $shortLink->id) }}" class="btn btn-primary d-flex align-items-center justify-content-center">
                            <x-core::icon name="ti ti-pencil" class="me-2" />
                            {{ trans('core/base::forms.edit') }}
                        </a>

                        {{-- Back to List --}}
                        <a href="{{ route('affiliate-pro.short-links.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                            <x-core::icon name="ti ti-arrow-left" class="me-2" />
                            {{ trans('plugins/affiliate-pro::short-link.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom Styles --}}
    <style>
        .font-monospace {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace !important;
            font-size: 0.875rem;
        }

        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .bg-primary.bg-opacity-10:hover,
        .bg-success.bg-opacity-10:hover,
        .bg-info.bg-opacity-10:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }

        .progress {
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.6s ease;
        }

        .btn {
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }
    </style>
@endsection

@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::affiliate.affiliate_reports'))

@section('content')
    {{-- Load Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <div class="bb-customer-card-list affiliate-reports-cards">
        {{-- Account Status Card (if not approved) --}}
        @if ($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::PENDING)
            <div class="bb-customer-card affiliate-status-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.account_status') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <span class="badge bg-warning text-dark">{{ trans('plugins/affiliate-pro::affiliate.pending') }}</span>
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

        @if ($affiliate->status == \Botble\AffiliatePro\Enums\AffiliateStatusEnum::APPROVED)
            {{-- Date Range Filter Card --}}
            <div class="bb-customer-card date-filter-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.report_filters') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <x-core::icon name="ti ti-filter" class="text-primary" />
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <form method="GET" action="{{ route('affiliate-pro.reports') }}" id="date-range-form">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">
                                    <x-core::icon name="ti ti-calendar" class="me-2" />
                                    {{ trans('plugins/affiliate-pro::affiliate.start_date') }}
                                </label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->translatedFormat('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="end_date" class="form-label">
                                    <x-core::icon name="ti ti-calendar" class="me-2" />
                                    {{ trans('plugins/affiliate-pro::affiliate.end_date') }}
                                </label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->translatedFormat('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <x-core::icon name="ti ti-search" class="me-2" />
                                    {{ trans('plugins/affiliate-pro::affiliate.filter') }}
                                </button>
                                <a href="{{ route('affiliate-pro.reports') }}" class="btn btn-outline-secondary">
                                    <x-core::icon name="ti ti-refresh" class="me-2" />
                                    {{ trans('plugins/affiliate-pro::affiliate.reset') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Performance Overview Card --}}
            <div class="bb-customer-card performance-overview-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.performance_overview') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <span class="badge bg-primary">{{ $startDate->translatedFormat('M d') }} - {{ $endDate->translatedFormat('M d') }}</span>
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <x-core::icon name="ti ti-click" class="text-primary" />
                                </div>
                                <div class="stats-info">
                                    <h6>{{ trans('plugins/affiliate-pro::affiliate.total_clicks') }}</h6>
                                    <p class="text-primary fw-bold">{{ number_format($statistics['total_clicks']) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <x-core::icon name="ti ti-target" class="text-success" />
                                </div>
                                <div class="stats-info">
                                    <h6>{{ trans('plugins/affiliate-pro::affiliate.total_conversions') }}</h6>
                                    <p class="text-success fw-bold">{{ number_format($statistics['total_conversions']) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <x-core::icon name="ti ti-percentage" class="text-info" />
                                </div>
                                <div class="stats-info">
                                    <h6>{{ trans('plugins/affiliate-pro::affiliate.conversion_rate') }}</h6>
                                    <p class="text-info fw-bold">{{ $statistics['conversion_rate'] }}%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <x-core::icon name="ti ti-coins" class="text-warning" />
                                </div>
                                <div class="stats-info">
                                    <h6>{{ trans('plugins/affiliate-pro::affiliate.total_earnings') }}</h6>
                                    <p class="text-warning fw-bold">{{ format_price($statistics['total_earnings']) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Performance Trend Chart Card --}}
            <div class="bb-customer-card performance-trend-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.performance_trend') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <x-core::icon name="ti ti-chart-line" class="text-primary" />
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="chart-container">
                        <canvas id="performance-chart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- Conversion Analytics Chart Card --}}
            <div class="bb-customer-card conversion-analytics-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.conversion_analytics') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <x-core::icon name="ti ti-chart-donut" class="text-success" />
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="chart-container">
                        <canvas id="conversion-chart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- Geographic Analytics Card --}}
            <div class="bb-customer-card geographic-analytics-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.geographic_analytics') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <x-core::icon name="ti ti-world" class="text-info" />
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="chart-section">
                                <h6 class="chart-title">
                                    <x-core::icon name="ti ti-flag" class="me-2" />
                                    {{ trans('plugins/affiliate-pro::affiliate.top_countries') }}
                                </h6>
                                <div class="chart-container">
                                    @if(count($statistics['geo_countries']) > 0)
                                        <canvas id="countries-chart" style="height: 300px;"></canvas>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="empty-chart-state">
                                                <x-core::icon name="ti ti-map-off" class="text-muted mb-2" style="font-size: 2rem;" />
                                                <p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_geographic_data') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-section">
                                <h6 class="chart-title">
                                    <x-core::icon name="ti ti-building" class="me-2" />
                                    {{ trans('plugins/affiliate-pro::affiliate.top_cities') }}
                                </h6>
                                <div class="chart-container">
                                    @if(count($statistics['geo_cities']) > 0)
                                        <canvas id="cities-chart" style="height: 300px;"></canvas>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="empty-chart-state">
                                                <x-core::icon name="ti ti-building-off" class="text-muted mb-2" style="font-size: 2rem;" />
                                                <p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_geographic_data') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Click History Card --}}
            <div class="bb-customer-card click-history-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.click_history') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <span class="badge bg-info">{{ count($clicks) }}</span>
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    @if(count($clicks) > 0)
                        <div class="bb-customer-card-list">
                            @foreach($clicks as $click)
                                <div class="bb-customer-card-content click-item">
                                    <div class="bb-customer-card-image">
                                        <div class="click-icon">
                                            @if($click->converted)
                                                <x-core::icon name="ti ti-circle-check" class="text-success" style="font-size: 2rem;" />
                                            @else
                                                <x-core::icon name="ti ti-click" class="text-primary" style="font-size: 2rem;" />
                                            @endif
                                        </div>
                                    </div>
                                    <div class="bb-customer-card-details">
                                        <div class="bb-customer-card-name">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-bold">{{ $click->created_at->translatedFormat('M d, Y H:i:s') }}</span>
                                                @if($click->converted)
                                                    <span class="badge bg-success">{{ trans('plugins/affiliate-pro::affiliate.yes') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ trans('plugins/affiliate-pro::affiliate.no') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="bb-customer-card-meta">
                                            <div class="click-details">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <div class="info-item">
                                                            <span class="label">{{ trans('plugins/affiliate-pro::affiliate.referrer_url') }}:</span>
                                                            <span class="value">
                                                                @if($click->referrer_url)
                                                                    <a href="{{ $click->referrer_url }}" target="_blank" class="text-decoration-none">
                                                                        <x-core::icon name="ti ti-external-link" class="me-1" />
                                                                        {{ Str::limit($click->referrer_url, 40) }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.direct') }}</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="info-item">
                                                            <span class="label">{{ trans('plugins/affiliate-pro::affiliate.landing_url') }}:</span>
                                                            <span class="value">
                                                                <a href="{{ $click->landing_url }}" target="_blank" class="text-decoration-none">
                                                                    <x-core::icon name="ti ti-external-link" class="me-1" />
                                                                    {{ Str::limit($click->landing_url, 40) }}
                                                                </a>
                                                            </span>
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

                        <div class="mt-3">
                            {!! $clicks->appends(request()->query())->links() !!}
                        </div>
                    @else
                        <div class="text-center p-4">
                            <div class="empty-state">
                                <x-core::icon name="ti ti-hand-click-off" class="text-muted mb-3" style="font-size: 3rem;" />
                                <h5 class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_click_data') }}</h5>
                                <p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_click_data') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Commission History Card --}}
            <div class="bb-customer-card commission-history-card">
                <div class="bb-customer-card-header">
                    <div class="bb-customer-card-title">
                        <span class="value">{{ trans('plugins/affiliate-pro::affiliate.commission_history') }}</span>
                    </div>
                    <div class="bb-customer-card-status">
                        <span class="badge bg-success">{{ count($commissions) }}</span>
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    @if(count($commissions) > 0)
                        <div class="bb-customer-card-list">
                            @foreach($commissions as $commission)
                                <div class="bb-customer-card-content commission-item">
                                    <div class="bb-customer-card-image">
                                        <div class="commission-icon">
                                            @if($commission->status == 'pending')
                                                <x-core::icon name="ti ti-clock" class="text-warning" style="font-size: 2rem;" />
                                            @elseif($commission->status == 'approved')
                                                <x-core::icon name="ti ti-circle-check" class="text-success" style="font-size: 2rem;" />
                                            @elseif($commission->status == 'rejected')
                                                <x-core::icon name="ti ti-circle-x" class="text-danger" style="font-size: 2rem;" />
                                            @endif
                                        </div>
                                    </div>
                                    <div class="bb-customer-card-details">
                                        <div class="bb-customer-card-name">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-bold">{{ format_price($commission->amount) }}</span>
                                                @if($commission->status == 'pending')
                                                    <span class="badge bg-warning text-dark">{{ trans('plugins/affiliate-pro::commission.statuses.pending') }}</span>
                                                @elseif($commission->status == 'approved')
                                                    <span class="badge bg-success">{{ trans('plugins/affiliate-pro::commission.statuses.approved') }}</span>
                                                @elseif($commission->status == 'rejected')
                                                    <span class="badge bg-danger">{{ trans('plugins/affiliate-pro::commission.statuses.rejected') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="bb-customer-card-meta">
                                            <div class="commission-details">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <div class="info-item">
                                                            <span class="label">{{ trans('plugins/affiliate-pro::commission.order') }}:</span>
                                                            <span class="value fw-bold">#{{ $commission->order_id }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="info-item">
                                                            <span class="label">{{ trans('plugins/affiliate-pro::commission.date') }}:</span>
                                                            <span class="value">{{ $commission->created_at->translatedFormat('M d, Y') }}</span>
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

                        <div class="mt-3">
                            {!! $commissions->appends(request()->query())->links() !!}
                        </div>
                    @else
                        <div class="text-center p-4">
                            <div class="empty-state">
                                <x-core::icon name="ti ti-coin-off" class="text-muted mb-3" style="font-size: 3rem;" />
                                <h5 class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_commissions') }}</h5>
                                <p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_commissions') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- JavaScript for Charts --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        // Debug: Log chart data
        console.log('Chart Data Debug:', {
            dates: {!! json_encode($statistics['chart_dates'] ?? []) !!},
            clicks: {!! json_encode($statistics['chart_clicks'] ?? []) !!},
            conversions: {!! json_encode($statistics['chart_conversions'] ?? []) !!},
            countries: {!! json_encode($statistics['geo_countries'] ?? []) !!},
            cities: {!! json_encode($statistics['geo_cities'] ?? []) !!},
            totalClicks: {{ $statistics['total_clicks'] ?? 0 }},
            totalConversions: {{ $statistics['total_conversions'] ?? 0 }}
        });

        // Performance Trend Chart
        try {
            const performanceCtx = document.getElementById('performance-chart');
            if (performanceCtx) {
                const chartDates = {!! json_encode($statistics['chart_dates'] ?? []) !!};
                const chartClicks = {!! json_encode($statistics['chart_clicks'] ?? []) !!};
                const chartConversions = {!! json_encode($statistics['chart_conversions'] ?? []) !!};

                // Check if we have data
                if (chartDates.length === 0) {
                    performanceCtx.parentElement.innerHTML = '<div class="text-center py-4"><p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.no_data_available') }}</p></div>';
                } else {
                    const chartData = {
                        labels: chartDates,
                        datasets: [
                            {
                                label: '{{ trans('plugins/affiliate-pro::affiliate.clicks') }}',
                                data: chartClicks,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: '{{ trans('plugins/affiliate-pro::affiliate.conversions') }}',
                                data: chartConversions,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false
                            }
                        ]
                    };

                    const performanceChart = new Chart(performanceCtx, {
                        type: 'line',
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff'
                                }
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: '{{ trans('plugins/affiliate-pro::affiliate.date') }}'
                                    }
                                },
                                y: {
                                    display: true,
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: '{{ trans('plugins/affiliate-pro::affiliate.count') }}'
                                    }
                                }
                            }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error creating performance chart:', error);
            const performanceContainer = document.getElementById('performance-chart').parentElement;
            performanceContainer.innerHTML = '<div class="text-center py-4"><p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.error_loading_chart') }}</p></div>';
        }

        // Conversion Analytics Chart
        try {
            const conversionCtx = document.getElementById('conversion-chart');
            if (conversionCtx) {
                const totalConversions = {{ $statistics['total_conversions'] ?? 0 }};
                const totalClicks = {{ $statistics['total_clicks'] ?? 0 }};
                const notConverted = Math.max(0, totalClicks - totalConversions);

                const conversionChart = new Chart(conversionCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['{{ trans('plugins/affiliate-pro::affiliate.converted') }}', '{{ trans('plugins/affiliate-pro::affiliate.not_converted') }}'],
                        datasets: [{
                            data: [totalConversions, notConverted],
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(201, 203, 207, 0.8)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(201, 203, 207, 1)'
                            ],
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error creating conversion chart:', error);
            const conversionContainer = document.getElementById('conversion-chart').parentElement;
            conversionContainer.innerHTML = '<div class="text-center py-4"><p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.error_loading_chart') }}</p></div>';
        }

        // Geographic Charts - Countries
        @if(count($statistics['geo_countries']) > 0)
        try {
            const countriesCtx = document.getElementById('countries-chart');
            if (countriesCtx) {
                const countriesChart = new Chart(countriesCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_keys($statistics['geo_countries'])) !!},
                        datasets: [{
                            label: '{{ trans('plugins/affiliate-pro::affiliate.clicks_by_country') }}',
                            data: {!! json_encode(array_values($statistics['geo_countries'])) !!},
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff'
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: '{{ trans('plugins/affiliate-pro::affiliate.clicks') }}'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: '{{ trans('plugins/affiliate-pro::affiliate.countries') }}'
                                }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error creating countries chart:', error);
            const countriesContainer = document.getElementById('countries-chart').parentElement;
            countriesContainer.innerHTML = '<div class="text-center py-4"><p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.error_loading_chart') }}</p></div>';
        }
        @endif

        // Geographic Charts - Cities
        @if(count($statistics['geo_cities']) > 0)
        try {
            const citiesCtx = document.getElementById('cities-chart');
            if (citiesCtx) {
                const citiesChart = new Chart(citiesCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_keys($statistics['geo_cities'])) !!},
                        datasets: [{
                            label: '{{ trans('plugins/affiliate-pro::affiliate.clicks_by_city') }}',
                            data: {!! json_encode(array_values($statistics['geo_cities'])) !!},
                            backgroundColor: 'rgba(75, 192, 192, 0.8)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff'
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: '{{ trans('plugins/affiliate-pro::affiliate.clicks') }}'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: '{{ trans('plugins/affiliate-pro::affiliate.cities') }}'
                                }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error creating cities chart:', error);
            const citiesContainer = document.getElementById('cities-chart').parentElement;
            citiesContainer.innerHTML = '<div class="text-center py-4"><p class="text-muted">{{ trans('plugins/affiliate-pro::affiliate.error_loading_chart') }}</p></div>';
        }
        @endif
    });
    </script>

    {{-- CSS Styles for Charts and Components --}}
    <style>
    /* Stats Cards */
    .stats-card {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
        height: 100%;
        border: 1px solid #e9ecef;
    }

    .stats-card:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-color: #dee2e6;
    }

    .stats-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        margin-right: 1rem;
    }

    .stats-info h6 {
        margin: 0;
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }

    .stats-info p {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    /* Chart Containers */
    .chart-container {
        position: relative;
        background-color: #fff;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e9ecef;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chart-container canvas {
        max-height: 300px;
        width: 100% !important;
        height: auto !important;
    }

    .chart-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e9ecef;
    }

    .chart-title {
        margin-bottom: 1rem;
        color: #495057;
        font-weight: 600;
    }

    .empty-chart-state {
        padding: 2rem;
    }

    /* Click and Commission Items */
    .click-item,
    .commission-item {
        transition: all 0.3s ease;
        border-radius: 8px;
        padding: 1.5rem;
        background-color: #f8f9fa;
        margin-bottom: 1rem;
        border: 1px solid #e9ecef;
    }

    .click-item:hover,
    .commission-item:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-color: #dee2e6;
    }

    .click-icon,
    .commission-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        margin-right: 1rem;
    }

    /* Info Items */
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

    .info-item .value a {
        color: #0d6efd;
        text-decoration: none;
    }

    .info-item .value a:hover {
        color: #0a58ca;
        text-decoration: underline;
    }

    /* Card Backgrounds */
    .performance-overview-card .bb-customer-card-body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .date-filter-card .bb-customer-card-body {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    }

    .performance-trend-card .bb-customer-card-body,
    .conversion-analytics-card .bb-customer-card-body,
    .geographic-analytics-card .bb-customer-card-body {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    }

    /* Form Enhancements */
    .date-filter-card .form-label {
        font-weight: 600;
        color: #495057;
    }

    /* Empty States */
    .empty-state {
        padding: 2rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-card {
            flex-direction: column;
            text-align: center;
            padding: 1rem;
        }

        .stats-icon {
            margin-right: 0;
            margin-bottom: 0.75rem;
            width: 40px;
            height: 40px;
        }

        .stats-info p {
            font-size: 1.25rem;
        }

        .click-item,
        .commission-item {
            padding: 1rem;
        }

        .click-icon,
        .commission-icon {
            width: 50px;
            height: 50px;
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

        .chart-section {
            margin-bottom: 1rem;
        }
    }

    /* Chart Responsive */
    @media (max-width: 576px) {
        .chart-container {
            padding: 0.5rem;
        }

        .chart-section {
            padding: 0.75rem;
        }
    }

    /* Badge Enhancements */
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }

    /* Loading States */
    .chart-container canvas {
        transition: opacity 0.3s ease;
    }

    /* Date Range Badge */
    .bb-customer-card-status .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    /* Hover Effects for Links */
    .bb-customer-card-content a {
        transition: all 0.2s ease;
    }

    .bb-customer-card-content a:hover {
        transform: translateX(2px);
    }
    </style>
@endsection

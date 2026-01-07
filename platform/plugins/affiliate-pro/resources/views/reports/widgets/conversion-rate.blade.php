<div class="card mb-3">
    <div class="card-header">
        <h4 class="card-title">{{ trans('plugins/affiliate-pro::reports.conversion_rate_analysis') }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center mb-3 mb-md-0">
                <div class="h1 text-primary mb-0">{{ number_format($conversionRate, 2) }}%</div>
                <div class="text-muted">{{ trans('plugins/affiliate-pro::reports.overall_conversion_rate') }}</div>
            </div>
            <div class="col-md-8">
                <div class="conversion-chart-container" style="height: 200px;">
                    <canvas id="conversion-rate-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h5>{{ trans('plugins/affiliate-pro::reports.conversion_breakdown') }}</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('plugins/affiliate-pro::reports.source') }}</th>
                            <th>{{ trans('plugins/affiliate-pro::reports.clicks') }}</th>
                            <th>{{ trans('plugins/affiliate-pro::reports.conversions') }}</th>
                            <th>{{ trans('plugins/affiliate-pro::reports.rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conversionSources as $source)
                            <tr>
                                <td>{{ $source['name'] }}</td>
                                <td>{{ number_format($source['clicks']) }}</td>
                                <td>{{ number_format($source['conversions']) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $source['rate'] }}%"></div>
                                        </div>
                                        <span>{{ number_format($source['rate'], 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($conversionRate < 2)
            <div class="mt-4">
                <div class="card border-0 bg-warning bg-opacity-10 border-start border-warning border-4 conversion-rate-performance-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-warning bg-opacity-20 rounded-circle p-2 d-inline-flex performance-icon-wrapper">
                                    <x-core::icon name="ti ti-alert-triangle" class="text-white" size="md" />
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="text-warning mb-2 fw-semibold">
                                    <x-core::icon name="ti ti-trending-up" class="me-1" size="sm" />
                                    {{ trans('plugins/affiliate-pro::reports.optimization_opportunity') }}
                                </h5>
                                <p class="text-muted mb-3">{{ trans('plugins/affiliate-pro::reports.conversion_rate_below_average') }}</p>

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center p-2 bg-white rounded performance-tip">
                                            <x-core::icon name="ti ti-target" class="text-primary me-2" size="sm" />
                                            <small class="text-muted">{{ trans('plugins/affiliate-pro::reports.target_relevant_audiences') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center p-2 bg-white rounded performance-tip">
                                            <x-core::icon name="ti ti-edit" class="text-info me-2" size="sm" />
                                            <small class="text-muted">{{ trans('plugins/affiliate-pro::reports.improve_promotional_content') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center p-2 bg-white rounded performance-tip">
                                            <x-core::icon name="ti ti-chart-line" class="text-success me-2" size="sm" />
                                            <small class="text-muted">{{ trans('plugins/affiliate-pro::reports.focus_higher_conversion_products') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($conversionRate >= 5)
            <div class="mt-4">
                <div class="card border-0 bg-success bg-opacity-10 border-start border-success border-4 conversion-rate-performance-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-success bg-opacity-20 rounded-circle p-2 d-inline-flex performance-icon-wrapper">
                                    <x-core::icon name="ti ti-trophy" class="text-success" size="md" />
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="text-success mb-2 fw-semibold">
                                    <x-core::icon name="ti ti-circle-check" class="me-1" size="sm" />
                                    {{ trans('plugins/affiliate-pro::reports.great_performance') }}
                                </h5>
                                <p class="text-muted mb-0">{{ trans('plugins/affiliate-pro::reports.excellent_conversion_rate') }}</p>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="badge bg-success bg-opacity-20 text-success fs-6 px-3 py-2 performance-badge">
                                    <x-core::icon name="ti ti-trending-up" class="me-1" size="sm" />
                                    {{ number_format($conversionRate, 1) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($conversionRate >= 2 && $conversionRate < 5)
            <div class="mt-4">
                <div class="card border-0 bg-info bg-opacity-10 border-start border-info border-4 conversion-rate-performance-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-info bg-opacity-20 rounded-circle p-2 d-inline-flex performance-icon-wrapper">
                                    <x-core::icon name="ti ti-chart-bar" class="text-info" size="md" />
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="text-info mb-2 fw-semibold">
                                    <x-core::icon name="ti ti-activity" class="me-1" size="sm" />
                                    {{ trans('plugins/affiliate-pro::reports.good_performance') }}
                                </h5>
                                <p class="text-muted mb-0">{{ trans('plugins/affiliate-pro::reports.average_conversion_rate') }}</p>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="badge bg-info bg-opacity-20 text-info fs-6 px-3 py-2 performance-badge">
                                    <x-core::icon name="ti ti-chart-line" class="me-1" size="sm" />
                                    {{ number_format($conversionRate, 1) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('footer')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('conversion-rate-chart').getContext('2d');

        const chartData = {
            labels: @json($chartLabels),
            datasets: [{
                label: '{{ trans('plugins/affiliate-pro::reports.conversion_rate_percentage') }}',
                data: @json($chartData),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush

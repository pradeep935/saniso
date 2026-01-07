<div class="card mb-3">
    <div class="card-header">
        <h4 class="card-title">{{ trans('plugins/affiliate-pro::reports.performance_metrics') }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <div class="text-primary mb-2">
                            <x-core::icon name="ti ti-click" class="text-primary" style="font-size: 2rem;" />
                        </div>
                        <h3 class="mb-1">{{ number_format($totalClicks) }}</h3>
                        <div class="text-muted">{{ trans('plugins/affiliate-pro::reports.total_clicks') }}</div>

                        @if($clicksChange > 0)
                            <div class="text-success small mt-2">
                                <x-core::icon name="ti ti-trending-up" /> {{ number_format($clicksChange, 1) }}% {{ trans('plugins/affiliate-pro::reports.vs_previous') }}
                            </div>
                        @elseif($clicksChange < 0)
                            <div class="text-danger small mt-2">
                                <x-core::icon name="ti ti-trending-down" /> {{ number_format(abs($clicksChange), 1) }}% {{ trans('plugins/affiliate-pro::reports.vs_previous') }}
                            </div>
                        @else
                            <div class="text-muted small mt-2">
                                <x-core::icon name="ti ti-minus" /> {{ trans('plugins/affiliate-pro::reports.no_change') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <div class="text-success mb-2">
                            <x-core::icon name="ti ti-shopping-cart" class="text-success" style="font-size: 2rem;" />
                        </div>
                        <h3 class="mb-1">{{ number_format($totalConversions) }}</h3>
                        <div class="text-muted">{{ trans('plugins/affiliate-pro::reports.conversions') }}</div>

                        @if($conversionsChange > 0)
                            <div class="text-success small mt-2">
                                <x-core::icon name="ti ti-trending-up" /> {{ number_format($conversionsChange, 1) }}% {{ trans('plugins/affiliate-pro::reports.vs_previous') }}
                            </div>
                        @elseif($conversionsChange < 0)
                            <div class="text-danger small mt-2">
                                <x-core::icon name="ti ti-trending-down" /> {{ number_format(abs($conversionsChange), 1) }}% {{ trans('plugins/affiliate-pro::reports.vs_previous') }}
                            </div>
                        @else
                            <div class="text-muted small mt-2">
                                <x-core::icon name="ti ti-minus" /> {{ trans('plugins/affiliate-pro::reports.no_change') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2">
                            <x-core::icon name="ti ti-coin" class="text-warning" style="font-size: 2rem;" />
                        </div>
                        <h3 class="mb-1">{{ format_price($totalEarnings) }}</h3>
                        <div class="text-muted">{{ trans('plugins/affiliate-pro::reports.earnings') }}</div>

                        @if($earningsChange > 0)
                            <div class="text-success small mt-2">
                                <x-core::icon name="ti ti-trending-up" /> {{ number_format($earningsChange, 1) }}% {{ trans('plugins/affiliate-pro::reports.vs_previous') }}
                            </div>
                        @elseif($earningsChange < 0)
                            <div class="text-danger small mt-2">
                                <x-core::icon name="ti ti-trending-down" /> {{ number_format(abs($earningsChange), 1) }}% {{ trans('plugins/affiliate-pro::reports.vs_previous') }}
                            </div>
                        @else
                            <div class="text-muted small mt-2">
                                <x-core::icon name="ti ti-minus" /> {{ trans('plugins/affiliate-pro::reports.no_change') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center">
                        <div class="text-info mb-2">
                            <x-core::icon name="ti ti-chart-bar" class="text-info" style="font-size: 2rem;" />
                        </div>
                        <h3 class="mb-1">{{ format_price($averageCommission) }}</h3>
                        <div class="text-muted">{{ trans('plugins/affiliate-pro::reports.avg_commission') }}</div>

                        @if($avgCommissionChange > 0)
                            <div class="text-success small mt-2">
                                <x-core::icon name="ti ti-trending-up" /> {{ number_format($avgCommissionChange, 1) }}% {{ trans('plugins/affiliate-pro::reports.vs_previous') }}
                            </div>
                        @elseif($avgCommissionChange < 0)
                            <div class="text-danger small mt-2">
                                <x-core::icon name="ti ti-trending-down" /> {{ number_format(abs($avgCommissionChange), 1) }}% {{ trans('plugins/affiliate-pro::reports.vs_previous') }}
                            </div>
                        @else
                            <div class="text-muted small mt-2">
                                <x-core::icon name="ti ti-minus" /> {{ trans('plugins/affiliate-pro::reports.no_change') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h5>{{ trans('plugins/affiliate-pro::reports.performance_over_time') }}</h5>
            <div class="performance-chart-container" style="height: 300px;">
                <canvas id="performance-chart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('footer')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('performance-chart').getContext('2d');

        const chartData = {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: '{{ trans('plugins/affiliate-pro::reports.clicks') }}',
                    data: @json($chartClicks),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    yAxisID: 'y'
                },
                {
                    label: '{{ trans('plugins/affiliate-pro::reports.conversions') }}',
                    data: @json($chartConversions),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    yAxisID: 'y'
                },
                {
                    label: '{{ trans('plugins/affiliate-pro::reports.earnings') }}',
                    data: @json($chartEarnings),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        };

        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.label === '{{ trans('plugins/affiliate-pro::reports.earnings') }}') {
                                    return label + '{{ get_application_currency()->symbol }}' + context.parsed.y.toFixed(2);
                                }
                                return label + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: '{{ trans('plugins/affiliate-pro::reports.clicks_conversions') }}'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: '{{ trans('plugins/affiliate-pro::reports.earnings') }}'
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '{{ get_application_currency()->symbol }}' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush

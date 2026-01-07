<div class="row">
    <div class="col-md-6">
        {!! app(\Botble\AffiliatePro\Http\Controllers\ReportController::class)->getPerformanceMetricsWidget(request()) !!}
    </div>
    <div class="col-md-6">
        {!! app(\Botble\AffiliatePro\Http\Controllers\ReportController::class)->getConversionRateWidget(request()) !!}
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>
                    {{ trans('plugins/affiliate-pro::reports.geographic_data') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                <div class="row">
                    <div class="col-md-8">
                        <div id="world-map" style="height: 400px;"></div>
                    </div>
                    <div class="col-md-4">
                        <h5>{{ trans('plugins/affiliate-pro::reports.top_countries') }}</h5>
                        <div id="country-chart" style="height: 200px;"></div>

                        <h5 class="mt-4">{{ trans('plugins/affiliate-pro::reports.top_cities') }}</h5>
                        <div id="city-chart" style="height: 200px;"></div>
                    </div>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>
                    {{ trans('plugins/affiliate-pro::reports.short_link_performance') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                <div class="row">
                    <div class="col-md-8">
                        <div id="short-link-chart" style="height: 400px;"></div>
                    </div>
                    <div class="col-md-4">
                        <h5>{{ trans('plugins/affiliate-pro::reports.top_performing_links') }}</h5>
                        <div id="top-links-table" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ trans('plugins/affiliate-pro::reports.link') }}</th>
                                        <th>{{ trans('plugins/affiliate-pro::reports.clicks') }}</th>
                                        <th>{{ trans('plugins/affiliate-pro::reports.conversions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3" class="text-center">{{ trans('plugins/affiliate-pro::reports.loading') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>
                    {{ trans('plugins/affiliate-pro::reports.commission_trends') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                <div id="commission-chart" style="height: 400px;"></div>
            </x-core::card.body>
        </x-core::card>
    </div>
</div>

@push('footer')
@php
    Assets::addScriptsDirectly('https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js');
    Assets::addScriptsDirectly('https://cdn.jsdelivr.net/npm/jsvectormap/dist/js/jsvectormap.min.js');
    Assets::addScriptsDirectly('https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js');
    Assets::addStylesDirectly('https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css');
@endphp

<script>
    $(document).ready(function() {
        // Load geographic data
        $.ajax({
            url: '{{ route('affiliate-pro.reports.geographic-data') }}',
            type: 'GET',
            data: {
                start_date: '{{ $startDate }}',
                end_date: '{{ $endDate }}'
            },
            success: function(res) {
                if (!res.error) {
                    renderGeographicData(res.data);
                }
            }
        });

        // Load short link performance data
        $.ajax({
            url: '{{ route('affiliate-pro.reports.short-link-performance') }}',
            type: 'GET',
            data: {
                start_date: '{{ $startDate }}',
                end_date: '{{ $endDate }}'
            },
            success: function(res) {
                if (!res.error) {
                    renderShortLinkPerformance(res.data);
                }
            }
        });

        // Load commission data
        $.ajax({
            url: '{{ route('affiliate-pro.reports.commission-data') }}',
            type: 'GET',
            data: {
                start_date: '{{ $startDate }}',
                end_date: '{{ $endDate }}'
            },
            success: function(res) {
                if (!res.error) {
                    renderCommissionData(res.data);
                }
            }
        });

        function renderGeographicData(data) {
            if (!data.country_data || data.country_data.length === 0) {
                $('#world-map').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.no_geographic_data') }}</p>
                    </div>
                `);
            } else {
                // Prepare data for the world map
                const mapData = {};
                data.country_data.forEach(function(item) {
                    mapData[item.country] = item.total;
                });

                try {
                    // Initialize the world map
                    const map = new jsVectorMap({
                        selector: '#world-map',
                        map: 'world',
                        zoomButtons: true,
                        zoomOnScroll: true,
                        markersSelectable: true,
                        markers: [],
                        markerStyle: {
                            initial: {
                                r: 7,
                                fill: '#ff5d5d',
                                stroke: '#fff',
                                strokeWidth: 2,
                            }
                        },
                        regionStyle: {
                            initial: {
                                fill: '#e2e8f0',
                                stroke: '#fff',
                                strokeWidth: 0.5,
                            },
                            hover: {
                                fill: '#cbd5e1',
                            },
                            selected: {
                                fill: '#94a3b8',
                            }
                        },
                        series: {
                            regions: [{
                                values: mapData,
                                scale: ['#c7d2fe', '#4f46e5'],
                                normalizeFunction: 'polynomial'
                            }]
                        },
                        onRegionTipShow: function(event, element, code) {
                            const total = mapData[code] || 0;
                            element.html(element.html() + ': ' + total + ' {{ trans('plugins/affiliate-pro::reports.clicks') }}');
                        }
                    });
                } catch (error) {
                    console.error('Error rendering world map:', error);
                    $('#world-map').html(`
                        <div class="text-center py-5">
                            <p>{{ trans('plugins/affiliate-pro::reports.chart_error') }}</p>
                        </div>
                    `);
                }
            }

            // Render country chart
            if (!data.country_data || data.country_data.length === 0) {
                $('#country-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.no_geographic_data') }}</p>
                    </div>
                `);
                return;
            }

            const countryLabels = data.country_data.slice(0, 5).map(item => item.country);
            const countrySeries = data.country_data.slice(0, 5).map(item => item.total);

            if (countryLabels.length === 0 || countrySeries.length === 0) {
                $('#country-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.no_geographic_data') }}</p>
                    </div>
                `);
                return;
            }

            const countryChart = new ApexCharts(document.querySelector('#country-chart'), {
                series: countrySeries,
                chart: {
                    type: 'bar',
                    height: 200,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                colors: ['#4f46e5'],
                dataLabels: {
                    enabled: true,
                    offsetX: -6,
                    style: {
                        fontSize: '12px',
                        colors: ['#fff']
                    }
                },
                xaxis: {
                    categories: countryLabels,
                }
            });

            try {
                countryChart.render();
            } catch (error) {
                console.error('Error rendering country chart:', error);
                $('#country-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.chart_error') }}</p>
                    </div>
                `);
            }

            // Render city chart
            const cityData = [];
            const topCountry = data.country_data[0]?.country;

            if (!topCountry || !data.city_data || !data.city_data[topCountry] || data.city_data[topCountry].length === 0) {
                $('#city-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.no_city_data') }}</p>
                    </div>
                `);
                return;
            }

            const cityLabels = data.city_data[topCountry].map(item => item.city);
            const citySeries = data.city_data[topCountry].map(item => item.total);

            if (cityLabels.length === 0 || citySeries.length === 0) {
                $('#city-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.no_city_data') }}</p>
                    </div>
                `);
                return;
            }

            const cityChart = new ApexCharts(document.querySelector('#city-chart'), {
                series: citySeries,
                chart: {
                    type: 'bar',
                    height: 200,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                colors: ['#10b981'],
                dataLabels: {
                    enabled: true,
                    offsetX: -6,
                    style: {
                        fontSize: '12px',
                        colors: ['#fff']
                    }
                },
                xaxis: {
                    categories: cityLabels,
                },
                title: {
                    text: topCountry,
                    align: 'center',
                    style: {
                        fontSize: '14px'
                    }
                }
            });

            try {
                cityChart.render();
            } catch (error) {
                console.error('Error rendering city chart:', error);
                $('#city-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.chart_error') }}</p>
                    </div>
                `);
            }
        }

        function renderShortLinkPerformance(data) {
            // Render short link chart
            if (data.daily_clicks && data.daily_clicks.length > 0) {
                // Check if any series has data
                let hasData = false;
                for (const series of data.daily_clicks) {
                    if (series.data && series.data.length > 0) {
                        hasData = true;
                        break;
                    }
                }

                if (!hasData) {
                    $('#short-link-chart').html(`
                        <div class="text-center py-5">
                            <p>{{ trans('plugins/affiliate-pro::reports.no_data') }}</p>
                        </div>
                    `);
                    return;
                }

                // Format the data properly for ApexCharts
                const formattedSeries = data.daily_clicks.map(series => {
                    return {
                        name: series.name,
                        data: series.data && series.data.length > 0 ? series.data.map(item => {
                            return {
                                x: new Date(item.x).getTime(),
                                y: item.y
                            };
                        }) : []
                    };
                });

                const shortLinkChart = new ApexCharts(document.querySelector('#short-link-chart'), {
                    series: formattedSeries,
                    chart: {
                        type: 'line',
                        height: 400,
                        toolbar: {
                            show: true
                        },
                        zoom: {
                            enabled: true
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 4
                    },
                    xaxis: {
                        type: 'datetime'
                    },
                    yaxis: {
                        title: {
                            text: '{{ trans('plugins/affiliate-pro::reports.clicks') }}'
                        }
                    },
                    tooltip: {
                        x: {
                            format: 'dd MMM yyyy'
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                });

                try {
                    shortLinkChart.render();
                } catch (error) {
                    console.error('Error rendering short link chart:', error);
                    $('#short-link-chart').html(`
                        <div class="text-center py-5">
                            <p>{{ trans('plugins/affiliate-pro::reports.chart_error') }}</p>
                        </div>
                    `);
                }
            }

            // Render top links table
            if (data.short_links && data.short_links.length > 0) {
                let tableHtml = '';

                data.short_links.forEach(function(link) {
                    const title = link.title || `Link #${link.id}`;
                    const productName = link.product ? ` (${link.product.name})` : '';

                    tableHtml += `
                        <tr>
                            <td title="${title}${productName}">
                                ${title.length > 20 ? title.substring(0, 20) + '...' : title}
                            </td>
                            <td>${link.clicks}</td>
                            <td>${link.conversions}</td>
                        </tr>
                    `;
                });

                $('#top-links-table tbody').html(tableHtml);
            } else {
                $('#top-links-table tbody').html(`
                    <tr>
                        <td colspan="3" class="text-center">{{ trans('plugins/affiliate-pro::reports.no_data') }}</td>
                    </tr>
                `);
            }
        }

        function renderCommissionData(data) {
            if (!data.commission_data || data.commission_data.length === 0) {
                $('#commission-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.no_data') }}</p>
                    </div>
                `);
                return;
            }

            // Check if all values are zero
            let hasNonZeroData = false;
            for (const item of data.commission_data) {
                if (item.y > 0) {
                    hasNonZeroData = true;
                    break;
                }
            }

            if (!hasNonZeroData) {
                $('#commission-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.no_commission_data') }}</p>
                    </div>
                `);
                return;
            }

            // Format the data properly for ApexCharts
            const formattedData = data.commission_data.map(item => {
                return {
                    x: new Date(item.x).getTime(),
                    y: item.y,
                    count: item.count
                };
            });

            // Make sure we have a valid data array for ApexCharts
            if (!formattedData || formattedData.length === 0) {
                $('#commission-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.no_commission_data') }}</p>
                    </div>
                `);
                return;
            }

            const commissionChart = new ApexCharts(document.querySelector('#commission-chart'), {
                series: [{
                    name: '{{ trans('plugins/affiliate-pro::reports.commission_amount') }}',
                    data: formattedData
                }],
                chart: {
                    type: 'area',
                    height: 400,
                    toolbar: {
                        show: true
                    },
                    zoom: {
                        enabled: true
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3,
                    }
                },
                markers: {
                    size: 4
                },
                xaxis: {
                    type: 'datetime'
                },
                yaxis: {
                    title: {
                        text: '{{ trans('plugins/affiliate-pro::reports.commission_amount') }}'
                    },
                    labels: {
                        formatter: function(val) {
                            return '{{ get_application_currency()->symbol }}' + val.toFixed(2);
                        }
                    }
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy'
                    },
                    y: {
                        formatter: function(val) {
                            return '{{ get_application_currency()->symbol }}' + val.toFixed(2);
                        }
                    }
                }
            });

            try {
                commissionChart.render();
            } catch (error) {
                console.error('Error rendering commission chart:', error);
                $('#commission-chart').html(`
                    <div class="text-center py-5">
                        <p>{{ trans('plugins/affiliate-pro::reports.chart_error') }}</p>
                    </div>
                `);
            }
        }
    });
</script>
@endpush

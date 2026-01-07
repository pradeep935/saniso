$(document).ready(function() {
    'use strict';
    
    // Initialize date range picker
    if (jQuery().daterangepicker && window.moment) {
        let $dateRange = $(document).find('.date-range-picker');
        
        if ($dateRange.length) {
            let dateFormat = $dateRange.data('format') || 'YYYY-MM-DD';
            let startDate = $dateRange.data('start-date') ? moment($dateRange.data('start-date')) : moment().startOf('month');
            let endDate = $dateRange.data('end-date') ? moment($dateRange.data('end-date')) : moment().endOf('month');
            
            let ranges = {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            };
            
            $dateRange.daterangepicker({
                ranges: ranges,
                alwaysShowCalendars: true,
                startDate: startDate,
                endDate: endDate,
                opens: 'left',
                drops: 'auto',
                locale: {
                    format: dateFormat
                }
            }, function(start, end, label) {
                $.ajax({
                    url: $dateRange.data('href'),
                    data: {
                        start_date: start.format('YYYY-MM-DD'),
                        end_date: end.format('YYYY-MM-DD')
                    },
                    type: 'GET',
                    success: function(data) {
                        if (data.error) {
                            Botble.showError(data.message);
                        } else {
                            window.location.reload();
                        }
                    },
                    error: function(data) {
                        Botble.handleError(data);
                    }
                });
            });
            
            $dateRange.on('apply.daterangepicker', function(ev, picker) {
                let $this = $(this);
                let formatValue = $this.data('format-value');
                if (!formatValue) {
                    formatValue = '__from__ - __to__';
                }
                let value = formatValue
                    .replace('__from__', picker.startDate.format(dateFormat))
                    .replace('__to__', picker.endDate.format(dateFormat));
                $this.find('span').text(value);
            });
        }
    }
});

{{ $widget->render('affiliate-pro') }}

<div class="mt-4">
    @include('plugins/affiliate-pro::reports.widgets.enhanced-reports', ['startDate' => $startDate, 'endDate' => $endDate])
</div>

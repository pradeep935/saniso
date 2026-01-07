<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\Base\Widgets\Card;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Illuminate\Http\Request;

class EnhancedReportsWidget extends Card
{
    public function getOptions(): array
    {
        return [
            'id' => 'widget_enhanced_reports',
            'width' => 'col-12',
        ];
    }

    public function getViewData(): array
    {
        $request = app(Request::class);
        [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport($request);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    public function getContent(): string
    {
        return view('plugins/affiliate-pro::reports.widgets.enhanced-reports', $this->getViewData())->render();
    }
}

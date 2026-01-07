<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\AffiliatePro\Models\Commission;
use Botble\Base\Widgets\Card;
use Carbon\CarbonPeriod;

class CommissionCard extends Card
{
    protected int $columns = 4;

    public function getOptions(): array
    {
        $data = Commission::query()
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->where('status', 'approved')
            ->selectRaw('sum(amount) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
            ->groupBy('period')
            ->pluck('total')
            ->toArray();

        return [
            'series' => [
                [
                    'data' => $data,
                ],
            ],
        ];
    }

    public function getViewData(): array
    {
        $revenue = Commission::query()
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->where('status', 'approved')
            ->sum('amount');

        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;

        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        $currentRevenue = Commission::query()
            ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
            ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
            ->where('status', 'approved')
            ->sum('amount');

        $previousRevenue = Commission::query()
            ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
            ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
            ->where('status', 'approved')
            ->sum('amount');

        $result = $currentRevenue - $previousRevenue;

        $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/affiliate-pro::reports.widgets.commission-card',
                compact('revenue', 'result')
            )->render(),
        ]);
    }

    public function getLabel(): string
    {
        return trans('plugins/affiliate-pro::reports.commission_this_month');
    }
}

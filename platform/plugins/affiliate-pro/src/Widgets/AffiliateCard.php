<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\Base\Widgets\Card;
use Carbon\CarbonPeriod;

class AffiliateCard extends Card
{
    protected int $columns = 4;

    public function getOptions(): array
    {
        $data = Affiliate::query()
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
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
        $count = Affiliate::query()
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->count();

        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;

        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        $currentAffiliates = Affiliate::query()
            ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
            ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->count();

        $previousAffiliates = Affiliate::query()
            ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
            ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->count();

        $result = $currentAffiliates - $previousAffiliates;

        $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/affiliate-pro::reports.widgets.affiliate-card',
                compact('count', 'result')
            )->render(),
        ]);
    }

    public function getLabel(): string
    {
        return trans('plugins/affiliate-pro::reports.active_affiliates');
    }
}

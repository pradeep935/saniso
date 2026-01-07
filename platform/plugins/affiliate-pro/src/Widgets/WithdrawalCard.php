<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\AffiliatePro\Enums\WithdrawalStatusEnum;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Widgets\Card;
use Carbon\CarbonPeriod;

class WithdrawalCard extends Card
{
    protected int $columns = 4;

    public function getOptions(): array
    {
        $data = Withdrawal::query()
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->where('status', WithdrawalStatusEnum::PENDING)
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
        $count = Withdrawal::query()
            ->where('status', WithdrawalStatusEnum::PENDING)
            ->count();

        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;

        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        $currentWithdrawals = Withdrawal::query()
            ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
            ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
            ->where('status', WithdrawalStatusEnum::PENDING)
            ->count();

        $previousWithdrawals = Withdrawal::query()
            ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
            ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
            ->where('status', WithdrawalStatusEnum::PENDING)
            ->count();

        $result = $currentWithdrawals - $previousWithdrawals;

        $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/affiliate-pro::reports.widgets.withdrawal-card',
                compact('count', 'result')
            )->render(),
        ]);
    }

    public function getLabel(): string
    {
        return trans('plugins/affiliate-pro::reports.pending_withdrawals');
    }
}

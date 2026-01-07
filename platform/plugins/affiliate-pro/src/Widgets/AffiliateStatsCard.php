<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\Commission;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Widgets\Card;
use Carbon\Carbon;

class AffiliateStatsCard extends Card
{
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
        $startOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now();

        $pendingCommissions = Commission::query()
            ->where('status', 'pending')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->count();

        $approvedCommissions = Commission::query()
            ->where('status', 'approved')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->count();

        $totalCommissionAmount = Commission::query()
            ->where('status', 'approved')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->sum('amount');

        $pendingWithdrawals = Withdrawal::query()
            ->where('status', 'pending')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->count();

        $activeAffiliates = Affiliate::query()
            ->where('status', AffiliateStatusEnum::APPROVED)
            ->count();

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/affiliate-pro::reports.widgets.general',
                compact(
                    'pendingCommissions',
                    'approvedCommissions',
                    'totalCommissionAmount',
                    'pendingWithdrawals',
                    'activeAffiliates'
                )
            )->render(),
        ]);
    }

    public function getColumns(): int
    {
        return 4;
    }

    public function getLabel(): string
    {
        return trans('plugins/affiliate-pro::reports.affiliate_stats');
    }
}

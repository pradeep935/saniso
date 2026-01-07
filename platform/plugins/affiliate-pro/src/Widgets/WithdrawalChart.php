<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\AffiliatePro\Models\Withdrawal;
use Botble\AffiliatePro\Widgets\Traits\HasCategory;
use Botble\Base\Widgets\Chart;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WithdrawalChart extends Chart
{
    use HasCategory;

    protected int $columns = 6;

    public function getOptions(): array
    {
        $data = $this->getData();

        return [
            'series' => [
                [
                    'name' => trans('plugins/affiliate-pro::reports.approved_withdrawals'),
                    'data' => Arr::get($data, 'approved', []),
                ],
                [
                    'name' => trans('plugins/affiliate-pro::reports.pending_withdrawals'),
                    'data' => Arr::get($data, 'pending', []),
                ],
            ],
            'colors' => ['#0ea5e9', '#eab308'],
            'xaxis' => [
                'categories' => Arr::get($data, 'dates', []),
            ],
        ];
    }

    public function getLabel(): string
    {
        return trans('plugins/affiliate-pro::reports.withdrawal_chart');
    }

    protected function getData(): array
    {
        $query = Withdrawal::query()
            ->select([
                DB::raw('DATE_FORMAT(created_at, "' . $this->dateFormat . '") as period'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN amount ELSE 0 END) as approved_amount'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount'),
            ])
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->groupBy('period')
            ->get();

        $data = $query->keyBy('period');
        $periods = $data->keys()->toArray();

        $approvedData = [];
        $pendingData = [];

        foreach ($periods as $period) {
            $item = $data->get($period);
            $approvedData[] = $item ? $item->approved_amount : 0;
            $pendingData[] = $item ? $item->pending_amount : 0;
        }

        return [
            'dates' => $this->translateCategories(array_flip($periods)),
            'approved' => $approvedData,
            'pending' => $pendingData,
        ];
    }
}

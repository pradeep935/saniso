<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\AffiliatePro\Tables\Reports\RecentWithdrawalsTable as RecentWithdrawalsTableReport;
use Botble\Base\Widgets\Table;

class RecentWithdrawalsTable extends Table
{
    protected string $table = RecentWithdrawalsTableReport::class;

    protected string $route = 'affiliate-pro.reports.recent-withdrawals';

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/affiliate-pro::reports.recent_withdrawals');
    }
}

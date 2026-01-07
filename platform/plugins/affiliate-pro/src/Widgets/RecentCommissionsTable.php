<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\AffiliatePro\Tables\Reports\RecentCommissionsTable as RecentCommissionsTableReport;
use Botble\Base\Widgets\Table;

class RecentCommissionsTable extends Table
{
    protected string $table = RecentCommissionsTableReport::class;

    protected string $route = 'affiliate-pro.reports.recent-commissions';

    protected int $columns = 6;

    public function getLabel(): string
    {
        return trans('plugins/affiliate-pro::reports.recent_commissions');
    }
}

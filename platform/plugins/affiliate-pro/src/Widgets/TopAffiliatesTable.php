<?php

namespace Botble\AffiliatePro\Widgets;

use Botble\AffiliatePro\Tables\Reports\TopAffiliatesTable as TopAffiliatesTableReport;
use Botble\Base\Widgets\Table;

class TopAffiliatesTable extends Table
{
    protected string $table = TopAffiliatesTableReport::class;

    protected string $route = 'affiliate-pro.reports.top-affiliates';

    protected int $columns = 12;

    public function getLabel(): string
    {
        return trans('plugins/affiliate-pro::reports.top_affiliates');
    }
}

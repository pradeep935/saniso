<?php

namespace Botble\AffiliatePro\Tables\Reports;

use Botble\AffiliatePro\Models\Commission;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class RecentCommissionsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Commission::class)
            ->setType(self::TABLE_TYPE_SIMPLE)
            ->setOption('id', 'table-recent-commissions')
            ->setOption('class', 'table-report-table')
            ->setOption('card_title', trans('plugins/affiliate-pro::reports.recent_commissions'))
            ->setView($this->simpleTableView())
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('affiliate_id')
                    ->title(trans('plugins/affiliate-pro::commission.affiliate'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        $item = $column->getItem();
                        if (! $item->affiliate) {
                            return '&mdash;';
                        }

                        return Html::link(route('affiliate-pro.edit', $item->affiliate->id), $item->affiliate->affiliate_code);
                    }),
                FormattedColumn::make('order_id')
                    ->title(trans('plugins/affiliate-pro::commission.order'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        $item = $column->getItem();
                        if (! $item->order) {
                            return '&mdash;';
                        }

                        return Html::link(route('orders.edit', $item->order->id), $item->order->code);
                    }),
                FormattedColumn::make('amount')
                    ->title(trans('plugins/affiliate-pro::commission.amount'))
                    ->searchable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        return format_price($column->getItem()->amount);
                    }),
                FormattedColumn::make('status')
                    ->title(trans('plugins/affiliate-pro::commission.status'))
                    ->getValueUsing(function (FormattedColumn $column) {
                        return $column->getItem()->status->toHtml();
                    }),
                CreatedAtColumn::make(),
            ])
            ->queryUsing(function (Builder $query) {
                [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport(request());

                return $query
                    ->select([
                        'id',
                        'affiliate_id',
                        'order_id',
                        'amount',
                        'status',
                        'created_at',
                    ])
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'desc');
            });
    }
}

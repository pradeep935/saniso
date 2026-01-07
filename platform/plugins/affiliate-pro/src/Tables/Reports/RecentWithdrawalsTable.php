<?php

namespace Botble\AffiliatePro\Tables\Reports;

use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class RecentWithdrawalsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Withdrawal::class)
            ->setType(self::TABLE_TYPE_SIMPLE)
            ->setOption('id', 'table-recent-withdrawals')
            ->setOption('class', 'table-report-table')
            ->setOption('card_title', trans('plugins/affiliate-pro::reports.recent_withdrawals'))
            ->setView($this->simpleTableView())
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('affiliate_id')
                    ->title(trans('plugins/affiliate-pro::withdrawal.affiliate'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        $item = $column->getItem();
                        if (! $item->affiliate) {
                            return '&mdash;';
                        }

                        return Html::link(route('affiliate-pro.edit', $item->affiliate->id), $item->affiliate->affiliate_code);
                    }),
                FormattedColumn::make('amount')
                    ->title(trans('plugins/affiliate-pro::withdrawal.amount'))
                    ->searchable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        return format_price($column->getItem()->amount);
                    }),
                Column::make('payment_method')
                    ->title(trans('plugins/affiliate-pro::withdrawal.payment_method')),
                EnumColumn::make('status')
                    ->title(trans('plugins/affiliate-pro::withdrawal.status')),
                CreatedAtColumn::make(),
            ])
            ->queryUsing(function (Builder $query) {
                [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport(request());

                return $query
                    ->select([
                        'id',
                        'affiliate_id',
                        'amount',
                        'status',
                        'payment_method',
                        'created_at',
                    ])
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'desc');
            });
    }
}

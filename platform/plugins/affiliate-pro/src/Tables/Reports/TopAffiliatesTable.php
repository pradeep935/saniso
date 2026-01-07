<?php

namespace Botble\AffiliatePro\Tables\Reports;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class TopAffiliatesTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Affiliate::class)
            ->setType(self::TABLE_TYPE_SIMPLE)
            ->setOption('id', 'table-top-affiliates')
            ->setOption('class', 'table-report-table')
            ->setOption('card_title', trans('plugins/affiliate-pro::reports.top_affiliates'))
            ->setOption('filters', [
                'affiliate_code' => [
                    'title' => trans('plugins/affiliate-pro::affiliate.affiliate_code'),
                    'type' => 'text',
                    'validate' => 'required|max:120',
                ],
            ])
            ->setView($this->simpleTableView())
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('customer_id')
                    ->title(trans('plugins/affiliate-pro::affiliate.customer'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        $item = $column->getItem();
                        if (! $item->customer) {
                            return '&mdash;';
                        }

                        return Html::link(route('customers.edit', $item->customer->id), $item->customer->name);
                    }),
                Column::make('affiliate_code')
                    ->title(trans('plugins/affiliate-pro::affiliate.affiliate_code'))
                    ->searchable(),
                FormattedColumn::make('total_commission')
                    ->title(trans('plugins/affiliate-pro::affiliate.total_commission'))
                    ->searchable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        return format_price($column->getItem()->total_commission);
                    }),
                FormattedColumn::make('balance')
                    ->title(trans('plugins/affiliate-pro::affiliate.balance'))
                    ->searchable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        return format_price($column->getItem()->balance);
                    }),
            ])
            ->queryUsing(function (Builder $query) {
                [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport(request());

                return $query
                    ->select([
                        'id',
                        'customer_id',
                        'affiliate_code',
                        'balance',
                        'total_commission',
                    ])
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('total_commission', 'desc');
            });
    }
}

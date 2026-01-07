<?php

namespace Botble\AffiliatePro\Tables;

use Botble\AffiliatePro\Enums\CommissionStatusEnum;
use Botble\AffiliatePro\Models\Commission;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\ViewAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\SelectBulkChange;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class CommissionTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Commission::class)
            ->addActions([
                ViewAction::make()->route('affiliate-pro.commissions.show'),
            ])
            ->addBulkChanges([
                SelectBulkChange::make()
                    ->name('status')
                    ->title(trans('core/base::tables.status'))
                    ->choices([
                        CommissionStatusEnum::PENDING => trans('plugins/affiliate-pro::commission.statuses.pending'),
                        CommissionStatusEnum::APPROVED => trans('plugins/affiliate-pro::commission.statuses.approved'),
                        CommissionStatusEnum::REJECTED => trans('plugins/affiliate-pro::commission.statuses.rejected'),
                    ])
                    ->validate(['required', 'in:' . implode(',', CommissionStatusEnum::values())]),
                CreatedAtBulkChange::make(),
            ])
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('affiliate_id')
                    ->title(trans('plugins/affiliate-pro::commission.affiliate'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        $affiliate = $column->getItem()->affiliate;
                        if (! $affiliate) {
                            return '&mdash;';
                        }

                        $customer = $affiliate->customer;
                        if (! $customer) {
                            return '&mdash;';
                        }

                        return Html::link(route('affiliate-pro.edit', $affiliate->id), $customer->name);
                    }),
                FormattedColumn::make('order_id')
                    ->title(trans('plugins/affiliate-pro::commission.order'))
                    ->getValueUsing(function (FormattedColumn $column) {
                        $orderCode = $column->getItem()->order?->code;

                        if (! $orderCode) {
                            return get_order_code($column->getItem()->order_id);
                        }

                        if (! $column->getItem()->order) {
                            return $orderCode;
                        }

                        return Html::link(route('orders.edit', $column->getItem()->order_id), $orderCode);
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
                return $query
                    ->select([
                        'id',
                        'affiliate_id',
                        'order_id',
                        'amount',
                        'description',
                        'status',
                        'created_at',
                    ]);
            });
    }
}

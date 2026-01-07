<?php

namespace Botble\AffiliatePro\Tables;

use Botble\AffiliatePro\Enums\WithdrawalStatusEnum;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\ViewAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\SelectBulkChange;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class WithdrawalTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Withdrawal::class)
            ->addActions([
                ViewAction::make()->route('affiliate-pro.withdrawals.show'),
            ])
            ->addBulkChanges([
                SelectBulkChange::make()
                    ->name('status')
                    ->title(trans('core/base::tables.status'))
                    ->choices(WithdrawalStatusEnum::labels())
                    ->validate('required|in:' . implode(',', WithdrawalStatusEnum::values())),
                CreatedAtBulkChange::make(),
            ])
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('affiliate_id')
                    ->title(trans('plugins/affiliate-pro::withdrawal.affiliate'))
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
                return $query
                    ->select([
                        'id',
                        'affiliate_id',
                        'amount',
                        'status',
                        'payment_method',
                        'created_at',
                    ]);
            });
    }
}

<?php

namespace Botble\AffiliatePro\Tables;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;

class PendingAffiliateTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Affiliate::class)
            ->addActions([
                Action::make('view')
                    ->route('affiliate-pro.pending.show')
                    ->permission('affiliate-pro.edit')
                    ->icon('ti ti-eye')
                    ->attributes([
                        'class' => 'btn btn-sm btn-icon btn-info',
                        'data-bs-toggle' => 'tooltip',
                        'data-bs-original-title' => trans('plugins/affiliate-pro::affiliate.view'),
                    ]),
            ])
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('customer_id')
                    ->title(trans('plugins/affiliate-pro::affiliate.customer'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        $customer = $column->getItem()->customer;
                        if (! $customer) {
                            return '&mdash;';
                        }

                        return Html::link(route('customers.edit', $customer->id), $customer->name);
                    }),
                Column::make('affiliate_code')
                    ->title(trans('plugins/affiliate-pro::affiliate.affiliate_code'))
                    ->searchable(),
                CreatedAtColumn::make()
                    ->title(trans('plugins/affiliate-pro::affiliate.application_date')),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->select([
                        'id',
                        'customer_id',
                        'affiliate_code',
                        'created_at',
                    ])
                    ->where('status', AffiliateStatusEnum::PENDING);
            });
    }
}

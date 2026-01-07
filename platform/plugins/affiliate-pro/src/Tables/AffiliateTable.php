<?php

namespace Botble\AffiliatePro\Tables;

use Botble\AffiliatePro\Enums\AffiliateStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Actions\ViewAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class AffiliateTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Affiliate::class)
            ->displayActionsAsDropdown(false)
            ->addHeaderAction(CreateHeaderAction::make()->route('affiliate-pro.create'))
            ->addActions([
                EditAction::make()->route('affiliate-pro.edit'),
                ViewAction::make()
                    ->route('affiliate-pro.show')
                    ->permission('affiliate-pro.index'),
                Action::make('extra')
                    ->renderUsing(function (Action $action) {
                        $item = $action->getItem();
                        $html = '';

                        if ($item->status != AffiliateStatusEnum::BANNED) {
                            $banAction = Action::make('ban')
                                ->route('affiliate-pro.ban')
                                ->setItem($item)
                                ->permission('affiliate-pro.edit')
                                ->color('danger')
                                ->icon('ti ti-lock')
                                ->label(trans('plugins/affiliate-pro::affiliate.ban'))
                                ->attributes([
                                    'class' => 'btn btn-sm btn-icon btn-danger',
                                    'data-bb-toggle' => 'confirm-action',
                                    'data-bb-message' => trans('plugins/affiliate-pro::affiliate.ban_confirmation_generic'),
                                ]);
                            $html .= $banAction->render();
                        } else {
                            $unbanAction = Action::make('unban')
                                ->route('affiliate-pro.unban')
                                ->setItem($item)
                                ->permission('affiliate-pro.edit')
                                ->color('success')
                                ->icon('ti ti-lock-open')
                                ->label(trans('plugins/affiliate-pro::affiliate.unban'))
                                ->attributes([
                                    'class' => 'btn btn-sm btn-icon btn-success',
                                    'data-bb-toggle' => 'confirm-action',
                                    'data-bb-message' => trans('plugins/affiliate-pro::affiliate.unban_confirmation_generic'),
                                ]);
                            $html .= $unbanAction->render();
                        }

                        return $html;
                    }),
                DeleteAction::make()->route('affiliate-pro.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('affiliate-pro.destroy'),
            ])
            ->addBulkChanges([
                StatusBulkChange::make()->choices(AffiliateStatusEnum::labels()),
                CreatedAtBulkChange::make(),
            ])
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('customer_id')
                    ->title(trans('plugins/affiliate-pro::affiliate.customer'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        $item = $column->getItem();

                        return Html::link(route('affiliate-pro.edit', $item->id), $item->customer?->name ?: '—');
                    }),
                Column::make('affiliate_code')
                    ->title(trans('plugins/affiliate-pro::affiliate.affiliate_code'))
                    ->searchable(),
                FormattedColumn::make('balance')
                    ->title(trans('plugins/affiliate-pro::affiliate.balance'))
                    ->searchable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        return format_price($column->getItem()->balance);
                    }),
                FormattedColumn::make('total_commission')
                    ->title(trans('plugins/affiliate-pro::affiliate.total_commission'))
                    ->searchable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        return format_price($column->getItem()->total_commission);
                    }),
                FormattedColumn::make('total_withdrawn')
                    ->title(trans('plugins/affiliate-pro::affiliate.total_withdrawn'))
                    ->searchable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        return format_price($column->getItem()->total_withdrawn);
                    }),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->queryUsing(function (Builder $query) {
                return $query
                    ->select([
                        'id',
                        'customer_id',
                        'affiliate_code',
                        'balance',
                        'total_commission',
                        'total_withdrawn',
                        'status',
                        'created_at',
                    ]);
            });
    }
}

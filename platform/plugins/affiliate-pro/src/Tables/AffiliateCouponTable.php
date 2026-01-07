<?php

namespace Botble\AffiliatePro\Tables;

use Botble\AffiliatePro\Models\AffiliateCoupon;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class AffiliateCouponTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(AffiliateCoupon::class)
            ->addActions([
                EditAction::make()->route('affiliate-pro.coupons.edit'),
                DeleteAction::make()->route('affiliate-pro.coupons.destroy'),
            ])
            ->addHeaderAction(CreateHeaderAction::make()->route('affiliate-pro.coupons.create'));
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('code', function (AffiliateCoupon $item) {
                return Html::tag('span', $item->code, ['class' => 'fw-bold']);
            })
            ->editColumn('affiliate_id', function (AffiliateCoupon $item) {
                return $item->affiliate ? $item->affiliate->affiliate_code : '—';
            })
            ->editColumn('discount_amount', function (AffiliateCoupon $item) {
                if ($item->discount_type === 'percentage') {
                    return $item->discount_amount . '%';
                }

                return format_price($item->discount_amount);
            })
            ->editColumn('expires_at', function (AffiliateCoupon $item) {
                if (! $item->expires_at) {
                    return '—';
                }

                return BaseHelper::formatDate($item->expires_at);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'affiliate_id',
                'code',
                'description',
                'discount_amount',
                'discount_type',
                'expires_at',
                'created_at',
            ])
            ->with(['affiliate']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('code')
                ->title(trans('plugins/affiliate-pro::coupon.code'))
                ->alignLeft(),
            Column::make('affiliate_id')
                ->title(trans('plugins/affiliate-pro::coupon.affiliate'))
                ->alignLeft(),
            Column::make('description')
                ->title(trans('plugins/affiliate-pro::coupon.description'))
                ->alignLeft(),
            Column::make('discount_amount')
                ->title(trans('plugins/affiliate-pro::coupon.discount'))
                ->alignLeft(),
            Column::make('expires_at')
                ->title(trans('plugins/affiliate-pro::coupon.expires_at'))
                ->alignLeft(),
            CreatedAtColumn::make(),
        ];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('affiliate-pro.coupons.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'code' => [
                'title' => trans('plugins/affiliate-pro::coupon.code'),
                'type' => 'text',
                'validate' => 'required|max:20',
            ],
            'description' => [
                'title' => trans('plugins/affiliate-pro::coupon.description'),
                'type' => 'text',
                'validate' => 'max:255',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}

<?php

namespace Botble\AffiliatePro\Tables;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateShortLink;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Actions\ViewAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\LinkableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class AffiliateShortLinkTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(AffiliateShortLink::class)
            ->addActions([
                ViewAction::make()->route('affiliate-pro.short-links.show'),
                EditAction::make()->route('affiliate-pro.short-links.edit'),
                DeleteAction::make()->route('affiliate-pro.short-links.destroy'),
            ]);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'affiliate_id',
                'title',
                'short_code',
                'destination_url',
                'product_id',
                'clicks',
                'conversions',
                'created_at',
            ])
            ->with(['affiliate.customer', 'product']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            FormattedColumn::make('affiliate_id')
                ->title(trans('plugins/affiliate-pro::short-link.affiliate'))
                ->alignLeft()
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    if (! $item->affiliate || ! $item->affiliate->customer) {
                        return '—';
                    }

                    return sprintf(
                        '<a href="%s">%s</a><br><small class="text-muted">%s</small>',
                        route('affiliate-pro.edit', $item->affiliate->id),
                        e($item->affiliate->customer->name),
                        e($item->affiliate->affiliate_code)
                    );
                }),
            LinkableColumn::make('title')
                ->title(trans('plugins/affiliate-pro::short-link.title'))
                ->route('affiliate-pro.short-links.edit')
                ->alignLeft()
                ->renderUsing(function (LinkableColumn $column) {
                    $item = $column->getItem();

                    $title = $item->title ?: trans('plugins/affiliate-pro::short-link.untitled');

                    return sprintf(
                        '<strong>%s</strong><br><small class="text-muted">%s</small>',
                        e($title),
                        e($item->short_code)
                    );
                }),
            FormattedColumn::make('destination_url')
                ->title(trans('plugins/affiliate-pro::short-link.destination_url'))
                ->alignLeft()
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    $url = $item->destination_url;
                    $displayUrl = strlen($url) > 50 ? substr($url, 0, 50) . '...' : $url;

                    return sprintf(
                        '<a href="%s" target="_blank" title="%s">%s</a>',
                        e($url),
                        e($url),
                        e($displayUrl)
                    );
                }),
            LinkableColumn::make('short_url')
                ->title(trans('plugins/affiliate-pro::short-link.short_url'))
                ->alignLeft()
                ->orderable(false)
                ->searchable(false)
                ->copyable()
                ->copyableState(function (FormattedColumn $column) {
                    return $column->getItem()->getShortUrl();
                })
                ->getValueUsing(function (FormattedColumn $column) {
                    return $column->getItem()->getShortUrl();
                }),
            FormattedColumn::make('product_id')
                ->title(trans('plugins/affiliate-pro::short-link.product'))
                ->alignLeft()
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    if (! $item->product) {
                        return '<span class="text-muted">' . trans('plugins/affiliate-pro::short-link.all_products') . '</span>';
                    }

                    return sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        route('products.edit', $item->product->id),
                        e($item->product->name)
                    );
                }),
            FormattedColumn::make('stats')
                ->title(trans('plugins/affiliate-pro::short-link.statistics'))
                ->alignCenter()
                ->orderable(false)
                ->searchable(false)
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    $conversionRate = $item->clicks > 0 ? round(($item->conversions / $item->clicks) * 100, 1) : 0;

                    return sprintf(
                        '<div class="text-center">
                            <div><strong>%d</strong> <small>%s</small></div>
                            <div><strong>%d</strong> <small>%s</small></div>
                            <div><strong>%s%%</strong> <small>%s</small></div>
                        </div>',
                        $item->clicks,
                        trans('plugins/affiliate-pro::short-link.clicks'),
                        $item->conversions,
                        trans('plugins/affiliate-pro::short-link.conversions'),
                        $conversionRate,
                        trans('plugins/affiliate-pro::short-link.conversion_rate')
                    );
                }),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('affiliate-pro.short-links.create'), 'affiliate.short-links.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('affiliate.short-links.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'affiliate_id' => [
                'title' => trans('plugins/affiliate-pro::short-link.affiliate'),
                'type' => 'select',
                'choices' => $this->getAffiliateChoices(),
                'validate' => 'required|max:120',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    protected function getAffiliateChoices(): array
    {
        return Affiliate::query()
            ->with('customer')
            ->get()
            ->pluck('customer.name', 'id')
            ->all();
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}

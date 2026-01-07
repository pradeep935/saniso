<?php

namespace Botble\MultiBranchInventory\Tables;

use Botble\Ecommerce\Models\Product;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class BranchInventoryTable extends TableAbstract
{
    protected int $pageLength = 100;
    
    protected bool $hasResponsive = false;
    
    protected $branchId = null;

    public function __construct(Request $request)
    {
        parent::__construct();
        $this->branchId = $request->get('branch_id');
    }

    public function hasColumnVisibilityEnabled(): bool
    {
        return false;
    }

    public function setup(): void
    {
        $this
            ->addColumns([
                IdColumn::make()
                    ->orderable(false)
                    ->getValueUsing(function (IdColumn $column) {
                        $product = $column->getItem();
                        return ! $product->is_variation ? $product->id : '';
                    }),
                ImageColumn::make()
                    ->orderable(false)
                    ->renderUsing(function (ImageColumn $column, $content) {
                        $product = $column->getItem();
                        $images = $product->images;
                        $images = $images ? json_decode($images, true) : [];
                        $firstImage = Arr::first($images) ?: null;

                        if ($product->is_variation) {
                            return '';
                        }

                        $content = $content ?: $firstImage;
                        return $column->formattedValue($content);
                    }),
                FormattedColumn::make('name')
                    ->title(trans('plugins/ecommerce::products.name'))
                    ->renderUsing(function (FormattedColumn $column) {
                        $product = $column->getItem();
                        return view('plugins/multi-branch-inventory::branch-inventory.columns.name', [
                            'product' => $product,
                        ]);
                    })
                    ->orderable(false),
                FormattedColumn::make('sku')
                    ->title('SKU')
                    ->renderUsing(function (FormattedColumn $column) {
                        $product = $column->getItem();
                        return '<code class="bg-light px-2 py-1 rounded">' . $product->sku . '</code>';
                    })
                    ->orderable(false),
                FormattedColumn::make('barcode')
                    ->title('Barcode')
                    ->renderUsing(function (FormattedColumn $column) {
                        $product = $column->getItem();
                        return $product->barcode ? '<code class="bg-light px-2 py-1 rounded">' . $product->barcode . '</code>' : '-';
                    })
                    ->orderable(false),
                FormattedColumn::make('quantity')
                    ->title('Quantity')
                    ->renderUsing(function (FormattedColumn $column) {
                        $product = $column->getItem();
                        $branchInventory = $product->branchInventories->first();
                        
                        if ($product->variations_count > 0 && !$product->is_variation) {
                            return '<span class="text-muted">&mdash;</span>';
                        }
                        
                        return view('plugins/multi-branch-inventory::branch-inventory.columns.quantity', [
                            'product' => $product,
                            'branchInventory' => $branchInventory,
                            'branchId' => $this->branchId,
                        ]);
                    })
                    ->orderable(false),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->query($this->query())
            ->filter(function ($query) {
                $keyword = $this->request()->input('search.value');

                if ($keyword) {
                    $keyword = '%' . $keyword . '%';
                    // Group search filters to avoid breaking other conditions
                    $query->where(function($q) use ($keyword) {
                        $q->where('ec_products.name', 'LIKE', $keyword)
                          ->orWhere('ec_products.sku', 'LIKE', $keyword)
                          ->orWhere('ec_products.barcode', 'LIKE', $keyword);
                    });
                }

                return $query;
            });

        return $this->toJson($data);
    }

    protected function hasOperations(): bool
    {
        return false;
    }

    public function query()
    {
        $query = Product::query()
            ->with([
                'branchInventories' => function($q) {
                    if ($this->branchId) {
                        $q->where('branch_id', $this->branchId);
                    }
                },
                'variations' => function($q) {
                    $q->with(['branchInventories' => function($subQ) {
                        if ($this->branchId) {
                            $subQ->where('branch_id', $this->branchId);
                        }
                    }]);
                },
                'parent' => function($q) {
                    $q->select('id', 'name');
                }
            ])
            ->withCount('variations')
            ->select([
                'ec_products.id',
                'ec_products.name',
                'ec_products.sku',
                'ec_products.barcode',
                'ec_products.images',
                'ec_products.is_variation',
                'ec_products.parent_id',
                'ec_products.quantity',
                'ec_products.status'
            ])
            ->where('ec_products.status', 'published');

        // Include both parent products and variations, but group them logically
        if ($this->branchId) {
            $query->where(function($q) {
                $q->whereHas('branchInventories', function($branchQuery) {
                    $branchQuery->where('branch_id', $this->branchId);
                })
                ->orWhereDoesntHave('branchInventories')
                ->orWhereHas('variations.branchInventories', function($variationQuery) {
                    $variationQuery->where('branch_id', $this->branchId);
                });
            });
        }

        // Order to group parent products with their variations
        $query->orderByRaw('CASE WHEN parent_id IS NULL THEN id ELSE parent_id END')
              ->orderBy('is_variation')
              ->orderBy('name');

        return $this->applyScopes($query);
    }
}
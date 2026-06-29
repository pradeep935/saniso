<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Illuminate\Database\Eloquent\Builder;

class AdminAjaxController extends BaseController
{
    public function searchProducts(): BaseHttpResponse
    {
        $products = Product::query()
            ->wherePublished()
            ->where('is_variation', false)
            ->when(request()->input('search'), function (Builder $query, string $search): void {
                $query->where('name', 'like', "%$search%");
            })
            ->select('name', 'id')
            ->paginate();

        return BaseHttpResponse::make()
            ->setData($products);
    }

    public function searchCategories(): BaseHttpResponse
    {
        $categories = ProductCategory::query()
            ->when(request()->input('search'), function (Builder $query, string $search): void {
                $query->where('name', 'like', "%$search%");
            })
            ->select('name', 'id')
            ->paginate();

        return BaseHttpResponse::make()
            ->setData($categories);
    }

    public function searchCollections(): BaseHttpResponse
    {
        $collections = ProductCollection::query()
            ->when(request()->input('search'), function (Builder $query, string $search): void {
                $query->where('name', 'like', "%$search%");
            })
            ->select('name', 'id')
            ->paginate();

        return BaseHttpResponse::make()
            ->setData($collections);
    }
}

<?php

use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Widget\AbstractWidget;
use Illuminate\Support\Collection;

class FooterProductCategoriesWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Footer Product Categories'),
            'description' => __('Display product categories in footer with various display options'),
            'title' => __('Footer Categories'),
            'subtitle' => '',
            'display_type' => 'top_sale', // top_sale, top_product, new_added, custom
            'category_ids' => [], // for custom selection
            'limit' => 10,
            'layout' => 'carousel', // carousel or grid
            'show_on_mobile_only' => 'no',
            'is_autoplay' => 'yes',
            'is_infinite' => 'yes',
            'autoplay_speed' => 3000,
            'slides_to_show' => 4,
            'slides_to_show_tablet' => 3,
            'slides_to_show_mobile' => 2,
            // Grid options
            'columns_xl' => 8,
            'columns_lg' => 6,
            'columns_md' => 4,
            'columns_sm' => 3,
            'columns_xs' => 2,
            'columns_xxs' => 1,
            'show_product_count' => 'no',
        ]);
    }

    protected function data(): array|Collection
    {
        $limit = (int) $this->getConfig('limit', 10);
        $displayType = $this->getConfig('display_type', 'top_sale');
        $customCategoryIds = $this->getConfig('category_ids', []);
        
        $categories = collect();
        
        try {
            // Use ProductCategoryHelper for proper language support
            switch ($displayType) {
                case 'top_sale':
                    // Try to get categories with sales data, fallback to featured if fails
                    try {
                        $categories = ProductCategory::query()
                            ->where('status', 'published')
                            ->withCount(['products as total_sales' => function ($query) {
                                $query->join('ec_order_product', 'ec_products.id', '=', 'ec_order_product.product_id')
                                      ->join('ec_orders', function($join) {
                                          $join->on('ec_order_product.order_id', '=', 'ec_orders.id')
                                               ->where('ec_orders.status', '=', 'completed');
                                      })
                                      ->selectRaw('SUM(ec_order_product.qty)');
                            }])
                            ->orderBy('total_sales', 'desc')
                            ->limit($limit)
                            ->get();
                            
                        if ($categories->isEmpty()) {
                            $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], $limit);
                        }
                    } catch (\Exception $e) {
                        $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], $limit);
                    }
                    break;
                    
                case 'top_product':
                    // Use ProductCategoryHelper with proper language support
                    $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], [], $limit);
                    if ($categories->isEmpty()) {
                        $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], $limit);
                    }
                    break;
                    
                case 'new_added':
                    // Use ProductCategoryHelper for new categories
                    $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], [], $limit);
                    if ($categories->isEmpty()) {
                        $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], $limit);
                    }
                    break;
                    
                case 'custom':
                    // Custom selected categories with language support
                    if (!empty($customCategoryIds) && is_array($customCategoryIds)) {
                        $categories = ProductCategoryHelper::getProductCategoriesWithUrl($customCategoryIds, [], $limit);
                    } else {
                        $categories = collect();
                    }
                    break;
                    
                default:
                    // Featured categories with proper language support
                    $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], $limit);
                    break;
            }

            // Final fallback if still empty
            if ($categories->isEmpty()) {
                $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], [], $limit);
            }
            
        } catch (\Exception $e) {
            // Final fallback with language support
            $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], $limit);
        }

        return [
            'categories' => $categories,
        ];
    }

    protected function requiredPlugins(): array
    {
        return ['ecommerce'];
    }
}

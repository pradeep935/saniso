<?php

use Botble\Ads\Facades\AdsManager;
use Botble\Contact\Forms\Fronts\ContactForm;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\FlashSale as FlashSaleFacade;
use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Botble\Ecommerce\Models\FlashSale;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Faq\Models\FaqCategory;
use Botble\Media\Facades\RvMedia;
use Botble\Shortcode\Compilers\Shortcode;
use Botble\Shortcode\Facades\Shortcode as ShortcodeFacade;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Theme\Farmart\Supports\Wishlist;

app()->booted(function (): void {
    ThemeSupport::registerGoogleMapsShortcode();
    ThemeSupport::registerYoutubeShortcode();

    function image_placeholder(?string $default = null, ?string $size = null): string
    {
        if (theme_option('lazy_load_image_enabled', 'yes') != 'yes' && $default) {
            if (Str::contains($default, ['https://', 'http://'])) {
                return $default;
            }

            return RvMedia::getImageUrl($default, $size);
        }

        if ($placeholder = theme_option('image-placeholder')) {
            return RvMedia::getImageUrl($placeholder);
        }

        return Theme::asset()->url('images/placeholder.png');
    }

    if (is_plugin_active('simple-slider')) {
        add_filter(SIMPLE_SLIDER_VIEW_TEMPLATE, function () {
            return Theme::getThemeNamespace() . '::partials.shortcodes.sliders';
        }, 120);

        add_filter(SHORTCODE_REGISTER_CONTENT_IN_ADMIN, function (?string $data, string $key, array $attributes) {
            if ($key == 'simple-slider' && is_plugin_active('ads')) {
                $ads = AdsManager::getData(true, true);

                $defaultAutoplay = 'yes';

                return $data . Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes', 'defaultAutoplay')) .
                    Theme::partial('shortcodes.select-ads-admin-config', compact('ads', 'attributes'));
            }

            return $data;
        }, 50, 3);
    }

    if (is_plugin_active('ads')) {
        function display_ads_advanced(?string $key, array $attributes = []): ?string
        {
            return AdsManager::displayAds($key, $attributes);
        }

        add_shortcode('theme-ads', __('Theme ads'), __('Theme ads'), function (Shortcode $shortcode) {
            $ads = [];
            $attributes = $shortcode->toArray();

            for ($i = 1; $i < 5; $i++) {
                if (isset($attributes['key_' . $i]) && ! empty($attributes['key_' . $i])) {
                    $ad = display_ads_advanced((string) $attributes['key_' . $i]);
                    if ($ad) {
                        $ads[] = $ad;
                    }
                }
            }

            $ads = array_filter($ads);

            if (! count($ads)) {
                return null;
            }

            return Theme::partial('shortcodes.ads.theme-ads', compact('ads'));
        });

        shortcode()->setAdminConfig('theme-ads', function (array $attributes) {
            $ads = AdsManager::getData(true, true);

            return Theme::partial('shortcodes.ads.theme-ads-admin-config', compact('ads', 'attributes'));
        });
    }

    if (is_plugin_active('ecommerce')) {
        add_shortcode(
            'featured-product-categories',
            __('Featured Product Categories'),
            __('Featured Product Categories'),
            function (Shortcode $shortcode) {
                $limit = (int) $shortcode->limit ?: 10;
                $displayType = $shortcode->display_type ?: 'featured';
                $categoryIds = $shortcode->category_ids ? array_filter(array_map('trim', explode(',', $shortcode->category_ids))) : [];

                $categories = collect();
                
                try {
                    switch ($displayType) {
                        case 'top_sale':
                            // Categories with most sales - simplified approach
                            $categories = ProductCategory::query()
                                ->where('status', 'published')
                                ->whereHas('products', function($query) {
                                    $query->whereHas('orderProducts', function($subQuery) {
                                        $subQuery->whereHas('order', function($orderQuery) {
                                            $orderQuery->where('status', 'completed');
                                        });
                                    });
                                })
                                ->withCount(['products as products_count'])
                                ->orderBy('products_count', 'desc')
                                ->limit($limit)
                                ->get();
                            break;
                            
                        case 'top_product':
                            // Categories with most products
                            $categories = ProductCategory::query()
                                ->where('status', 'published')
                                ->withCount(['products' => function($query) {
                                    $query->where('status', 'published');
                                }])
                                ->having('products_count', '>', 0)
                                ->orderBy('products_count', 'desc')
                                ->limit($limit)
                                ->get();
                            break;
                            
                        case 'new_added':
                            // Recently added categories
                            $categories = ProductCategory::query()
                                ->where('status', 'published')
                                ->whereHas('products', function($query) {
                                    $query->where('status', 'published');
                                })
                                ->orderBy('created_at', 'desc')
                                ->limit($limit)
                                ->get();
                            break;
                            
                        case 'custom':
                            // Custom selected categories
                            if (!empty($categoryIds)) {
                                $categories = ProductCategory::query()
                                    ->where('status', 'published')
                                    ->whereIn('id', $categoryIds)
                                    ->orderByRaw('FIELD(id, ' . implode(',', array_map('intval', $categoryIds)) . ')')
                                    ->limit($limit)
                                    ->get();
                            }
                            break;
                            
                        default:
                            // Featured categories (default)
                            $categories = ProductCategory::query()
                                ->where('status', 'published')
                                ->where('is_featured', 1)
                                ->limit($limit)
                                ->get();
                            break;
                    }

                    // If no categories found, fallback to featured categories
                    if ($categories->isEmpty() && $displayType !== 'featured') {
                        $categories = ProductCategory::query()
                            ->where('status', 'published')
                            ->where('is_featured', 1)
                            ->limit($limit)
                            ->get();
                    }
                    
                    // Add URL attribute for each category if not present
                    $categories->each(function ($category) {
                        if (!isset($category->url)) {
                            $category->url = $category->slug;
                        }
                    });
                    
                } catch (\Exception $e) {
                    // Final fallback
                    $categories = ProductCategory::query()
                        ->where('status', 'published')
                        ->where('is_featured', 1)
                        ->limit($limit)
                        ->get();
                }

                if ($categories->isEmpty()) {
                    return null;
                }

                return Theme::partial('shortcodes.ecommerce.featured-product-categories', [
                    'title' => $shortcode->title,
                    'subtitle' => $shortcode->subtitle,
                    'categories' => $categories,
                    'shortcode' => $shortcode,
                ]);
            }
        );

        ShortcodeFacade::setAdminConfig('featured-product-categories', function (array $attributes) {
            return ShortcodeForm::createFromArray($attributes)
                ->add('title', 'text', [
                    'label' => __('Title'),
                    'value' => Arr::get($attributes, 'title'),
                    'placeholder' => __('Title'),
                ])
                ->add('subtitle', 'text', [
                    'label' => __('Subtitle'),
                    'value' => Arr::get($attributes, 'subtitle'),
                    'placeholder' => __('Subtitle'),
                ])
                ->add('display_type', 'customSelect', [
                    'label' => __('Display Type'),
                    'choices' => [
                        'featured' => __('Featured Categories'),
                        'top_sale' => __('Top Sale Categories'),
                        'top_product' => __('Top Product Categories'),
                        'new_added' => __('New Added Categories'),
                        'custom' => __('Custom Categories'),
                    ],
                    'selected' => Arr::get($attributes, 'display_type', 'featured'),
                ])
                ->add('category_ids', 'text', [
                    'label' => __('Category IDs (comma separated)'),
                    'value' => Arr::get($attributes, 'category_ids'),
                    'placeholder' => __('1,2,3,4 (only for custom display type)'),
                    'help_block' => [
                        'text' => __('Enter category IDs separated by commas. Only used when display type is "Custom Categories".'),
                    ],
                ])
                ->add('limit', 'number', [
                    'label' => __('Limit'),
                    'value' => Arr::get($attributes, 'limit', 10),
                    'placeholder' => __('Number of categories to display'),
                ])
                ->add('layout_type', 'customSelect', [
                    'label' => __('Layout Type'),
                    'choices' => [
                        'carousel_only' => __('Carousel Only'),
                        'grid_only' => __('Grid Only'),
                        'responsive' => __('Responsive (Carousel on Desktop/Tablet, Grid on Mobile)'),
                    ],
                    'selected' => Arr::get($attributes, 'layout_type', 'responsive'),
                ])
                ->add('mobile_breakpoint', 'customSelect', [
                    'label' => __('Mobile Breakpoint (when to switch to grid)'),
                    'choices' => [
                        '768' => __('768px and below'),
                        '576' => __('576px and below'),
                        '480' => __('480px and below'),
                    ],
                    'selected' => Arr::get($attributes, 'mobile_breakpoint', '768'),
                ])
                ->add('carousel_slides_xl', 'customSelect', [
                    'label' => __('Carousel Slides (XL - 1700px+)'),
                    'choices' => [4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
                    'selected' => Arr::get($attributes, 'carousel_slides_xl', 8),
                ])
                ->add('carousel_slides_lg', 'customSelect', [
                    'label' => __('Carousel Slides (LG - 1500-1699px)'),
                    'choices' => [4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
                    'selected' => Arr::get($attributes, 'carousel_slides_lg', 7),
                ])
                ->add('carousel_slides_md', 'customSelect', [
                    'label' => __('Carousel Slides (MD - 1200-1499px)'),
                    'choices' => [3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7],
                    'selected' => Arr::get($attributes, 'carousel_slides_md', 6),
                ])
                ->add('carousel_slides_sm', 'customSelect', [
                    'label' => __('Carousel Slides (SM - 992-1199px)'),
                    'choices' => [2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6],
                    'selected' => Arr::get($attributes, 'carousel_slides_sm', 5),
                ])
                ->add('carousel_slides_tablet', 'customSelect', [
                    'label' => __('Carousel Slides (Tablet - 768-991px)'),
                    'choices' => [2 => 2, 3 => 3, 4 => 4, 5 => 5],
                    'selected' => Arr::get($attributes, 'carousel_slides_tablet', 4),
                ])
                ->add('carousel_slides_mobile', 'customSelect', [
                    'label' => __('Carousel Slides (Mobile - below 768px)'),
                    'choices' => [1 => 1, 2 => 2, 3 => 3, 4 => 4],
                    'selected' => Arr::get($attributes, 'carousel_slides_mobile', 2),
                ])
                ->add('is_autoplay', 'customSelect', [
                    'label' => __('Is autoplay? (Carousel)'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_autoplay', 'yes'),
                ])
                ->add('is_infinite', 'customSelect', [
                    'label' => __('Loop? (Carousel)'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_infinite', 'yes'),
                ])
                ->add('autoplay_speed', 'customSelect', [
                    'label' => __('Autoplay speed (if autoplay enabled)'),
                    'choices' => theme_get_autoplay_speed_options(),
                    'selected' => Arr::get($attributes, 'autoplay_speed', 3000),
                ])
                ->add('grid_columns_desktop', 'customSelect', [
                    'label' => __('Grid Columns (Desktop)'),
                    'choices' => [4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
                    'selected' => Arr::get($attributes, 'grid_columns_desktop', 6),
                ])
                ->add('grid_columns_tablet', 'customSelect', [
                    'label' => __('Grid Columns (Tablet)'),
                    'choices' => [2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6],
                    'selected' => Arr::get($attributes, 'grid_columns_tablet', 4),
                ])
                ->add('grid_columns_mobile', 'customSelect', [
                    'label' => __('Grid Columns (Mobile)'),
                    'choices' => [1 => 1, 2 => 2, 3 => 3],
                    'selected' => Arr::get($attributes, 'grid_columns_mobile', 2),
                ]);
        });

        add_shortcode(
            'featured-product-categories-grid',
            __('Featured Product Categories Grid'),
            __('Featured Product Categories Grid'),
            function (Shortcode $shortcode) {
                $limit = (int) $shortcode->limit ?: 12;

                $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], $limit);

                if ($categories->isEmpty()) {
                    return null;
                }

                return Theme::partial('shortcodes.ecommerce.featured-product-categories-grid', [
                    'title' => $shortcode->title,
                    'subtitle' => $shortcode->subtitle,
                    'categories' => $categories,
                    'shortcode' => $shortcode,
                ]);
            }
        );

        shortcode()->setAdminConfig('featured-product-categories-grid', function (array $attributes) {
            return Theme::partial('shortcodes.ecommerce.featured-product-categories-grid-admin-config', compact('attributes'));
        });

        add_shortcode('featured-brands', __('Featured Brands'), __('Featured Brands'), function (Shortcode $shortcode) {
            $limit = (int) $shortcode->limit ?: 8;
            $randomOrder = $shortcode->random_order === 'yes';

            if ($randomOrder) {
                // For random order, get brands without cache and shuffle them
                $brands = get_featured_brands($limit);
                if (!$brands->isEmpty()) {
                    $brands = $brands->shuffle();
                }
            } else {
                // Use normal order
                $brands = get_featured_brands($limit);
            }

            if ($brands->isEmpty()) {
                return null;
            }

            return Theme::partial('shortcodes.ecommerce.featured-brands', [
                'title' => $shortcode->title,
                'subtitle' => $shortcode->subtitle,
                'brands' => $brands,
                'shortcode' => $shortcode,
            ]);
        });

        ShortcodeFacade::setAdminConfig('featured-brands', function (array $attributes) {
            return ShortcodeForm::createFromArray($attributes)
                ->add('title', 'text', [
                    'label' => __('Title'),
                    'value' => Arr::get($attributes, 'title'),
                    'placeholder' => __('Title'),
                ])
                ->add('subtitle', 'text', [
                    'label' => __('Subtitle'),
                    'value' => Arr::get($attributes, 'subtitle'),
                    'placeholder' => __('Subtitle'),
                ])
                ->add('limit', 'number', [
                    'label' => __('Limit'),
                    'value' => Arr::get($attributes, 'limit', 8),
                    'placeholder' => __('Number of brands to display'),
                ])
                ->add('is_autoplay', 'customSelect', [
                    'label' => __('Is autoplay?'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_autoplay', 'yes'),
                ])
                ->add('is_infinite', 'customSelect', [
                    'label' => __('Loop?'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_infinite', 'yes'),
                ])
                ->add('autoplay_speed', 'customSelect', [
                    'label' => __('Autoplay speed (if autoplay enabled)'),
                    'choices' => theme_get_autoplay_speed_options(),
                    'selected' => Arr::get($attributes, 'autoplay_speed', 3000),
                ])
                ->add('slides_to_show', 'customSelect', [
                    'label' => __('Slides to show'),
                    'choices' => [4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10],
                    'selected' => Arr::get($attributes, 'slides_to_show', 4),
                ])
                ->add('random_order', 'customSelect', [
                    'label' => __('Random order on page refresh?'),
                    'choices' => [
                        'no' => trans('core/base::base.no'),
                        'yes' => trans('core/base::base.yes'),
                    ],
                    'selected' => Arr::get($attributes, 'random_order', 'no'),
                ]);
        });

        if (FlashSaleFacade::isEnabled()) {
            add_shortcode('flash-sale', __('Flash sale'), __('Flash sale'), function (Shortcode $shortcode) {
                $flashSale = FlashSale::query()
                    ->notExpired()
                    ->where('id', $shortcode->flash_sale_id)
                    ->wherePublished()
                    ->with([
                        'products' => function ($query) {
                            $reviewParams = EcommerceHelper::withReviewsParams();

                            if (EcommerceHelper::isReviewEnabled()) {
                                $query->withAvg($reviewParams['withAvg'][0], $reviewParams['withAvg'][1]);
                            }

                            return $query
                                ->wherePublished()
                                ->with(EcommerceHelper::withProductEagerLoadingRelations())
                                ->withCount($reviewParams['withCount']);
                        },
                    ])
                    ->first();

                if (! $flashSale || $flashSale->products->isEmpty()) {
                    return null;
                }

                $isFlashSale = true;
                $wishlistIds = Wishlist::getWishlistIds($flashSale->products->pluck('id')->all());

                return Theme::partial('shortcodes.ecommerce.flash-sale', [
                    'shortcode' => $shortcode,
                    'flashSale' => $flashSale,
                    'isFlashSale' => $isFlashSale,
                    'wishlistIds' => $wishlistIds,
                    'subtitle' => $shortcode->subtitle,
                ]);
            });

            ShortcodeFacade::setAdminConfig('flash-sale', function (array $attributes) {
                $flashSales = FlashSale::query()
                    ->wherePublished()
                    ->notExpired()
                    ->pluck('name', 'id')
                    ->toArray();

                return ShortcodeForm::createFromArray($attributes)
                    ->add('title', 'text', [
                        'label' => __('Title'),
                        'value' => Arr::get($attributes, 'title'),
                        'placeholder' => __('Title'),
                    ])
                    ->add('subtitle', 'text', [
                        'label' => __('Subtitle'),
                        'value' => Arr::get($attributes, 'subtitle'),
                        'placeholder' => __('Subtitle'),
                    ])
                    ->add('flash_sale_id', 'customSelect', [
                        'label' => __('Select a flash sale'),
                        'choices' => $flashSales,
                        'selected' => Arr::get($attributes, 'flash_sale_id'),
                    ])
                    ->add('is_autoplay', 'customSelect', [
                        'label' => __('Is autoplay?'),
                        'choices' => [
                            'yes' => trans('core/base::base.yes'),
                            'no' => trans('core/base::base.no'),
                        ],
                        'selected' => Arr::get($attributes, 'is_autoplay', 'yes'),
                    ])
                    ->add('is_infinite', 'customSelect', [
                        'label' => __('Loop?'),
                        'choices' => [
                            'yes' => trans('core/base::base.yes'),
                            'no' => trans('core/base::base.no'),
                        ],
                        'selected' => Arr::get($attributes, 'is_infinite', 'yes'),
                    ])
                    ->add('autoplay_speed', 'customSelect', [
                        'label' => __('Autoplay speed (if autoplay enabled)'),
                        'choices' => theme_get_autoplay_speed_options(),
                        'selected' => Arr::get($attributes, 'autoplay_speed', 3000),
                    ]);
            });
        }

        add_shortcode(
            'product-collections',
            __('Product Collections'),
            __('Product Collections'),
            function (Shortcode $shortcode) {
                if ($shortcode->collection_id) {
                    $collectionIds = [$shortcode->collection_id];
                } else {
                    $collectionIds = ProductCollection::query()
                        ->wherePublished()
                        ->pluck('id')
                        ->all();
                }

                $limit = (int) $shortcode->limit ?: 8;

                $products = get_products_by_collections(array_merge([
                    'collections' => [
                        'by' => 'id',
                        'value_in' => $collectionIds,
                    ],
                    'take' => $limit,
                    'with' => EcommerceHelper::withProductEagerLoadingRelations(),
                ], EcommerceHelper::withReviewsParams()));

                if ($products->isEmpty()) {
                    return null;
                }

                $wishlistIds = Wishlist::getWishlistIds($products->pluck('id')->all());

                return Theme::partial('shortcodes.ecommerce.product-collections', [
                    'title' => $shortcode->title,
                    'limit' => $limit,
                    'shortcode' => $shortcode,
                    'products' => $products,
                    'wishlistIds' => $wishlistIds,
                    // Display control options
                    'showTitle' => $shortcode->show_title !== 'no',
                    'showRating' => $shortcode->show_rating !== 'no',
                    'showPrice' => $shortcode->show_price !== 'no',
                    'showDescription' => $shortcode->show_description !== 'no',
                    'showStoreInfo' => $shortcode->show_store_info !== 'no',
                    'showLabels' => $shortcode->show_labels !== 'no',
                    'showAddToCart' => $shortcode->show_add_to_cart !== 'no',
                    'showWishlist' => $shortcode->show_wishlist !== 'no',
                ]);
            }
        );

        shortcode()->setAdminConfig('product-collections', function (array $attributes) {
            $productCollections = get_product_collections(select: ['id', 'name', 'slug']);

            return ShortcodeForm::createFromArray($attributes)
                ->add('title', 'text', [
                    'label' => __('Title'),
                    'value' => Arr::get($attributes, 'title'),
                    'placeholder' => __('Title'),
                ])
                ->add('collection_id', 'customSelect', [
                    'label' => __('Select a collection'),
                    'choices' => $productCollections->pluck('name', 'id')->toArray(),
                    'selected' => Arr::get($attributes, 'collection_id'),
                ])
                ->add('limit', 'number', [
                    'label' => __('Limit'),
                    'value' => Arr::get($attributes, 'limit', 8),
                    'placeholder' => __('Number of products to display'),
                ])
                ->add('is_autoplay', 'customSelect', [
                    'label' => __('Is autoplay?'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_autoplay', 'yes'),
                ])
                ->add('is_infinite', 'customSelect', [
                    'label' => __('Loop?'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_infinite', 'yes'),
                ])
                ->add('autoplay_speed', 'customSelect', [
                    'label' => __('Autoplay speed (if autoplay enabled)'),
                    'choices' => theme_get_autoplay_speed_options(),
                    'selected' => Arr::get($attributes, 'autoplay_speed', 3000),
                ])
                ->add('show_title', 'customSelect', [
                    'label' => __('Show Product Title'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_title', 'yes'),
                ])
                ->add('show_rating', 'customSelect', [
                    'label' => __('Show Rating'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_rating', 'yes'),
                ])
                ->add('show_price', 'customSelect', [
                    'label' => __('Show Price'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_price', 'yes'),
                ])
                ->add('show_description', 'customSelect', [
                    'label' => __('Show Description'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_description', 'yes'),
                ])
                ->add('show_store_info', 'customSelect', [
                    'label' => __('Show Store Information'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_store_info', 'yes'),
                ])
                ->add('show_labels', 'customSelect', [
                    'label' => __('Show Product Labels'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_labels', 'yes'),
                ])
                ->add('show_add_to_cart', 'customSelect', [
                    'label' => __('Show Add to Cart Button'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_add_to_cart', 'yes'),
                ])
                ->add('show_wishlist', 'customSelect', [
                    'label' => __('Show Wishlist Button'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_wishlist', 'yes'),
                ]);
        });

        add_shortcode(
            'product-category-products',
            __('Product category products'),
            __('Product category products'),
            function (Shortcode $shortcode) {
                $category = ProductCategory::query()
                    ->wherePublished()
                    ->where('id', (int) $shortcode->category_id)
                    ->with([
                        'activeChildren' => function (HasMany $query) {
                            return $query->limit(3);
                        },
                    ])
                    ->first();

                if (! $category) {
                    return null;
                }

                $limit = (int) $shortcode->limit ?: 8;

                $categoryIds = ProductCategory::getChildrenIds($category->activeChildren, [$category->id]);

                $products = app(ProductInterface::class)->getProductsByCategories(array_merge([
                    'categories' => [
                        'by' => 'id',
                        'value_in' => $categoryIds,
                    ],
                    'take' => $limit,
                ], EcommerceHelper::withReviewsParams()));

                if ($products->isEmpty()) {
                    return null;
                }

                $wishlistIds = Wishlist::getWishlistIds($products->pluck('id')->all());

                return Theme::partial('shortcodes.ecommerce.product-category-products', compact('category', 'products', 'shortcode', 'limit', 'wishlistIds') + [
                    // Display control options
                    'showTitle' => $shortcode->show_title !== 'no',
                    'showRating' => $shortcode->show_rating !== 'no',
                    'showPrice' => $shortcode->show_price !== 'no',
                    'showDescription' => $shortcode->show_description !== 'no',
                    'showStoreInfo' => $shortcode->show_store_info !== 'no',
                    'showLabels' => $shortcode->show_labels !== 'no',
                    'showAddToCart' => $shortcode->show_add_to_cart !== 'no',
                    'showWishlist' => $shortcode->show_wishlist !== 'no',
                ]);
            }
        );

        ShortcodeFacade::setAdminConfig('product-category-products', function (array $attributes) {
            $categories = ProductCategory::query()
                ->wherePublished()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
            
            return ShortcodeForm::createFromArray($attributes)
                ->add('category_id', 'customSelect', [
                    'label' => __('Select category'),
                    'choices' => ['' => __('-- Select Category --')] + $categories,
                    'selected' => Arr::get($attributes, 'category_id'),
                ])
                ->add('number_of_categories', 'number', [
                    'label' => __('Limit number of categories'),
                    'value' => Arr::get($attributes, 'number_of_categories', 3),
                    'placeholder' => __('Default: 3'),
                ])
                ->add('limit', 'number', [
                    'label' => __('Limit number of products'),
                    'value' => Arr::get($attributes, 'limit'),
                    'placeholder' => __('Unlimited by default'),
                ])
                ->add('is_autoplay', 'customSelect', [
                    'label' => __('Is autoplay?'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_autoplay', 'yes'),
                ])
                ->add('infinite', 'customSelect', [
                    'label' => __('Loop?'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'infinite', 'yes'),
                ])
                ->add('autoplay_speed', 'customSelect', [
                    'label' => __('Autoplay speed'),
                    'choices' => theme_get_autoplay_speed_options(),
                    'selected' => Arr::get($attributes, 'autoplay_speed', 3000),
                ])
                ->add('large_screen_columns', 'customSelect', [
                    'label' => __('Large Screen Columns (1400px+)'),
                    'choices' => [
                        '8' => '8 Columns',
                        '7' => '7 Columns',
                        '6' => '6 Columns (Default)',
                        '5' => '5 Columns',
                        '4' => '4 Columns',
                        '3' => '3 Columns',
                        '2' => '2 Columns',
                        '1' => '1 Column',
                    ],
                    'selected' => Arr::get($attributes, 'large_screen_columns', '6'),
                ])
                ->add('desktop_columns', 'customSelect', [
                    'label' => __('Desktop Columns (1200px - 1399px)'),
                    'choices' => [
                        '8' => '8 Columns',
                        '7' => '7 Columns',
                        '6' => '6 Columns',
                        '5' => '5 Columns (Default)',
                        '4' => '4 Columns',
                        '3' => '3 Columns',
                        '2' => '2 Columns',
                        '1' => '1 Column',
                    ],
                    'selected' => Arr::get($attributes, 'desktop_columns', '5'),
                ])
                ->add('tablet_columns', 'customSelect', [
                    'label' => __('Tablet Columns (1024px - 1199px)'),
                    'choices' => [
                        '6' => '6 Columns',
                        '5' => '5 Columns',
                        '4' => '4 Columns (Default)',
                        '3' => '3 Columns',
                        '2' => '2 Columns',
                        '1' => '1 Column',
                    ],
                    'selected' => Arr::get($attributes, 'tablet_columns', '4'),
                ])
                ->add('mobile_columns', 'customSelect', [
                    'label' => __('Mobile Columns (768px - 1023px)'),
                    'choices' => [
                        '4' => '4 Columns',
                        '3' => '3 Columns (Default)',
                        '2' => '2 Columns',
                        '1' => '1 Column',
                    ],
                    'selected' => Arr::get($attributes, 'mobile_columns', '3'),
                ])
                ->add('small_mobile_columns', 'customSelect', [
                    'label' => __('Small Mobile Columns (below 768px)'),
                    'choices' => [
                        '1' => '1 Column',
                        '1.25' => '1.25 Columns (1 + 25% peek)',
                        '1.5' => '1.5 Columns (1 + 50% peek) - Default',
                        '2' => '2 Columns',
                        '2.25' => '2.25 Columns (2 + 25% peek)',
                        '2.5' => '2.5 Columns (2 + 50% peek)',
                        '3' => '3 Columns',
                    ],
                    'selected' => Arr::get($attributes, 'small_mobile_columns', '1.5'),
                ])
                ->add('show_title', 'customSelect', [
                    'label' => __('Show Product Title'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_title', 'yes'),
                ])
                ->add('show_rating', 'customSelect', [
                    'label' => __('Show Rating'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_rating', 'yes'),
                ])
                ->add('show_price', 'customSelect', [
                    'label' => __('Show Price'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_price', 'yes'),
                ])
                ->add('show_description', 'customSelect', [
                    'label' => __('Show Description'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_description', 'yes'),
                ])
                ->add('show_store_info', 'customSelect', [
                    'label' => __('Show Store Information'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_store_info', 'yes'),
                ])
                ->add('show_labels', 'customSelect', [
                    'label' => __('Show Product Labels'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_labels', 'yes'),
                ])
                ->add('show_add_to_cart', 'customSelect', [
                    'label' => __('Show Add to Cart Button'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_add_to_cart', 'yes'),
                ])
                ->add('show_wishlist', 'customSelect', [
                    'label' => __('Show Wishlist Button'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_wishlist', 'yes'),
                ]);
        });

        add_shortcode('featured-products', __('Featured products'), __('Featured products'), function (Shortcode $shortcode) {
            $limit = (int) $shortcode->limit ?: 10;

            $products = get_featured_products([
                'take' => $limit,
                'with' => EcommerceHelper::withProductEagerLoadingRelations(),
            ] + EcommerceHelper::withReviewsParams());

            if ($products->isEmpty()) {
                return null;
            }

            $wishlistIds = Wishlist::getWishlistIds(collect($products->toArray())->pluck('id')->all());

            return Theme::partial('shortcodes.ecommerce.featured-products', [
                'title' => $shortcode->title,
                'subtitle' => $shortcode->subtitle,
                'shortcode' => $shortcode,
                'products' => $products,
                'wishlistIds' => $wishlistIds,
                // Display control options
                'showTitle' => $shortcode->show_title !== 'no',
                'showRating' => $shortcode->show_rating !== 'no',
                'showPrice' => $shortcode->show_price !== 'no',
                'showDescription' => $shortcode->show_description !== 'no',
                'showStoreInfo' => $shortcode->show_store_info !== 'no',
                'showLabels' => $shortcode->show_labels !== 'no',
                'showAddToCart' => $shortcode->show_add_to_cart !== 'no',
                'showWishlist' => $shortcode->show_wishlist !== 'no',
            ]);
        });

        ShortcodeFacade::setAdminConfig('featured-products', function (array $attributes) {
            return ShortcodeForm::createFromArray($attributes)
                ->add('title', 'text', [
                    'label' => __('Title'),
                    'value' => Arr::get($attributes, 'title'),
                    'placeholder' => __('Title'),
                ])
                ->add('subtitle', 'text', [
                    'label' => __('Subtitle'),
                    'value' => Arr::get($attributes, 'subtitle'),
                    'placeholder' => __('Subtitle'),
                ])
                ->add('limit', 'number', [
                    'label' => __('Limit'),
                    'value' => Arr::get($attributes, 'limit', 10),
                    'placeholder' => __('Number of products to display'),
                ])
                ->add('is_autoplay', 'customSelect', [
                    'label' => __('Is autoplay?'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_autoplay', 'yes'),
                ])
                ->add('is_infinite', 'customSelect', [
                    'label' => __('Loop?'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'is_infinite', 'yes'),
                ])
                ->add('autoplay_speed', 'customSelect', [
                    'label' => __('Autoplay speed (if autoplay enabled)'),
                    'choices' => theme_get_autoplay_speed_options(),
                    'selected' => Arr::get($attributes, 'autoplay_speed', 3000),
                ])
                ->add('show_title', 'customSelect', [
                    'label' => __('Show Product Title'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_title', 'yes'),
                ])
                ->add('show_rating', 'customSelect', [
                    'label' => __('Show Rating'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_rating', 'yes'),
                ])
                ->add('show_price', 'customSelect', [
                    'label' => __('Show Price'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_price', 'yes'),
                ])
                ->add('show_description', 'customSelect', [
                    'label' => __('Show Description'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_description', 'yes'),
                ])
                ->add('show_store_info', 'customSelect', [
                    'label' => __('Show Store Information'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_store_info', 'yes'),
                ])
                ->add('show_labels', 'customSelect', [
                    'label' => __('Show Product Labels'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_labels', 'yes'),
                ])
                ->add('show_add_to_cart', 'customSelect', [
                    'label' => __('Show Add to Cart Button'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_add_to_cart', 'yes'),
                ])
                ->add('show_wishlist', 'customSelect', [
                    'label' => __('Show Wishlist Button'),
                    'choices' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'selected' => Arr::get($attributes, 'show_wishlist', 'yes'),
                ]);
        });
    }

    if (is_plugin_active('blog')) {
        add_shortcode('featured-posts', __('Featured Blog Posts'), __('Featured Blog Posts'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.featured-posts', compact('shortcode'));
        });

        shortcode()->setAdminConfig('featured-posts', function (array $attributes) {
            return Theme::partial('shortcodes.featured-posts-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('contact')) {
        add_filter(CONTACT_FORM_TEMPLATE_VIEW, function () {
            return Theme::getThemeNamespace() . '::partials.shortcodes.contact-form';
        }, 120);
    }

    add_shortcode('contact-info-boxes', __('Contact info boxes'), __('Contact info boxes'), function (Shortcode $shortcode) {
        $form = ContactForm::createFromArray(
            Arr::except($shortcode->toArray(), ['name', 'email', 'phone', 'content', 'subject', 'address'])
        );

        return Theme::partial('shortcodes.contact-info-boxes', compact('shortcode', 'form'));
    });

    shortcode()->setAdminConfig('contact-info-boxes', function (array $attributes) {
        return Theme::partial('shortcodes.contact-info-boxes-admin-config', compact('attributes'));
    });

    if (is_plugin_active('faq')) {
        add_shortcode('faq', __('FAQs'), __('FAQs'), function (Shortcode $shortcode) {
            $categoryIds = array_filter((array) $shortcode->category_ids);

            $categoriesQuery = FaqCategory::query()
                ->wherePublished()
                ->with([
                    'faqs' => function (HasMany $query): void {
                        $query->wherePublished();
                    },
                ])
                ->orderBy('order')->latest();

            if (! empty($categoryIds)) {
                $categoriesQuery->whereIn('id', $categoryIds);
            }

            $categories = $categoriesQuery->get();

            if ($categories->isEmpty()) {
                return null;
            }

            return Theme::partial('shortcodes.faq', [
                'title' => $shortcode->title,
                'subtitle' => $shortcode->subtitle,
                'categories' => $categories,
            ]);
        });

        ShortcodeFacade::setAdminConfig('faq', function (array $attributes) {
            $categories = FaqCategory::query()
                ->wherePublished()
                ->pluck('name', 'id')
                ->toArray();

            return ShortcodeForm::createFromArray($attributes)
                ->add('title', 'text', [
                    'label' => __('Title'),
                    'value' => Arr::get($attributes, 'title'),
                    'placeholder' => __('Title'),
                ])
                ->add('subtitle', 'text', [
                    'label' => __('Subtitle'),
                    'value' => Arr::get($attributes, 'subtitle'),
                    'placeholder' => __('Subtitle'),
                ])
                ->add('category_ids', 'multiCheckList', [
                    'label' => __('Categories'),
                    'choices' => $categories,
                    'value' => Arr::get($attributes, 'category_ids', []),
                    'help_block' => [
                        'text' => __('Select categories to display. If none is selected, all categories will be displayed.'),
                    ],
                ]);
        });
    }

    add_shortcode('coming-soon', __('Coming Soon'), __('Coming Soon'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.coming-soon', compact('shortcode'));
    });

    shortcode()->setAdminConfig('coming-soon', function (array $attributes) {
        return Theme::partial('shortcodes.coming-soon-admin-config', compact('attributes'));
    });

    add_shortcode('site-features', __('Site features'), __('Site features'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.site-features', compact('shortcode'));
    });

    ShortcodeFacade::setAdminConfig('site-features', function (array $attributes) {
        $fields = [
            'title' => [
                'title' => __('Title'),
                'required' => true,
            ],
            'subtitle' => [
                'title' => __('Subtitle'),
                'required' => true,
            ],
            'icon' => [
                'type' => 'image',
                'title' => __('Icon'),
                'required' => true,
            ],
        ];

        return ShortcodeForm::createFromArray($attributes)
            ->add('title', 'text', [
                'label' => __('Title'),
            ])
            ->add('feature_tabs', 'tabs', [
                'fields' => $fields,
                'shortcode_attributes' => $attributes,
            ]);
    });

    // Advanced Video Block Shortcode
    add_shortcode('advanced-video-block', __('Advanced Video Block'), __('Advanced Video Block'), function (Shortcode $shortcode) {
        $videos = [];
        
        // Parse video data from shortcode attributes
        for ($i = 1; $i <= 20; $i++) {
            $videoTitle = $shortcode->{"video_title_$i"};
            $videoDescription = $shortcode->{"video_description_$i"};
            $videoType = $shortcode->{"video_type_$i"};
            $videoSource = $shortcode->{"video_source_$i"};
            $videoThumbnail = $shortcode->{"video_thumbnail_$i"};
            
            if ($videoTitle || $videoSource) {
                $videos[] = [
                    'title' => $videoTitle,
                    'description' => $videoDescription,
                    'type' => $videoType ?: 'upload',
                    'source' => $videoSource,
                    'thumbnail' => $videoThumbnail,
                ];
            }
        }

        if (empty($videos)) {
            return null;
        }

        return Theme::partial('shortcodes.advanced-video-block', [
            'title' => $shortcode->title,
            'description' => $shortcode->description,
            'videos' => $videos,
            'shortcode' => $shortcode,
        ]);
    });

    ShortcodeFacade::setAdminConfig('advanced-video-block', function (array $attributes) {
        return Theme::partial('shortcodes.advanced-video-block-admin-config', compact('attributes'));
    });

    // Project Request CTA Button Shortcode
    add_shortcode('project-request-cta', __('Project Request CTA'), __('Add a call-to-action button for project requests'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.project-request-cta', [
            'shortcode' => $shortcode,
        ]);
    });

    ShortcodeFacade::setAdminConfig('project-request-cta', function (array $attributes) {
        return ShortcodeForm::createFromArray([
            'button_text' => [
                'title' => __('Button Text'),
                'type' => 'text',
                'default_value' => 'Request Project Quote',
            ],
            'button_style' => [
                'title' => __('Button Style'),
                'type' => 'select',
                'choices' => [
                    'primary' => __('Primary'),
                    'secondary' => __('Secondary'),
                    'success' => __('Success'),
                    'info' => __('Info'),
                    'warning' => __('Warning'),
                    'danger' => __('Danger'),
                ],
                'default_value' => 'primary',
            ],
            'button_size' => [
                'title' => __('Button Size'),
                'type' => 'select',
                'choices' => [
                    'sm' => __('Small'),
                    'md' => __('Medium'),
                    'lg' => __('Large'),
                ],
                'default_value' => 'md',
            ],
            'show_icon' => [
                'title' => __('Show Icon'),
                'type' => 'select',
                'choices' => [
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ],
                'default_value' => 'yes',
            ],
            'alignment' => [
                'title' => __('Alignment'),
                'type' => 'select',
                'choices' => [
                    'left' => __('Left'),
                    'center' => __('Center'),
                    'right' => __('Right'),
                ],
                'default_value' => 'center',
            ],
            'custom_css_class' => [
                'title' => __('Custom CSS Class'),
                'type' => 'text',
                'default_value' => '',
            ],
        ]);
    });

    // Project Request Form Shortcode
    ShortcodeFacade::register('project-request-form', __('Project Request Form'), __('Display project request form'), function (Shortcode $shortcode): string {
        return Theme::partial('shortcodes.project-request-form', []);
    }, '');

    // Project Request CTA Button Shortcode
    ShortcodeFacade::register('project-request-cta', __('Project Request CTA'), __('Display project request call-to-action button'), function (Shortcode $shortcode): string {
        return Theme::partial('shortcodes.project-request-cta', [
            'title' => $shortcode->title ?: 'Request Project Quote',
            'class' => $shortcode->class ?: 'btn btn-primary',
        ]);
    }, '', [
        'title' => [
            'title' => __('Button Text'),
            'type' => 'text',
            'default_value' => 'Request Project Quote',
        ],
        'class' => [
            'title' => __('CSS Classes'),
            'type' => 'text',
            'default_value' => 'btn btn-primary',
        ],
    ]);
});

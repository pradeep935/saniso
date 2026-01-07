@php
    use Botble\Ecommerce\Models\ProductCategory;
    use Botble\Base\Facades\BaseHelper;
    use Botble\Menu\Models\Menu as MenuModel;
    use Botble\Menu\Models\MenuNode;
    use Botble\Menu\Models\MenuLocation;
    use Botble\Language\Facades\Language;
    use Botble\Ecommerce\Facades\Currency as CurrencyFacade;

    // Get all product categories for the advanced menu with language support
    $allCategories = [];
    if (is_plugin_active('ecommerce')) {
        $categoryQuery = ProductCategory::query()
            ->where('status', 'published')
            ->with(['children', 'children.children'])
            ->whereNull('parent_id')
            ->orderBy('order');
            
        // Add language support using translations if language plugin is active
        if (is_plugin_active('language')) {
            $currentLocale = Language::getCurrentLocale();
            // Get the full language code (e.g., en_US, nl_NL) from the languages table
            $currentLangCode = \DB::table('languages')->where('lang_locale', $currentLocale)->value('lang_code');
            
            if ($currentLangCode) {
                // Join with translations table for proper language support
                $categoryQuery->leftJoin('ec_product_categories_translations as t', function($join) use ($currentLangCode) {
                    $join->on('ec_product_categories.id', '=', 't.ec_product_categories_id')
                         ->where('t.lang_code', '=', $currentLangCode);
                })
                ->select('ec_product_categories.*', 't.name as translated_name', 't.slug as translated_slug')
                ->orderBy('ec_product_categories.order');
            }
        }
        
        $allCategories = $categoryQuery->get();
        
        // If using translations, update the names and slugs
        if (is_plugin_active('language')) {
            $allCategories->each(function($category) {
                if ($category->translated_name) {
                    $category->name = $category->translated_name;
                }
                if ($category->translated_slug) {
                    $category->slug = $category->translated_slug;
                }
            });
        }
    }

    // Get menu items - use same approach as main menu (let Botble handle languages)
    $mainMenu = collect([]);
    $headerMenu = collect([]);
    
    // Get currencies for currency switcher
    $currencies = [];
    if (is_plugin_active('ecommerce')) {
        try {
            // Use Currency facade for more reliable currency fetching
            $allCurrencies = CurrencyFacade::currencies();
            $currencies = $allCurrencies && $allCurrencies->count() > 0 ? $allCurrencies : [];
        } catch (Exception $e) {
            $currencies = [];
        }
    }
    // Ensure $currencies is always an array or collection
    $currencies = $currencies instanceof \Illuminate\Support\Collection ? $currencies : collect($currencies);
    
    // Simple approach: Use Botble's built-in menu system exactly like main menu
    try {
        // Get mobile menu using the exact same pattern as main menu in header.blade.php
        // This should automatically handle language switching
        $menuLocation = MenuLocation::where('location', 'mobile-menu')->first();
        if ($menuLocation && $menuLocation->menu) {
            $mobileMenuModel = $menuLocation->menu;
            
            // Get menu items using the same pattern as the original code
            $mainMenu = MenuNode::where('menu_id', $mobileMenuModel->id)
                ->with(['child.child'])
                ->whereNull('parent_id')
                ->orderBy('position')
                ->get();
                
            // If no items found with null parent_id, try with parent_id = 0
            if ($mainMenu->isEmpty()) {
                $mainMenu = MenuNode::where('menu_id', $mobileMenuModel->id)
                    ->with(['child.child'])
                    ->where('parent_id', 0)
                    ->orderBy('position')
                    ->get();
            }
            
            // If still no items, get all items for this menu
            if ($mainMenu->isEmpty()) {
                $mainMenu = MenuNode::where('menu_id', $mobileMenuModel->id)
                    ->with(['child.child'])
                    ->orderBy('position')
                    ->get();
            }
        }
        
    } catch (Exception $e) {
        // Fallback to empty collections if there's an error
        $mainMenu = collect([]);
        $headerMenu = collect([]);
        
        // Log the error for debugging
        if (config('app.debug')) {
            logger('Mobile menu loading error: ' . $e->getMessage());
        }
    }
@endphp

{{-- Advanced Mobile Menu Panel --}}
<div class="panel--sidebar advanced-mobile-menu" id="navigation-mobile">
    <div class="panel__header d-flex align-items-center">
        <span class="svg-icon close-toggle--sidebar">
            <svg>
                <use href="#svg-icon-arrow-left" xlink:href="#svg-icon-arrow-left"></use>
            </svg>
        </span>
        <div class="mobile-menu-logo ms-2">
            <a href="{{ url('/') }}" aria-label="{{ theme_option('site_title', config('app.name')) }}">
                {!! Theme::getLogoImage(['style' => 'max-height:36px; display:block']) !!}
            </a>
        </div>
    </div>
    
    <div class="panel__content">
        <div class="mobile-menu-wrapper">
            {{-- Main Menu Level --}}
            <div class="menu-level menu-level-main active" id="menu-main">
                <ul class="menu--mobile-advanced">
                    
                            
                    {{-- Mobile Menu Items from Admin Panel --}}
                    @foreach ($mainMenu as $menuItem)
                        {{-- Check if this is a special menu item --}}
                        @php
                            $isSpecialItem = false;
                            $specialType = '';
                            
                            // Check if this menu item has special functionality based on URL or title
                            $url = strtolower($menuItem->url ?? '');
                            $title = strtolower($menuItem->title ?? '');
                            
                            if (strpos($url, '#categories') !== false || strpos($title, 'categories') !== false) {
                                $isSpecialItem = true;
                                $specialType = 'categories';
                            } elseif (strpos($url, '#category-') === 0) {
                                // Dynamic category link: #category-slug or #category-id
                                $isSpecialItem = true;
                                $specialType = 'dynamic-category';
                                $categoryIdentifier = substr($url, 10); // Remove '#category-' prefix
                            } elseif (strpos($url, '#language') !== false || strpos($title, 'language') !== false) {
                                $isSpecialItem = true;
                                $specialType = 'language';
                            } elseif (strpos($url, '#currency') !== false || strpos($title, 'currency') !== false) {
                                $isSpecialItem = true;
                                $specialType = 'currency';
                            } elseif (strpos($url, '#login') !== false || strpos($title, 'login') !== false || strpos($title, 'account') !== false) {
                                $isSpecialItem = true;
                                $specialType = 'account';
                            } elseif (strpos($url, '#wishlist') !== false || strpos($title, 'wishlist') !== false) {
                                $isSpecialItem = true;
                                $specialType = 'wishlist';
                            } elseif (strpos($url, '#cart') !== false || strpos($title, 'cart') !== false) {
                                $isSpecialItem = true;
                                $specialType = 'cart';
                            } elseif (strpos($url, '#compare') !== false || strpos($title, 'compare') !== false) {
                                $isSpecialItem = true;
                                $specialType = 'compare';
                            }
                        @endphp

                        @if ($isSpecialItem)
                            {{-- Render special menu items --}}
                            @if ($specialType === 'categories' && is_plugin_active('ecommerce') && $allCategories->isNotEmpty())
                                <li class="menu-item menu-item-has-children menu-item-categories">
                                    <a href="javascript:void(0)" class="menu-item-link" data-target="menu-cat-categories">
                                        @if ($menuItem->icon_html)
                                            {!! $menuItem->icon_html !!}
                                        @else
                                            <span class="menu-icon">
                                                <svg width="20" height="20">
                                                    <use href="#svg-icon-list"></use>
                                                </svg>
                                            </span>
                                        @endif
                                        <span>{{ $menuItem->title }}</span>
                                        <span class="menu-arrow">
                                            <svg width="16" height="16">
                                                <use href="#svg-icon-chevron-right"></use>
                                            </svg>
                                        </span>
                                    </a>
                                </li>
                            @elseif ($specialType === 'dynamic-category' && is_plugin_active('ecommerce') && $allCategories->isNotEmpty())
                                @php
                                    // Find category by slug or ID with language support
                                    $targetCategory = null;
                                    if (is_numeric($categoryIdentifier)) {
                                        // Find by ID
                                        $targetCategory = $allCategories->where('id', $categoryIdentifier)->first();
                                    } else {
                                        // Find by slug
                                        $targetCategory = $allCategories->where('slug', $categoryIdentifier)->first();
                                    }
                                    
                                    // Generate language-aware URL if category found
                                    $categoryUrl = '';
                                    if ($targetCategory) {
                                        if (is_plugin_active('language') && method_exists($targetCategory, 'url')) {
                                            $categoryUrl = Language::getLocalizedURL(null, $targetCategory->url);
                                        } else {
                                            $categoryUrl = $targetCategory->url ?? route('public.products');
                                        }
                                    }
                                @endphp
                                @if ($targetCategory && $categoryUrl)
                                    <li class="menu-item">
                                        <a href="{{ $categoryUrl }}" class="menu-item-link">
                                            @if ($menuItem->icon_html)
                                                {!! $menuItem->icon_html !!}
                                            @else
                                                <span class="menu-icon">
                                                    <svg width="20" height="20">
                                                        <use href="#svg-icon-tag"></use>
                                                    </svg>
                                                </span>
                                            @endif
                                            <span>{{ $menuItem->title }}</span>
                                        </a>
                                    </li>
                                @endif
                            @elseif ($specialType === 'language' && is_plugin_active('language') && count($supportedLocales = Language::getSupportedLocales()) > 1)
                                <li class="menu-item menu-item-has-children menu-item-language">
                                    <a href="javascript:void(0)" class="menu-item-link" data-target="menu-language">
                                        @if ($menuItem->icon_html)
                                            {!! $menuItem->icon_html !!}
                                        @else
                                            <span class="menu-icon">
                                                <svg width="20" height="20">
                                                    <use href="#svg-icon-globe"></use>
                                                </svg>
                                            </span>
                                        @endif
                                        <span>{{ $menuItem->title }} ({{ strtoupper(Language::getCurrentLocale()) }})</span>
                                        <span class="menu-arrow">
                                            <svg width="16" height="16">
                                                <use href="#svg-icon-chevron-right"></use>
                                            </svg>
                                        </span>
                                    </a>
                                </li>
                            @elseif ($specialType === 'currency' && is_plugin_active('ecommerce') && $currencies->count() > 1)
                                <li class="menu-item menu-item-has-children menu-item-currency">
                                    <a href="javascript:void(0)" class="menu-item-link" data-target="menu-currency">
                                        @if ($menuItem->icon_html)
                                            {!! $menuItem->icon_html !!}
                                        @else
                                            <span class="menu-icon">
                                                <svg width="20" height="20">
                                                    <use href="#svg-icon-dollar-sign"></use>
                                                </svg>
                                            </span>
                                        @endif
                                        <span>{{ $menuItem->title }} ({{ get_application_currency() ? get_application_currency()->title : 'USD' }})</span>
                                        <span class="menu-arrow">
                                            <svg width="16" height="16">
                                                <use href="#svg-icon-chevron-right"></use>
                                            </svg>
                                        </span>
                                    </a>
                                </li>
                            @elseif ($specialType === 'account' && is_plugin_active('ecommerce'))
                                @if (auth('customer')->check())
                                    <li class="menu-item menu-item-has-children menu-item-account">
                                        <a href="javascript:void(0)" class="menu-item-link" data-target="menu-account">
                                            @if ($menuItem->icon_html)
                                                {!! $menuItem->icon_html !!}
                                            @else
                                                <span class="menu-icon">
                                                    <svg width="20" height="20">
                                                        <use href="#svg-icon-user"></use>
                                                    </svg>
                                                </span>
                                            @endif
                                            <span>{{ auth('customer')->user()->name }}</span>
                                            <span class="menu-arrow">
                                                <svg width="16" height="16">
                                                    <use href="#svg-icon-chevron-right"></use>
                                                </svg>
                                            </span>
                                        </a>
                                    </li>
                                @else
                                    <li class="menu-item">
                                        @if (Route::has('customer.login'))
                                            <a href="{{ route('customer.login') }}" class="menu-item-link">
                                        @else
                                            <a href="/login" class="menu-item-link">
                                        @endif
                                            @if ($menuItem->icon_html)
                                                {!! $menuItem->icon_html !!}
                                            @else
                                                <span class="menu-icon">
                                                    <svg width="20" height="20">
                                                        <use href="#svg-icon-user"></use>
                                                    </svg>
                                                </span>
                                            @endif
                                            <span>{{ $menuItem->title }}</span>
                                        </a>
                                    </li>
                                @endif
                            @elseif ($specialType === 'wishlist' && is_plugin_active('ecommerce') && EcommerceHelper::isWishlistEnabled())
                                <li class="menu-item">
                                    @if (Route::has('public.wishlist'))
                                        <a href="{{ route('public.wishlist') }}" class="menu-item-link">
                                    @else
                                        <a href="/wishlist" class="menu-item-link">
                                    @endif
                                        @if ($menuItem->icon_html)
                                            {!! $menuItem->icon_html !!}
                                        @else
                                            <span class="menu-icon">
                                                <svg width="20" height="20">
                                                    <use href="#svg-icon-heart"></use>
                                                </svg>
                                            </span>
                                        @endif
                                        <span>{{ $menuItem->title }}</span>
                                        @if (is_plugin_active('ecommerce'))
                                            <span class="menu-badge">{{ \Botble\Ecommerce\Facades\Cart::instance('wishlist')->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                            @elseif ($specialType === 'cart' && is_plugin_active('ecommerce'))
                                <li class="menu-item">
                                    @if (Route::has('public.cart'))
                                        <a href="{{ route('public.cart') }}" class="menu-item-link">
                                    @else
                                        <a href="/cart" class="menu-item-link">
                                    @endif
                                        @if ($menuItem->icon_html)
                                            {!! $menuItem->icon_html !!}
                                        @else
                                            <span class="menu-icon">
                                                <svg width="20" height="20">
                                                    <use href="#svg-icon-cart"></use>
                                                </svg>
                                            </span>
                                        @endif
                                        <span>{{ $menuItem->title }}</span>
                                        @if (is_plugin_active('ecommerce'))
                                            <span class="menu-badge">{{ \Botble\Ecommerce\Facades\Cart::instance('cart')->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                            @elseif ($specialType === 'compare' && is_plugin_active('ecommerce') && EcommerceHelper::isCompareEnabled())
                                <li class="menu-item">
                                    @if (Route::has('public.compare'))
                                        <a href="{{ route('public.compare') }}" class="menu-item-link">
                                    @else
                                        <a href="/compare" class="menu-item-link">
                                    @endif
                                        @if ($menuItem->icon_html)
                                            {!! $menuItem->icon_html !!}
                                        @else
                                            <span class="menu-icon">
                                                <svg width="20" height="20">
                                                    <use href="#svg-icon-repeat"></use>
                                                </svg>
                                            </span>
                                        @endif
                                        <span>{{ $menuItem->title }}</span>
                                    </a>
                                </li>
                            @endif
                        @else
                            {{-- Regular menu items --}}
                            <li class="menu-item {{ $menuItem->child->isNotEmpty() ? 'menu-item-has-children' : '' }}">
                                @if ($menuItem->child->isNotEmpty())
                                    <a href="javascript:void(0)" class="menu-item-link" data-target="menu-node-{{ $menuItem->id }}">
                                        {!! $menuItem->icon_html !!}
                                        <span>{{ $menuItem->title }}</span>
                                        <span class="menu-arrow">
                                            <svg width="16" height="16">
                                                <use href="#svg-icon-chevron-right"></use>
                                            </svg>
                                        </span>
                                    </a>
                                @else
                                    @php
                                        // Generate language-aware URL for regular menu items
                                        $menuItemUrl = $menuItem->url;
                                        if (is_plugin_active('language') && $menuItemUrl && !str_starts_with($menuItemUrl, 'http')) {
                                            $menuItemUrl = Language::getLocalizedURL(null, $menuItemUrl);
                                        }
                                    @endphp
                                    <a href="{{ $menuItemUrl }}" class="menu-item-link">
                                        {!! $menuItem->icon_html !!}
                                        <span>{{ $menuItem->title }}</span>
                                    </a>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            {{-- Categories Sub-Menu --}}
            @if (is_plugin_active('ecommerce') && $allCategories->isNotEmpty())
                <div class="menu-level menu-level-categories" id="menu-cat-categories">
                    <div class="menu-level-header">
                        <button class="menu-back-btn" data-target="menu-main">
                            <svg width="16" height="16">
                                <use href="#svg-icon-arrow-left"></use>
                            </svg>
                            <span>{{ __('Back') }}</span>
                        </button>
                        <h4>{{ __('Product Categories') }}</h4>
                    </div>
                    <ul class="menu--mobile-advanced">
                        @foreach ($allCategories as $category)
                            <li class="menu-item {{ $category->children->isNotEmpty() ? 'menu-item-has-children' : '' }}">
                                @if ($category->children->isNotEmpty())
                                    <a href="javascript:void(0)" class="menu-item-link category-item" data-target="menu-cat-category-{{ $category->id }}">
                                        @if ($category->image)
                                            <div class="category-image">
                                                <img src="{{ RvMedia::getImageUrl($category->image, 'thumb', false, RvMedia::getDefaultImage()) }}" 
                                                     alt="{{ $category->name }}" 
                                                     loading="lazy">
                                            </div>
                                        @else
                                            <span class="menu-icon">
                                                <svg width="20" height="20">
                                                    <use href="#svg-icon-folder"></use>
                                                </svg>
                                            </span>
                                        @endif
                                        <span>{{ $category->name }}</span>
                                        <span class="menu-arrow">
                                            <svg width="16" height="16">
                                                <use href="#svg-icon-chevron-right"></use>
                                            </svg>
                                        </span>
                                    </a>
                                @else
                                    <a href="{{ $category->url }}" class="menu-item-link category-item">
                                        @if ($category->image)
                                            <div class="category-image">
                                                <img src="{{ RvMedia::getImageUrl($category->image, 'thumb', false, RvMedia::getDefaultImage()) }}" 
                                                     alt="{{ $category->name }}" 
                                                     loading="lazy">
                                            </div>
                                        @else
                                            <span class="menu-icon">
                                                <svg width="20" height="20">
                                                    <use href="#svg-icon-folder"></use>
                                                </svg>
                                            </span>
                                        @endif
                                        <span>{{ $category->name }}</span>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Individual Category Sub-Menus --}}
                @foreach ($allCategories as $category)
                    @if ($category->children->isNotEmpty())
                        <div class="menu-level menu-level-subcategory" id="menu-cat-category-{{ $category->id }}">
                            <div class="menu-level-header">
                                <button class="menu-back-btn" data-target="menu-cat-categories">
                                    <svg width="16" height="16">
                                        <use href="#svg-icon-arrow-left"></use>
                                    </svg>
                                    <span>{{ __('Back') }}</span>
                                </button>
                                <h4>{{ $category->name }}</h4>
                            </div>
                            <ul class="menu--mobile-advanced">
                                {{-- View All Link --}}
                                <li class="menu-item menu-item-view-all">
                                    <a href="{{ $category->url }}" class="menu-item-link">
                                        <span class="menu-icon">
                                            <svg width="20" height="20">
                                                <use href="#svg-icon-eye"></use>
                                            </svg>
                                        </span>
                                        <span>{{ __('View All in :category', ['category' => $category->name]) }}</span>
                                    </a>
                                </li>
                                
                                {{-- Child Categories --}}
                                @foreach ($category->children as $child)
                                    <li class="menu-item {{ $child->children->isNotEmpty() ? 'menu-item-has-children' : '' }}">
                                        @if ($child->children->isNotEmpty())
                                            <a href="javascript:void(0)" class="menu-item-link" data-target="menu-cat-category-{{ $child->id }}">
                                                <span>{{ $child->name }}</span>
                                                <span class="menu-arrow">
                                                    <svg width="16" height="16">
                                                        <use href="#svg-icon-chevron-right"></use>
                                                    </svg>
                                                </span>
                                            </a>
                                        @else
                                            @php
                                                $childUrl = $child->url;
                                                if (is_plugin_active('language') && $childUrl && !str_starts_with($childUrl, 'http')) {
                                                    $childUrl = Language::getLocalizedURL(null, $childUrl);
                                                }
                                            @endphp
                                            <a href="{{ $childUrl }}" class="menu-item-link">
                                                <span>{{ $child->name }}</span>
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Grandchild Categories --}}
                        @foreach ($category->children as $child)
                            @if ($child->children->isNotEmpty())
                                <div class="menu-level menu-level-subcategory" id="menu-cat-category-{{ $child->id }}">
                                    <div class="menu-level-header">
                                        <button class="menu-back-btn" data-target="menu-cat-category-{{ $category->id }}">
                                            <svg width="16" height="16">
                                                <use href="#svg-icon-arrow-left"></use>
                                            </svg>
                                            <span>{{ __('Back') }}</span>
                                        </button>
                                        <h4>{{ $child->name }}</h4>
                                    </div>
                                    <ul class="menu--mobile-advanced">
                                        <li class="menu-item menu-item-view-all">
                                            @php
                                                $childViewAllUrl = $child->url;
                                                if (is_plugin_active('language') && $childViewAllUrl && !str_starts_with($childViewAllUrl, 'http')) {
                                                    $childViewAllUrl = Language::getLocalizedURL(null, $childViewAllUrl);
                                                }
                                            @endphp
                                            <a href="{{ $childViewAllUrl }}" class="menu-item-link">
                                                <span class="menu-icon">
                                                    <svg width="20" height="20">
                                                        <use href="#svg-icon-eye"></use>
                                                    </svg>
                                                </span>
                                                <span>{{ __('View All in :category', ['category' => $child->name]) }}</span>
                                            </a>
                                        </li>
                                        @foreach ($child->children as $grandchild)
                                            <li class="menu-item">
                                                @php
                                                    $grandchildUrl = $grandchild->url;
                                                    if (is_plugin_active('language') && $grandchildUrl && !str_starts_with($grandchildUrl, 'http')) {
                                                        $grandchildUrl = Language::getLocalizedURL(null, $grandchildUrl);
                                                    }
                                                @endphp
                                                <a href="{{ $grandchildUrl }}" class="menu-item-link">
                                                    <span>{{ $grandchild->name }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endif

            {{-- Account Sub-Menu --}}
            @if (is_plugin_active('ecommerce') && auth('customer')->check())
                <div class="menu-level menu-level-account" id="menu-account">
                    <div class="menu-level-header">
                        <button class="menu-back-btn" data-target="menu-main">
                            <svg width="16" height="16">
                                <use href="#svg-icon-arrow-left"></use>
                            </svg>
                            <span>{{ __('Back') }}</span>
                        </button>
                        <h4>{{ __('My Account') }}</h4>
                    </div>
                    <ul class="menu--mobile-advanced">
                        <li class="menu-item">
                            <a href="{{ route('customer.overview') }}" class="menu-item-link">
                                <span class="menu-icon">
                                    <svg width="20" height="20">
                                        <use href="#svg-icon-user"></use>
                                    </svg>
                                </span>
                                <span>{{ __('Account Overview') }}</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('customer.orders') }}" class="menu-item-link">
                                <span class="menu-icon">
                                    <svg width="20" height="20">
                                        <use href="#svg-icon-package"></use>
                                    </svg>
                                </span>
                                <span>{{ __('Orders & Invoices') }}</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('customer.order_returns') }}" class="menu-item-link">
                                <span class="menu-icon">
                                    <svg width="20" height="20">
                                        <use href="#svg-icon-rotate-ccw"></use>
                                    </svg>
                                </span>
                                <span>{{ __('Returns') }}</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ url('orders/tracking') }}" class="menu-item-link">
                                <span class="menu-icon">
                                    <svg width="20" height="20">
                                        <use href="#svg-icon-truck"></use>
                                    </svg>
                                </span>
                                <span>{{ __('Order Tracking') }}</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('customer.logout') }}" class="menu-item-link menu-item-logout">
                                <span class="menu-icon">
                                    <svg width="20" height="20">
                                        <use href="#svg-icon-log-out"></use>
                                    </svg>
                                </span>
                                <span>{{ __('Logout') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            {{-- Language Sub-Menu --}}
            @if (is_plugin_active('language') && count($supportedLocales = Language::getSupportedLocales()) > 1)
                <div class="menu-level menu-level-language" id="menu-language">
                    <div class="menu-level-header">
                        <button class="menu-back-btn" data-target="menu-main">
                            <svg width="16" height="16">
                                <use href="#svg-icon-arrow-left"></use>
                            </svg>
                            <span>{{ __('Back') }}</span>
                        </button>
                        <h4>{{ __('Choose Language') }}</h4>
                    </div>
                    <ul class="menu--mobile-advanced">
                        @foreach ($supportedLocales as $localeCode => $properties)
                            <li class="menu-item {{ $localeCode == Language::getCurrentLocale() ? 'menu-item-active' : '' }}">
                                <a href="{{ Language::getLocalizedURL($localeCode) }}" class="menu-item-link">
                                    @if (isset($properties['flag']))
                                        <span class="language-flag">{{ $properties['flag'] }}</span>
                                    @endif
                                    <span>{{ $properties['native'] ?? ($properties['name'] ?? $localeCode) }}</span>
                                    @if ($localeCode == Language::getCurrentLocale())
                                        <span class="menu-check">
                                            <svg width="16" height="16">
                                                <use href="#svg-icon-check"></use>
                                            </svg>
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Currency Sub-Menu --}}
            @if (is_plugin_active('ecommerce') && $currencies->count() > 1)
                <div class="menu-level menu-level-currency" id="menu-currency">
                    <div class="menu-level-header">
                        <button class="menu-back-btn" data-target="menu-main">
                            <svg width="16" height="16">
                                <use href="#svg-icon-arrow-left"></use>
                            </svg>
                            <span>{{ __('Back') }}</span>
                        </button>
                        <h4>{{ __('Choose Currency') }}</h4>
                    </div>
                    <ul class="menu--mobile-advanced">
                        @foreach ($currencies as $currency)
                            <li class="menu-item {{ $currency->id == get_application_currency_id() ? 'menu-item-active' : '' }}">
                                @if (Route::has('public.change-currency'))
                                    <a href="{{ route('public.change-currency', $currency->title) }}" class="menu-item-link">
                                @else
                                    <a href="#" class="menu-item-link" onclick="alert('Currency switching not available')">
                                @endif
                                    <span>{{ $currency->title }}</span>
                                    @if ($currency->id == get_application_currency_id())
                                        <span class="menu-check">
                                            <svg width="16" height="16">
                                                <use href="#svg-icon-check"></use>
                                            </svg>
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Custom Menu Sub-Menus from Admin Panel --}}
            @foreach ($mainMenu as $menuItem)
                @if ($menuItem->child->isNotEmpty())
                    <div class="menu-level menu-level-custom" id="menu-node-{{ $menuItem->id }}">
                        <div class="menu-level-header">
                            <button class="menu-back-btn" data-target="menu-main">
                                <svg width="16" height="16">
                                    <use href="#svg-icon-arrow-left"></use>
                                </svg>
                                <span>{{ __('Back') }}</span>
                            </button>
                            <h4>{{ $menuItem->title }}</h4>
                        </div>
                        <ul class="menu--mobile-advanced">
                            @foreach ($menuItem->child as $child)
                                <li class="menu-item {{ $child->child->isNotEmpty() ? 'menu-item-has-children' : '' }}">
                                    @if ($child->child->isNotEmpty())
                                        <a href="javascript:void(0)" class="menu-item-link" data-target="menu-node-{{ $child->id }}">
                                            {!! $child->icon_html !!}
                                            <span>{{ $child->title }}</span>
                                            <span class="menu-arrow">
                                                <svg width="16" height="16">
                                                    <use href="#svg-icon-chevron-right"></use>
                                                </svg>
                                            </span>
                                        </a>
                                    @else
                                        @php
                                            $childMenuUrl = $child->url;
                                            if (is_plugin_active('language') && $childMenuUrl && !str_starts_with($childMenuUrl, 'http')) {
                                                $childMenuUrl = Language::getLocalizedURL(null, $childMenuUrl);
                                            }
                                        @endphp
                                        <a href="{{ $childMenuUrl }}" class="menu-item-link">
                                            {!! $child->icon_html !!}
                                            <span>{{ $child->title }}</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Third Level Menus --}}
                    @foreach ($menuItem->child as $child)
                        @if ($child->child->isNotEmpty())
                            <div class="menu-level menu-level-custom" id="menu-node-{{ $child->id }}">
                                <div class="menu-level-header">
                                    <button class="menu-back-btn" data-target="menu-node-{{ $menuItem->id }}">
                                        <svg width="16" height="16">
                                            <use href="#svg-icon-arrow-left"></use>
                                        </svg>
                                        <span>{{ __('Back') }}</span>
                                    </button>
                                    <h4>{{ $child->title }}</h4>
                                </div>
                                <ul class="menu--mobile-advanced">
                                    @foreach ($child->child as $grandchild)
                                        <li class="menu-item">
                                            @php
                                                $grandchildMenuUrl = $grandchild->url;
                                                if (is_plugin_active('language') && $grandchildMenuUrl && !str_starts_with($grandchildMenuUrl, 'http')) {
                                                    $grandchildMenuUrl = Language::getLocalizedURL(null, $grandchildMenuUrl);
                                                }
                                            @endphp
                                            <a href="{{ $grandchildMenuUrl }}" class="menu-item-link">
                                                {!! $grandchild->icon_html !!}
                                                <span>{{ $grandchild->title }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
</div>

{{-- CSS Styles for Advanced Mobile Menu --}}
<style>
/* Panel Overlay */
.panel-overlay-layer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 998;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    pointer-events: none;
}

.panel-overlay-layer.active {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

/* Advanced Mobile Menu Panel */
.advanced-mobile-menu {
    position: fixed;
    top: 0;
    left: -100%;
    width: 300px;
    max-width: 85vw;
    height: 100vh;
    background: #fff;
    z-index: 999;
    transition: left 0.3s ease;
    box-shadow: 2px 0 10px rgba(234, 234, 234, 0.1);
}

.advanced-mobile-menu.active {
    left: 0;
}

/* Panel Header */
.advanced-mobile-menu .panel__header {
    padding: 15px 20px;
    background: #4b9fda;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid #ffffffff;
}

.advanced-mobile-menu .panel__header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #fff;
}

.advanced-mobile-menu .close-toggle--sidebar {
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 5px;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.advanced-mobile-menu .close-toggle--sidebar:hover {
    background: rgba(255, 255, 255, 0.1);
}

/* Panel Content */
.advanced-mobile-menu .panel__content {
    padding: 0;
    position: relative;
    overflow: hidden;
    height: calc(100vh - 70px);
}

.mobile-menu-wrapper {
    position: relative;
    height: 100%;
}

.menu-level {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    background: linear-gradient(190deg, #4ba0d9 0%, #2974a7 100%);
    overflow-y: auto;
    display: none;
}

.menu-level.active {
    transform: translateX(0);
    display: block;
}

.menu-level-header {
    padding: 15px 20px;
    border-bottom: 1px solid #ffffffff;
    background: linear-gradient(190deg, #4ba0d9 0%, #2974a7 100%);
    display: flex;
    align-items: center;
    gap: 15px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.menu-back-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-weight: 500;
    cursor: pointer;
    font-size: 14px;
    fill: #fff;
}

.menu-back-btn:hover {
    color: #f5f5f5;
}

.menu-level-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
}

.menu--mobile-advanced {
    list-style: none;
    margin: 0;
    padding: 0;
}

.menu--mobile-advanced .menu-item {
    border-bottom: 1px solid #f0f0f0;
}

.menu--mobile-advanced .menu-item:last-child {
    border-bottom: none;
}

.menu-item-link {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    text-decoration: none;
    color: #fff;
    transition: all 0.2s ease;
    gap: 12px;
    font-size: 15px;
}

.menu-item-link:hover {
    background:linear-gradient(190deg, #4ba0d9 0%, #2974a7 100%);
    color: #f5f5f5;
    text-decoration: none;
}

.menu-item-has-children .menu-item-link {
    position: relative;
}

.menu-arrow {
    margin-left: auto;
    color: #fff;
    transition: transform 0.2s ease;
    pointer-events: none; /* FUNCTIONALITY FIX: Let clicks pass through to parent link */
}

.menu-item-link:hover .menu-arrow {
    color: #f5f5f5;
    transform: translateX(2px);
}

/* FUNCTIONALITY FIX: Make SVG elements not block clicks */
.menu-arrow,
.menu-arrow svg,
.menu-arrow svg use,
.menu-arrow svg *,
.menu-icon svg,
.menu-icon svg use,
.menu-icon svg * {
    pointer-events: none !important;
}

/* Ensure menu links are always clickable */
.menu-item-link {
    pointer-events: auto !important;
    cursor: pointer !important;
    position: relative;
    -webkit-tap-highlight-color: rgba(0,0,0,0.1);
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}

/* Make entire link area tappable on mobile */
.menu-item-has-children .menu-item-link {
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
    min-height: 48px !important;
}

.menu-icon {
    color: #fff;
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

/* Icon styles for dynamic icons from menu admin */
.menu-item-link i,
.menu-item-link .fa,
.menu-item-link .fas,
.menu-item-link .far,
.menu-item-link .fal,
.menu-item-link .fab,
.menu-item-link .svg-icon {
    margin-right: 12px;
    color: #ffffffff;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.menu-item-link:hover i,
.menu-item-link:hover .fa,
.menu-item-link:hover .fas,
.menu-item-link:hover .far,
.menu-item-link:hover .fal,
.menu-item-link:hover .fab,
.menu-item-link:hover .svg-icon {
    color: #f5f5f5;
}
.mobile-menu-logo img {
    filter: brightness(0) invert(1);
    drop-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}


.menu-badge {
    margin-left: auto;
    background: #123779;
    color: #fff;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    min-width: 18px;
    text-align: center;
    font-weight: 600;
}

.menu-check {
    margin-left: auto;
    color: #28a745;
}

.menu-item-active .menu-item-link {
    background: #e8f4fd;
    color: #123779;
}

.menu-item-view-all .menu-item-link {
    background: #f8f9fa;
    font-weight: 600;
    color: #123779;
    border-bottom: 2px solid #e9ecef;
}

.menu-item-logout .menu-item-link {
    color: #dc3545;
}

.menu-item-logout .menu-item-link:hover {
    background: #fdf2f2;
    color: #dc3545;
}

/* Category Images */
.category-image {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Menu Item Images */
.menu-item-image {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
}

.menu-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Language Flags */
.language-flag {
    font-size: 18px;
    display: flex;
    align-items: center;
    margin-right: 4px;
}

/* Body scroll lock when menu is open */
body.panel-open {
    overflow: hidden;
}

/* Toggle button styling */
.toggle--sidebar {
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: background 0.2s ease;
    display: inline-block;
    position: relative;
    z-index: 10;
}

.toggle--sidebar:hover {
    background: #f0f0f0;
}

/* Ensure menu toggle is clickable */
.mobile-menu-toggle {
    position: relative;
    z-index: 10;
    pointer-events: auto;
}

/* Responsive Adjustments */
@media (max-width: 480px) {
    .advanced-mobile-menu {
        width: 280px;
        max-width: 90vw;
    }
    
    .menu-item-link {
        padding: 12px 15px;
        font-size: 14px;
    }
    
    .menu-level-header {
        padding: 12px 15px;
    }
    
    .category-image {
        width: 28px;
        height: 28px;
    }
}

/* Animation Classes for smooth transitions */
.menu-level.slide-in {
    transform: translateX(0);
}

.menu-level.slide-out {
    transform: translateX(-100%);
}

/* Loading state */
.menu-item-loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Focus states for accessibility */
.menu-item-link:focus,
.menu-back-btn:focus,
.close-toggle--sidebar:focus {
    outline: 2px solid #123779;
    outline-offset: 2px;
}
</style>

{{-- JavaScript for Advanced Mobile Menu --}}
<script>
(function() {
    'use strict';
    
    // IMMEDIATE TEST - This should show up right away
    console.log('🚀 Mobile menu script loading...');
    console.log('🚀 Script loaded at:', new Date().toLocaleTimeString());
    
    function initMobileMenu() {
        console.log('📱 === INITIALIZING MOBILE MENU ===');
        
        const menuWrapper = document.querySelector('.mobile-menu-wrapper');
        const mobileMenuPanel = document.querySelector('#navigation-mobile');
        const closeButton = document.querySelector('#navigation-mobile .close-toggle--sidebar');
        
        console.log('📱 Menu wrapper found:', !!menuWrapper);
        console.log('📱 Mobile panel found:', !!mobileMenuPanel);
        console.log('📱 Close button found:', !!closeButton);
        
        if (!menuWrapper || !mobileMenuPanel) {
            console.error('❌ Mobile menu elements not found');
            console.error('❌ menuWrapper:', menuWrapper);
            console.error('❌ mobileMenuPanel:', mobileMenuPanel);
            return;
        }
        
        // Create overlay if it doesn't exist
        let overlay = document.querySelector('.panel-overlay-layer');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'panel-overlay-layer';
            document.body.appendChild(overlay);
            console.log('✅ Created overlay element');
        }
        
        // Function to show menu level
        function showMenuLevel(targetId) {
            console.log('🎯 === SHOW MENU LEVEL ===');
            console.log('🎯 Target ID:', targetId);
            
            const allMenuLevels = menuWrapper.querySelectorAll('.menu-level');
            console.log('📋 Total menu levels:', allMenuLevels.length);
            
            // List all menu level IDs for debugging
            const allIds = Array.from(allMenuLevels).map(function(l) { return l.id; });
            console.log('📋 Available IDs:', allIds);
            
            // Remove active from all
            allMenuLevels.forEach(function(level) {
                level.classList.remove('active');
                console.log('➖ Removed active from:', level.id);
            });
            
            // Find and activate target
            const targetLevel = document.getElementById(targetId);
            if (targetLevel) {
                targetLevel.classList.add('active');
                console.log('✅ Activated:', targetId);
                console.log('✅ Element classes:', targetLevel.className);
                console.log('✅ Has active?', targetLevel.classList.contains('active'));
                
                // Force reflow to ensure CSS transition works
                void targetLevel.offsetWidth;
                
                // Scroll to top of new level
                targetLevel.scrollTop = 0;
            } else {
                console.error('❌ Target not found:', targetId);
                console.error('❌ Looking for: #' + targetId);
                console.error('❌ Available IDs:', allIds);
            }
        }
        
        // Initialize with main menu
        showMenuLevel('menu-main');
        
        // Watch for when the panel becomes active
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const isActive = mobileMenuPanel.classList.contains('active');
                    
                    if (isActive) {
                        showMenuLevel('menu-main');
                        overlay.classList.add('active');
                        document.body.classList.add('panel-open');
                        console.log('✅ Panel opened');
                    } else {
                        overlay.classList.remove('active');
                        document.body.classList.remove('panel-open');
                        console.log('✅ Panel closed');
                    }
                }
            });
        });
        
        observer.observe(mobileMenuPanel, { attributes: true });
        
        // Close button handler
        if (closeButton) {
            closeButton.addEventListener('click', function(e) {
                setTimeout(function() {
                    showMenuLevel('menu-main');
                }, 300);
            });
        }
        
        // Overlay click handler
        overlay.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            mobileMenuPanel.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('panel-open');
            if (window._scrollBar && window._scrollBar.reset) {
                window._scrollBar.reset();
            }
            setTimeout(function() {
                showMenuLevel('menu-main');
            }, 300);
        });
        
        // CRITICAL: Click handler for submenu navigation
        // Use capture phase with precise targeting
        menuWrapper.addEventListener('click', function(e) {
            console.log('🖱️ === CLICK DETECTED ===');
            console.log('🖱️ Target:', e.target.tagName, e.target.className);
            
            // Find the closest element with data-target (link OR button)
            const targetElement = e.target.closest('[data-target]');
            
            if (!targetElement) {
                console.log('✔️ No data-target element - allowing normal navigation');
                return;
            }
            
            const targetId = targetElement.getAttribute('data-target');
            
            if (targetId) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('✅ FOUND data-target:', targetId);
                console.log('🚀 Navigating to:', targetId);
                showMenuLevel(targetId);
            }
        }, true); // CAPTURE PHASE = true
        
        // Escape key handler
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenuPanel.classList.contains('active')) {
                mobileMenuPanel.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('panel-open');
                if (window._scrollBar && window._scrollBar.reset) {
                    window._scrollBar.reset();
                }
                setTimeout(function() {
                    showMenuLevel('menu-main');
                }, 300);
            }
        });
        
        console.log('✅ Mobile menu initialized successfully');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    } else {
        initMobileMenu();
    }
})();
</script>

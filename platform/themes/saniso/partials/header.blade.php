<!DOCTYPE html>
<html {!! Theme::htmlAttributes() !!}>
<head>
    <meta charset="utf-8">
    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1"
    />
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <style>
        :root {
            --primary-color: {{ theme_option('primary_color', '#fab528') }};
            --primary-color-rgb: {{ implode(', ', BaseHelper::hexToRgb(theme_option('primary_color', '#fab528'))) }};
            --heading-color: {{ theme_option('heading_color', '#000') }};
            --text-color: {{ theme_option('text_color', '#000') }};
            --primary-button-color: {{ theme_option('primary_button_color', '#000') }};
            --primary-button-background-color: {{ theme_option('primary_button_background_color') ?: theme_option('primary_color', '#fab528') }};
            --top-header-background-color: {{ theme_option('top_header_background_color', '#f7f7f7') }};
            --top-header-text-color: {{ theme_option('top_header_text_color') ?: theme_option('header_text_color', '#000') }};
            --middle-header-background-color: {{ theme_option('middle_header_background_color', '#fff') }};
            --middle-header-text-color: {{ theme_option('middle_header_text_color') ?: theme_option('header_text_color', '#000') }};
            --bottom-header-background-color: {{ theme_option('bottom_header_background_color', '#fff') }};
            --bottom-header-text-color: {{ theme_option('bottom_header_text_color') ?: theme_option('header_text_color', '#000') }};
            --header-text-color: {{ theme_option('header_text_color', '#000') }};
            --header-text-secondary-color: {{ BaseHelper::hexToRgba(theme_option('header_text_color', '#000'), 0.5) }};
            --header-deliver-color: {{ BaseHelper::hexToRgba(theme_option('header_deliver_color', '#000'), 0.15) }};
            --header-mobile-background-color: {{ theme_option('header_mobile_background_color', '#fff') }};
            --header-mobile-icon-color: {{ theme_option('header_mobile_icon_color', '#222') }};
            --footer-text-color: {{ theme_option('footer_text_color', '#555') }};
            --footer-heading-color: {{ theme_option('footer_heading_color', '#555') }};
            --footer-hover-color: {{ theme_option('footer_hover_color', '#fab528') }};
            --footer-border-color: {{ theme_option('footer_border_color', '#dee2e6') }};
        }

.svg-icon svg
 {
  
    vertical-align: middle !important;
    
}
/* shop by catgegory */
.menu--product-categories {
    position: relative;
    display: inline-block;
}

.menu--product-categories .menu__toggle {
    cursor: pointer;
    user-select: none;
}

.menu--product-categories .menu__content {
    display: none;
    position: absolute;
    left: -271px !important; /* adjust as needed for alignment */
    top: 120%;
    width: 320px;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-top: none;
    z-index: 100;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    padding: 0;
    margin-top: 0;
}

.menu--product-categories.open .menu__content {
    display: block;
}

.menu--dropdown {
    list-style: none;
    margin: 0;
    padding: 0;
}

.cat-has-children {
    position: relative;
}

.cat-megamenu-panel {
    display: none;
    position: absolute;
    left: 100%;
    top: 0;
    min-width: 1100px;
    max-width: 1400px;
    width: 1100px;
    background: #fff;
    padding: 10px;
    z-index: 1000;
    border-radius: 0 6px 6px 0;
    white-space: normal;
    overflow: visible;
    max-height: 420px;      
    overflow-y: auto;
    overflow-x: hidden;
}

.cat-has-children:hover > .cat-megamenu-panel {
    display: block;
}

/* Masonry columns for mega menu */
.cat-megamenu-masonry {
    columns: 4;
    column-gap: 32px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.cat-megamenu-masonry > li {
    break-inside: avoid;
    margin-bottom: 18px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f2f2f2;
    background: none;
}

.cat-megamenu-masonry > li > a {
    font-weight: 600;
    font-size: 15px;
    color: #1a3978;
    margin-bottom: 4px;
    display: block;
}

.cat-megamenu__list {
    list-style: none;
    padding: 0;
    margin: 0 0 0 10px;
}

.cat-megamenu__list li {
    margin-bottom: 4px;
}

.cat-megamenu__list a {
    color: #222;
    font-size: 13px;
    text-decoration: none;
    display: block;
    transition: color 0.2s;
    font-weight: 400;
}

.cat-megamenu__list a:hover {
    color: #1a3978;
}

/* Optional: Arrow for dropdown (like cart) */
@media (min-width: 1200px) {
    .menu--product-categories .menu__content:before {
        background-color: #fff;
        border-left: 1px solid #e1e1e1;
        border-top: 1px solid #e1e1e1;
        content: "";
        display: inline-block;
        height: 16px;
        position: absolute;
        right: 40px;
        top: -8px;
        transform: rotate(45deg);
        transform-origin: 50% 50%;
        width: 16px;
        z-index: 1100;
    }
}

@media (max-width: 1200px) {
    .cat-megamenu-panel {
        min-width: 600px;
        max-width: 100vw;
        width: 100vw;
        padding: 20px 10px;
    }
    .cat-megamenu-masonry {
        columns: 2;
        column-gap: 16px;
    }
}
@media (max-width: 900px) {
    .cat-megamenu-panel {
        position: static;
        min-width: 0;
        max-width: 100vw;
        width: 100vw;
        padding: 10px 24px;
        box-shadow: none;
    }
    .cat-megamenu-masonry {
        columns: 1;
        column-gap: 0;
    }
}
.menu--dropdown {
    position: relative;
}
.menu--dropdown > li {
    position: static;
}


.header__extra .cart-text {
    display: none !important;
}

@media (max-width: 1199px) {
    .header-mobile {
       padding: 20px  15px 0px 15px !important; 
   
    }
}
.form--quick-search button,
.form--quick-search-mobile button {
    background: #123779 !important;
    color: #fff !important;
}
.bb-form-quick-search .btn svg {
    fill: #fff;
    width: 20px !important;
    height: 20px !important;
}

/* Ensure product thumbnails display as consistent squares */
.product-thumbnail__img,
.bb-quick-view-gallery-images img,
.bb-product-gallery-images img,
.img-fluid-eq__wrap img,
.product-thumbnail img,
.product-gallery__images img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* crop to fill */
    display: block;
}

/* Containers used to create fixed square areas. Uses aspect-ratio where supported */
.img-fluid-eq__wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1; /* modern browsers - keep for general use */
    overflow: hidden;
}

/* Product thumbnails - Allow natural aspect ratio */
.product-thumbnail,
.product-thumbnail .img-fluid-eq__wrap {
    position: relative;
    width: 100%;
    /* Remove aspect-ratio for product images only */
    overflow: hidden;
    min-height: 200px; /* Minimum height for consistency */
    max-height: 400px; /* Maximum height to prevent overly tall images */
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Ensure product images maintain their natural proportions */
.product-thumbnail img {
    width: 100%;
    height: auto;
    max-height: 100%;
    object-fit: contain; /* Maintain aspect ratio without cropping */
}

/* Fallback for browsers without aspect-ratio */
.no-aspect-ratio .img-fluid-eq__wrap {
    height: 0;
    padding-bottom: 100%; /* Keep square ratio for general use */
}

.no-aspect-ratio .product-thumbnail {
    /* No forced ratio for product thumbnails */
}

/* Account Dropdown Styles */
.account-dropdown-li {
    position: relative;
}
.account-dropdown-toggle {
    cursor: pointer;
    font-weight: 500;
    color: var(--text-color);
    padding: 6px 10px;
    border-radius: 4px;
    transition: background 0.2s;
    text-decoration: none;
    user-select: none;
    display: flex;
    align-items: center;
    gap: 6px;
}
.account-dropdown-toggle:hover,
.account-dropdown-toggle:focus {
    background: #f5f5f5;
}
.account-dropdown-menu {
    display: none;
    position: absolute;
    top: 120%;
    right: 0;
    min-width: 240px;
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.08);
    z-index: 10;
    padding: 10px 0;
    list-style: none;
    text-align: left;
}
.account-dropdown-li.open .account-dropdown-menu {
    display: block;
}
.account-dropdown-menu li {
    padding: 8px 20px;
    font-size: 15px;
    color: #222;
    white-space: nowrap;
    text-align: left;
}
.account-dropdown-menu li.divider {
    border-top: 1px solid #ececec;
    margin: 6px 0;
    padding: 0;
}
.account-dropdown-menu li a {
    color: #222;
    text-decoration: none;
    transition: color 0.2s;
    display: block;
}
.account-dropdown-menu li a:hover {
    color: var(--primary-color, #fab528);
}
.account-dropdown-li .account-arrow {
    display: flex;
    align-items: center;
    margin-left: 4px;
    transition: transform 0.2s;
}
.account-dropdown-li.open .account-arrow {
    transform: rotate(180deg);
}
.account-welcome-text {
    font-weight: bold;
    color: var(--primary-color, #fab528);
}
.account-signin-btn {
    background: var(--primary-color, #fab528) !important;
    color: #fff !important;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    padding: 10px 0;
    margin: 12px 0 8px 0;
    text-align: center !important;
    display: block;
    width: 100%;
    transition: background 0.2s;
}
.account-signin-btn:hover {
    background: #e0a800 !important;
    color: #fff !important;
}
.account-register-row {
    font-size: 14px;
    text-align: left;
    margin-bottom: 0;
    padding: 8px 20px 8px 20px;
    white-space: normal;
    display: flex;
    align-items: center;
    gap: 6px;
}
.account-register-link {
    color: #4ba0d9 !important;
    font-weight: 600;
    margin-left: 4px;
    text-decoration: none;
    display: inline;
}
.account-register-link:hover {
    text-decoration: underline;
}
.header .header-middle .header__right .header__extra.header-compare {
    line-height: 1;
    margin: 0 15px 0 0px !important;
    position: relative;
}

.header .header-middle .header-wrapper {
    padding: 10px 0 !important;
}


.header .header-middle .header-items.header__right ul.language-dropdown,
.header .header-middle .header-items.header__right ul.language-dropdown li {
    list-style: none !important;
}

.header .header-middle .header-items.header__right ul.language-dropdown li::marker {
    display: none !important;
    content: none !important;
}
/* Middle header language & currency dropdown styles */
.header .header-middle .header-items.header__right > ul > li {
    position: relative;
}

.header .header-middle .header-items.header__right > ul > li > ul.language-dropdown {
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 0;
    left: 0;
    min-width: 120px;
    opacity: 0;
    padding: 0;
    position: absolute;
    top: 100%;
    transform: translateY(20px);
    transition: all 0.25s cubic-bezier(0.645, 0.045, 0.355, 1);
    visibility: hidden;
    z-index: 2;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    pointer-events: none;
}

.header .header-middle .header-items.header__right > ul > li:hover > ul.language-dropdown,
.header .header-middle .header-items.header__right > ul > li:focus-within > ul.language-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto;
}

.header .header-middle .header-items.header__right > ul > li .language-dropdown-active {
    cursor: pointer;
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--text-color);
    padding: 6px 10px;
    border-radius: 4px;
    transition: background 0.2s;
    text-decoration: none;
}

.header .header-middle .header-items.header__right > ul > li .language-dropdown-active:hover,
.header .header-middle .header-items.header__right > ul > li .language-dropdown-active:focus {
    background: #f5f5f5;
}

.header .header-middle .header-items.header__right > ul > li a {
    color: var(--text-color);
    font-weight: 500;
    text-decoration: none;
    outline: none;
    transition: 0.5s;
}

.header .header-middle .header-items.header__right > ul > li ul.language-dropdown li a {
    display: block;
    padding: 8px 16px;
    color: #222;
    text-decoration: none;
    font-size: 15px;
    transition: background 0.2s;
}

.header .header-middle .header-items.header__right > ul > li ul.language-dropdown li a:hover {
    background: #f7f7f7;
    color: var(--primary-color, #fab528);
}

/* Remove marker/bullet from dropdown items */
.header .header-middle .header-items.header__right > ul > li ul.language-dropdown li a span::before {
    content: none !important;
    display: none !important;
}

/* General link focus/hover reset */
a:focus,
button:focus,
input:focus,
select:focus,
textarea:focus {
    outline: none;
    text-decoration: none;
}

a {
    outline: none;
    text-decoration: none;
    transition: 0.5s;
    position: relative;
    color: inherit;
}

.header .header-top .header-info > ul > li:before {
    background: #dedfe2;
    content: "";
    height: 10px;
    position: absolute;
    right: -10px;
    top: 50%;
    transform: translateY(-50%);
    width: 1px;
}
               
      
   @media (max-width: 991.98px) {
    .header-top,
    .header-middle,
    .header-bottom {
        display: none !important;
    }
    .header-mobile {
        display: flex !important;
    }
}
@media (min-width: 992px) {
    .header-mobile {
        display: none !important;
    }
}
 .header-mobile .language-switcher,
.header-mobile .language-switcher ul,
.header-mobile .language-switcher li {
    list-style: none !important;
    margin: 0;
    padding: 0;
}
@media (max-width: 991.98px) {
    .header-mobile .account-dropdown-toggle span:not(:first-child) {
        display: none !important;
    }
}
               
               
               /*mobile menu */
               
@media (max-width: 991.98px) {
    .header-top, .header-middle, .header-bottom { display: none !important; }
    .header-mobile { display: flex !important; align-items: center; justify-content: space-between; padding: 8px 12px; }
    .header-items-mobile { display: flex !important; align-items: center; }
    .header-items-mobile--left { flex: 0 0 auto; }
    .header-items-mobile--center { flex: 1 1 0; justify-content: center; display: flex !important; }
    .header-items-mobile--right { flex: 0 0 auto; gap: 10px; justify-content: flex-end; display: flex !important; align-items: center; }
    .logo { display: flex; align-items: center; justify-content: center; width: 100%; }
    .header__extra { display: flex; align-items: center; }
    .header-item-counter { margin-left: 4px; font-size: 13px; background: var(--primary-color, #fab528); color: #fff; border-radius: 50%; padding: 2px 6px; min-width: 20px; text-align: center; display: inline-block; }
    .account-dropdown-toggle span:not(:first-child) { display: none !important; }
}
@media (max-width: 991.98px) {
    .header-mobile .svg-icon svg,
    .header-mobile .btn-shopping-cart svg,
    .header-mobile .account-dropdown-toggle svg {
        width: 28px !important;
        height: 28px !important;
    }
}
@media (max-width: 991.98px) {
    .header-mobile {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        padding: 8px 8px;
        background: var(--header-mobile-background-color, #fff);
    }
    .header-items-mobile--left {
        flex: 0 0 30%;
        max-width: 30%;
        min-width: 0;
        display: flex !important;
        align-items: center;
        justify-content: flex-start;
    }
    .header-items-mobile--center {
        flex: 0 0 33%;
        max-width: 33%;
        min-width: 0;
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    .header-items-mobile--right {
        flex: 1 1 0;
        min-width: 0;
        display: flex !important;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }
    .logo {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
  .account-dropdown-menu{
    right: -69px;
}
                        
.panel__header .close-toggle--sidebar{
color:#fff !important;
}
        .panel__header h3{
        color:#ffffff;
        }

/* Global Button Hover Styles - White Text and Icons */
.btn:hover,
.btn-primary:hover,
.btn-secondary:hover,
button:hover,
input[type="button"]:hover,
input[type="submit"]:hover,
input[type="reset"]:hover,
.btn:focus,
.btn-primary:focus,
.btn-secondary:focus,
button:focus,
input[type="button"]:focus,
input[type="submit"]:focus,
input[type="reset"]:focus {
    color: #fff !important;
}

/* Specific button classes hover */
.quick-view:hover,
.checkout:hover,
.search-submit:hover,
.close-toggle--sidebar:hover,
.menu__toggle:hover,
.dropdown-toggle:hover,
.page-link:hover,
.slick-arrow:hover,
.cart--mini .mini-cart__buttons .btn:hover,
.nav-link:hover,
.btn-close:hover,
.filter-widget .btn:hover,
.toolbar-view__icon a:hover,
.pagination-numeric-short > a:hover,
.social-login .sl-button:hover,
.subscribe-form .btn:hover,
.newsletter-form .btn:hover,
.contact-form .btn:hover,
.form-widget .btn:hover,
.quantity .increase:hover,
.quantity .decrease:hover,
.breadcrumb .btn:hover,
.widget .btn:hover,
.modal .btn:hover,
.panel .btn:hover,
.header .btn:hover,
.footer .btn:hover {
    color: #fff !important;
}

/* Button Icons Hover - White Icons */
.btn:hover i,
.btn:focus i,
.btn:hover svg,
.btn:focus svg,
.btn:hover .icon,
.btn:focus .icon,
button:hover i,
button:focus i,
button:hover svg,
button:focus svg,
button:hover .icon,
button:focus .icon,
.add-to-cart-button:hover i,
.quick-view:hover i,
.compare:hover i,
.search-submit:hover i,
.menu__toggle:hover i,
.dropdown-toggle:hover i,
.page-link:hover i,
.slick-arrow:hover i,
.btn-close:hover i,
.social-login .sl-button:hover i {
    color: #fff !important;
    fill: #fff !important;
}

/* Header specific button styles */
.btn-shopping-cart, .btn-wishlist { 
    display: flex; 
    align-items: center; 
    padding: 6px 10px; 
}

.header-mobile .btn-wishlist svg,
.header-mobile .btn-shopping-cart svg {
    width: 18px;
    height: 18px;
}

    </style>

    @php
        Theme::asset()->remove('language-css');
        Theme::asset()
            ->container('footer')
            ->remove('language-public-js');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-owl-carousel-css');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-owl-carousel-js');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-css');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-js');
    @endphp

    {!! Theme::header() !!}
    
    {{-- Disable all tooltips across the website --}}
    <link rel="stylesheet" href="{{ Theme::asset()->url('css/disable-tooltips-optimized.css') }}">
    <script src="{{ Theme::asset()->url('js/disable-tooltips-optimized.js') }}" defer></script>
</head>

<body {!! Theme::bodyAttributes() !!}>
    @if (theme_option('preloader_enabled', 'yes') == 'yes')
        {!! Theme::partial('preloader') !!}
    @endif

    {!! Theme::partial('svg-icons') !!}
    {!! apply_filters(THEME_FRONT_BODY, null) !!}

    <header
        class="header header-js-handler"
        data-sticky="{{ theme_option('sticky_header_enabled', 'yes') == 'yes' ? 'true' : 'false' }}"
    >
             {{--    
        <div @class([
            'header-top d-none d-lg-block',
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'top',
        ])>
            <div class="container-xxxl">
                <div class="header-wrapper">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="header-info">
                                {!! Menu::renderMenuLocation('header-navigation', ['view' => 'menu-default']) !!}
                            </div>
                        </div>
                       
                        <div class="col-6">
                            <div class="header-info header-info-right">
                                <ul>
                                    @if (is_plugin_active('language'))
                                        {!! Theme::partial('language-switcher') !!}
                                    @endif
                                    @if (is_plugin_active('ecommerce'))
                                        @if (count($currencies) > 1)
                                            <li>
                                                <a
                                                    class="language-dropdown-active"
                                                    href="#"
                                                >
                                                    <span>{{ get_application_currency()->title }}</span>
                                                    <span class="svg-icon">
                                                        <svg>
                                                            <use
                                                                href="#svg-icon-chevron-down"
                                                                xlink:href="#svg-icon-chevron-down"
                                                            ></use>
                                                        </svg>
                                                    </span>
                                                </a>
                                                <ul class="language-dropdown">
                                                    @foreach ($currencies as $currency)
                                                        @if ($currency->id !== get_application_currency_id())
                                                            <li>
                                                                <a
                                                                    href="{{ route('public.change-currency', $currency->title) }}">
                                                                    <span>{{ $currency->title }}</span>
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endif
                                        @if (auth('customer')->check())
                                            <li>
                                                <a
                                                    href="{{ route('customer.overview') }}">{{ auth('customer')->user()->name }}</a>
                                                <span class="d-inline-block ms-1">(<a
                                                        class="color-primary"
                                                        href="{{ route('customer.logout') }}"
                                                    >{{ __('Logout') }}</a>)</span>
                                            </li>
                                        @else
                                            <li><a href="{{ route('customer.login') }}">{{ __('Login') }}</a></li>
                                            <li><a href="{{ route('customer.register') }}">{{ __('Register') }}</a>
                                            </li>
                                        @endif
                                    @endif
                                </ul>
                            </div>
                        </div>
                        --}}
                    </div>
                </div>
            </div>
        </div>
        <div @class([
            'header-middle',
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'middle',
        ])>
            <div class="container-xxxl">
                <div class="header-wrapper">
                    <div class="header-items header__left" style="display: flex; align-items: center; gap: 20px;">
    <div class="logo">
        <a href="{{ BaseHelper::getHomepageUrl() }}">
            {!! Theme::getLogoImage(['style' => 'max-height: 45px']) !!}
        </a>
    </div>
   
</div>
<div class="header-items header__center" style="display: flex; align-items: center; gap: 20px;">
    {{-- Shop by Category --}}
    @if (is_plugin_active('ecommerce') && theme_option('enabled_product_categories_on_header', 'yes') == 'yes')
        <div class="menu--product-categories" style="margin-right: 20px;">
            <div class="menu__toggle js-category-toggle">
                <span class="svg-icon">
                    <svg>
                        <use href="#svg-icon-list" xlink:href="#svg-icon-list"></use>
                    </svg>
                </span>
                <span class="menu__toggle-title">{{ __('Shop by Category') }}</span>
            </div>
            <div
                class="menu__content"
                data-bb-toggle="init-categories-dropdown"
                data-bb-target=".menu--dropdown"
                data-url="{{ route('public.ajax.categories-dropdown') }}"
                style="z-index: 100;"
            >
                <ul class="menu--dropdown">
                    @include(Theme::getThemeNamespace('partials.product-categories-dropdown'))
                </ul>
            </div>
        </div>
    @endif

    {{-- Quick Search --}}
    @if (is_plugin_active('ecommerce'))
        <x-plugins-ecommerce::fronts.ajax-search class="form--quick-search">
            <x-plugins-ecommerce::fronts.ajax-search.input type="text" class="form-control input-search-product" />
            <button class="btn" type="submit" aria-label="Submit" style="border-radius:0 8px 8px 0;">
                <span class="svg-icon">
                    <svg>
                        <use href="#svg-icon-search" xlink:href="#svg-icon-search"></use>
                    </svg>
                </span>
            </button>
        </x-plugins-ecommerce::fronts.ajax-search>
    @endif
</div>
                    <div class="header-items header__right">
                        {{-- Language, Currency & Account: now before compare/wishlist/cart --}}
                        <ul class="header-lang-currency d-flex align-items-center" style="margin:0 20px 0 0; padding:0; list-style:none; gap:12px;">
                            @if (is_plugin_active('language'))
                                <li>
                                    {!! Theme::partial('language-switcher') !!}
                                </li>
                            @endif
                            @if (is_plugin_active('ecommerce'))
                                @if (count($currencies) > 1)
                                    <li style="position:relative;">
                                        <a class="language-dropdown-active" href="#">
                                            <span>{{ get_application_currency()->title }}</span>
                                            <span class="svg-icon">
                                                <svg>
                                                    <use
                                                        href="#svg-icon-chevron-down"
                                                        xlink:href="#svg-icon-chevron-down"
                                                    ></use>
                                                </svg>
                                            </span>
                                        </a>
                                        <ul class="language-dropdown">
                                            @foreach ($currencies as $currency)
                                                @if ($currency->id !== get_application_currency_id())
                                                    <li>
                                                        <a href="{{ route('public.change-currency', $currency->title) }}">
                                                            <span>{{ $currency->title }}</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                                {{-- Account Dropdown --}}
                                <li class="account-dropdown-li" style="position:relative;">
                                    <a href="#" class="account-dropdown-toggle" id="accountDropdownToggle" style="display:flex;align-items:center;gap:6px;">
                                        <span class="svg-icon">
                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="8" r="4" stroke="#123779" stroke-width="2"/>
                                                <path d="M4 20c0-2.21 3.582-4 8-4s8 1.79 8 4" stroke="#123779" stroke-width="2"/>
                                            </svg>
                                        </span>
                                        <span>Account</span>
                                        <span class="account-arrow" style="transition:transform 0.2s;">
                                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                                <path d="M6 8L10 12L14 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                    </a>
                                    <ul class="account-dropdown-menu" id="accountDropdownMenu">
                                        @if(auth('customer')->check())
                                            <li><strong>{{ auth('customer')->user()->name }}</strong></li>
                                            <li><span class="account-welcome-text">Welcome back.</span></li>
                                            <li><a href="{{ route('customer.overview') }}">My Account</a></li>
                                            <li><a href="{{ route('customer.orders') }}">Orders and invoices</a></li>
                                            <li><a href="{{ route('customer.order_returns') }}">Returns orders</a></li>
                                            <li><a href="{{ url('orders/tracking') }}">Order Tracking</a></li>
                                            <li><a href="{{ url('customer/affiliate') }}">Affiliate</a></li>
                                            <li><a href="{{ url('customer/become-vendor') }}">Become Vendor</a></li>
                                            <li><a href="{{ route('public.wishlist') }}">Wish list</a></li>
                                            <li class="divider"></li>
                                            <li>
                                                <a
                                                    class="color-primary"
                                                    href="{{ route('customer.logout') }}"
                                                >{{ __('Logout') }}</a>
                                                
                                            </li>
                                        @else
                                            <li><span class="account-welcome-text">Welcome back.</span></li>
                                            <li><a href="{{ route('customer.login') }}">My Account</a></li>
                                            <li><a href="{{ route('customer.login') }}">Orders and invoices</a></li>
                                            <li><a href="{{ route('customer.login') }}">Returns Orders</a></li>
                                            <li><a href="{{ url('orders/tracking') }}">Order Tracking</a></li>
                                            <li><a href="{{ route('customer.login') }}">Affiliate</a></li>
                                            <li><a href="{{ route('customer.login') }}">Become Vendor</a></li>
                                            <li><a href="{{ route('customer.login') }}">Wish list</a></li>
                                            <li>
                                                <a href="{{ route('customer.login') }}" class="account-signin-btn">
                                                    Sign in
                                                </a>
                                            </li>
                                            <li class="account-register-row">
                                                Don't have an account yet?
                                                <a href="{{ route('customer.register') }}" class="account-register-link">Start here</a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                        </ul>

                        @if (is_plugin_active('ecommerce'))
                            @if (EcommerceHelper::isCompareEnabled())
                                <div class="header__extra header-compare">
                                    <a
                                        class="btn-compare"
                                        href="{{ route('public.compare') }}"
                                    >
                                        <i class="icon-repeat"></i>
                                        <span
                                            class="header-item-counter">{{ Cart::instance('compare')->count() }}</span>
                                    </a>
                                </div>
                            @endif
                            @if (EcommerceHelper::isWishlistEnabled())
                                <div class="header__extra header-wishlist">
                                    <a
                                        class="btn-wishlist"
                                        href="{{ route('public.wishlist') }}"
                                    >
                                        <span class="svg-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-wishlist"
                                                    xlink:href="#svg-icon-wishlist"
                                                ></use>
                                            </svg>
                                        </span>
                                        <span class="header-item-counter">
                                            {{ auth('customer')->check()? auth('customer')->user()->wishlist()->count(): Cart::instance('wishlist')->count() }}
                                        </span>
                                    </a>
                                </div>
                            @endif
                            @if (EcommerceHelper::isCartEnabled())
                                <div
                                    class="header__extra cart--mini"
                                    role="button"
                                    tabindex="0"
                                >
                                    <div class="header__extra">
                                        <a
                                            class="btn-shopping-cart"
                                            href="{{ route('public.cart') }}"
                                        >
                                            <span class="svg-icon">
                                                <svg>
                                                    <use
                                                        href="#svg-icon-cart"
                                                        xlink:href="#svg-icon-cart"
                                                    ></use>
                                                </svg>
                                            </span>
                                            <span
                                                class="header-item-counter">{{ Cart::instance('cart')->count() }}</span>
                                        </a>
                                        <span class="cart-text">
                                            <span class="cart-title">{{ __('Your Cart') }}</span>
                                            <span class="cart-price-total">
                                                <span class="cart-amount">
                                                    <bdi>
                                                        <span>{{ format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax()) }}</span>
                                                    </bdi>
                                                </span>
                                            </span>
                                        </span>
                                    </div>
                                    <div
                                        class="cart__content"
                                        id="cart-mobile"
                                    >
                                        <div class="backdrop"></div>
                                        <div class="mini-cart-content">
                                            <div class="widget-shopping-cart-content">
                                                {!! Theme::partial('cart-mini.list') !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div @class([
            'header-bottom',
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'bottom',
        ])>
            <div class="header-wrapper">
                <nav class="navigation">
                    <div class="container-xxxl">
                        <div @class(['navigation__center', 'ps-0' => theme_option('enabled_product_categories_on_header', 'yes') != 'yes'])>
                            {!! Menu::renderMenuLocation('main-menu', [
                                'view' => 'menu',
                                'options' => ['class' => 'menu'],
                            ]) !!}
                        </div>
                        <div class="navigation__right">
                            @if (is_plugin_active('ecommerce') && EcommerceHelper::isEnabledCustomerRecentlyViewedProducts())
                                <div
                                    class="header-recently-viewed"
                                    data-url="{{ route('public.ajax.recently-viewed-products') }}"
                                    role="button"
                                >
                                    <h3 class="recently-title">
                                        <span class="svg-icon recent-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-refresh"
                                                    xlink:href="#svg-icon-refresh"
                                                ></use>
                                            </svg>
                                        </span>
                                        {{ __('Recently Viewed') }}
                                    </h3>
                                    <div class="recently-viewed-inner container-xxxl">
                                        <div class="recently-viewed-content">
                                            <div class="loading--wrapper">
                                                <div class="loading"></div>
                                            </div>
                                            <div class="recently-viewed-products"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div
            class="header-mobile header-js-handler"
            data-sticky="{{ theme_option('sticky_header_mobile_enabled', 'yes') == 'yes' ? 'true' : 'false' }}"
        >
            <!-- MOBILE HEADER TOP: Logo left, right side: language, user, wishlist, cart -->
            <div class="header-mobile-top" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0px 8px 0px; background: var(--header-mobile-background-color, #fff); border-bottom: 1px solid #f0f0f0;">
                <div class="header-items-mobile--left" style="flex:0 0 auto; display:flex; align-items:center;">
                    <div class="logo">
                        <a href="{{ BaseHelper::getHomepageUrl() }}">
                            {!! Theme::getLogoImage(['style' => 'max-height: 45px']) !!}
                        </a>
                    </div>
                </div>
                <div class="header-items-mobile--right" style="flex:1 1 0; display:flex; align-items:center; justify-content:flex-end; gap:0px;">
                    <!-- Language Switcher FIRST -->
                    @if (is_plugin_active('language'))
                        <div class="language-switcher">
                            {!! Theme::partial('language-switcher') !!}
                        </div>
                    @endif
                    <!-- User Icon -->
                    <div class="btn-shopping-cart account-mobile account-dropdown-li">
                        <a href="#" class="account-dropdown-toggle" id="accountDropdownToggleMobile" aria-label="Account">
                            
                                <span class="svg-icon">
  <svg width="48" height="48" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
    <path d="M256 281.6c-77.6 0-140.8-63.2-140.8-140.8S178.4 0 256 0s140.8 63.2 140.8 140.8S333.6 281.6 256 281.6zm0-256c-63.5 0-115.2 51.7-115.2 115.2S192.5 256 256 256s115.2-51.7 115.2-115.2S319.5 25.6 256 25.6z"/>
    <path d="M460.8 512H51.2C30 512 12.8 494.8 12.8 473.6c0-2 0.5-44.4 31.4-85.8 18.1-24.1 42.8-43.2 73.5-56.8 37.5-16.7 84-25.1 138.4-25.1s100.9 8.4 138.4 25.1c30.7 13.6 55.4 32.7 73.4 56.8 31 41.3 31.4 83.7 31.4 85.5 0 21.2-17.2 38.4-38.4 38.4zM256 332.8c-89.3 0-155.1 24.4-190.5 70.6-26.5 34.6-27.1 69.9-27.1 70.3 0 7 5.7 12.8 12.8 12.8h409.6c7.1 0 12.8-5.7 12.8-12.8 0-0.3-0.6-35.7-27.1-70.3C411.1 357.2 345.2 332.8 256 332.8z"/>
  </svg>
</span>

                            </span>
                        </a>
                        <ul class="account-dropdown-menu" id="accountDropdownMenuMobile">
                            @if(auth('customer')->check())
                                <li><strong>{{ auth('customer')->user()->name }}</strong></li>
                                <li><span class="account-welcome-text">Welcome back.</span></li>
                                <li><a href="{{ route('customer.overview') }}">My Account</a></li>
                                <li><a href="{{ route('customer.orders') }}">Orders and invoices</a></li>
                                <li><a href="{{ route('customer.order_returns') }}">Returns orders</a></li>
                                <li><a href="{{ url('orders/tracking') }}">Order Tracking</a></li>
                                <li><a href="{{ url('customer/affiliate') }}">Affiliate</a></li>
                                <li><a href="{{ url('customer/become-vendor') }}">Become Vendor</a></li>
                                <li><a href="{{ route('public.wishlist') }}">Wish list</a></li>
                                <li class="divider"></li>
                                <li>
                                    <a class="color-primary" href="{{ route('customer.logout') }}">{{ __('Logout') }}</a>
                                </li>
                            @else
                                <li><span class="account-welcome-text">Welcome back.</span></li>
                                <li><a href="{{ route('customer.login') }}">My Account</a></li>
                                <li><a href="{{ route('customer.login') }}">Orders and invoices</a></li>
                                <li><a href="{{ route('customer.login') }}">Returns Orders</a></li>
                                <li><a href="{{ url('orders/tracking') }}">Order Tracking</a></li>
                                <li><a href="{{ route('customer.login') }}">Affiliate</a></li>
                                <li><a href="{{ route('customer.login') }}">Become Vendor</a></li>
                                <li><a href="{{ route('customer.login') }}">Wish list</a></li>
                                <li>
                                    <a href="{{ route('customer.login') }}" class="account-signin-btn">
                                        Sign in
                                    </a>
                                </li>
                                <li class="account-register-row">
                                    Don't have an account yet?
                                    <a href="{{ route('customer.register') }}" class="account-register-link">Start here</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <!-- Wishlist -->
                    @if (is_plugin_active('ecommerce') && EcommerceHelper::isWishlistEnabled())
                        <div class="header__extra header-wishlist">
                            <a class="btn-wishlist" href="{{ route('public.wishlist') }}">
                                <span class="svg-icon">
                                    <svg>
                                        <use href="#svg-icon-wishlist" xlink:href="#svg-icon-wishlist"></use>
                                    </svg>
                                </span>
                                <span class="header-item-counter">
                                    {{ auth('customer')->check()? auth('customer')->user()->wishlist()->count(): Cart::instance('wishlist')->count() }}
                                </span>
                            </a>
                        </div>
                    @endif
                    <!-- Cart -->
                    @if (is_plugin_active('ecommerce') && EcommerceHelper::isCartEnabled())
                        <div class="header__extra cart--mini" role="button" tabindex="0">
                            <a class="btn-shopping-cart" href="{{ route('public.cart') }}">
                                <span class="svg-icon">
                                    <svg>
                                        <use href="#svg-icon-cart" xlink:href="#svg-icon-cart"></use>
                                    </svg>
                                </span>
                                <span class="header-item-counter">{{ Cart::instance('cart')->count() }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <!-- MOBILE HEADER BOTTOM: Toggle & Quick Search -->
            <div class="header-mobile-bottom" style="display: flex; align-items: center; padding: 8px 0 8px 0; background: #fff; border-bottom: 1px solid #f0f0f0; width: 100%;">
                <!-- Toggle Icon -->
                <div class="menu-mobile" style="flex:0 0 40px; max-width:40px; min-width:40px; display:flex; align-items:center; justify-content:center;">
                    <a href="#navigation-mobile" class="icon menu-icon toggle--sidebar mobile-menu-toggle" data-target="#navigation-mobile">
                        <span class="svg-icon">
                            <svg>
                                <use href="#svg-icon-list" xlink:href="#svg-icon-list"></use>
                            </svg>
                        </span>
                    </a>
                </div>
                <!-- Quick Search -->
                @if (is_plugin_active('ecommerce'))
                    <div class="mobile-quick-search-wrapper" style="flex:1 1 0; display:flex; align-items:center; padding-left:8px;">
                        <x-plugins-ecommerce::fronts.ajax-search class="form--quick-search-mobile" style="display: flex; width:100%;">
                            <x-plugins-ecommerce::fronts.ajax-search.input type="text" class="form-control input-search-product" style="flex:1; min-width:0; font-size:14px; height:40px; border-radius:8px 0 0 8px; border:1px solid #eee;" placeholder="{{ __('Search products...') }}" />
                            <button class="btn" type="submit" aria-label="Submit" style="margin-left: 0; height:40px; border-radius:0 8px 8px 0;  color:#fff; min-width:40px; display:flex; align-items:center; justify-content:center;">
                                <span class="svg-icon">
                                    <svg>
                                        <use href="#svg-icon-search" xlink:href="#svg-icon-search"></use>
                                    </svg>
                                </span>
                            </button>
                        </x-plugins-ecommerce::fronts.ajax-search>
                    </div>
                @endif
            </div>
        </div>
    </header>

    {{-- Advanced Mobile Menu System --}}
    {!! Theme::partial('mobile-menu-advanced') !!}
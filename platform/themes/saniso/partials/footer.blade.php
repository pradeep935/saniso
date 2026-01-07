<footer id="footer">
    <!-- mobile menu gradient moved to the mobile menu partial as requested -->
        @if ($preFooterSidebar = dynamic_sidebar('pre_footer_sidebar'))
            <div class="footer-info border-top">
                <div class="container-xxxl py-3">
                    {!! $preFooterSidebar !!}
                </div>
            </div>
        @endif
        @if ($footerSidebar = dynamic_sidebar('footer_sidebar'))
            <div class="footer-widgets">
                <div class="container-xxxl">
                    <div class="row border-top py-5 footer-accordion-wrapper">
                        {!! $footerSidebar !!}
                    </div>
                </div>
            </div>
        @endif
        @if ($bottomFooterSidebar = dynamic_sidebar('bottom_footer_sidebar'))
            <div class="container-xxxl">
                <div
                    class="footer__links"
                    id="footer-links"
                >
                    {!! $bottomFooterSidebar !!}
                </div>
            </div>
        @endif
        
        <!-- Category Carousel - SECOND on mobile, BEFORE main container on desktop -->
        @if (is_plugin_active('ecommerce'))
            {{-- Footer Product Categories Widget Area --}}
            @php
                $footerCategoriesSidebar = dynamic_sidebar('footer_categories_sidebar');
            @endphp
            
            @if ($footerCategoriesSidebar)
                <div class="category-section d-none d-md-block">
                    {!! $footerCategoriesSidebar !!}
                </div>
            @else
                {{-- Fallback to existing carousel if no widget is configured --}}
                <div class="category-section d-none d-md-block">
                    {!! Theme::partial('footer-category-carousel') !!}
                </div>
            @endif
        @endif
        
        <div class="container-xxxl">
            <div class="row border-top py-4">
                <!-- Payment Methods - FIRST on mobile, SECOND on desktop -->
                <div class="col-12 col-md-4 col-lg-6 py-2 order-1 order-md-2">
                    @if (theme_option('payment_methods_image'))
                        <div class="footer-payments d-flex justify-content-center">
                            @if (theme_option('payment_methods_link'))
                                <a
                                    href="{{ url(theme_option('payment_methods_link')) }}"
                                    target="_blank"
                                >
                            @endif

                            <img
                                class="lazyload"
                                data-src="{{ RvMedia::getImageUrl(theme_option('payment_methods_image')) }}"
                                alt="footer-payments"
                                style="max-height: 40px; width: auto;"
                            >

                            @if (theme_option('payment_methods_link'))
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Category Carousel - SECOND on mobile, hidden on desktop -->
                @if (is_plugin_active('ecommerce'))
                    @if ($footerCategoriesSidebar)
                        <div class="col-12 py-2 order-2 d-md-none">
                            {!! $footerCategoriesSidebar !!}
                        </div>
                    @else
                        {{-- Fallback to existing carousel if no widget is configured --}}
                        <div class="col-12 py-2 order-2 d-md-none">
                            {!! Theme::partial('footer-category-carousel') !!}
                        </div>
                    @endif
                @endif
                
                <!-- Social Links - THIRD on mobile, THIRD on desktop -->
                <div class="col-12 col-md-4 col-lg-3 py-2 order-3 order-md-3">
                    <div class="footer-socials d-flex justify-content-center">
                        @if ($socialLinks = Theme::getSocialLinks())
                            <div class="d-flex align-items-center flex-wrap justify-content-center">
                                <p class="me-3 mb-0 d-none d-md-block">{{ __('Stay connected:') }}</p>
                                <div class="footer-socials-container">
                                    <ul class="ps-0 mb-0 d-flex justify-content-center">
                                        @foreach($socialLinks as $socialLink)
                                            @continue(! $socialLink->getUrl() || ! $socialLink->getIconHtml())

                                            <li class="d-inline-block ps-1 my-1 mx-1">
                                                <a {!! $socialLink->getAttributes() !!}>{{ $socialLink->getIconHtml() }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Copyright Section - FOURTH on mobile, FIRST on desktop -->
                <div class="col-12 col-md-4 col-lg-3 py-2 order-4 order-md-1">
                    <div class="copyright d-flex justify-content-center justify-content-md-start">
                        <span>{!! Theme::getSiteCopyright() !!}</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    @if (is_plugin_active('ecommerce'))
        <div
            class="panel--sidebar"
            id="navigation-mobile"
        >
            <div class="panel__header">
                <span class="svg-icon close-toggle--sidebar">
                    <svg>
                        <use
                            href="#svg-icon-arrow-left"
                            xlink:href="#svg-icon-arrow-left"
                        ></use>
                    </svg>
                </span>
                <h3>{{ __('Categories') }}</h3>
            </div>
            <div
                class="panel__content"
                data-bb-toggle="init-categories-dropdown"
                data-bb-target=".product-category-dropdown-wrapper"
                data-url="{{ route('public.ajax.categories-dropdown') }}"
            >
                <ul class="menu--mobile product-category-dropdown-wrapper"></ul>
            </div>
        </div>
    @endif

    <div
        class="panel--sidebar"
        id="menu-mobile"
    >
        <div class="panel__header">
            <span class="svg-icon close-toggle--sidebar">
                <svg>
                    <use
                        href="#svg-icon-arrow-left"
                        xlink:href="#svg-icon-arrow-left"
                    ></use>
                </svg>
            </span>
            <h3>{{ __('Menu') }}</h3>
        </div>
        <div class="panel__content">
            {!! Menu::renderMenuLocation('main-menu', [
                'view' => 'menu',
                'options' => ['class' => 'menu--mobile'],
            ]) !!}

            {!! Menu::renderMenuLocation('header-navigation', [
                'view' => 'menu',
                'options' => ['class' => 'menu--mobile'],
            ]) !!}

            <ul class="menu--mobile">

                @if (is_plugin_active('ecommerce'))
                    @if (EcommerceHelper::isCompareEnabled())
                        <li><a href="{{ route('public.compare') }}"><span>{{ __('Compare') }}</span></a></li>
                    @endif

                    @if (count($currencies) > 1)
                        <li class="menu-item-has-children">
                            <a href="#">
                                <span>{{ get_application_currency()->title }}</span>
                                <span class="sub-toggle">
                                    <span class="svg-icon">
                                        <svg>
                                            <use
                                                href="#svg-icon-chevron-down"
                                                xlink:href="#svg-icon-chevron-down"
                                            ></use>
                                        </svg>
                                    </span>
                                </span>
                            </a>
                            <ul class="sub-menu">
                                @foreach ($currencies as $currency)
                                    @if ($currency->id !== get_application_currency_id())
                                        <li><a
                                                href="{{ route('public.change-currency', $currency->title) }}"><span>{{ $currency->title }}</span></a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endif
                @if (is_plugin_active('language'))
                    @php
                        $supportedLocales = Language::getSupportedLocales();
                    @endphp

                    @if ($supportedLocales && count($supportedLocales) > 1)
                        @php
                            $languageDisplay = setting('language_display', 'all');
                        @endphp
                        <li class="menu-item-has-children">
                            <a href="#">
                                @if ($languageDisplay == 'all' || $languageDisplay == 'flag')
                                    {!! language_flag(Language::getCurrentLocaleFlag(), Language::getCurrentLocaleName()) !!}
                                @endif
                                @if ($languageDisplay == 'all' || $languageDisplay == 'name')
                                    {{ Language::getCurrentLocaleName() }}
                                @endif
                                <span class="sub-toggle">
                                    <span class="svg-icon">
                                        <svg>
                                            <use
                                                href="#svg-icon-chevron-down"
                                                xlink:href="#svg-icon-chevron-down"
                                            ></use>
                                        </svg>
                                    </span>
                                </span>
                            </a>
                            <ul class="sub-menu">
                                @foreach ($supportedLocales as $localeCode => $properties)
                                    @if ($localeCode != Language::getCurrentLocale())
                                        <li>
                                            <a
                                                href="{{ Language::getSwitcherUrl($localeCode, $properties['lang_code']) }}">
                                                @if ($languageDisplay == 'all' || $languageDisplay == 'flag')
                                                    {!! language_flag($properties['lang_flag'], $properties['lang_name']) !!}
                                                @endif
                                                @if ($languageDisplay == 'all' || $languageDisplay == 'name')
                                                    <span>{{ $properties['lang_name'] }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
    <div
        class="panel--sidebar panel--sidebar__right"
        id="search-mobile"
    >
        <div class="panel__header">
            @if (is_plugin_active('ecommerce'))
                <x-plugins-ecommerce::fronts.ajax-search class="form--quick-search bb-form-quick-search w-100">
                    <div class="search-inner-content">
                        <div class="text-search">
                            <div class="search-wrapper">
                                <x-plugins-ecommerce::fronts.ajax-search.input type="text" class="search-field input-search-product" />
                                <button
                                    class="btn"
                                    type="submit"
                                    aria-label="Submit"
                                >
                                    <span class="svg-icon">
                                        <svg>
                                            <use
                                                href="#svg-icon-search"
                                                xlink:href="#svg-icon-search"
                                            ></use>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <a
                                class="close-search-panel close-toggle--sidebar"
                                href="#"
                                aria-label="Search"
                            >
                                <span class="svg-icon">
                                    <svg>
                                        <use
                                            href="#svg-icon-times"
                                            xlink:href="#svg-icon-times"
                                        ></use>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </x-plugins-ecommerce::fronts.ajax-search>
            @endif
        </div>
    </div>

    <!-- 
    <div class="footer-mobile">
        <ul class="menu--footer">
            <li>
                <a href="{{ BaseHelper::getHomepageUrl() }}">
                    <i class="icon-home3"></i>
                    <span>{{ __('Home') }}</span>
                </a>
            </li>
            @if (is_plugin_active('ecommerce'))
                <li>
                    <a
                        class="toggle--sidebar"
                        href="#navigation-mobile"
                    >
                        <i class="icon-list"></i>
                        <span>{{ __('Category') }}</span>
                    </a>
                </li>
                @if (EcommerceHelper::isCartEnabled())
                    <li>
                        <a
                            class="toggle--sidebar"
                            href="#cart-mobile"
                        >
                            <i class="icon-cart">
                                <span class="cart-counter">{{ Cart::instance('cart')->count() }}</span>
                            </i>
                            <span>{{ __('Cart') }}</span>
                        </a>
                    </li>
                @endif
                @if (EcommerceHelper::isWishlistEnabled())
                    <li>
                        <a href="{{ route('public.wishlist') }}">
                            <i class="icon-heart"></i>
                            <span>{{ __('Wishlist') }}</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('customer.overview') }}">
                        <i class="icon-user"></i>
                        <span>{{ __('Account') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
    <!-- ===== End Footer Mobile Menu ===== -->
    @if (is_plugin_active('ecommerce'))
        {!! Theme::partial('ecommerce.quick-view-modal') !!}
    @endif
    {!! Theme::partial('toast') !!}

    <div class="panel-overlay-layer"></div>
    <div id="back2top">
        <span class="svg-icon">
            <svg>
                <use
                    href="#svg-icon-arrow-up"
                    xlink:href="#svg-icon-arrow-up"
                ></use>
            </svg>
        </span>
    </div>

    <script>
        'use strict';

        window.trans = {
            "View All": "{{ __('View All') }}",
            "No reviews!": "{{ __('No reviews!') }}"
        };

        window.siteConfig = {
            "url": "{{ BaseHelper::getHomepageUrl() }}",
            "img_placeholder": "{{ theme_option('lazy_load_image_enabled', 'yes') == 'yes' ? image_placeholder() : null }}",
            "countdown_text": {
                "days": "{{ __('days') }}",
                "hours": "{{ __('hours') }}",
                "minutes": "{{ __('mins') }}",
                "seconds": "{{ __('secs') }}"
            }
        };

        @if (is_plugin_active('ecommerce') && EcommerceHelper::isCartEnabled())
            window.siteConfig.ajaxCart = "{{ route('public.ajax.cart') }}";
            window.siteConfig.cartUrl = "{{ route('public.cart') }}";
        @endif
    </script>

    {!! Theme::footer() !!}

    {{-- Category filter functionality is now embedded in categories.blade.php template --}}

    {{-- Additional tooltip disabling for dynamic content --}}
    <script>
        // Additional protection against tooltips in dynamic content
        document.addEventListener('DOMContentLoaded', function() {
            // Disable any remaining Bootstrap tooltip initialization
            if (typeof bootstrap !== 'undefined') {
                const originalTooltip = bootstrap.Tooltip;
                if (originalTooltip) {
                    bootstrap.Tooltip = function() {
                        return {
                            show: function() {},
                            hide: function() {},
                            toggle: function() {},
                            dispose: function() {},
                            enable: function() {},
                            disable: function() {},
                            toggleEnabled: function() {},
                            update: function() {}
                        };
                    };
                }
            }
            
            // Remove title attributes periodically
            setInterval(function() {
                const elementsWithTitle = document.querySelectorAll('[title]');
                elementsWithTitle.forEach(function(element) {
                    const title = element.getAttribute('title');
                    if (title) {
                        element.setAttribute('data-original-title', title);
                        element.removeAttribute('title');
                    }
                });
            }, 3000);
        });
    </script>

    <style>
        /* Footer Widgets Background and Text Color */
        .footer-widgets {
            background-color: #0090e3;
            color: white;
        }
        
        .footer-widgets * {
            color: white !important;
        }
        
        .footer-widgets a {
            color: white !important;
            text-decoration: none;
        }
        
        .footer-widgets a:hover {
            color: #f0f0f0 !important;
            text-decoration: underline;
        }
        
        .footer-widgets .widget-title {
            color: white !important;
        }
        
        .footer-widgets input[type="email"] {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .footer-widgets input[type="email"]::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .footer-widgets .btn {
            background-color: rgba(18,55,121, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .footer-widgets .btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        /* Category Slider Widget in Footer */
        .footer-category-carousel {
            background-color: #f8f9fa;
            padding: 30px 0;
            margin-bottom: 0;
        }
        
        .footer-category-carousel .container-xxxl {
            padding: 0 15px;
        }
        
        .footer-category-carousel h3,
        .footer-category-carousel .widget-title {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
        }
        
        /* Mobile Footer Category Carousel - Hide Partial Slides */
        @media (max-width: 767px) {
            .widget-product-categories .slick-list {
                overflow: hidden !important;
                padding: 0 !important;
            }
            
            .widget-product-categories .slick-slide {
                width: 50% !important;
                max-width: 50% !important;
                min-width: 50% !important;
                padding: 0 5px;
                box-sizing: border-box;
            }
            
            .widget-product-categories .slick-track {
                display: flex !important;
                width: auto !important;
            }
            
            .widget-product-categories .product-category-item {
                width: 100% !important;
                padding: 8px !important;
                box-sizing: border-box;
            }
            
            /* Ensure no partial slides are visible */
            .widget-product-categories .slick-slider {
                margin: 0;
                padding: 0;
            }
            
            .widget-product-categories .slick-list .slick-track {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }
        
        /* Mobile Footer Order Control - using Bootstrap flex utilities */
        @media (max-width: 767px) {
            /* Ensure proper flex behavior on mobile */
            .container-xxxl .row.border-top {
                display: flex !important;
                flex-direction: column !important;
            }
            
            /* Ensure mobile spacing */
            .container-xxxl .row.border-top > div {
                margin-bottom: 15px;
            }
        }
        
        /* Footer Accordion Styles for Mobile */
        @media (max-width: 991px) {
            .footer-accordion-wrapper .widget:has(ul, .menu, nav) {
                border-bottom: 1px solid #e9ecef;
                margin-bottom: 0;
            }
            .mb-5 {
                margin-bottom: 0.2rem !important;
            }
            .footer-accordion-wrapper .widget:has(ul, .menu, nav):last-child {
                border-bottom: none;
            }
            
            .footer-accordion-wrapper .footer-accordion-title {
                position: relative;
                cursor: pointer;
                padding: 5px 0;
                margin-bottom: 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: all 0.3s ease;
            }
            
            .footer-accordion-wrapper .footer-accordion-title:after {
                content: '▼';
                font-size: 14px;
                transition: transform 0.3s ease;
                color: white !important;
            }
            
            .footer-accordion-wrapper .footer-accordion-title.active:after {
                content: '▲';
                transform: rotate(0deg);
                color: white !important;
            }
            
            .footer-accordion-wrapper .footer-accordion-content {
                display: none;
                padding-bottom: 15px;
                animation: slideDown 0.3s ease;
            }
            
            .footer-accordion-wrapper .footer-accordion-content.active {
                display: block;
            }
            
            /* Don't apply accordion to logo, newsletter, or text widgets */
            .footer-accordion-wrapper .widget:has(img):not(:has(ul, .menu, nav)),
            .footer-accordion-wrapper .widget:has(input[type="email"]),
            .footer-accordion-wrapper .widget:has(.newsletter),
            .footer-accordion-wrapper .widget:has(.logo),
            .footer-accordion-wrapper .widget:not(:has(ul, .menu, nav, a)) {
                border-bottom: none;
            }
            
            .footer-accordion-wrapper .widget:has(img):not(:has(ul, .menu, nav)) .widget-title,
            .footer-accordion-wrapper .widget:has(input[type="email"]) .widget-title,
            .footer-accordion-wrapper .widget:has(.newsletter) .widget-title,
            .footer-accordion-wrapper .widget:has(.logo) .widget-title,
            .footer-accordion-wrapper .widget:not(:has(ul, .menu, nav, a)) .widget-title:after {
                display: none;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    max-height: 0;
                }
                to {
                    opacity: 1;
                    max-height: 200px;
                }
            }
        }
        
        /* Keep normal layout on desktop */
        @media (min-width: 992px) {
            .footer-accordion-wrapper .footer-accordion-content {
                display: block !important;
            }
        }
        
        /* Footer Links Accordion Styles for Mobile */
        @media (max-width: 991px) {
            #footer-links {
                margin-top: 20px;
            }
            
            #footer-links div {
                margin-bottom: 15px;
            }
            
            #footer-links strong {
                font-weight: bold !important;
                cursor: pointer !important;
                user-select: none;
                transition: all 0.3s ease;
            }
            
            #footer-links strong:hover {
                color: #007bff;
            }
            
            #footer-links a {
                text-decoration: none;
                color: #666;
                transition: color 0.3s ease;
                font-size: 14px;
                padding: 5px 0;
                border-bottom: 1px solid #f0f0f0;
                margin-left: 10px;
            }
            
            #footer-links a.accordion-hidden {
                display: none !important;
            }
            
            #footer-links a.accordion-visible {
                display: block !important;
            }
            
            #footer-links a:hover {
                color: #007bff;
                text-decoration: underline;
            }
        }
        
        /* Keep normal layout on desktop for footer links */
        @media (min-width: 992px) {
            #footer-links a {
                display: inline !important;
                margin-right: 15px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Footer links accordion functionality - separate from main widget accordion
            function initFooterLinksAccordion() {
                if (window.innerWidth <= 991) {
                    const footerLinksContainer = document.querySelector('#footer-links');
                    if (footerLinksContainer) {
                        const strongTags = footerLinksContainer.querySelectorAll('strong');
                        
                        strongTags.forEach(function(strong) {
                            // Skip if already processed
                            if (strong.querySelector('span')) return;
                            
                            const paragraph = strong.parentElement;
                            const links = paragraph.querySelectorAll('a');
                            
                            if (links.length > 0) {
                                // Style the strong tag as clickable
                                strong.style.cursor = 'pointer';
                                strong.style.display = 'flex';
                                strong.style.justifyContent = 'space-between';
                                strong.style.alignItems = 'center';
                                strong.style.padding = '10px 0';
                                strong.style.borderBottom = '1px solid #e9ecef';
                                strong.style.marginBottom = '0';
                                
                                // Add arrow indicator
                                const arrow = document.createElement('span');
                                arrow.innerHTML = '▼';
                                arrow.style.fontSize = '12px';
                                arrow.style.color = '#666';
                                arrow.style.transition = 'transform 0.3s ease';
                                strong.appendChild(arrow);
                                
                                // Hide links initially using CSS class
                                links.forEach(function(link) {
                                    link.classList.add('accordion-hidden');
                                    link.classList.remove('accordion-visible');
                                });
                                
                                // Add click event
                                strong.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const isOpen = links[0].classList.contains('accordion-visible');
                                    
                                    if (isOpen) {
                                        // Close
                                        links.forEach(function(link) {
                                            link.classList.add('accordion-hidden');
                                            link.classList.remove('accordion-visible');
                                        });
                                        arrow.innerHTML = '▼';
                                    } else {
                                        // Open
                                        links.forEach(function(link) {
                                            link.classList.add('accordion-visible');
                                            link.classList.remove('accordion-hidden');
                                        });
                                        arrow.innerHTML = '▲';
                                    }
                                });
                            }
                        });
                    }
                }
            }
            
            // Footer accordion functionality for mobile
            if (window.innerWidth <= 991) {
                const footerWidgets = document.querySelectorAll('.footer-accordion-wrapper .widget');
                
                footerWidgets.forEach(function(widget) {
                    const title = widget.querySelector('.widget-title, h3, h4, h5, h6');
                    const content = widget.querySelector('ul, .menu, nav, .form-widget, form, input');
                    
                    // Only apply accordion to widgets with content
                    if (title && content) {
                        // Check widget title text to determine if it should have accordion
                        const titleText = title.textContent.trim().toLowerCase();
                        
                        // Skip accordion ONLY for company info (Saniso B.V.)
                        if (titleText.includes('saniso')) {
                            return;
                        }
                        
                        // Apply accordion to ALL other sections
                        // Add accordion classes
                        title.classList.add('footer-accordion-title');
                        
                        // Find the actual content container
                        let contentContainer = widget.querySelector('ul, .menu, nav, .form-widget, .widget-description, form');
                        if (!contentContainer) {
                            // If no specific container found, use the next sibling after title
                            contentContainer = title.nextElementSibling;
                        }
                        
                        if (contentContainer) {
                            contentContainer.classList.add('footer-accordion-content');
                            
                            // Hide content initially
                            contentContainer.style.display = 'none';
                            
                            // Add click event
                            title.addEventListener('click', function() {
                                const isActive = contentContainer.style.display === 'block';
                                
                                if (isActive) {
                                    contentContainer.style.display = 'none';
                                    title.classList.remove('active');
                                } else {
                                    contentContainer.style.display = 'block';
                                    title.classList.add('active');
                                }
                            });
                        }
                    }
                });
            }
            
            // Initialize footer links accordion
            initFooterLinksAccordion();
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const footerContents = document.querySelectorAll('.footer-accordion-wrapper .footer-accordion-content');
            
            if (window.innerWidth > 991) {
                // Show all content on desktop
                footerContents.forEach(function(content) {
                    content.style.display = 'block';
                });
                
                // Reset footer links to normal layout
                const footerLinks = document.querySelectorAll('#footer-links a');
                footerLinks.forEach(function(link) {
                    link.classList.remove('accordion-hidden', 'accordion-visible');
                    link.style.display = 'inline';
                    link.style.marginRight = '15px';
                });
            } else {
                // Hide content on mobile (will be controlled by accordion)
                footerContents.forEach(function(content) {
                    if (!content.parentElement.querySelector('.footer-accordion-title.active')) {
                        content.style.display = 'none';
                    }
                });
                
                // Reinitialize footer links accordion for mobile
                setTimeout(function() {
                    if (window.innerWidth <= 991) {
                        const footerLinksContainer = document.querySelector('#footer-links');
                        if (footerLinksContainer) {
                            const strongTags = footerLinksContainer.querySelectorAll('strong');
                            
                            strongTags.forEach(function(strong) {
                                // Skip if already processed
                                if (strong.querySelector('span')) return;
                                
                                const paragraph = strong.parentElement;
                                const links = paragraph.querySelectorAll('a');
                                
                                if (links.length > 0) {
                                    // Style the strong tag as clickable
                                    strong.style.cursor = 'pointer';
                                    strong.style.display = 'flex';
                                    strong.style.justifyContent = 'space-between';
                                    strong.style.alignItems = 'center';
                                    strong.style.padding = '10px 0';
                                    strong.style.borderBottom = '1px solid #e9ecef';
                                    strong.style.marginBottom = '0';
                                    
                                    // Add arrow indicator
                                    const arrow = document.createElement('span');
                                    arrow.innerHTML = '▼';
                                    arrow.style.fontSize = '12px';
                                    arrow.style.color = '#666';
                                    arrow.style.transition = 'transform 0.3s ease';
                                    strong.appendChild(arrow);
                                    
                                    // Hide links initially using CSS class
                                    links.forEach(function(link) {
                                        link.classList.add('accordion-hidden');
                                        link.classList.remove('accordion-visible');
                                    });
                                    
                                    // Add click event
                                    strong.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        const isOpen = links[0].classList.contains('accordion-visible');
                                        
                                        if (isOpen) {
                                            // Close
                                            links.forEach(function(link) {
                                                link.classList.add('accordion-hidden');
                                                link.classList.remove('accordion-visible');
                                            });
                                            arrow.innerHTML = '▼';
                                        } else {
                                            // Open
                                            links.forEach(function(link) {
                                                link.classList.add('accordion-visible');
                                                link.classList.remove('accordion-hidden');
                                            });
                                            arrow.innerHTML = '▲';
                                        }
                                    });
                                }
                            });
                        }
                    }
                }, 100);
            }
        });
        
        // Fix mobile carousel alignment and prevent partial slides
        function fixMobileCarouselAlignment() {
            if (window.innerWidth <= 767) {
                const carousels = document.querySelectorAll('.widget-product-categories .slick-slider, .footer-category-carousel .slick-slider');
                
                carousels.forEach(function(carousel) {
                    const track = carousel.querySelector('.slick-track');
                    const slides = carousel.querySelectorAll('.slick-slide');
                    
                    if (track && slides.length > 0) {
                        // Force exact 50% width on each slide
                        slides.forEach(function(slide) {
                            slide.style.width = '50%';
                            slide.style.maxWidth = '50%';
                            slide.style.minWidth = '50%';
                            slide.style.flex = '0 0 50%';
                            slide.style.margin = '0';
                            slide.style.padding = '0 5px';
                            slide.style.boxSizing = 'border-box';
                        });
                        
                        // Ensure track positioning is correct
                        track.style.display = 'flex';
                        track.style.width = 'auto';
                        track.style.margin = '0';
                        track.style.padding = '0';
                        
                        // Force list container to hide overflow
                        const list = carousel.querySelector('.slick-list');
                        if (list) {
                            list.style.overflow = 'hidden';
                            list.style.padding = '0';
                            list.style.margin = '0';
                        }
                    }
                });
            }
        }
        
        // Run on carousel initialization (mobile only, and only if slick exists)
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 767 && typeof $.fn.slick === 'function') {
                requestAnimationFrame(fixMobileCarouselAlignment);
            }
        });
        
        // Throttled window resize for alignment (mobile only)
        let resizeTimer;
        window.addEventListener('resize', function() {
            if (window.innerWidth > 767) return;
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                requestAnimationFrame(fixMobileCarouselAlignment);
            }, 120);
        });
        
        // Run after slick initialization (mobile only)
        $(document).on('init', '.slick-slider', function() {
            if (window.innerWidth <= 767) {
                requestAnimationFrame(fixMobileCarouselAlignment);
            }
        });
        
        // Run after slick slides change (mobile only)
        $(document).on('afterChange', '.slick-slider', function() {
            if (window.innerWidth <= 767) {
                requestAnimationFrame(fixMobileCarouselAlignment);
            }
        });
    </script>

    @include('packages/theme::toast-notification')
    </body>

    </html>
